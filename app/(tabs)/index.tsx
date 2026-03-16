import React, { useState, useMemo } from 'react';
import {
  View, Text, FlatList, TextInput, TouchableOpacity,
  StyleSheet, Alert, Modal, ScrollView, SafeAreaView,
  KeyboardAvoidingView, Platform, Keyboard, TouchableWithoutFeedback
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as Print from 'expo-print';
import * as Sharing from 'expo-sharing';
import { useAppStore, Product, LocalSale, Customer } from '../../store/appStore';
import { useAuthStore } from '../../store/authStore';
import { useLangStore } from '../../store/langStore';

type PaymentMethod = 'cash' | 'mobile_money' | 'insurance' | 'credit';

function genLocalId(): string {
  return `${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
}

export default function POSScreen() {
  const { t } = useLangStore();
  const { products, cart, addToCart, removeFromCart, updateCartQty, clearCart, cartTotal, queueSale, customers } = useAppStore();
  const { store } = useAuthStore();
  const [search, setSearch] = useState('');
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>('cash');
  const [discount, setDiscount] = useState('0');
  const [notes, setNotes] = useState('');
  const [checkoutVisible, setCheckoutVisible] = useState(false);
  const [cartModalVisible, setCartModalVisible] = useState(false);
  const [receiptVisible, setReceiptVisible] = useState(false);
  const [lastSale, setLastSale] = useState<LocalSale | null>(null);
  const [amountReceived, setAmountReceived] = useState('');

  // Customer selection
  const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);
  const [customerSearch, setCustomerSearch] = useState('');
  const [customerModalVisible, setCustomerModalVisible] = useState(false);

  const filteredCustomers = useMemo(() => {
    return customers.filter(c => 
      c.name.toLowerCase().includes(customerSearch.toLowerCase()) ||
      (c.phone && c.phone.includes(customerSearch))
    );
  }, [customers, customerSearch]);

  // Use the store's configured currency (set at login from the server)
  const currency = store?.currency || 'CDF';

  const filtered = useMemo(
    () =>
      products
        .filter((p) => p.stock > 0)
        .filter((p) =>
          p.name.toLowerCase().includes(search.toLowerCase()) ||
          (p.barcode ?? '').includes(search)
        ),
    [products, search]
  );

  const total = cartTotal();
  const discountAmt = parseFloat(discount) || 0;
  const finalAmount = Math.max(0, total - discountAmt);
  const changeGiven = Math.max(0, (parseFloat(amountReceived) || 0) - finalAmount);

  const handleAddToCart = (product: Product) => {
    const success = addToCart(product, 1);
    if (!success) {
      Alert.alert('Stock insuffisant', `Il ne reste que ${product.stock} unité(s) de ${product.name} en stock.`);
    }
  };

  const handleUpdateQty = (productId: number, currentQty: number, change: number) => {
    const newQty = currentQty + change;
    const item = cart.find(c => c.product.id === productId);
    if (!item) return;
    
    const success = updateCartQty(productId, newQty);
    if (!success && change > 0) {
       Alert.alert('Stock', `Maximum disponible : ${item.product.stock}`);
    }
  };

  const handleCheckout = () => {
    if (cart.length === 0) {
      Alert.alert('Panier vide', 'Ajoutez des produits avant de valider.');
      return;
    }
    setCartModalVisible(false);
    setTimeout(() => {
      setCheckoutVisible(true);
    }, 100);
  };

  const confirmSale = () => {
    const receivedVal = (amountReceived || '').trim();
    
    // Logic: 
    // - If it's a 'credit' sale and field is empty, assume 0 paid.
    // - If it's NOT a 'credit' sale and field is empty, assume paid in full.
    // - If field has a value, use it (handles "0" correctly).
    let amountPaidNum: number;
    if (receivedVal === '') {
      amountPaidNum = paymentMethod === 'credit' ? 0 : finalAmount;
    } else {
      // Use parseFloat but fallback to 0 if NaN helper
      const parsed = parseFloat(receivedVal.replace(',', '.')); // Handle comma decimals
      amountPaidNum = isNaN(parsed) ? 0 : parsed;
    }

    const debtAmount = Math.max(0, finalAmount - amountPaidNum);

    if (debtAmount > 0.009 && !selectedCustomer) {
      Alert.alert(
        'Client requis', 
        `Cette vente laisse un solde impayé de ${debtAmount.toFixed(2)} ${currency}. Veuillez sélectionner un client pour enregistrer cette dette.`
      );
      return;
    }

    const sale: LocalSale = {
      local_id: genLocalId(),
      sold_at: new Date().toISOString(),
      payment_method: paymentMethod,
      customer_uuid: selectedCustomer?.uuid || selectedCustomer?.local_id || undefined,
      customer_name: selectedCustomer?.name,
      customer_phone: selectedCustomer?.phone || undefined,
      notes: notes || '',
      discount: discountAmt || 0,
      amount_paid: amountPaidNum,
      change_given: Math.max(0, amountPaidNum - finalAmount),
      total_amount: total,
      final_amount: finalAmount,
      is_synced: false,
      items: cart.map((c) => ({
        product_id: c.product.id,
        product_name: c.product.name,
        quantity: c.quantity,
        unit_price: c.unit_price,
        discount: 0,
        subtotal: c.subtotal,
      })),
    };

    queueSale(sale);
    setLastSale(sale);
    clearCart();
    setDiscount('0');
    setAmountReceived('');
    setNotes('');
    setSelectedCustomer(null);
    setCheckoutVisible(false);
    setReceiptVisible(true);
  };

  // -- PRINT & PDF GENERATION --
  const generateReceiptHTML = (sale: LocalSale) => {
    const itemsHtml = sale.items.map(i => `
      <tr>
        <td>${i.product_name}</td>
        <td class="right">${i.quantity}</td>
        <td class="right">${parseFloat(i.unit_price as any).toFixed(2)}</td>
        <td class="right">${parseFloat(i.subtotal as any).toFixed(2)}</td>
      </tr>
    `).join('');

    return `
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8" />
          <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 10px; color: #111; max-width: 320px; margin: 0 auto; }
            .header { text-align: center; margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
            .title { font-size: 20px; font-weight: bold; margin: 0; text-transform: uppercase; }
            .subtitle { font-size: 14px; color: #444; margin: 5px 0 0 0; font-weight: bold; }
            .store-info { font-size: 11px; color: #555; margin-top: 5px; line-height: 1.3; }
            .sale-info { font-size: 12px; margin-bottom: 15px; line-height: 1.4; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px; }
            th { text-align: left; border-bottom: 1px solid #333; padding: 5px 0; }
            td { padding: 6px 0; border-bottom: 1px solid #eee; }
            .right { text-align: right; }
            .totals { margin-top: 10px; font-size: 13px; text-align: right; line-height: 1.5; }
            .grand-total { font-size: 18px; font-weight: bold; padding-top: 8px; border-top: 1px solid #333; margin-top: 8px; }
            .footer { text-align: center; font-size: 11px; color: #777; margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 15px; }
            @media print { body { padding: 0; } }
          </style>
        </head>
        <body>
          <div class="header">
            ${store?.logo ? `<img src="${store.logo}" style="max-height: 60px; margin-bottom: 10px;" />` : ''}
            <h1 class="title">${store?.name ?? 'NOTRE MAGASIN'}</h1>
            <p class="subtitle">REÇU DE CAISSE</p>
            <div class="store-info">
              ${store?.address ? `<div>${store.address}</div>` : ''}
              ${store?.phone ? `<div>Tél: ${store.phone}</div>` : ''}
              ${store?.email ? `<div>Email: ${store.email}</div>` : ''}
              ${store?.license_number ? `<div>ID: ${store.license_number}</div>` : ''}
            </div>
          </div>
          
          <div class="sale-info">
            <div><strong>Date :</strong> ${new Date(sale.sold_at).toLocaleString('fr-FR')}</div>
            <div><strong>N° Réf :</strong> ${sale.local_id}</div>
            <div><strong>Paiement :</strong> ${sale.payment_method.toUpperCase()}</div>
            ${sale.customer_name ? `
              <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dotted #ccc;">
                <strong>Client :</strong> ${sale.customer_name}<br/>
                ${sale.customer_phone ? `<strong>Tél :</strong> ${sale.customer_phone}` : ''}
              </div>
            ` : ''}
          </div>
          
          <table>
            <thead>
              <tr>
                <th>Désignation</th>
                <th class="right">Qté</th>
                <th class="right">Total</th>
              </tr>
            </thead>
            <tbody>
              ${sale.items.map(i => `
                <tr>
                  <td>${i.product_name}<br/><small>${parseFloat(i.unit_price as any).toFixed(2)}</small></td>
                  <td class="right">${i.quantity}</td>
                  <td class="right">${parseFloat(i.subtotal as any).toFixed(2)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
          
          <div class="totals">
            <div>Sous-total : ${sale.total_amount.toFixed(2)} ${currency}</div>
            ${sale.discount > 0 ? `<div>Remise : -${sale.discount.toFixed(2)} ${currency}</div>` : ''}
            <div class="grand-total">TOTAL : ${sale.final_amount.toFixed(2)} ${currency}</div>
            <div style="margin-top: 8px;">
              Reçu : ${sale.amount_paid.toFixed(2)} ${currency}<br/>
              ${sale.change_given > 0 ? `Rendu : ${sale.change_given.toFixed(2)} ${currency}` : ''}
              ${(sale.final_amount - sale.amount_paid) > 0.01 ? `<strong>Reste (Dette) : ${(sale.final_amount - sale.amount_paid).toFixed(2)} ${currency}</strong>` : ''}
            </div>
          </div>
          
          <div class="footer">
            <p>Merci de votre visite et à bientôt !</p>
            <p style="font-size: 9px; margin-top: 4px;">Facture générée par SalesSaaS</p>
          </div>
        </body>
      </html>
    `;
  };

  const handlePrint = async () => {
    if (!lastSale) return;
    try {
      const html = generateReceiptHTML(lastSale);
      await Print.printAsync({ html });
    } catch (e) {
      Alert.alert('Erreur', 'Impossible d\'imprimer le reçu.');
    }
  };

  const handleSharePDF = async () => {
    if (!lastSale) return;
    try {
      const html = generateReceiptHTML(lastSale);
      const { uri } = await Print.printToFileAsync({ html, width: 280 });
      const isAvailable = await Sharing.isAvailableAsync();
      if (isAvailable) {
        await Sharing.shareAsync(uri, { UTI: '.pdf', mimeType: 'application/pdf', dialogTitle: 'Partager le reçu' });
      } else {
        Alert.alert('Oups', 'Le partage n\'est pas disponible sur cet appareil.');
      }
    } catch (e) {
      Alert.alert('Erreur', 'Impossible de générer le PDF.');
    }
  };
  // -- END PRINT --

  const renderProduct = ({ item }: { item: Product }) => {
    const inCart = cart.find((c) => c.product.id === item.id);
    return (
      <TouchableOpacity style={styles.productCard} onPress={() => handleAddToCart(item)}>
        <View style={styles.productInfo}>
          <Text style={styles.productName} numberOfLines={2}>{item.name}</Text>
          <Text style={styles.productSub}>{item.description || 'Produit'} · {item.unit}</Text>
          <Text style={styles.productStock}>Stock: {item.stock}</Text>
        </View>
        <View style={styles.productRight}>
          <Text style={styles.productPrice}>{parseFloat(item.selling_price).toFixed(2)} {currency}</Text>
          {inCart ? (
            <View style={styles.qtyBadge}>
              <Text style={styles.qtyBadgeText}>×{inCart.quantity}</Text>
            </View>
          ) : (
            <View style={styles.addBtn}>
              <Ionicons name="add" size={18} color="#fff" />
            </View>
          )}
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Search */}
      <View style={styles.searchRow}>
        <Ionicons name="search-outline" size={18} color="#888" style={{ marginRight: 8 }} />
        <TextInput
          style={styles.searchInput}
          placeholder={t('pos.search')}
          placeholderTextColor="#666"
          value={search}
          onChangeText={setSearch}
        />
      </View>

      <View style={styles.body}>
        {/* Product List */}
        <FlatList
          data={filtered}
          keyExtractor={(p) => String(p.id)}
          renderItem={renderProduct}
          style={styles.productList}
          contentContainerStyle={{ paddingBottom: 100 }}
          ListEmptyComponent={<Text style={styles.empty}>Aucun produit disponible.</Text>}
        />
      </View>

      {/* Floating Cart Bar (Mobile Friendly) */}
      {cart.length > 0 && (
        <View style={styles.floatingCartBar}>
          <TouchableOpacity style={styles.floatingCartContent} onPress={() => setCartModalVisible(true)}>
            <View style={styles.floatingCartLeft}>
              <View style={styles.floatingCartIconWrap}>
                <Ionicons name="cart" size={24} color="#fff" />
                <View style={styles.floatingCartBadge}>
                  <Text style={styles.floatingCartBadgeText}>{cart.length}</Text>
                </View>
              </View>
              <Text style={styles.floatingCartText}>{t('pos.cart')}</Text>
            </View>
            <View style={styles.floatingCartRight}>
              <Text style={styles.floatingCartTotal}>{total.toFixed(2)} {currency}</Text>
              <Ionicons name="chevron-up" size={20} color="#fff" />
            </View>
          </TouchableOpacity>
        </View>
      )}

      {/* Cart Modal */}
      <Modal visible={cartModalVisible} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.cartModalCard}>
            <View style={styles.cartModalHeader}>
              <Text style={styles.cartModalTitle}>Panier ({cart.length})</Text>
              <TouchableOpacity onPress={() => setCartModalVisible(false)} style={styles.closeBtn}>
                <Ionicons name="close" size={24} color="#aaa" />
              </TouchableOpacity>
            </View>

            <ScrollView style={{ maxHeight: 300, marginBottom: 16 }}>
              {cart.map((item) => (
                <View key={item.product.id} style={styles.cartModalItem}>
                  <View style={styles.cartModalItemInfo}>
                    <Text style={styles.cartModalItemName} numberOfLines={1}>{item.product.name}</Text>
                    <Text style={styles.cartModalItemPrice}>{parseFloat(item.product.selling_price).toFixed(2)} {currency} / unité</Text>
                  </View>
                  <View style={styles.cartModalItemControls}>
                    <TouchableOpacity onPress={() => handleUpdateQty(item.product.id, item.quantity, -1)} style={styles.qtyBtn}>
                      <Ionicons name="remove" size={18} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.cartModalItemQty}>{item.quantity}</Text>
                    <TouchableOpacity onPress={() => handleUpdateQty(item.product.id, item.quantity, 1)} style={styles.qtyBtn}>
                      <Ionicons name="add" size={18} color="#fff" />
                    </TouchableOpacity>
                  </View>
                  <Text style={styles.cartModalItemSubtotal}>{item.subtotal.toFixed(2)}</Text>
                  <TouchableOpacity onPress={() => removeFromCart(item.product.id)} style={styles.trashBtn}>
                    <Ionicons name="trash-outline" size={20} color="#e74c3c" />
                  </TouchableOpacity>
                </View>
              ))}
              {cart.length === 0 && (
                <Text style={styles.empty}>Le panier est vide.</Text>
              )}
            </ScrollView>

            <View style={styles.cartModalFooter}>
              <View style={styles.totalRowFixed}>
                <Text style={styles.totalLabel}>Total :</Text>
                <Text style={styles.totalValueLG}>{total.toFixed(2)} {currency}</Text>
              </View>
              <TouchableOpacity style={styles.checkoutBtnLG} onPress={handleCheckout}>
                <Ionicons name="checkmark-circle-outline" size={22} color="#fff" />
                <Text style={styles.checkoutBtnTextLG}>Valider la vente</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Checkout Modal */}
      <Modal visible={checkoutVisible} animationType="slide" transparent>
        <KeyboardAvoidingView 
          behavior={Platform.OS === 'ios' ? 'padding' : 'padding'}
          style={{ flex: 1 }}
        >
          <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
            <View style={styles.modalOverlay}>
              <View style={styles.modalCard}>
                <ScrollView contentContainerStyle={{ paddingBottom: 40 }} showsVerticalScrollIndicator={false}>
                  <Text style={styles.modalTitle}>Confirmer la vente</Text>
                  <Text style={styles.modalTotal}>Total : ${total.toFixed(2)} ${currency}</Text>

            <Text style={styles.modalLabel}>Remise ({currency})</Text>
            <TextInput
              style={styles.modalInput}
              keyboardType="numeric"
              value={discount}
              onChangeText={setDiscount}
              placeholder="0"
              placeholderTextColor="#888"
            />
            
            <Text style={styles.modalLabel}>Montant Reçu ({currency})</Text>
            <TextInput
              style={styles.modalInput}
              keyboardType="numeric"
              value={amountReceived}
              onChangeText={setAmountReceived}
              placeholder="0"
              placeholderTextColor="#888"
            />

            <Text style={styles.modalFinal}>Montant final : {finalAmount.toFixed(2)} {currency}</Text>
            {changeGiven > 0 && <Text style={[styles.modalFinal, { backgroundColor: 'rgba(52,152,219,0.1)', color: '#3498db' }]}>Monnaie à rendre : {changeGiven.toFixed(2)} {currency}</Text>}
            {finalAmount > (parseFloat(amountReceived) || 0) && selectedCustomer && (
              <Text style={[styles.modalFinal, { backgroundColor: 'rgba(231,76,60,0.1)', color: '#e74c3c' }]}>
                Dette à enregistrer : {(finalAmount - (parseFloat(amountReceived) || 0)).toFixed(2)} {currency}
              </Text>
            )}

            <Text style={styles.modalLabel}>Mode de paiement</Text>
            <View style={styles.paymentMethods}>
              {(['cash', 'mobile_money', 'insurance', 'credit'] as PaymentMethod[]).map((pm) => (
                <TouchableOpacity
                  key={pm}
                  style={[styles.pmBtn, paymentMethod === pm && styles.pmBtnActive]}
                  onPress={() => setPaymentMethod(pm)}
                >
                  <Text style={[styles.pmBtnText, paymentMethod === pm && styles.pmBtnTextActive]}>
                    {pm === 'cash' ? '💵 Cash' : pm === 'mobile_money' ? '📱 Mobile' : pm === 'insurance' ? '🏥 Assurance' : '📋 Crédit'}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>

            <Text style={styles.modalLabel}>Client (Facultatif, requis pour crédit)</Text>
            <TouchableOpacity 
              style={styles.customerPicker} 
              onPress={() => setCustomerModalVisible(true)}
            >
              <Ionicons name="person-outline" size={18} color="#888" />
              <Text style={[styles.customerPickerText, !selectedCustomer && { color: '#666' }]}>
                {selectedCustomer ? selectedCustomer.name : 'Sélectionner un client...'}
              </Text>
              {selectedCustomer && (
                <TouchableOpacity onPress={() => setSelectedCustomer(null)}>
                  <Ionicons name="close-circle" size={18} color="#e74c3c" />
                </TouchableOpacity>
              )}
            </TouchableOpacity>

            <Text style={styles.modalLabel}>Notes</Text>
            <TextInput
              style={[styles.modalInput, { height: 60 }]}
              value={notes}
              onChangeText={setNotes}
              multiline
              placeholder="Remarques..."
              placeholderTextColor="#888"
            />

                  <View style={styles.modalButtons}>
                    <TouchableOpacity style={styles.cancelBtn} onPress={() => setCheckoutVisible(false)}>
                      <Text style={{ color: '#888', fontWeight: '600' }}>Annuler</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={styles.confirmBtn} onPress={confirmSale}>
                      <Text style={{ color: '#fff', fontWeight: 'bold' }}>Confirmer</Text>
                    </TouchableOpacity>
                  </View>
                </ScrollView>
              </View>
            </View>
          </TouchableWithoutFeedback>
        </KeyboardAvoidingView>
      </Modal>

      {/* Customer Selection Modal */}
      <Modal visible={customerModalVisible} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Choisir un client</Text>
              <TouchableOpacity onPress={() => setCustomerModalVisible(false)}>
                <Ionicons name="close" size={24} color="#aaa" />
              </TouchableOpacity>
            </View>
            
            <View style={styles.searchRow}>
              <Ionicons name="search-outline" size={18} color="#888" />
              <TextInput
                style={styles.searchInput}
                placeholder="Rechercher..."
                placeholderTextColor="#666"
                value={customerSearch}
                onChangeText={setCustomerSearch}
              />
            </View>

            <FlatList
              data={filteredCustomers}
              keyExtractor={(item) => item.local_id || String(item.id)}
              renderItem={({ item }) => (
                <TouchableOpacity 
                  style={styles.customerItem} 
                  onPress={() => {
                    setSelectedCustomer(item);
                    setCustomerModalVisible(false);
                  }}
                >
                  <Text style={styles.customerName}>{item.name}</Text>
                  {item.phone && <Text style={styles.customerPhone}>{item.phone}</Text>}
                </TouchableOpacity>
              )}
              style={{ maxHeight: 400 }}
              ListEmptyComponent={<Text style={styles.empty}>Aucun client trouvé.</Text>}
            />
          </View>
        </View>
      </Modal>

      {/* Receipt Success Options Modal */}
      <Modal visible={receiptVisible} animationType="fade" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.receiptCard}>
            <View style={styles.successIcon}>
              <Ionicons name="checkmark-circle" size={60} color="#2ecc71" />
            </View>
            <Text style={styles.receiptTitle}>Vente Réussie !</Text>
            <Text style={styles.receiptSub}>La vente a été enregistrée avec succès.</Text>

            <View style={styles.receiptActions}>
              <TouchableOpacity style={styles.printBtn} onPress={handlePrint}>
                <Ionicons name="print-outline" size={20} color="#fff" />
                <Text style={styles.printBtnText}>Imprimer</Text>
              </TouchableOpacity>
              
              <TouchableOpacity style={styles.pdfBtn} onPress={handleSharePDF}>
                <Ionicons name="document-text-outline" size={20} color="#1a73e8" />
                <Text style={styles.pdfBtnText}>Sauver PDF</Text>
              </TouchableOpacity>
            </View>

            <TouchableOpacity style={styles.nextCustomerBtn} onPress={() => setReceiptVisible(false)}>
              <Text style={styles.nextCustomerText}>Suivant</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
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
  body: { flex: 1, flexDirection: 'row' },
  productList: { flex: 1 },
  productCard: {
    flexDirection: 'row', backgroundColor: '#161b22',
    marginHorizontal: 8, marginVertical: 4, borderRadius: 10,
    padding: 12, borderWidth: 1, borderColor: 'rgba(255,255,255,0.07)',
  },
  productInfo: { flex: 1 },
  productName: { color: '#fff', fontWeight: '600', fontSize: 14, marginBottom: 3 },
  productSub: { color: '#888', fontSize: 12 },
  productStock: { color: '#5bc85b', fontSize: 11, marginTop: 2 },
  productRight: { alignItems: 'flex-end', justifyContent: 'space-between' },
  productPrice: { color: '#10b981', fontWeight: 'bold', fontSize: 14 },
  addBtn: {
    backgroundColor: '#10b981', borderRadius: 20,
    width: 32, height: 32, alignItems: 'center', justifyContent: 'center',
  },
  qtyBadge: {
    backgroundColor: '#10b981', borderRadius: 12,
    paddingHorizontal: 10, paddingVertical: 4,
  },
  qtyBadgeText: { color: '#fff', fontWeight: 'bold', fontSize: 13 },
  empty: { color: '#666', textAlign: 'center', marginTop: 40, fontSize: 15 },

  // Floating Cart Bar
  floatingCartBar: {
    position: 'absolute', bottom: 16, left: 16, right: 16,
    backgroundColor: '#10b981', borderRadius: 16,
    shadowColor: '#10b981', shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3, shadowRadius: 8, elevation: 8,
  },
  floatingCartContent: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    paddingHorizontal: 20, paddingVertical: 16,
  },
  floatingCartLeft: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  floatingCartIconWrap: { position: 'relative' },
  floatingCartBadge: {
    position: 'absolute', top: -8, right: -10,
    backgroundColor: '#e74c3c', borderRadius: 10, minWidth: 20, height: 20,
    justifyContent: 'center', alignItems: 'center', borderWidth: 2, borderColor: '#10b981',
  },
  floatingCartBadgeText: { color: '#fff', fontSize: 10, fontWeight: 'bold', paddingHorizontal: 4 },
  floatingCartText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },
  floatingCartRight: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  floatingCartTotal: { color: '#fff', fontWeight: '800', fontSize: 18 },

  // Cart Modal (Replacement for side panel)
  cartModalCard: {
    backgroundColor: '#161b22', borderTopLeftRadius: 24, borderTopRightRadius: 24,
    padding: 24, maxHeight: '80%', width: '100%',
  },
  cartModalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 },
  cartModalTitle: { color: '#fff', fontWeight: 'bold', fontSize: 20 },
  closeBtn: { padding: 4 },
  cartModalItem: {
    flexDirection: 'row', alignItems: 'center',
    paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.06)',
  },
  cartModalItemInfo: { flex: 1, paddingRight: 8 },
  cartModalItemName: { color: '#fff', fontSize: 15, fontWeight: '600', marginBottom: 4 },
  cartModalItemPrice: { color: '#888', fontSize: 12 },
  cartModalItemControls: { flexDirection: 'row', alignItems: 'center', backgroundColor: 'rgba(255,255,255,0.05)', borderRadius: 8, padding: 4, marginRight: 12 },
  qtyBtn: {
    backgroundColor: 'rgba(255,255,255,0.1)', borderRadius: 6,
    width: 28, height: 28, alignItems: 'center', justifyContent: 'center',
  },
  cartModalItemQty: { color: '#fff', fontWeight: 'bold', width: 30, textAlign: 'center', fontSize: 15 },
  cartModalItemSubtotal: { color: '#10b981', fontWeight: 'bold', fontSize: 15, width: 60, textAlign: 'right' },
  trashBtn: { padding: 8, marginLeft: 4 },
  cartModalFooter: { borderTopWidth: 1, borderTopColor: 'rgba(255,255,255,0.1)', paddingTop: 16 },
  totalRowFixed: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: 16 },
  totalLabel: { color: '#aaa', fontWeight: '600', fontSize: 16 },
  totalValueLG: { color: '#fff', fontWeight: 'bold', fontSize: 24 },
  checkoutBtnLG: {
    flexDirection: 'row', backgroundColor: '#10b981',
    padding: 16, borderRadius: 12, alignItems: 'center',
    justifyContent: 'center', gap: 8,
  },
  checkoutBtnTextLG: { color: '#fff', fontWeight: 'bold', fontSize: 16 },

  // Checkout Modal
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.7)', justifyContent: 'flex-end' },
  modalCard: {
    backgroundColor: '#161b22', borderTopLeftRadius: 24, borderTopRightRadius: 24,
    padding: 24, width: '100%',
  },
  modalTitle: { color: '#fff', fontWeight: 'bold', fontSize: 20, marginBottom: 4 },
  modalTotal: { color: '#10b981', fontSize: 22, fontWeight: '800', marginBottom: 20 },
  modalLabel: { color: '#aaa', fontWeight: '600', fontSize: 13, marginBottom: 8, marginTop: 16 },
  modalFinal: { color: '#10b981', fontWeight: 'bold', fontSize: 18, marginTop: 8, padding: 12, backgroundColor: 'rgba(16,185,129,0.1)', borderRadius: 8 },
  modalInput: {
    backgroundColor: 'rgba(255,255,255,0.07)', borderRadius: 10,
    padding: 12, color: '#fff', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)',
  },
  paymentMethods: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  pmBtn: {
    paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.2)',
  },
  pmBtnActive: { backgroundColor: '#10b981', borderColor: '#10b981' },
  pmBtnText: { color: '#aaa', fontSize: 12 },
  pmBtnTextActive: { color: '#fff', fontWeight: 'bold' },
  modalButtons: { flexDirection: 'row', gap: 12, marginTop: 24, marginBottom: 16 },
  cancelBtn: {
    flex: 1, padding: 16, borderRadius: 12,
    borderWidth: 1, borderColor: '#555', alignItems: 'center',
  },
  confirmBtn: {
    flex: 1, padding: 16, borderRadius: 12,
    backgroundColor: '#10b981', alignItems: 'center',
  },

  // Receipt Modal
  receiptCard: {
    backgroundColor: '#161b22', borderRadius: 20,
    padding: 24, margin: 30, alignItems: 'center',
    borderWidth: 1, borderColor: 'rgba(46, 204, 113, 0.3)',
  },
  successIcon: { marginBottom: 16 },
  receiptTitle: { color: '#fff', fontWeight: 'bold', fontSize: 22, marginBottom: 8 },
  receiptSub: { color: '#aaa', fontSize: 14, textAlign: 'center', marginBottom: 24 },
  receiptActions: { flexDirection: 'row', gap: 12, width: '100%', marginBottom: 16 },
  printBtn: {
    flex: 1, flexDirection: 'row', gap: 8, alignItems: 'center', justifyContent: 'center',
    backgroundColor: '#10b981', paddingVertical: 14, borderRadius: 12,
  },
  printBtnText: { color: '#fff', fontWeight: 'bold', fontSize: 15 },
  pdfBtn: {
    flex: 1, flexDirection: 'row', gap: 8, alignItems: 'center', justifyContent: 'center',
    backgroundColor: 'rgba(16,185,129,0.15)', paddingVertical: 14, borderRadius: 12,
    borderWidth: 1, borderColor: 'rgba(16,185,129,0.3)',
  },
  pdfBtnText: { color: '#10b981', fontWeight: 'bold', fontSize: 15 },
  nextCustomerBtn: {
    width: '100%', paddingVertical: 14, alignItems: 'center',
    borderTopWidth: 1, borderTopColor: 'rgba(255,255,255,0.1)', marginTop: 8,
  },
  nextCustomerText: { color: '#888', fontWeight: 'bold', fontSize: 15 },
  
  // New Customer Selection styles
  customerPicker: {
    flexDirection: 'row', alignItems: 'center', backgroundColor: 'rgba(255,255,255,0.07)',
    borderRadius: 10, padding: 12, borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)',
    gap: 10,
  },
  customerPickerText: { color: '#fff', flex: 1, fontSize: 14 },
  modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  customerItem: { paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.05)' },
  customerName: { color: '#fff', fontSize: 16, fontWeight: '500' },
  customerPhone: { color: '#888', fontSize: 13, marginTop: 2 },
});
