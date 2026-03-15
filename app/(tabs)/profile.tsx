import React, { useState } from 'react';
import {
  View, Text, TouchableOpacity, StyleSheet, Alert,
  ScrollView, ActivityIndicator, SafeAreaView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuthStore } from '../../store/authStore';
import { useAppStore, LocalSale } from '../../store/appStore';
import { SyncService } from '../../services/SyncService';
import { useLangStore } from '../../store/langStore';
import { useRouter } from 'expo-router';
import NetInfo from '@react-native-community/netinfo';

export default function ProfileScreen() {
  const router = useRouter();
  const { user, store, logout } = useAuthStore();
  const { lang, setLang } = useLangStore();
  const { offlineQueue, syncedSales, lastSyncAt } = useAppStore();
  const [syncing, setSyncing] = useState(false);

  const pendingCount = offlineQueue.filter((s) => !s.is_synced).length;

  const handleSync = async () => {
    const netState = await NetInfo.fetch();
    if (!netState.isConnected) {
      Alert.alert('Hors-ligne', 'Pas de connexion internet. Réessayez quand vous êtes connecté.');
      return;
    }
    setSyncing(true);
    try {
      // 1. Push pending changes FIRST
      const { success, error } = await SyncService.pushData();

      // 2. Pull products and customers AFTER pushing
      await SyncService.pullData();
      
      if (success) {
          Alert.alert('✅ Synchronisation terminée', 'Toutes les données sont à jour.');
      } else {
          Alert.alert('⚠️ Synchronisation partielle', error || 'Certaines requêtes ont échoué.');
      }
    } catch (e) {
      Alert.alert('Erreur', 'La synchronisation a échoué. Réessayez.');
    } finally {
      setSyncing(false);
    }
  };

  const handleLogout = () => {
    Alert.alert('Déconnexion', 'Êtes-vous sûr de vouloir vous déconnecter ?', [
      { text: 'Annuler', style: 'cancel' },
      {
        text: 'Déconnecter',
        style: 'destructive',
        onPress: () => SyncService.logout(),
      },
    ]);
  };

  const formatDate = (iso: string | null) => {
    if (!iso) return 'Jamais';
    return new Date(iso).toLocaleString('fr-FR', {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit',
    });
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.content}>

        {/* User Card */}
        <View style={styles.userCard}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>
              {user?.name?.[0]?.toUpperCase() ?? '?'}
            </Text>
          </View>
          <View>
            <Text style={styles.userName}>{user?.name ?? '—'}</Text>
            <Text style={styles.userEmail}>{user?.email ?? '—'}</Text>
            <Text style={styles.userRole}>{user?.role ?? '—'}</Text>
          </View>
        </View>

        {/* Store */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>🏪 Store</Text>
          <Text style={styles.sectionValue}>{store?.name ?? '—'}</Text>
          <Text style={styles.sectionSub}>Devise : {store?.currency ?? 'CDF'}</Text>
        </View>

        {/* Sync Status */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>🔄 Synchronisation</Text>
          <View style={styles.syncStats}>
            <View style={styles.statBox}>
              <Text style={[styles.statNum, pendingCount > 0 && { color: '#f39c12' }]}>
                {pendingCount}
              </Text>
              <Text style={styles.statLabel}>En attente</Text>
            </View>
            <View style={styles.statBox}>
              <Text style={styles.statNum}>{syncedSales.length}</Text>
              <Text style={styles.statLabel}>Synchronisées</Text>
            </View>
          </View>
          <Text style={styles.lastSync}>Dernière sync : {formatDate(lastSyncAt)}</Text>

          <TouchableOpacity
            style={[styles.syncBtn, syncing && styles.syncBtnDisabled]}
            onPress={handleSync}
            disabled={syncing}
          >
            {syncing ? (
              <ActivityIndicator color="#fff" size="small" />
            ) : (
              <>
                <Ionicons name="cloud-upload-outline" size={20} color="#fff" />
                <Text style={styles.syncBtnText}>
                  Synchroniser{pendingCount > 0 ? ` (${pendingCount})` : ''}
                </Text>
              </>
            )}
          </TouchableOpacity>
        </View>

        {/* Recent Sales / History */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>🧾 Historique des ventes</Text>
          {[...offlineQueue, ...syncedSales].slice(-10).reverse().map((s) => {
            const mins = (Date.now() - new Date(s.sold_at).getTime()) / (1000 * 60);
            const canCancel = mins < 20;

            return (
              <View key={s.local_id} style={styles.saleRow}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.saleDate}>{new Date(s.sold_at).toLocaleString('fr-FR')}</Text>
                  <Text style={styles.saleItems}>{s.items.length} art. · {s.payment_method}</Text>
                </View>
                <View style={{ alignItems: 'flex-end' }}>
                  <Text style={styles.saleAmount}>{s.total_amount.toFixed(2)} {store?.currency || 'CDF'}</Text>
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 4 }}>
                    {canCancel && (
                      <TouchableOpacity 
                         onPress={() => {
                          Alert.alert('Annulation', 'Voulez-vous annuler cette vente ?', [
                            { text: 'Non' },
                            { text: 'Annuler la vente', style: 'destructive', onPress: () => {
                                const res = useAppStore.getState().cancelSale(s.local_id);
                                Alert.alert(res.success ? 'Succès' : 'Erreur', res.message);
                            }}
                          ]);
                        }}
                      >
                        <Text style={{ color: '#e74c3c', fontSize: 11, fontWeight: 'bold' }}>ANNULER</Text>
                      </TouchableOpacity>
                    )}
                    <Text style={[styles.saleSyncTag, s.is_synced && styles.saleSyncDone]}>
                      {s.is_synced ? 'Synced' : 'Local'}
                    </Text>
                  </View>
                </View>
              </View>
            );
          })}
          {offlineQueue.length === 0 && syncedSales.length === 0 && (
            <Text style={styles.empty}>Aucune vente enregistrée.</Text>
          )}
        </View>

        {/* Settings Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>PARAMÈTRES</Text>
          <View style={styles.langRow}>
            <Text style={styles.langLabel}>Langue / Language</Text>
            <View style={styles.langButtons}>
              <TouchableOpacity 
                style={[styles.langBtn, lang === 'fr' && styles.langBtnActive]} 
                onPress={() => setLang('fr')}
              >
                <Text style={[styles.langBtnText, lang === 'fr' && styles.langBtnTextActive]}>FR</Text>
              </TouchableOpacity>
              <TouchableOpacity 
                style={[styles.langBtn, lang === 'en' && styles.langBtnActive]} 
                onPress={() => setLang('en')}
              >
                <Text style={[styles.langBtnText, lang === 'en' && styles.langBtnTextActive]}>EN</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>

        <TouchableOpacity style={styles.statsBtn} onPress={() => router.push('/(tabs)/stats')}>
          <Ionicons name="stats-chart" size={20} color="#fff" />
          <Text style={styles.statsBtnText}>Consulter les Statistiques</Text>
        </TouchableOpacity>

        {/* Logout */}
        <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout}>
          <Ionicons name="log-out-outline" size={20} color="#e74c3c" />
          <Text style={styles.logoutText}>Se déconnecter</Text>
        </TouchableOpacity>

      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#0d1117' },
  content: { padding: 16, paddingBottom: 40 },
  userCard: {
    flexDirection: 'row', alignItems: 'center', gap: 16,
    backgroundColor: '#161b22', borderRadius: 14, padding: 16,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)', marginBottom: 16,
  },
  avatar: {
    width: 60, height: 60, borderRadius: 30,
    backgroundColor: '#10b981', alignItems: 'center', justifyContent: 'center',
  },
  avatarText: { color: '#fff', fontWeight: 'bold', fontSize: 26 },
  userName: { color: '#fff', fontWeight: 'bold', fontSize: 18 },
  userEmail: { color: '#888', fontSize: 13, marginTop: 2 },
  userRole: { color: '#10b981', fontSize: 12, marginTop: 3, textTransform: 'capitalize' },
  section: {
    backgroundColor: '#161b22', borderRadius: 14, padding: 16,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)', marginBottom: 12,
  },
  sectionTitle: { color: '#aaa', fontWeight: '700', fontSize: 13, marginBottom: 8, textTransform: 'uppercase', letterSpacing: 0.5 },
  sectionValue: { color: '#fff', fontWeight: 'bold', fontSize: 18 },
  sectionSub: { color: '#888', fontSize: 13, marginTop: 4 },
  syncStats: { flexDirection: 'row', gap: 16, marginBottom: 10 },
  statBox: { flex: 1, backgroundColor: 'rgba(255,255,255,0.04)', borderRadius: 10, padding: 12, alignItems: 'center' },
  statNum: { color: '#fff', fontWeight: 'bold', fontSize: 28 },
  statLabel: { color: '#888', fontSize: 12, marginTop: 4 },
  lastSync: { color: '#666', fontSize: 12, marginBottom: 12 },
  syncBtn: {
    flexDirection: 'row', backgroundColor: '#10b981',
    padding: 14, borderRadius: 12, alignItems: 'center', justifyContent: 'center', gap: 8,
  },
  syncBtnDisabled: { backgroundColor: '#333' },
  syncBtnText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },
  saleRow: {
    flexDirection: 'row', justifyContent: 'space-between',
    borderTopWidth: 1, borderTopColor: 'rgba(255,255,255,0.06)', paddingTop: 8, marginTop: 8,
  },
  saleDate: { color: '#ddd', fontSize: 12 },
  saleItems: { color: '#888', fontSize: 11 },
  saleAmount: { color: '#10b981', fontWeight: 'bold', textAlign: 'right' },
  saleSyncTag: { color: '#f39c12', fontSize: 11, textAlign: 'right' },
  saleSyncDone: { color: '#2ecc71' },
  statsBtn: {
    flexDirection: 'row', backgroundColor: '#3498db',
    padding: 14, borderRadius: 12, alignItems: 'center', justifyContent: 'center', gap: 8,
    marginBottom: 12,
  },
  statsBtnText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },
  empty: { color: '#666', textAlign: 'center', marginTop: 20, fontSize: 14 },
  
  // Lang styles
  langRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 },
  langLabel: { color: '#fff', fontSize: 14 },
  langButtons: { flexDirection: 'row', gap: 8 },
  langBtn: { paddingHorizontal: 16, paddingVertical: 8, borderRadius: 8, backgroundColor: '#161b22', borderWidth: 1, borderColor: '#333' },
  langBtnActive: { backgroundColor: '#10b981', borderColor: '#10b981' },
  langBtnText: { color: '#888', fontWeight: 'bold' },
  langBtnTextActive: { color: '#fff' },
  logoutBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8,
    borderWidth: 1, borderColor: '#e74c3c', borderRadius: 12,
    padding: 14, marginTop: 8,
  },
  logoutText: { color: '#e74c3c', fontWeight: 'bold', fontSize: 16 },
});
