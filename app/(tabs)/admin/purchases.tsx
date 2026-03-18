import React, { useEffect, useState, useCallback, useMemo } from 'react';
import {
  View, Text, StyleSheet, FlatList, SafeAreaView, TouchableOpacity,
  ActivityIndicator, Alert, Modal, TextInput, ScrollView, KeyboardAvoidingView, Platform, StatusBar
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import axios from 'axios';
import { useAuthStore } from '../../../store/authStore';
import { useLangStore } from '../../../store/langStore';
import { useAppStore } from '../../../store/appStore';

interface PurchaseItem { product_name: string; quantity: number; unit_price: number; subtotal: number; }
interface Purchase {
  id: number; date: string | null; number: string | null;
  supplier_name: string | null; total_amount: number;
  amount_paid: number; status: string | null; items: PurchaseItem[];
}
interface Product { id: number; name: string; }
interface Supplier { id: number; name: string; }
interface LineItem { product_id: number; product_name: string; quantity: string; unit_price: string; productSearch: string; }

const statusColor = (s: string | null) => {
  if (s === 'paid') return { bg: 'rgba(16,185,129,0.1)', text: '#10b981' };
  if (s === 'partial') return { bg: 'rgba(234,179,8,0.1)', text: '#eab308' };
  return { bg: 'rgba(239,68,68,0.1)', text: '#ef4444' };
};

export default function PurchasesScreen() {
  const { t, lang } = useLangStore();
  const { apiUrl, token, store } = useAuthStore();
  const currency = store?.currency || 'CDF';
  const [purchases, setPurchases] = useState<Purchase[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [suppliers, setSuppliers] = useState<Supplier[]>([]);
  const [loading, setLoading] = useState(true);

  // Create modal
  const [createVisible, setCreateVisible] = useState(false);
  const [saving, setSaving] = useState(false);
  const [purchaseDate, setPurchaseDate] = useState(new Date().toISOString().split('T')[0]);
  const [supplierId, setSupplierId] = useState<number | null>(null);
  const [lines, setLines] = useState<LineItem[]>([{ product_id: 0, product_name: '', quantity: '1', unit_price: '0', productSearch: '' }]);
  const [createAmountPaid, setCreateAmountPaid] = useState('');

  // Payment modal
  const [payModalVisible, setPayModalVisible] = useState(false);
  const [selectedPurchase, setSelectedPurchase] = useState<Purchase | null>(null);
  const [amountPaid, setAmountPaid] = useState('');
  const [paying, setPaying] = useState(false);

  const headers = { Authorization: `Bearer ${token}` };

  const fetchAll = useCallback(async () => {
    setLoading(true);
    try {
      const [purchRes, prodRes, suppRes] = await Promise.all([
        axios.get(`${apiUrl}/api/admin/purchases`, { headers }),
        axios.get(`${apiUrl}/api/products`, { headers }),
        axios.get(`${apiUrl}/api/admin/suppliers`, { headers }),
      ]);
      setPurchases(purchRes.data.purchases || []);
      setProducts(prodRes.data.products || []);
      setSuppliers(suppRes.data.suppliers || []);
    } catch (e: any) {
      Alert.alert(t('shared.error'), e?.response?.data?.message || t('admin.purchases_error'));
    } finally {
      setLoading(false);
    }
  }, [apiUrl, token]);

  useEffect(() => { fetchAll(); }, []);

  // ── Create purchase ──────────────────────────────────────────────
  const openCreate = () => {
    setPurchaseDate(new Date().toISOString().split('T')[0]);
    setSupplierId(null);
    setLines([{ product_id: 0, product_name: '', quantity: '1', unit_price: '0', productSearch: '' }]);
    setCreateAmountPaid('');
    setCreateVisible(true);
  };

  const addLine = () => setLines(p => [...p, { product_id: 0, product_name: '', quantity: '1', unit_price: '0', productSearch: '' }]);
  const removeLine = (i: number) => setLines(p => p.filter((_, idx) => idx !== i));

  const updateLine = (i: number, field: keyof LineItem, val: string) =>
    setLines(p => p.map((l, idx) => idx === i ? { ...l, [field]: val } : l));

  const pickProduct = (i: number, p: Product) =>
    setLines(prev => prev.map((l, idx) => idx === i
      ? { ...l, product_id: p.id, product_name: p.name, productSearch: p.name }
      : l));

  const total = lines.reduce((acc, l) => acc + parseFloat(l.quantity || '0') * parseFloat(l.unit_price || '0'), 0);

  const handleSave = async () => {
    const validLines = lines.filter(l => l.product_id > 0 && parseFloat(l.quantity) > 0);
    if (!purchaseDate) { Alert.alert(t('shared.validation'), t('admin.date_required')); return; }
    if (!validLines.length) { Alert.alert(t('shared.validation'), t('admin.add_product_req')); return; }
    setSaving(true);
    try {
      await axios.post(`${apiUrl}/api/admin/purchases`, {
        purchase_date: purchaseDate,
        supplier_id: supplierId,
        items: validLines.map(l => ({ product_id: l.product_id, quantity: parseInt(l.quantity), unit_price: parseFloat(l.unit_price) })),
        amount_paid: parseFloat(createAmountPaid || '0'),
      }, { headers });
      setCreateVisible(false);
      fetchAll();
    } catch (e: any) {
      Alert.alert(t('shared.error'), e?.response?.data?.message || t('admin.purchase_save_error'));
    } finally { setSaving(false); }
  };

  // ── Payment ──────────────────────────────────────────────────────
  const openPayModal = (p: Purchase) => {
    setSelectedPurchase(p);
    setAmountPaid(String(p.amount_paid || ''));
    setPayModalVisible(true);
  };

  const handlePay = async () => {
    if (!selectedPurchase) return;
    setPaying(true);
    try {
      const res = await axios.patch(`${apiUrl}/api/admin/purchases/${selectedPurchase.id}/pay`,
        { amount_paid: parseFloat(amountPaid || '0') }, { headers });
      setPurchases(prev => prev.map(p => p.id === selectedPurchase.id
        ? { ...p, amount_paid: res.data.amount_paid, status: res.data.status }
        : p));
      setPayModalVisible(false);
    } catch (e: any) {
      Alert.alert(t('shared.error'), e?.response?.data?.message || t('admin.payment_save_error'));
    } finally { setPaying(false); }
  };

  // ── Render ───────────────────────────────────────────────────────
  const renderPurchase = ({ item }: { item: Purchase }) => {
    const sc = statusColor(item.status);
    const balance = parseFloat(String(item.total_amount || 0)) - parseFloat(String(item.amount_paid || 0));
    return (
      <TouchableOpacity style={styles.card} onPress={() => openPayModal(item)} activeOpacity={0.85}>
        <View style={styles.cardHeader}>
          <View>
            <Text style={styles.number}>{item.number || 'N/A'}</Text>
            <Text style={styles.date}>{item.date ? new Date(item.date).toLocaleDateString(lang === 'fr' ? 'fr-FR' : 'en-US') : '---'}</Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: sc.bg }]}>
            <Text style={[styles.statusText, { color: sc.text }]}>{t(`admin.status.${item.status || 'pending'}` as any).toUpperCase()}</Text>
          </View>
        </View>
        {item.supplier_name ? (
          <View style={styles.supplierRow}>
            <Ionicons name="business-outline" size={13} color="#666" />
            <Text style={styles.supplierName}>{item.supplier_name}</Text>
          </View>
        ) : null}
        <View style={styles.itemsList}>
          {(item.items || []).slice(0, 2).map((l, i) => (
            <Text key={i} style={styles.itemLine}>• {l.product_name} ×{l.quantity}</Text>
          ))}
          {(item.items || []).length > 2 && <Text style={styles.more}>+{item.items.length - 2} {t('shared.others')}</Text>}
        </View>
        <View style={styles.cardFooter}>
          <View>
            <Text style={styles.footerLabel}>{t('pos.total')}</Text>
            <Text style={styles.footerAmount}>{parseFloat(String(item.total_amount || 0)).toFixed(2)} {currency}</Text>
          </View>
          <View style={{ alignItems: 'flex-end' }}>
            <Text style={styles.footerLabel}>{t('customers.debt')}</Text>
            <Text style={[styles.footerAmount, { color: balance > 0 ? '#ef4444' : '#10b981' }]}>{balance.toFixed(2)} {currency}</Text>
          </View>
        </View>
        <Text style={styles.tapHint}>{t('admin.tap_to_pay')}</Text>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>{t('admin.purchases_title')}</Text>
          <Text style={styles.headerSub}>{t('admin.count_purchases', { count: purchases.length })}</Text>
        </View>
        <View style={{ flexDirection: 'row' }}>
          <TouchableOpacity onPress={fetchAll} disabled={loading} style={{ padding: 4 }}>
            <Ionicons name="refresh" size={22} color="#10b981" />
          </TouchableOpacity>
          <TouchableOpacity onPress={openCreate} style={{ padding: 4, marginLeft: 8 }}>
            <Ionicons name="add-circle" size={22} color="#6366f1" />
          </TouchableOpacity>
        </View>
      </View>

      {loading ? (
        <View style={styles.center}><ActivityIndicator size="large" color="#10b981" /></View>
      ) : (
        <FlatList
          data={purchases}
          keyExtractor={i => i.id.toString()}
          renderItem={renderPurchase}
          contentContainerStyle={styles.list}
          ListEmptyComponent={<Text style={styles.empty}>{t('admin.no_purchases_found')}</Text>}
        />
      )}

      {/* ── Create Modal ──────────────────────────────────────── */}
      <Modal visible={createVisible} animationType="slide" transparent onRequestClose={() => setCreateVisible(false)}>
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={styles.modalOverlay}>
          <View style={[styles.modalContent, { maxHeight: '92%' }]}>
            <ScrollView showsVerticalScrollIndicator={false}>
              <Text style={styles.modalTitle}>{t('admin.new_purchase')}</Text>
              <Text style={styles.label2}>{t('pos.date')} *</Text>
              <TextInput style={styles.input} placeholder={t('admin.date_placeholder')} placeholderTextColor="#666" value={purchaseDate} onChangeText={setPurchaseDate} />
              <Text style={styles.label2}>{t('admin.supplier')}</Text>
              <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginBottom: 14 }}>
                <TouchableOpacity onPress={() => setSupplierId(null)} style={[styles.chip, supplierId === null && styles.chipActive]}>
                  <Text style={[styles.chipText, supplierId === null && { color: '#fff' }]}>{t('shared.none')}</Text>
                </TouchableOpacity>
                {suppliers.map(s => (
                  <TouchableOpacity key={s.id} onPress={() => setSupplierId(s.id)} style={[styles.chip, supplierId === s.id && styles.chipActive]}>
                    <Text style={[styles.chipText, supplierId === s.id && { color: '#fff' }]}>{s.name}</Text>
                  </TouchableOpacity>
                ))}
              </ScrollView>
              <Text style={styles.label2}>{t('admin.products_title')} *</Text>
              {lines.map((line, idx) => (
                <LineEditor
                  key={idx}
                  line={line}
                  idx={idx}
                  allProducts={products}
                  onUpdate={updateLine}
                  onPick={pickProduct}
                  onRemove={removeLine}
                  showRemove={lines.length > 1}
                  currency={currency}
                />
              ))}
              <TouchableOpacity onPress={addLine} style={styles.addLineBtn}>
                <Ionicons name="add" size={18} color="#6366f1" />
                <Text style={styles.addLineBtnText}>{t('admin.add_line')}</Text>
              </TouchableOpacity>

              <View style={styles.totalRow}>
                <Text style={styles.totalLabel}>{t('admin.total_estimated')} :</Text>
                <Text style={styles.totalValue}>{total.toFixed(2)} {currency}</Text>
              </View>

              <Text style={styles.label2}>{t('pos.amount_paid')} ({t('shared.optional')})</Text>
              <TextInput
                style={styles.input}
                placeholder="Ex: 50000"
                placeholderTextColor="#666"
                keyboardType="decimal-pad"
                value={createAmountPaid}
                onChangeText={setCreateAmountPaid}
              />
              <TouchableOpacity
                style={[styles.input, { alignItems: 'center', justifyContent: 'center', padding: 12, borderColor: 'rgba(16,185,129,0.3)', marginBottom: 20 }]}
                onPress={() => setCreateAmountPaid(String(total))}>
                <Text style={{ color: '#10b981', fontWeight: '600' }}>{t('pos.pay_all')} ({total.toFixed(2)} {currency})</Text>
              </TouchableOpacity>

              <View style={styles.modalActions}>
                <TouchableOpacity style={styles.cancelBtn} onPress={() => setCreateVisible(false)}>
                  <Text style={styles.cancelBtnText}>{t('shared.cancel')}</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.saveBtn} onPress={handleSave} disabled={saving}>
                  {saving ? <ActivityIndicator color="#fff" /> : <Text style={styles.saveBtnText}>{t('shared.save')}</Text>}
                </TouchableOpacity>
              </View>
            </ScrollView>
          </View>
        </KeyboardAvoidingView>
      </Modal>

      {/* ── Payment Modal ─────────────────────────────────────── */}
      <Modal visible={payModalVisible} animationType="slide" transparent onRequestClose={() => setPayModalVisible(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>{t('admin.register_payment')}</Text>
            {selectedPurchase && (
              <>
                <View style={styles.payInfoRow}>
                  <Text style={styles.label2}>{t('admin.purchase_total')} :</Text>
                  <Text style={styles.payValue}>{parseFloat(String(selectedPurchase.total_amount || 0)).toFixed(2)} {currency}</Text>
                </View>
                <View style={styles.payInfoRow}>
                  <Text style={styles.label2}>{t('admin.already_paid')} :</Text>
                  <Text style={[styles.payValue, { color: '#10b981' }]}>{parseFloat(String(selectedPurchase.amount_paid || 0)).toFixed(2)} {currency}</Text>
                </View>
                <View style={styles.payInfoRow}>
                  <Text style={styles.label2}>{t('admin.remaining_to_pay')} :</Text>
                  <Text style={[styles.payValue, { color: '#ef4444' }]}>
                    {(parseFloat(String(selectedPurchase.total_amount || 0)) - parseFloat(String(selectedPurchase.amount_paid || 0))).toFixed(2)} {currency}
                  </Text>
                </View>
                <Text style={[styles.label2, { marginTop: 16 }]}>{t('admin.total_amount_paid_req')}</Text>
                <TextInput
                  style={styles.input}
                  placeholder="Ex: 50000"
                  placeholderTextColor="#666"
                  keyboardType="decimal-pad"
                  value={amountPaid}
                  onChangeText={setAmountPaid}
                />
                <TouchableOpacity
                  style={[styles.input, { alignItems: 'center', justifyContent: 'center', padding: 12, borderColor: 'rgba(16,185,129,0.3)', marginBottom: 6 }]}
                  onPress={() => setAmountPaid(String(selectedPurchase.total_amount))}>
                  <Text style={{ color: '#10b981', fontWeight: '600' }}>{t('pos.pay_all')} ({parseFloat(String(selectedPurchase.total_amount || 0)).toFixed(2)} {currency})</Text>
                </TouchableOpacity>
              </>
            )}
            <View style={[styles.modalActions, { marginTop: 16 }]}>
              <TouchableOpacity style={styles.cancelBtn} onPress={() => setPayModalVisible(false)}>
                <Text style={styles.cancelBtnText}>{t('shared.cancel')}</Text>
              </TouchableOpacity>
              <TouchableOpacity style={[styles.saveBtn, { backgroundColor: '#10b981' }]} onPress={handlePay} disabled={paying}>
                {paying ? <ActivityIndicator color="#fff" /> : <Text style={styles.saveBtnText}>{t('shared.confirm')}</Text>}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

// ── Line item editor with product search ───────────────────────────
function LineEditor({ line, idx, allProducts, onUpdate, onPick, onRemove, showRemove, currency }: {
  line: LineItem; idx: number; allProducts: Product[];
  onUpdate: (i: number, f: keyof LineItem, v: string) => void;
  onPick: (i: number, p: Product) => void;
  onRemove: (i: number) => void;
  showRemove: boolean; currency: string;
}) {
  const { t } = useLangStore();
  const filtered = useMemo(() => {
    const q = line.productSearch.toLowerCase();
    if (!q) return [];
    return allProducts.filter(p => p.name.toLowerCase().includes(q)).slice(0, 6);
  }, [line.productSearch, allProducts]);

  const subtotal = parseFloat(line.quantity || '0') * parseFloat(line.unit_price || '0');

  return (
    <View style={s.lineCard}>
      <View style={s.lineHeader}>
        <Text style={s.lineTitle}>{t('admin.line')} {idx + 1}{line.product_name ? ` — ${line.product_name}` : ''}</Text>
        {showRemove && (
          <TouchableOpacity onPress={() => onRemove(idx)}>
            <Ionicons name="trash-outline" size={16} color="#ef4444" />
          </TouchableOpacity>
        )}
      </View>

      {/* Search field */}
      <View style={s.searchBox}>
        <Ionicons name="search" size={15} color="#666" style={{ marginRight: 6 }} />
        <TextInput
          style={s.searchInput}
          placeholder={t('pos.search_placeholder')}
          placeholderTextColor="#555"
          value={line.productSearch}
          onChangeText={v => onUpdate(idx, 'productSearch', v)}
        />
        {line.product_id > 0 && (
          <TouchableOpacity onPress={() => { onUpdate(idx, 'productSearch', ''); onUpdate(idx, 'product_name', ''); onUpdate(idx, 'product_id' as any, '0'); }}>
            <Ionicons name="close-circle" size={16} color="#666" />
          </TouchableOpacity>
        )}
      </View>

      {/* Suggestions */}
      {filtered.length > 0 && (
        <View style={s.suggestions}>
          {filtered.map(p => (
            <TouchableOpacity key={p.id} style={s.suggestion} onPress={() => onPick(idx, p)}>
              <Text style={s.suggestionText}>{p.name}</Text>
            </TouchableOpacity>
          ))}
        </View>
      )}

      {/* Qty + price */}
      <View style={{ flexDirection: 'row', gap: 8, marginTop: 8 }}>
        <TextInput
          style={[s.miniInput, { flex: 1 }]}
          placeholder={t('pos.qty')}
          placeholderTextColor="#555"
          keyboardType="number-pad"
          value={line.quantity}
          onChangeText={v => onUpdate(idx, 'quantity', v)}
        />
        <TextInput
          style={[s.miniInput, { flex: 2 }]}
          placeholder={t('admin.unit_price')}
          placeholderTextColor="#555"
          keyboardType="decimal-pad"
          value={line.unit_price}
          onChangeText={v => onUpdate(idx, 'unit_price', v)}
        />
      </View>
      {subtotal > 0 && <Text style={s.subtotalText}>{t('pos.subtotal')} : {subtotal.toFixed(2)} {currency}</Text>}
    </View>
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
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  list: { padding: 16 },
  card: { backgroundColor: '#161b22', borderRadius: 12, padding: 16, marginBottom: 14, borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)' },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 10 },
  number: { color: '#fff', fontSize: 15, fontWeight: 'bold' },
  date: { color: '#666', fontSize: 12, marginTop: 2 },
  statusBadge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
  statusText: { fontSize: 10, fontWeight: 'bold' },
  supplierRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
  supplierName: { color: '#888', fontSize: 12, marginLeft: 5 },
  itemsList: { borderTopWidth: 1, borderTopColor: 'rgba(255,255,255,0.05)', paddingTop: 8, marginBottom: 10 },
  itemLine: { color: '#888', fontSize: 12, marginBottom: 3 },
  more: { color: '#555', fontSize: 11, fontStyle: 'italic' },
  cardFooter: { flexDirection: 'row', justifyContent: 'space-between' },
  footerLabel: { color: '#666', fontSize: 11 },
  footerAmount: { color: '#fff', fontWeight: '700', fontSize: 15 },
  tapHint: { color: '#444', fontSize: 10, textAlign: 'center', marginTop: 10, fontStyle: 'italic' },
  empty: { color: '#666', textAlign: 'center', marginTop: 40 },
  // Modal shared
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.75)', justifyContent: 'flex-end' },
  modalContent: { backgroundColor: '#161b22', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 24 },
  modalTitle: { color: '#fff', fontSize: 18, fontWeight: 'bold', marginBottom: 20 },
  label2: { color: '#aaa', fontSize: 13, marginBottom: 8 },
  input: { backgroundColor: '#0d1117', color: '#fff', borderRadius: 10, padding: 12, marginBottom: 12, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)', fontSize: 14 },
  chip: { paddingHorizontal: 14, paddingVertical: 7, borderRadius: 20, backgroundColor: 'rgba(255,255,255,0.05)', marginRight: 8, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)' },
  chipActive: { backgroundColor: '#10b981', borderColor: '#10b981' },
  chipText: { color: '#aaa', fontWeight: '600', fontSize: 13 },
  addLineBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', padding: 12, borderRadius: 10, borderWidth: 1, borderColor: 'rgba(99,102,241,0.4)', borderStyle: 'dashed', marginBottom: 14 },
  addLineBtnText: { color: '#6366f1', fontWeight: '600', marginLeft: 6 },
  totalRow: { flexDirection: 'row', justifyContent: 'space-between', padding: 12, backgroundColor: 'rgba(16,185,129,0.08)', borderRadius: 10, marginBottom: 16 },
  totalLabel: { color: '#aaa', fontSize: 14 },
  totalValue: { color: '#10b981', fontWeight: '800', fontSize: 16 },
  modalActions: { flexDirection: 'row', gap: 12 },
  cancelBtn: { flex: 1, padding: 14, borderRadius: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)', alignItems: 'center' },
  cancelBtnText: { color: '#aaa', fontWeight: '600' },
  saveBtn: { flex: 1, padding: 14, borderRadius: 10, backgroundColor: '#6366f1', alignItems: 'center' },
  saveBtnText: { color: '#fff', fontWeight: '700' },
  // Payment modal
  payInfoRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  payValue: { color: '#fff', fontWeight: '700', fontSize: 15 },
});

const s = StyleSheet.create({
  lineCard: { backgroundColor: 'rgba(255,255,255,0.03)', borderRadius: 10, padding: 12, marginBottom: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.07)' },
  lineHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
  lineTitle: { color: '#aaa', fontSize: 12, fontWeight: '600', flex: 1 },
  searchBox: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#0d1117', borderRadius: 8, paddingHorizontal: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)' },
  searchInput: { flex: 1, color: '#fff', paddingVertical: 9, fontSize: 13 },
  suggestions: { backgroundColor: '#0d1117', borderRadius: 8, marginTop: 4, borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)', overflow: 'hidden' },
  suggestion: { paddingVertical: 9, paddingHorizontal: 12, borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.05)' },
  suggestionText: { color: '#fff', fontSize: 13 },
  miniInput: { backgroundColor: '#0d1117', color: '#fff', borderRadius: 8, padding: 10, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)', fontSize: 13 },
  subtotalText: { color: '#10b981', fontSize: 11, marginTop: 5, textAlign: 'right' },
});
