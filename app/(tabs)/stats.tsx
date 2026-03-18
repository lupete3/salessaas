import React, { useMemo, useState } from 'react';
import {
  View, Text, StyleSheet, ScrollView, SafeAreaView, TouchableOpacity, TextInput, Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import axios from 'axios';
import * as Print from 'expo-print';
import * as Sharing from 'expo-sharing';
import { useAppStore, LocalSale } from '../../store/appStore';
import { useAuthStore } from '../../store/authStore';
import { useLangStore } from '../../store/langStore';

export default function StatisticsScreen() {
  const { t } = useLangStore();
  const { offlineQueue, syncedSales, expenses, customers, products } = useAppStore();
  const { store, isAdmin, token, apiUrl } = useAuthStore();
  const { lang } = useLangStore();
  const currency = store?.currency ?? 'CDF';
  const locale = lang === 'fr' ? 'fr-FR' : 'en-US';

  const [adminStats, setAdminStats] = useState<any>(null);
  const [loadingStats, setLoadingStats] = useState(false);

  // Filters
  const [historySearch, setHistorySearch] = useState('');
  const [dateFilter, setDateFilter] = useState<'today' | 'week' | 'all'>('today');

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

  const fetchAdminStats = async () => {
    if (!isAdmin) return;
    setLoadingStats(true);
    try {
      const res = await axios.get(`${apiUrl}/api/admin/stats`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setAdminStats(res.data);
    } catch (error) {
      console.error('Stats fetch error:', error);
    } finally {
      setLoadingStats(false);
    }
  };

  React.useEffect(() => {
    if (isAdmin) fetchAdminStats();
  }, [isAdmin]);

  const filteredHistory = useMemo(() => {
    let list = [...allSales].sort((a, b) => new Date(b.sold_at).getTime() - new Date(a.sold_at).getTime());

    // Date filter
    const now = new Date();
    if (dateFilter === 'today') {
      const today = now.toISOString().split('T')[0];
      list = list.filter(s => s.sold_at.startsWith(today));
    } else if (dateFilter === 'week') {
      const sevenDaysAgo = new Date(now.setDate(now.getDate() - 7));
      list = list.filter(s => new Date(s.sold_at) >= sevenDaysAgo);
    }

    // Search filter (customer name or local_id)
    if (historySearch) {
      const q = historySearch.toLowerCase();
      list = list.filter(s => 
        (s.customer_name?.toLowerCase().includes(q)) || 
        s.local_id.toLowerCase().includes(q)
      );
    }

    return list;
  }, [allSales, dateFilter, historySearch]);

  // -- REPRINT LOGIC (Merged from index.tsx) --
  const generateReceiptHTML = (sale: LocalSale) => {
    return `
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8" />
          <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 0; margin: 0; color: #000; width: 100%; }
            .receipt-container { padding: 10px; width: 100%; box-sizing: border-box; }
            .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 10px; }
            .title { font-size: 18px; font-weight: bold; margin: 0; text-transform: uppercase; }
            .subtitle { font-size: 13px; margin: 4px 0 0 0; font-weight: bold; }
            .store-info { font-size: 11px; margin-top: 5px; line-height: 1.3; }
            .sale-info { font-size: 12px; margin-bottom: 15px; line-height: 1.4; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 12px; }
            th { text-align: left; border-bottom: 1px solid #000; padding: 6px 0; }
            td { padding: 6px 0; border-bottom: 1px dotted #eee; }
            .right { text-align: right; }
            .totals { margin-top: 10px; font-size: 12px; text-align: right; line-height: 1.5; }
            .grand-total { font-size: 16px; font-weight: bold; padding-top: 8px; border-top: 2px solid #000; margin-top: 8px; }
            .footer { text-align: center; font-size: 10px; color: #666; margin-top: 25px; border-top: 1px dashed #ccc; padding-top: 12px; }
            @media print { body { padding: 0; } }
          </style>
        </head>
        <body>
          <div class="receipt-container">
            <div class="header">
              ${store?.logo ? `<img src="${store.logo}" style="max-height: 60px; margin-bottom: 10px;" />` : ''}
              <h1 class="title">${store?.name ?? t('pos.receipt_title')}</h1>
              <p class="subtitle">${t('pos.receipt_subtitle')}</p>
              <div class="store-info">
                ${store?.address ? `<div>${store.address}</div>` : ''}
                ${store?.phone ? `<div>${t('pos.phone')}: ${store.phone}</div>` : ''}
                ${store?.email ? `<div>Email: ${store.email}</div>` : ''}
              </div>
            </div>
            
            <div class="sale-info">
              <div><strong>${t('pos.date')} :</strong> ${new Date(sale.sold_at).toLocaleString(lang === 'en' ? 'en-US' : 'fr-FR')}</div>
              <div><strong>${t('pos.ref')} :</strong> ${sale.local_id}</div>
              <div><strong>${t('pos.payment_method')} :</strong> ${t('pos.pay_methods.' + (sale.payment_method || 'cash').toLowerCase())}</div>
              ${sale.customer_name ? `
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dotted #eee;">
                  <strong>${t('pos.customer')} :</strong> ${sale.customer_name}<br/>
                  ${sale.customer_phone ? `<strong>${t('pos.phone')} :</strong> ${sale.customer_phone}` : ''}
                </div>
              ` : ''}
            </div>
            
            <table>
              <thead>
                <tr>
                  <th>${t('pos.designation')}</th>
                  <th class="right">${t('pos.qty')}</th>
                  <th class="right">${t('pos.total')}</th>
                </tr>
              </thead>
              <tbody>
                ${sale.items.map(i => `
                  <tr>
                    <td>${i.product_name}<br/><small style="color: #666">${parseFloat(String(i.unit_price || 0)).toFixed(2)} ${currency}</small></td>
                    <td class="right">${i.quantity}</td>
                    <td class="right">${parseFloat(String(i.subtotal || 0)).toFixed(2)}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
            
            <div class="totals">
              <div>${t('pos.subtotal')} : ${parseFloat(String(sale.total_amount || 0)).toFixed(2)} ${currency}</div>
              ${sale.discount > 0 ? `<div>${t('pos.discount')} : -${parseFloat(String(sale.discount || 0)).toFixed(2)} ${currency}</div>` : ''}
              <div class="grand-total">${t('pos.total').toUpperCase()} : ${parseFloat(String(sale.final_amount || 0)).toFixed(2)} ${currency}</div>
              <div style="margin-top: 12px; border-top: 1px dotted #eee; padding-top: 10px;">
                ${t('pos.received')} : ${parseFloat(String(sale.amount_paid || 0)).toFixed(2)} ${currency}<br/>
                ${sale.change_given > 0 ? `${t('pos.rendu')} : ${parseFloat(String(sale.change_given || 0)).toFixed(2)} ${currency}<br/>` : ''}
                ${(sale.final_amount - sale.amount_paid) > 0.01 ? `<strong>${t('pos.reste_dette')} : ${parseFloat(String(sale.final_amount - sale.amount_paid)).toFixed(2)} ${currency}</strong>` : ''}
              </div>
            </div>
            
            <div class="footer">
              <p>${t('pos.footer_msg')}</p>
              <p style="font-size: 10px; margin-top: 6px;">${t('pos.generated_by')}</p>
            </div>
          </div>
        </body>
      </html>
    `;
  };

  const handlePrint = async (sale: LocalSale) => {
    try {
      const html = generateReceiptHTML(sale);
      const { uri } = await Print.printToFileAsync({ html, width: 220 });
      await Print.printAsync({ uri });
    } catch (e) {
      Alert.alert(t('shared.error'), t('pos.print_error'));
    }
  };

  const handleSharePDF = async (sale: LocalSale) => {
    try {
      const html = generateReceiptHTML(sale);
      const { uri } = await Print.printToFileAsync({ html, width: 220 });
      await Sharing.shareAsync(uri, { UTI: '.pdf', mimeType: 'application/pdf' });
    } catch (e) {
      Alert.alert(t('shared.error'), t('pos.pdf_error'));
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.content}>
        
        {/* Daily Summary */}
        <View style={styles.heroRow}>
          <View style={styles.heroCard}>
            <Text style={styles.heroLabel}>
              {isAdmin ? t('stats.net_revenue') : t('stats.sales_today')}
            </Text>
            <Text style={[styles.heroValue, isAdmin && { color: '#10b981' }]}>
              {(isAdmin ? (adminStats?.profit ?? 0) : stats.revenueToday).toFixed(2)} {currency}
            </Text>
            <Text style={styles.heroSub}>
              {isAdmin 
                ? `${t('pos.total')}: ${adminStats?.revenue?.toFixed(2) ?? '0.00'}` 
                : t('stats.count_transactions', { count: stats.salesCountToday })}
            </Text>
          </View>
        </View>

        {isAdmin && (
          <View style={styles.tilesRow}>
            <View style={[styles.tile, { borderLeftColor: '#10b981' }]}>
              <Ionicons name="analytics-outline" size={24} color="#10b981" />
              <Text style={styles.tileValue}>{adminStats?.profit?.toFixed(2) ?? '0'}</Text>
              <Text style={styles.tileLabel}>{t('stats.net_profit')}</Text>
            </View>
            <View style={[styles.tile, { borderLeftColor: '#3498db' }]}>
              <Ionicons name="cube-outline" size={24} color="#3498db" />
              <Text style={styles.tileValue}>{adminStats?.stock_valuation?.toLocaleString() ?? '0'}</Text>
              <Text style={styles.tileLabel}>{t('stats.stock_value')}</Text>
            </View>
          </View>
        )}

        {/* Financial Overview Tiles */}
        <View style={styles.tilesRow}>
          <View style={[styles.tile, { borderLeftColor: '#e74c3c' }]}>
            <Ionicons name="people-outline" size={24} color="#e74c3c" />
            <Text style={styles.tileValue}>{stats.totalDebt.toFixed(2)}</Text>
            <Text style={styles.tileLabel}>{t('customers.debt_total')}</Text>
          </View>
          <View style={[styles.tile, { borderLeftColor: '#f39c12' }]}>
            <Ionicons name="wallet-outline" size={24} color="#f39c12" />
            <Text style={styles.tileValue}>{stats.totalExpenses.toFixed(2)}</Text>
            <Text style={styles.tileLabel}>{t('expenses.total')}</Text>
          </View>
        </View>

        {/* Sale History Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>📜 {t('stats.sales_history')}</Text>
          
          {/* Filters */}
          <View style={styles.filterBar}>
            <TextInput
              style={styles.historySearch}
              placeholder={t('stats.search_placeholder')}
              placeholderTextColor="#666"
              value={historySearch}
              onChangeText={setHistorySearch}
            />
          </View>
          <View style={styles.dateTabs}>
            {(['today', 'week', 'all'] as const).map(d => (
              <TouchableOpacity 
                key={d} 
                style={[styles.dateTab, dateFilter === d && styles.dateTabActive]}
                onPress={() => setDateFilter(d)}
              >
                <Text style={[styles.dateTabText, dateFilter === d && styles.dateTabTextActive]}>
                  {d === 'today' ? t('stats.tabs.today') : d === 'week' ? t('stats.tabs.7days') : t('stats.tabs.all')}
                </Text>
              </TouchableOpacity>
            ))}
          </View>

          {filteredHistory.map((s) => (
            <View key={s.local_id} style={styles.historyRow}>
              <View style={styles.historyInfo}>
                <Text style={styles.historyClient}>{s.customer_name || t('pos.anonymous')}</Text>
                <Text style={styles.historyMeta}>
                  {new Date(s.sold_at).toLocaleDateString(locale, { day: '2-digit', month: '2-digit' })} · 
                  {new Date(s.sold_at).toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit' })} · 
                  {t('pos.pay_methods.' + (s.payment_method || 'cash').toLowerCase())} · 
                  {!s.is_synced && <Text style={{color: '#e67e22'}}>{t('stats.sync_pending')}</Text>}
                </Text>
              </View>
              <View style={styles.historyRight}>
                <Text style={styles.historyAmount}>{s.final_amount.toFixed(2)} {currency}</Text>
                <View style={styles.historyActions}>
                  <TouchableOpacity onPress={() => handlePrint(s)} style={styles.actionBtn}>
                    <Ionicons name="print-outline" size={18} color="#10b981" />
                  </TouchableOpacity>
                  <TouchableOpacity onPress={() => handleSharePDF(s)} style={styles.actionBtn}>
                    <Ionicons name="share-outline" size={18} color="#3498db" />
                  </TouchableOpacity>
                </View>
              </View>
            </View>
          ))}
          {filteredHistory.length === 0 && (
            <Text style={styles.empty}>{t('stats.no_sales_found')}</Text>
          )}
        </View>

        {/* Top Products Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>🏆 {t('stats.top_products')}</Text>
          {stats.topProducts.map((p, idx) => (
            <View key={idx} style={styles.listRow}>
              <View style={styles.rankBadge}>
                <Text style={styles.rankText}>{idx + 1}</Text>
              </View>
              <Text style={styles.listName} numberOfLines={1}>{p.name}</Text>
              <Text style={styles.listQty}>{t('stats.sold_count', { count: p.qty })}</Text>
            </View>
          ))}
          {stats.topProducts.length === 0 && (
            <Text style={styles.empty}>{t('stats.no_data')}</Text>
          )}
        </View>

        {/* Inventory Alert */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>⚠️ {t('stats.stock_alerts')}</Text>
          {products.filter(p => p.stock <= p.min_stock).slice(0, 5).map((p) => (
            <View key={p.id} style={styles.listRow}>
              <Ionicons name="warning" size={16} color="#e67e22" style={{marginRight: 8}} />
              <Text style={styles.listName} numberOfLines={1}>{p.name}</Text>
              <Text style={[styles.listQty, { color: '#e67e22' }]}>{t('stats.stock_remaining', { count: p.stock })}</Text>
            </View>
          ))}
          {products.filter(p => p.stock <= p.min_stock).length === 0 && (
            <Text style={styles.empty}>{t('stats.stock_all_good')}</Text>
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
  
  // History Styles
  filterBar: { marginBottom: 12 },
  historySearch: {
    backgroundColor: 'rgba(255,255,255,0.03)', borderRadius: 8, padding: 10,
    color: '#fff', fontSize: 14, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)',
  },
  dateTabs: { flexDirection: 'row', gap: 8, marginBottom: 16 },
  dateTab: {
    flex: 1, paddingVertical: 8, borderRadius: 8, backgroundColor: 'rgba(255,255,255,0.05)',
    alignItems: 'center',
  },
  dateTabActive: { backgroundColor: 'rgba(16,185,129,0.2)' },
  dateTabText: { color: '#888', fontSize: 12, fontWeight: '600' },
  dateTabTextActive: { color: '#10b981' },
  
  historyRow: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.05)',
  },
  historyInfo: { flex: 1 },
  historyClient: { color: '#fff', fontWeight: '600', fontSize: 14, marginBottom: 2 },
  historyMeta: { color: '#666', fontSize: 11 },
  historyRight: { alignItems: 'flex-end' },
  historyAmount: { color: '#10b981', fontWeight: 'bold', fontSize: 14, marginBottom: 6 },
  historyActions: { flexDirection: 'row', gap: 12 },
  actionBtn: { padding: 4 },

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
