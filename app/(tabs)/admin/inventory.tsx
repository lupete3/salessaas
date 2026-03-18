import React, { useEffect, useState, useMemo } from 'react';
import {
  View, Text, StyleSheet, FlatList, SafeAreaView, TouchableOpacity, ActivityIndicator, Alert, TextInput,
  Platform, StatusBar
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import axios from 'axios';
import { useAuthStore } from '../../../store/authStore';
import { useLangStore } from '../../../store/langStore';

interface InventoryItem {
  id: number;
  name: string;
  stock: number;
  buying_price: number;
  selling_price: number;
  total_valuation: number;
}

export default function InventoryScreen() {
  const { t } = useLangStore();
  const { apiUrl, token, store } = useAuthStore();
  const currency = store?.currency || 'CDF';
  const [inventory, setInventory] = useState<InventoryItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');

  const fetchInventory = async () => {
    setLoading(true);
    try {
      const res = await axios.get(`${apiUrl}/api/admin/inventories`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setInventory(res.data.inventory);
    } catch (error) {
      Alert.alert(t('shared.error'), t('admin.inventory_error'));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchInventory();
  }, []);

  const filteredInventory = useMemo(() => {
    if (!search) return inventory;
    return inventory.filter(item => 
      item.name.toLowerCase().includes(search.toLowerCase())
    );
  }, [inventory, search]);

  const totalValuation = useMemo(() => {
    return inventory.reduce((sum, item) => sum + item.total_valuation, 0);
  }, [inventory]);

  const renderItem = ({ item }: { item: InventoryItem }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <Text style={styles.name}>{item.name}</Text>
        <View style={[styles.stockBadge, { backgroundColor: (item.stock ?? 0) <= 0 ? 'rgba(239,68,68,0.1)' : 'rgba(16,185,129,0.1)' }]}>
           <Text style={[styles.stockText, { color: (item.stock ?? 0) <= 0 ? '#ef4444' : '#10b981' }]}>
             {t('pos.stock')}: {item.stock ?? 0}
           </Text>
        </View>
      </View>
      
      <View style={styles.pricesRow}>
        <View style={styles.priceCol}>
          <Text style={styles.priceLabel}>{t('admin.buying_price')}</Text>
          <Text style={styles.priceValue}>{parseFloat(String(item.buying_price || 0)).toFixed(2)}</Text>
        </View>
        <View style={styles.priceCol}>
          <Text style={styles.priceLabel}>{t('admin.selling_price')}</Text>
          <Text style={styles.priceValue}>{parseFloat(String(item.selling_price || 0)).toFixed(2)}</Text>
        </View>
        <View style={styles.priceCol}>
          <Text style={styles.priceLabel}>{t('admin.total_valuation')}</Text>
          <Text style={[styles.priceValue, { color: '#10b981' }]}>{parseFloat(String(item.total_valuation || 0)).toFixed(2)}</Text>
        </View>
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>{t('admin.inventory_title')}</Text>
          <Text style={styles.headerSubtitle}>{t('pos.total')}: {totalValuation.toFixed(2)} {currency}</Text>
        </View>
        <TouchableOpacity onPress={fetchInventory} disabled={loading}>
          <Ionicons name="refresh" size={24} color="#10b981" />
        </TouchableOpacity>
      </View>

      <View style={styles.searchBar}>
        <Ionicons name="search" size={18} color="#666" style={styles.searchIcon} />
        <TextInput
          style={styles.searchInput}
          placeholder={t('pos.search_placeholder')}
          placeholderTextColor="#666"
          value={search}
          onChangeText={setSearch}
        />
      </View>

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color="#10b981" />
        </View>
      ) : (
        <FlatList
          data={filteredInventory}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.list}
          ListEmptyComponent={<Text style={styles.empty}>{t('admin.no_products_found')}</Text>}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1, backgroundColor: '#0d1117',
    paddingTop: Platform.OS === 'android' ? StatusBar.currentHeight : 0,
  },
  header: { 
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', 
    padding: 20, borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.08)' 
  },
  headerTitle: { color: '#fff', fontSize: 20, fontWeight: 'bold' },
  headerSubtitle: { color: '#10b981', fontSize: 13, marginTop: 4, fontWeight: '600' },
  center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  searchBar: { 
    flexDirection: 'row', alignItems: 'center', backgroundColor: '#161b22', 
    margin: 16, paddingHorizontal: 16, borderRadius: 10, borderWidth: 1, 
    borderColor: 'rgba(255,255,255,0.08)' 
  },
  searchIcon: { marginRight: 10 },
  searchInput: { flex: 1, color: '#fff', paddingVertical: 12, fontSize: 15 },
  list: { padding: 16, paddingTop: 0 },
  card: { 
    backgroundColor: '#161b22', borderRadius: 12, padding: 16, 
    marginBottom: 12, borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)' 
  },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  name: { color: '#fff', fontSize: 16, fontWeight: 'bold', flex: 1 },
  stockBadge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
  stockText: { fontSize: 11, fontWeight: 'bold' },
  pricesRow: { flexDirection: 'row', justifyContent: 'space-between', borderTopWidth: 1, borderTopColor: 'rgba(255,255,255,0.05)', paddingTop: 12 },
  priceCol: { alignItems: 'flex-start' },
  priceLabel: { color: '#666', fontSize: 10, textTransform: 'uppercase', marginBottom: 4 },
  priceValue: { color: '#fff', fontSize: 13, fontWeight: '600' },
  empty: { color: '#666', textAlign: 'center', marginTop: 40 },
});
