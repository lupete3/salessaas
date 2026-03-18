import React, { useState, useMemo } from 'react';
import {
  View, Text, FlatList, TextInput, TouchableOpacity,
  StyleSheet, SafeAreaView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuthStore } from '../../store/authStore';
import { useLangStore } from '../../store/langStore';
import { useAppStore, Product } from '../../store/appStore';

export default function ProductsScreen() {
  const { t } = useLangStore();
  const { products } = useAppStore();
  const { store } = useAuthStore();
  const currency = store?.currency || 'CDF';
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState<'all' | 'low' | 'out'>('all');

  const filtered = useMemo(() => {
    let list = products.filter((p) =>
      p.name.toLowerCase().includes(search.toLowerCase()) ||
      (p.barcode ?? '').includes(search)
    );
    if (filter === 'low')  list = list.filter((p) => p.stock > 0 && p.stock <= p.min_stock);
    if (filter === 'out')  list = list.filter((p) => p.stock <= 0);
    return list.sort((a, b) => a.name.localeCompare(b.name));
  }, [products, search, filter]);

  const stockColor = (qty: number, minQty: number) => {
    if (qty <= 0)  return '#e74c3c';
    if (qty <= minQty)  return '#f39c12';
    return '#2ecc71';
  };

  const renderItem = ({ item }: { item: Product }) => (
    <View style={styles.card}>
      <View style={styles.cardLeft}>
        <Text style={styles.name} numberOfLines={2}>{item.name}</Text>
        {item.description ? <Text style={styles.sci}>{item.description}</Text> : null}
        <View style={styles.tags}>
        </View>
      </View>
      <View style={styles.cardRight}>
        <Text style={styles.price}>{parseFloat(item.selling_price).toFixed(2)} {currency}</Text>
        <View style={[styles.stockBadge, { backgroundColor: stockColor(item.stock, item.min_stock) + '22' }]}>
          <Text style={[styles.stockText, { color: stockColor(item.stock, item.min_stock) }]}>
            {item.stock} {item.unit}
          </Text>
        </View>
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      {/* Search */}
      <View style={styles.searchRow}>
        <Ionicons name="search-outline" size={18} color="#888" style={{ marginRight: 8 }} />
        <TextInput
          style={styles.searchInput}
          placeholder={t('explore.search_placeholder')}
          placeholderTextColor="#666"
          value={search}
          onChangeText={setSearch}
        />
      </View>

      {/* Filters */}
      <View style={styles.filterRow}>
        {(['all', 'low', 'out'] as const).map((f) => (
          <TouchableOpacity
            key={f}
            style={[styles.filterBtn, filter === f && styles.filterBtnActive]}
            onPress={() => setFilter(f)}
          >
            <Text style={[styles.filterText, filter === f && styles.filterTextActive]}>
              {f === 'all' ? t('explore.filter_all') : f === 'low' ? t('explore.filter_low') : t('explore.filter_out')}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      {/* Stats */}
      <View style={styles.statsRow}>
        <Text style={styles.statsText}>{t('explore.count_products', { count: filtered.length })}</Text>
        <Text style={styles.statsText}>
          {t('explore.count_out', { count: products.filter(p => p.stock <= 0).length })}
        </Text>
      </View>

      <FlatList
        data={filtered}
        keyExtractor={(p) => String(p.id)}
        renderItem={renderItem}
        ListEmptyComponent={<Text style={styles.empty}>{t('explore.no_product')}</Text>}
        contentContainerStyle={{ paddingBottom: 20 }}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#0d1117' },
  searchRow: {
    flexDirection: 'row', alignItems: 'center',
    backgroundColor: '#161b22', margin: 12, borderRadius: 10,
    paddingHorizontal: 12, paddingVertical: 10,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)',
  },
  searchInput: { flex: 1, color: '#fff', fontSize: 15 },
  filterRow: { flexDirection: 'row', paddingHorizontal: 12, gap: 8, marginBottom: 8 },
  filterBtn: {
    paddingHorizontal: 12, paddingVertical: 7, borderRadius: 20,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)',
  },
  filterBtnActive: { backgroundColor: '#10b981', borderColor: '#10b981' },
  filterText: { color: '#888', fontSize: 12 },
  filterTextActive: { color: '#fff', fontWeight: 'bold' },
  statsRow: {
    flexDirection: 'row', justifyContent: 'space-between',
    paddingHorizontal: 16, marginBottom: 8,
  },
  statsText: { color: '#666', fontSize: 12 },
  card: {
    flexDirection: 'row', backgroundColor: '#161b22',
    marginHorizontal: 12, marginVertical: 5, borderRadius: 12,
    padding: 14, borderWidth: 1, borderColor: 'rgba(255,255,255,0.07)',
  },
  cardLeft: { flex: 1 },
  cardRight: { alignItems: 'flex-end', justifyContent: 'space-between', minWidth: 80 },
  name: { color: '#fff', fontWeight: '600', fontSize: 14, marginBottom: 2 },
  sci: { color: '#888', fontSize: 11, fontStyle: 'italic', marginBottom: 4 },
  tags: { flexDirection: 'row', flexWrap: 'wrap', gap: 4 },
  tag: {
    backgroundColor: 'rgba(16,185,129,0.15)', color: '#6ee7b7',
    fontSize: 11, paddingHorizontal: 6, paddingVertical: 2, borderRadius: 4,
  },
  tagRx: { backgroundColor: 'rgba(231,76,60,0.15)', color: '#e74c3c' },
  price: { color: '#10b981', fontWeight: 'bold', fontSize: 15, marginBottom: 6 },
  stockBadge: { borderRadius: 8, paddingHorizontal: 8, paddingVertical: 3 },
  stockText: { fontWeight: '700', fontSize: 12 },
  empty: { color: '#666', textAlign: 'center', marginTop: 60, fontSize: 15 },
});
