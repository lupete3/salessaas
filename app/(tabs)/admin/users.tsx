import React, { useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, FlatList, SafeAreaView, TouchableOpacity,
  ActivityIndicator, Alert, Modal, TextInput, ScrollView, Switch,
  Platform, StatusBar
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import axios from 'axios';
import { useAuthStore } from '../../../store/authStore';
import { useLangStore } from '../../../store/langStore';

interface Role { id: number; name: string; slug: string; }
interface User {
  id: number; name: string; email: string;
  role: string; is_active: boolean; role_id?: number;
}

export default function UsersScreen() {
  const { t } = useLangStore();
  const { apiUrl, token } = useAuthStore();
  const [users, setUsers] = useState<User[]>([]);
  const [roles, setRoles] = useState<Role[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalVisible, setModalVisible] = useState(false);
  const [editUser, setEditUser] = useState<User | null>(null);

  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [selectedRoleId, setSelectedRoleId] = useState<number | null>(null);
  const [saving, setSaving] = useState(false);

  const headers = { Authorization: `Bearer ${token}` };

  const fetchData = async () => {
    setLoading(true);
    try {
      const [usersRes, rolesRes] = await Promise.all([
        axios.get(`${apiUrl}/api/admin/users`, { headers }),
        axios.get(`${apiUrl}/api/admin/roles`, { headers }),
      ]);
      setUsers(usersRes.data.users);
      setRoles(rolesRes.data.roles);
    } catch {
      Alert.alert(t('shared.error'), t('admin.data_load_error'));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchData(); }, []);

  const handleToggle = async (id: number) => {
    try {
      const res = await axios.patch(`${apiUrl}/api/admin/users/${id}/toggle`, {}, { headers });
      setUsers(prev => prev.map(u => u.id === id ? { ...u, is_active: res.data.is_active } : u));
    } catch {
      Alert.alert(t('shared.error'), t('admin.status_update_error'));
    }
  };

  const openCreate = () => {
    setEditUser(null);
    setName(''); setEmail(''); setPassword(''); setSelectedRoleId(null);
    setModalVisible(true);
  };

  const openEdit = (u: User) => {
    setEditUser(u);
    setName(u.name);
    setEmail(u.email);
    setPassword('');
    setSelectedRoleId(u.role_id ?? null);
    setModalVisible(true);
  };

  const handleSave = async () => {
    if (!name || !email || !selectedRoleId) {
      Alert.alert(t('shared.validation'), t('admin.user_fields_req'));
      return;
    }
    if (!editUser && !password) {
      Alert.alert(t('shared.validation'), t('admin.password_required'));
      return;
    }
    setSaving(true);
    try {
      if (editUser) {
        const payload: any = { name, email, role_id: selectedRoleId };
        if (password) payload.password = password;
        await axios.put(`${apiUrl}/api/admin/users/${editUser.id}`, payload, { headers });
      } else {
        await axios.post(`${apiUrl}/api/admin/users`, { name, email, password, role_id: selectedRoleId }, { headers });
      }
      setModalVisible(false);
      fetchData();
    } catch (e: any) {
      Alert.alert(t('shared.error'), e?.response?.data?.message || t('shared.save_error'));
    } finally {
      setSaving(false);
    }
  };

  const renderItem = ({ item }: { item: User }) => (
    <View style={styles.card}>
      <View style={styles.avatar}>
        <Text style={styles.avatarText}>{item.name.charAt(0).toUpperCase()}</Text>
      </View>
      <View style={styles.info}>
        <Text style={styles.name}>{item.name}</Text>
        <Text style={styles.email}>{item.email}</Text>
        <View style={[styles.roleBadge, { backgroundColor: item.role === 'Propriétaire' || item.role === 'proprietaire' ? 'rgba(16,185,129,0.15)' : 'rgba(99,102,241,0.15)' }]}>
          <Text style={[styles.roleText, { color: item.role === 'Propriétaire' || item.role === 'proprietaire' ? '#10b981' : '#6366f1' }]}>{t('admin.roles.' + (item.role || 'user').toLowerCase())}</Text>
        </View>
      </View>
      <TouchableOpacity onPress={() => openEdit(item)} style={styles.editBtn}>
        <Ionicons name="pencil-outline" size={18} color="#6366f1" />
      </TouchableOpacity>
      <Switch
        value={item.is_active}
        onValueChange={() => handleToggle(item.id)}
        trackColor={{ false: '#333', true: '#10b981' }}
        thumbColor={item.is_active ? '#fff' : '#666'}
        style={{ marginLeft: 8 }}
      />
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>{t('admin.users_title')}</Text>
          <Text style={styles.headerSub}>{t('admin.count_accounts', { count: users.length })}</Text>
        </View>
        <View style={styles.headerActions}>
          <TouchableOpacity onPress={fetchData} disabled={loading} style={styles.iconBtn}>
            <Ionicons name="refresh" size={22} color="#10b981" />
          </TouchableOpacity>
          <TouchableOpacity onPress={openCreate} style={[styles.iconBtn, { marginLeft: 8 }]}>
            <Ionicons name="add-circle" size={22} color="#6366f1" />
          </TouchableOpacity>
        </View>
      </View>

      {loading ? (
        <View style={styles.center}><ActivityIndicator size="large" color="#10b981" /></View>
      ) : (
        <FlatList
          data={users}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.list}
          ListEmptyComponent={<Text style={styles.empty}>{t('admin.no_users_found')}</Text>}
        />
      )}

      <Modal visible={modalVisible} animationType="slide" transparent onRequestClose={() => setModalVisible(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>{editUser ? t('admin.edit_user') : t('admin.new_user')}</Text>
            <TextInput style={styles.input} placeholder={t('admin.full_name_req')} placeholderTextColor="#666" value={name} onChangeText={setName} />
            <TextInput style={styles.input} placeholder={t('shared.email_req')} placeholderTextColor="#666" keyboardType="email-address" value={email} onChangeText={setEmail} autoCapitalize="none" />
            <TextInput style={styles.input} placeholder={editUser ? t('admin.new_password_opt') : t('admin.password_req')} placeholderTextColor="#666" secureTextEntry value={password} onChangeText={setPassword} />
            <Text style={styles.label}>{t('shared.role')} *</Text>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 16 }}>
              {roles.map(r => (
                <TouchableOpacity
                  key={r.id}
                  onPress={() => setSelectedRoleId(r.id)}
                  style={[styles.roleChip, selectedRoleId === r.id && styles.roleChipActive]}>
                  <Text style={[styles.roleChipText, selectedRoleId === r.id && { color: '#fff' }]}>{t('admin.roles.' + (r.slug || 'user').toLowerCase())}</Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
            <View style={styles.modalActions}>
              <TouchableOpacity style={styles.cancelBtn} onPress={() => setModalVisible(false)}>
                <Text style={styles.cancelBtnText}>{t('shared.cancel')}</Text>
              </TouchableOpacity>
              <TouchableOpacity style={styles.saveBtn} onPress={handleSave} disabled={saving}>
                {saving ? <ActivityIndicator color="#fff" /> : <Text style={styles.saveBtnText}>{editUser ? t('shared.modify') : t('shared.create')}</Text>}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1, backgroundColor: '#0d1117',
    paddingTop: Platform.OS === 'android' ? StatusBar.currentHeight : 0,
  },
  header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 20, borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.08)' },
  headerTitle: { color: '#fff', fontSize: 20, fontWeight: 'bold' },
  headerSub: { color: '#666', fontSize: 12, marginTop: 2 },
  headerActions: { flexDirection: 'row' },
  iconBtn: { padding: 4 },
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  list: { padding: 16 },
  card: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#161b22', borderRadius: 12, padding: 14, marginBottom: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)' },
  avatar: { width: 44, height: 44, borderRadius: 22, backgroundColor: 'rgba(99,102,241,0.2)', alignItems: 'center', justifyContent: 'center', marginRight: 12 },
  avatarText: { color: '#6366f1', fontWeight: 'bold', fontSize: 18 },
  info: { flex: 1 },
  name: { color: '#fff', fontWeight: '600', fontSize: 15 },
  email: { color: '#666', fontSize: 12, marginTop: 2 },
  roleBadge: { alignSelf: 'flex-start', borderRadius: 6, paddingHorizontal: 8, paddingVertical: 2, marginTop: 6 },
  roleText: { fontSize: 11, fontWeight: '700' },
  editBtn: { padding: 6 },
  empty: { color: '#666', textAlign: 'center', marginTop: 40 },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.7)', justifyContent: 'flex-end' },
  modalContent: { backgroundColor: '#161b22', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 24 },
  modalTitle: { color: '#fff', fontSize: 18, fontWeight: 'bold', marginBottom: 20 },
  input: { backgroundColor: '#0d1117', color: '#fff', borderRadius: 10, padding: 14, marginBottom: 14, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)', fontSize: 15 },
  label: { color: '#aaa', fontSize: 13, marginBottom: 10 },
  roleChip: { paddingHorizontal: 16, paddingVertical: 8, borderRadius: 20, backgroundColor: 'rgba(255,255,255,0.05)', marginRight: 8, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)' },
  roleChipActive: { backgroundColor: '#6366f1', borderColor: '#6366f1' },
  roleChipText: { color: '#aaa', fontWeight: '600' },
  modalActions: { flexDirection: 'row', gap: 12, marginTop: 8 },
  cancelBtn: { flex: 1, padding: 14, borderRadius: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)', alignItems: 'center' },
  cancelBtnText: { color: '#aaa', fontWeight: '600' },
  saveBtn: { flex: 1, padding: 14, borderRadius: 10, backgroundColor: '#6366f1', alignItems: 'center' },
  saveBtnText: { color: '#fff', fontWeight: '700' },
});
