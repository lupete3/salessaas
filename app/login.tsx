import React, { useState } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { BlurView } from 'expo-blur';
import { StatusBar } from 'expo-status-bar';
import { useRouter } from 'expo-router';

import { SyncService } from '../services/SyncService';
import { useLangStore } from '../store/langStore';

export default function LoginScreen() {
  const { t } = useLangStore();
  const [apiUrl, setApiUrl] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  const handleLogin = async () => {
    if (!apiUrl.trim() || !email.trim() || !password) {
      Alert.alert(t('auth.required_fields'), t('auth.fill_all'));
      return;
    }

    setLoading(true);
    const errorMsg = await SyncService.login(apiUrl, email, password);
    setLoading(false);

    if (errorMsg) {
      Alert.alert(t('auth.login_error'), errorMsg);
    } else {
      // Sync initial data (products, customers) after successful login
      SyncService.pullData().catch(() => {});
      router.replace('/(tabs)');
    }
  };

  return (
    <View style={styles.container}>
      <StatusBar style="light" />
      <LinearGradient
        colors={['#064e3b', '#022c22', '#000000']}
        style={styles.background}
      />

      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.keyboardView}
      >
        <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
          {/* Logo */}
          <View style={styles.logoContainer}>
            <View style={styles.logoCircle}>
              <Text style={styles.logoEmoji}>📦</Text>
            </View>
            <Text style={styles.title}>SalesSaaS</Text>
            <Text style={styles.subtitle}>{t('auth.subtitle')}</Text>
          </View>

          {/* Card */}
          <View style={styles.cardContainer}>
            <BlurView intensity={25} tint="dark" style={styles.card}>

              {/* API URL */}
              <Text style={styles.label}>{t('auth.server_url')}</Text>
              <TextInput
                style={styles.input}
                placeholder={t('auth.url_placeholder')}
                placeholderTextColor="#888"
                value={apiUrl}
                onChangeText={setApiUrl}
                autoCapitalize="none"
                keyboardType="url"
                returnKeyType="next"
                testID="input-api-url"
              />

              {/* Email */}
              <Text style={styles.label}>{t('auth.email')}</Text>
              <TextInput
                style={styles.input}
                placeholder={t('auth.email_placeholder')}
                placeholderTextColor="#888"
                value={email}
                onChangeText={setEmail}
                autoCapitalize="none"
                keyboardType="email-address"
                returnKeyType="next"
                testID="input-email"
              />

              {/* Password */}
              <Text style={styles.label}>{t('auth.password')}</Text>
              <TextInput
                style={styles.input}
                placeholder="••••••••"
                placeholderTextColor="#888"
                value={password}
                onChangeText={setPassword}
                secureTextEntry
                returnKeyType="done"
                onSubmitEditing={handleLogin}
                testID="input-password"
              />

              {/* Submit */}
              <TouchableOpacity
                style={[styles.button, loading && styles.buttonDisabled]}
                onPress={handleLogin}
                disabled={loading}
                testID="btn-login"
              >
                {loading ? (
                  <ActivityIndicator color="#fff" />
                ) : (
                  <Text style={styles.buttonText}>{t('auth.login_btn')}</Text>
                )}
              </TouchableOpacity>

              <Text style={styles.hint}>
                {t('auth.hint')}
              </Text>
            </BlurView>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  background: { position: 'absolute', left: 0, right: 0, top: 0, bottom: 0 },
  keyboardView: { flex: 1 },
  scroll: { flexGrow: 1, justifyContent: 'center', padding: 20 },
  logoContainer: { alignItems: 'center', marginBottom: 36 },
  logoCircle: {
    width: 88,
    height: 88,
    borderRadius: 44,
    backgroundColor: '#10b981',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 14,
    shadowColor: '#10b981',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.5,
    shadowRadius: 12,
    elevation: 12,
  },
  logoEmoji: { fontSize: 44 },
  title: { fontSize: 30, fontWeight: 'bold', color: '#fff', letterSpacing: 1 },
  subtitle: { fontSize: 14, color: '#aaa', marginTop: 6 },
  cardContainer: {
    borderRadius: 20,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.12)',
  },
  card: { padding: 24, backgroundColor: 'rgba(20,30,50,0.5)' },
  label: { color: '#ddd', marginBottom: 6, fontSize: 13, fontWeight: '600', marginLeft: 2 },
  input: {
    backgroundColor: 'rgba(255,255,255,0.08)',
    borderRadius: 12,
    paddingHorizontal: 16,
    paddingVertical: 14,
    color: '#fff',
    fontSize: 15,
    marginBottom: 18,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.15)',
  },
  button: {
    backgroundColor: '#10b981',
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 4,
    shadowColor: '#10b981',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.4,
    shadowRadius: 8,
    elevation: 6,
  },
  buttonDisabled: { backgroundColor: '#555', shadowOpacity: 0 },
  buttonText: { color: '#fff', fontSize: 17, fontWeight: 'bold' },
  hint: { color: '#888', fontSize: 12, textAlign: 'center', marginTop: 18, lineHeight: 18 },
});
