import React, { useMemo } from 'react';
import {
  View, Text, StyleSheet, ScrollView, SafeAreaView,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppStore } from '../../store/appStore';
import { useAuthStore } from '../../store/authStore';

export default function StatisticsScreen() {
  const { offlineQueue, syncedSales, expenses, customers, products } = useAppStore();
  const { store } = useAuthStore();
  const currency = store?.currency ?? 'CDF';

  const allSales = useMemo(() => [...offlineQueue, ...syncedSales], [offlineQueue, syncedSales]);

  // Calculations
  const stats = useMemo(() => {
    const today = new Date().toISOString().split('T')[0];
    const salesToday = allSales.filter(s => s.sold_at.startsWith(today));
    const revenueToday = salesToday.reduce((sum, s) => sum + s.total_amount, 0);
    
    const totalDebt = customers.reduce((sum, c) => sum + (c.total_debt || 0), 0);
    const totalExpenses = expenses.reduce((sum, e) => sum + (e.amount || 0), 0);

    // Top Products
    const productSales: { [id: number]: { name: string, qty: number } } = {};
    allSales.forEach(s => {
      s.items.forEach(i => {
        if (!productSales[i.product_id]) {
          productSales[i.product_id] = { name: i.product_name, qty: 0 };
        }
        productSales[i.product_id].qty += i.quantity;
      });
    });

    const topProducts = Object.values(productSales)
      .sort((a, b) => b.qty - a.qty)
      .slice(0, 5);

    return {
      revenueToday,
      salesCountToday: salesToday.length,
      totalDebt,
      totalExpenses,
      topProducts
    };
  }, [allSales, customers, expenses]);

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.content}>
        
        {/* Daily Summary */}
        <View style={styles.heroRow}>
          <View style={styles.heroCard}>
            <Text style={styles.heroLabel}>Ventes Aujourd'hui</Text>
            <Text style={styles.heroValue}>{stats.revenueToday.toFixed(2)} {currency}</Text>
            <Text style={styles.heroSub}>{stats.salesCountToday} transactions</Text>
          </View>
        </View>

        {/* Financial Overview Tiles */}
        <View style={styles.tilesRow}>
          <View style={[styles.tile, { borderLeftColor: '#e74c3c' }]}>
            <Ionicons name="people-outline" size={24} color="#e74c3c" />
            <Text style={styles.tileValue}>{stats.totalDebt.toFixed(2)}</Text>
            <Text style={styles.tileLabel}>Dettes Clients</Text>
          </View>
          <View style={[styles.tile, { borderLeftColor: '#f39c12' }]}>
            <Ionicons name="wallet-outline" size={24} color="#f39c12" />
            <Text style={styles.tileValue}>{stats.totalExpenses.toFixed(2)}</Text>
            <Text style={styles.tileLabel}>Total Dépenses</Text>
          </View>
        </View>

        {/* Top Products Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>🏆 Produits les plus vendus</Text>
          {stats.topProducts.map((p, idx) => (
            <View key={idx} style={styles.listRow}>
              <View style={styles.rankBadge}>
                <Text style={styles.rankText}>{idx + 1}</Text>
              </View>
              <Text style={styles.listName} numberOfLines={1}>{p.name}</Text>
              <Text style={styles.listQty}>{p.qty} vendus</Text>
            </View>
          ))}
          {stats.topProducts.length === 0 && (
            <Text style={styles.empty}>Aucune donnée de vente.</Text>
          )}
        </View>

        {/* Inventory Alert */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>⚠️ Alertes Stock</Text>
          {products.filter(p => p.stock <= p.min_stock).slice(0, 5).map((p) => (
            <View key={p.id} style={styles.listRow}>
              <Ionicons name="warning" size={16} color="#e67e22" style={{marginRight: 8}} />
              <Text style={styles.listName} numberOfLines={1}>{p.name}</Text>
              <Text style={[styles.listQty, { color: '#e67e22' }]}>{p.stock} restant</Text>
            </View>
          ))}
          {products.filter(p => p.stock <= p.min_stock).length === 0 && (
            <Text style={styles.empty}>Tout est en stock !</Text>
          )}
        </View>

      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#0d1117' },
  content: { padding: 16, paddingBottom: 40 },
  heroRow: { marginBottom: 16 },
  heroCard: {
    backgroundColor: '#161b22', borderRadius: 16, padding: 24,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)',
    alignItems: 'center',
  },
  heroLabel: { color: '#888', fontSize: 14, marginBottom: 8 },
  heroValue: { color: '#10b981', fontSize: 36, fontWeight: '800' },
  heroSub: { color: '#666', fontSize: 14, marginTop: 4 },
  tilesRow: { flexDirection: 'row', gap: 12, marginBottom: 16 },
  tile: {
    flex: 1, backgroundColor: '#161b22', borderRadius: 12, padding: 16,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)',
    borderLeftWidth: 4,
  },
  tileValue: { color: '#fff', fontSize: 20, fontWeight: 'bold', marginVertical: 4 },
  tileLabel: { color: '#888', fontSize: 12 },
  section: {
    backgroundColor: '#161b22', borderRadius: 14, padding: 16,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.08)', marginBottom: 16,
  },
  sectionTitle: { color: '#aaa', fontWeight: '700', fontSize: 13, marginBottom: 12, textTransform: 'uppercase' },
  listRow: {
    flexDirection: 'row', alignItems: 'center', paddingVertical: 10,
    borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.05)',
  },
  rankBadge: {
    backgroundColor: 'rgba(16,185,129,0.1)', width: 24, height: 24, borderRadius: 12,
    alignItems: 'center', justifyContent: 'center', marginRight: 12,
  },
  rankText: { color: '#10b981', fontSize: 12, fontWeight: 'bold' },
  listName: { color: '#fff', flex: 1, fontSize: 14 },
  listQty: { color: '#888', fontSize: 13 },
  empty: { color: '#666', fontSize: 13, textAlign: 'center', paddingVertical: 10 },
});
