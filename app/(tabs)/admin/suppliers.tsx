import React, { useEffect, useState, useMemo } from 'react';
import {
  View, Text, StyleSheet, FlatList, SafeAreaView, TouchableOpacity,
  ActivityIndicator, Alert, Modal, TextInput, Platform, StatusBar
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import axios from 'axios';
import { useAuthStore } from '../../../store/authStore';
import { useLangStore } from '../../../store/langStore';

interface Supplier {
  id: number; name: string; phone?: string; email?: string; address?: string;
}

export default function SuppliersScreen() {
  const { t } = useLangStore();
  const { apiUrl, token } = useAuthStore();
  const [suppliers, setSuppliers] = useState<Supplier[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [modalVisible, setModalVisible] = useState(false);
  const [editSupplier, setEditSupplier] = useState<Supplier | null>(null);
  const [saving, setSaving] = useState(false);

  const [form, setForm] = useState({ name: '', phone: '', email: '', address: '' });

  const headers = { Authorization: `Bearer ${token}` };

  const fetchSuppliers = async () => {
    setLoading(true);
    try {
      const res = await axios.get(`${apiUrl}/api/admin/suppliers`, { headers });
      setSuppliers(res.data.suppliers);
    } catch {
      Alert.alert(t('shared.error'), t('admin.suppliers_error'));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchSuppliers(); }, []);

  const filtered = useMemo(() => {
    if (!search) return suppliers;
    return suppliers.filter(s => s.name.toLowerCase().includes(search.toLowerCase()));
  }, [suppliers, search]);

  const openCreate = () => {
    setEditSupplier(null);
    setForm({ name: '', phone: '', email: '', address: '' });
    setModalVisible(true);
  };

  const openEdit = (s: Supplier) => {
    setEditSupplier(s);
    setForm({ name: s.name, phone: s.phone || '', email: s.email || '', address: s.address || '' });
    setModalVisible(true);
  };

  const handleSave = async () => {
    if (!form.name) {
      Alert.alert(t('shared.validation'), t('admin.name_required'));
      return;
    }
    setSaving(true);
    try {
      const payload = { name: form.name, phone: form.phone, email: form.email, address: form.address };
      if (editSupplier) {
        await axios.put(`${apiUrl}/api/admin/suppliers/${editSupplier.id}`, payload, { headers });
      } else {
        await axios.post(`${apiUrl}/api/admin/suppliers`, payload, { headers });
      }
      setModalVisible(false);
      fetchSuppliers();
    } catch (e: any) {
      Alert.alert(t('shared.error'), e?.response?.data?.message || t('shared.save_error'));
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = (s: Supplier) => {
    Alert.alert(
      t('shared.delete'),
      t('admin.delete_confirm', { name: s.name }),
      [
        { text: t('shared.cancel'), style: 'cancel' },
        {
          text: t('shared.delete'), style: 'destructive',
          onPress: async () => {
            try {
              await axios.delete(`${apiUrl}/api/admin/suppliers/${s.id}`, { headers });
              fetchSuppliers();
            } catch {
              Alert.alert(t('shared.error'), t('shared.delete_error'));
            }
          },
        },
      ]
    );
  };

  const renderItem = ({ item }: { item: Supplier }) => (
    <View style={styles.card}>
      <View style={styles.avatar}>
        <Text style={styles.avatarText}>{item.name.charAt(0).toUpperCase()}</Text>
      </View>
      <View style={styles.info}>
        <Text style={styles.name}>{item.name}</Text>
        {item.phone ? <Text style={styles.sub}><Ionicons name="call-outline" size={11} color="#666" /> {item.phone}</Text> : null}
        {item.email ? <Text style={styles.sub}><Ionicons name="mail-outline" size={11} color="#666" /> {item.email}</Text> : null}
        {item.address ? <Text style={styles.sub} numberOfLines={1}><Ionicons name="location-outline" size={11} color="#666" /> {item.address}</Text> : null}
      </View>
      <View style={styles.actions}>
        <TouchableOpacity onPress={() => openEdit(item)} style={styles.actionBtn}>
          <Ionicons name="pencil-outline" size={18} color="#6366f1" />
        </TouchableOpacity>
        <TouchableOpacity onPress={() => handleDelete(item)} style={styles.actionBtn}>
          <Ionicons name="trash-outline" size={18} color="#ef4444" />
        </TouchableOpacity>
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>{t('admin.suppliers_title')}</Text>
          <Text style={styles.headerSub}>{t('admin.count_suppliers', { count: suppliers.length })}</Text>
        </View>
        <View style={styles.headerActions}>
          <TouchableOpacity onPress={fetchSuppliers} disabled={loading} style={styles.iconBtn}>
            <Ionicons name="refresh" size={22} color="#10b981" />
          </TouchableOpacity>
          <TouchableOpacity onPress={openCreate} style={[styles.iconBtn, { marginLeft: 8 }]}>
            <Ionicons name="add-circle" size={22} color="#6366f1" />
          </TouchableOpacity>
        </View>
      </View>

      <View style={styles.searchBar}>
        <Ionicons name="search" size={18} color="#666" style={{ marginRight: 8 }} />
        <TextInput
          style={styles.searchInput}
          placeholder={t('admin.search_supplier_placeholder')}
          placeholderTextColor="#666"
          value={search}
          onChangeText={setSearch}
        />
      </View>

      {loading ? (
        <View style={styles.center}><ActivityIndicator size="large" color="#10b981" /></View>
      ) : (
        <FlatList
          data={filtered}
          keyExtractor={i => i.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.list}
          ListEmptyComponent={<Text style={styles.empty}>{t('admin.no_suppliers_found')}</Text>}
        />
      )}

      <Modal visible={modalVisible} animationType="slide" transparent onRequestClose={() => setModalVisible(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>{editSupplier ? t('admin.edit_supplier') : t('admin.new_supplier')}</Text>
            <TextInput style={styles.input} placeholder={t('admin.name_req')} placeholderTextColor="#666" value={form.name} onChangeText={v => setForm(f => ({ ...f, name: v }))} />
            <TextInput style={styles.input} placeholder={t('shared.phone')} placeholderTextColor="#666" keyboardType="phone-pad" value={form.phone} onChangeText={v => setForm(f => ({ ...f, phone: v }))} />
            <TextInput style={styles.input} placeholder={t('shared.email')} placeholderTextColor="#666" keyboardType="email-address" autoCapitalize="none" value={form.email} onChangeText={v => setForm(f => ({ ...f, email: v }))} />
            <TextInput style={[styles.input, { height: 80 }]} placeholder={t('shared.address')} placeholderTextColor="#666" multiline value={form.address} onChangeText={v => setForm(f => ({ ...f, address: v }))} />
            <View style={styles.modalActions}>
              <TouchableOpacity style={styles.cancelBtn} onPress={() => setModalVisible(false)}>
                <Text style={styles.cancelBtnText}>{t('shared.cancel')}</Text>
              </TouchableOpacity>
              <TouchableOpacity style={styles.saveBtn} onPress={handleSave} disabled={saving}>
                {saving ? <ActivityIndicator color="#fff" /> : <Text style={styles.saveBtnText}>{editSupplier ? t('shared.modify') : t('shared.create')}</Text>}
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
  searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#161b22', margin: 16, paddingHorizontal: 14, borderRadius: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)' },
  searchInput: { flex: 1, color: '#fff', paddingVertical: 12, fontSize: 14 },
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  list: { padding: 16, paddingTop: 0 },
  card: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#161b22', borderRadius: 12, padding: 14, marginBottom: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)' },
  avatar: { width: 44, height: 44, borderRadius: 22, backgroundColor: 'rgba(16,185,129,0.15)', alignItems: 'center', justifyContent: 'center', marginRight: 12 },
  avatarText: { color: '#10b981', fontWeight: 'bold', fontSize: 18 },
  info: { flex: 1 },
  name: { color: '#fff', fontWeight: '600', fontSize: 15 },
  sub: { color: '#666', fontSize: 12, marginTop: 3 },
  actions: { flexDirection: 'row', gap: 4 },
  actionBtn: { padding: 6 },
  empty: { color: '#666', textAlign: 'center', marginTop: 40 },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.7)', justifyContent: 'flex-end' },
  modalContent: { backgroundColor: '#161b22', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 24 },
  modalTitle: { color: '#fff', fontSize: 18, fontWeight: 'bold', marginBottom: 20 },
  input: { backgroundColor: '#0d1117', color: '#fff', borderRadius: 10, padding: 14, marginBottom: 12, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)', fontSize: 14 },
  modalActions: { flexDirection: 'row', gap: 12, marginTop: 8 },
  cancelBtn: { flex: 1, padding: 14, borderRadius: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)', alignItems: 'center' },
  cancelBtnText: { color: '#aaa', fontWeight: '600' },
  saveBtn: { flex: 1, padding: 14, borderRadius: 10, backgroundColor: '#10b981', alignItems: 'center' },
  saveBtnText: { color: '#fff', fontWeight: '700' },
});
