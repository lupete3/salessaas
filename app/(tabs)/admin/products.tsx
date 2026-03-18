import React, { useEffect, useState, useMemo } from 'react';
import {
  View, Text, StyleSheet, FlatList, SafeAreaView, TouchableOpacity,
  ActivityIndicator, Alert, Modal, TextInput, Platform, StatusBar
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import axios from 'axios';
import { useAuthStore } from '../../../store/authStore';
import { useLangStore } from '../../../store/langStore';

interface Product {
  id: number; name: string; stock: number; buying_price: number;
  selling_price: number; total_valuation: number;
}

export default function AdminProductsScreen() {
  const { t } = useLangStore();
  const { apiUrl, token, store } = useAuthStore();
  const currency = store?.currency || 'CDF';
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [modalVisible, setModalVisible] = useState(false);
  const [editProduct, setEditProduct] = useState<Product | null>(null);
  const [saving, setSaving] = useState(false);

  const [form, setForm] = useState({ name: '', purchase_price: '', selling_price: '', stock_quantity: '', min_stock: '' });

  const headers = { Authorization: `Bearer ${token}` };

  const fetchProducts = async () => {
    setLoading(true);
    try {
      const res = await axios.get(`${apiUrl}/api/admin/inventories`, { headers });
      setProducts(res.data.inventory);
    } catch {
      Alert.alert(t('shared.error'), t('admin.products_error'));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchProducts(); }, []);

  const filtered = useMemo(() => {
    if (!search) return products;
    return products.filter(p => p.name.toLowerCase().includes(search.toLowerCase()));
  }, [products, search]);

  const openCreate = () => {
    setEditProduct(null);
    setForm({ name: '', purchase_price: '', selling_price: '', stock_quantity: '', min_stock: '' });
    setModalVisible(true);
  };

  const openEdit = (p: Product) => {
    setEditProduct(p);
    setForm({
      name: p.name,
      purchase_price: parseFloat(String(p.buying_price || 0)).toFixed(2),
      selling_price: parseFloat(String(p.selling_price || 0)).toFixed(2),
      stock_quantity: String(p.stock ?? 0),
      min_stock: '',
    });
    setModalVisible(true);
  };

  const handleSave = async () => {
    if (!form.name || !form.purchase_price || !form.selling_price || !form.stock_quantity) {
      Alert.alert(t('shared.validation'), t('shared.fill_required'));
      return;
    }
    setSaving(true);
    try {
      const payload = {
        name: form.name,
        purchase_price: parseFloat(form.purchase_price),
        selling_price: parseFloat(form.selling_price),
        stock_quantity: parseInt(form.stock_quantity),
        min_stock: form.min_stock ? parseInt(form.min_stock) : 5,
      };
      if (editProduct) {
        await axios.put(`${apiUrl}/api/admin/products/${editProduct.id}`, payload, { headers });
      } else {
        await axios.post(`${apiUrl}/api/admin/products`, payload, { headers });
      }
      setModalVisible(false);
      fetchProducts();
    } catch (e: any) {
      Alert.alert(t('shared.error'), e?.response?.data?.message || t('shared.save_error'));
    } finally {
      setSaving(false);
    }
  };

  const renderItem = ({ item }: { item: Product }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <Text style={styles.productName} numberOfLines={1}>{item.name}</Text>
        <TouchableOpacity onPress={() => openEdit(item)} style={styles.editBtn}>
          <Ionicons name="pencil-outline" size={16} color="#6366f1" />
        </TouchableOpacity>
      </View>
      <View style={styles.cardRow}>
        <View style={styles.stat}>
          <Text style={styles.statLabel}>{t('pos.stock')}</Text>
          <Text style={[styles.statValue, { color: (item.stock ?? 0) <= 0 ? '#ef4444' : (item.stock ?? 0) <= 5 ? '#f59e0b' : '#10b981' }]}>{item.stock ?? 0}</Text>
        </View>
        <View style={styles.stat}>
          <Text style={styles.statLabel}>{t('admin.buying_price_short')}</Text>
          <Text style={styles.statValue}>{parseFloat(String(item.buying_price || 0)).toFixed(0)}</Text>
        </View>
        <View style={styles.stat}>
          <Text style={styles.statLabel}>{t('admin.selling_price_short')}</Text>
          <Text style={[styles.statValue, { color: '#6366f1' }]}>{parseFloat(String(item.selling_price || 0)).toFixed(0)}</Text>
        </View>
        <View style={styles.stat}>
          <Text style={styles.statLabel}>{t('admin.valuation_short')}</Text>
          <Text style={[styles.statValue, { color: '#f59e0b' }]}>{parseFloat(String(item.total_valuation || 0)).toFixed(0)}</Text>
        </View>
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>{t('admin.products_title')}</Text>
          <Text style={styles.headerSub}>{t('admin.count_products', { count: products.length })}</Text>
        </View>
        <View style={styles.headerActions}>
          <TouchableOpacity onPress={fetchProducts} disabled={loading} style={styles.iconBtn}>
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
          placeholder={t('pos.search_placeholder')}
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
          keyExtractor={item => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.list}
          ListEmptyComponent={<Text style={styles.empty}>{t('admin.no_products_found')}</Text>}
        />
      )}

      <Modal visible={modalVisible} animationType="slide" transparent onRequestClose={() => setModalVisible(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>{editProduct ? t('admin.edit_product') : t('admin.new_product')}</Text>
            <TextInput style={styles.input} placeholder={t('admin.product_name_req')} placeholderTextColor="#666" value={form.name} onChangeText={v => setForm(f => ({ ...f, name: v }))} />
            <View style={styles.row}>
              <TextInput style={[styles.input, { flex: 1, marginRight: 8 }]} placeholder={t('admin.buy_price_req')} placeholderTextColor="#666" keyboardType="decimal-pad" value={form.purchase_price} onChangeText={v => setForm(f => ({ ...f, purchase_price: v }))} />
              <TextInput style={[styles.input, { flex: 1 }]} placeholder={t('admin.sell_price_req')} placeholderTextColor="#666" keyboardType="decimal-pad" value={form.selling_price} onChangeText={v => setForm(f => ({ ...f, selling_price: v }))} />
            </View>
            <View style={styles.row}>
              <TextInput style={[styles.input, { flex: 1, marginRight: 8 }]} placeholder={t('admin.initial_stock_req')} placeholderTextColor="#666" keyboardType="number-pad" value={form.stock_quantity} onChangeText={v => setForm(f => ({ ...f, stock_quantity: v }))} />
              <TextInput style={[styles.input, { flex: 1 }]} placeholder={t('admin.min_stock_opt')} placeholderTextColor="#666" keyboardType="number-pad" value={form.min_stock} onChangeText={v => setForm(f => ({ ...f, min_stock: v }))} />
            </View>
            <View style={styles.modalActions}>
              <TouchableOpacity style={styles.cancelBtn} onPress={() => setModalVisible(false)}>
                <Text style={styles.cancelBtnText}>{t('shared.cancel')}</Text>
              </TouchableOpacity>
              <TouchableOpacity style={styles.saveBtn} onPress={handleSave} disabled={saving}>
                {saving ? <ActivityIndicator color="#fff" /> : <Text style={styles.saveBtnText}>{editProduct ? t('shared.modify') : t('shared.create')}</Text>}
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
  card: { backgroundColor: '#161b22', borderRadius: 12, padding: 14, marginBottom: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)' },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  productName: { color: '#fff', fontWeight: '600', fontSize: 15, flex: 1 },
  editBtn: { padding: 4 },
  cardRow: { flexDirection: 'row', justifyContent: 'space-between' },
  stat: { alignItems: 'center' },
  statLabel: { color: '#555', fontSize: 10, textTransform: 'uppercase', marginBottom: 2 },
  statValue: { color: '#fff', fontWeight: '700', fontSize: 14 },
  empty: { color: '#666', textAlign: 'center', marginTop: 40 },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.7)', justifyContent: 'flex-end' },
  modalContent: { backgroundColor: '#161b22', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 24 },
  modalTitle: { color: '#fff', fontSize: 18, fontWeight: 'bold', marginBottom: 20 },
  row: { flexDirection: 'row' },
  input: { backgroundColor: '#0d1117', color: '#fff', borderRadius: 10, padding: 14, marginBottom: 14, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)', fontSize: 14 },
  modalActions: { flexDirection: 'row', gap: 12, marginTop: 4 },
  cancelBtn: { flex: 1, padding: 14, borderRadius: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)', alignItems: 'center' },
  cancelBtnText: { color: '#aaa', fontWeight: '600' },
  saveBtn: { flex: 1, padding: 14, borderRadius: 10, backgroundColor: '#6366f1', alignItems: 'center' },
  saveBtnText: { color: '#fff', fontWeight: '700' },
});
