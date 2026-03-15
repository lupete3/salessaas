import React, { useState, useMemo } from 'react';
import {
  View, Text, FlatList, TextInput, TouchableOpacity,
  StyleSheet, SafeAreaView, Alert
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppStore, Customer } from '../../store/appStore';
import { useAuthStore } from '../../store/authStore';

export default function CustomersScreen() {
  const { customers, addCustomer, addDebtPayment } = useAppStore();
  const { store } = useAuthStore();
  const currency = store?.currency ?? 'CDF';

  const [search, setSearch] = useState('');
  const [showingAdd, setShowingAdd] = useState(false);
  const [newName, setNewName] = useState('');
  const [newPhone, setNewPhone] = useState('');

  const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);
  const [paymentAmount, setPaymentAmount] = useState('');

  const filtered = useMemo(() => {
    return customers
      .filter((c) =>
        c.name.toLowerCase().includes(search.toLowerCase()) ||
        (c.phone && c.phone.includes(search))
      )
      .sort((a, b) => a.name.localeCompare(b.name));
  }, [customers, search]);

  const handleAdd = () => {
    if (!newName.trim()) {
      Alert.alert('Erreur', 'Le nom du client est requis.');
      return;
    }
    const newCustomer: Customer = {
      local_id: `loc_cust_${Date.now()}`,
      name: newName.trim(),
      phone: newPhone.trim() || null,
      total_debt: 0,
      is_synced: false
    };
    addCustomer(newCustomer);
    setShowingAdd(false);
    setNewName('');
    setNewPhone('');
  };
   const [selectedSaleUuid, setSelectedSaleUuid] = useState<string | null>(null);

  const customerSalesWithDebt = useMemo(() => {
    if (!selectedCustomer) return [];
    const uuid = selectedCustomer.uuid || selectedCustomer.local_id;
    const allSales = [...useAppStore.getState().offlineQueue, ...useAppStore.getState().syncedSales];
    return allSales.filter(s => s.customer_uuid === uuid && s.final_amount > (s.amount_paid || 0))
                   .sort((a, b) => new Date(b.sold_at).getTime() - new Date(a.sold_at).getTime());
  }, [selectedCustomer, customers]);

  const handlePayment = () => {
    const amount = parseFloat(paymentAmount);
    if (isNaN(amount) || amount <= 0 || !selectedCustomer) {
      Alert.alert('Erreur', 'Montant invalide.');
      return;
    }

    if (amount > selectedCustomer.total_debt + 0.01) { // Add small epsilon for float precision
        Alert.alert('Attention', 'Le montant dépasse la dette totale du client. Voulez-vous continuer ?', [
            { text: 'Annuler', style: 'cancel' },
            { text: 'Continuer', onPress: () => confirmPayment(amount) }
        ]);
    } else {
        confirmPayment(amount);
    }
  };

  const confirmPayment = (amount: number) => {
    addDebtPayment({
        local_id: `loc_pay_${Date.now()}`,
        customer_uuid: selectedCustomer?.uuid || selectedCustomer?.local_id || '',
        sale_uuid: selectedSaleUuid || undefined,
        amount: amount,
        payment_method: 'cash',
        paid_at: new Date().toISOString(),
        is_synced: false
    });
    setSelectedCustomer(null);
    setPaymentAmount('');
    setSelectedSaleUuid(null);
    Alert.alert('Succès', 'Paiement enregistré.');
  };

  const renderItem = ({ item }: { item: Customer }) => (
    <View style={styles.card}>
      <View style={styles.cardLeft}>
        <Text style={styles.name}>{item.name}</Text>
        {item.phone ? <Text style={styles.phone}>{item.phone}</Text> : null}
      </View>
      <View style={styles.cardRight}>
        <Text style={styles.debtLabel}>Dette:</Text>
        <Text style={[styles.debtAmount, item.total_debt <= 0 && styles.debtZero]}>
          {parseFloat(String(item.total_debt)).toFixed(2)} {currency}
        </Text>
        {item.total_debt > 0 && (
            <TouchableOpacity 
                style={styles.payBtn}
                onPress={() => setSelectedCustomer(item)}
            >
                <Text style={styles.payBtnText}>Payer</Text>
            </TouchableOpacity>
        )}
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      {/* Header / Search */}
      <View style={styles.searchRow}>
        <Ionicons name="search-outline" size={18} color="#888" style={{ marginRight: 8 }} />
        <TextInput
          style={styles.searchInput}
          placeholder="Rechercher un client..."
          placeholderTextColor="#666"
          value={search}
          onChangeText={setSearch}
        />
        <TouchableOpacity onPress={() => setShowingAdd(!showingAdd)} style={styles.addBtn}>
          <Ionicons name={showingAdd ? "close" : "add"} size={20} color="#fff" />
        </TouchableOpacity>
      </View>

      {/* Stats */}
      <View style={styles.statsRow}>
        <Text style={styles.statsText}>{filtered.length} client(s)</Text>
        <Text style={styles.statsText}>
          {customers.filter(c => c.total_debt > 0).length} débiteur(s)
        </Text>
      </View>

      {/* Add new customer form */}
      {showingAdd && (
        <View style={styles.addForm}>
          <Text style={styles.formTitle}>Nouveau Client</Text>
          <TextInput
            style={styles.input}
            placeholder="Nom complet *"
            placeholderTextColor="#888"
            value={newName}
            onChangeText={setNewName}
          />
          <TextInput
            style={styles.input}
            placeholder="Numéro de téléphone"
            placeholderTextColor="#888"
            value={newPhone}
            onChangeText={setNewPhone}
            keyboardType="phone-pad"
          />
          <TouchableOpacity style={styles.submitBtn} onPress={handleAdd}>
            <Text style={styles.submitText}>Enregistrer</Text>
          </TouchableOpacity>
        </View>
      )}

      {/* Payment Form (Overlay-like) */}
      {selectedCustomer && (
          <View style={[styles.addForm, { position: 'absolute', top: 50, left: 12, right: 12, zIndex: 10, elevation: 5, shadowColor: '#000', shadowOffset: { width:0, height:4}, shadowOpacity: 0.5, shadowRadius: 10 }]}>
            <View style={{flexDirection:'row', justifyContent:'space-between', marginBottom: 10}}>
                <Text style={styles.formTitle}>Paiement: {selectedCustomer.name}</Text>
                <TouchableOpacity onPress={() => { setSelectedCustomer(null); setSelectedSaleUuid(null); }}>
                    <Ionicons name="close" size={24} color="#888" />
                </TouchableOpacity>
            </View>
            
            <Text style={{color: '#888', marginBottom: 12}}>
                Dette totale: <Text style={{color:'#e74c3c', fontWeight:'bold'}}>{selectedCustomer.total_debt.toFixed(2)} {currency}</Text>
            </Text>

            {customerSalesWithDebt.length > 0 && (
                <View style={{maxHeight: 180, marginBottom: 15}}>
                    <Text style={{color:'#fff', fontSize: 13, marginBottom: 8}}>Choisir une vente spécifique (Optionnel) :</Text>
                    <FlatList 
                        data={customerSalesWithDebt}
                        keyExtractor={s => s.local_id}
                        renderItem={({item}) => (
                           <TouchableOpacity 
                             style={[
                                styles.saleOption, 
                                selectedSaleUuid === item.local_id && styles.saleOptionSelected
                             ]}
                             onPress={() => setSelectedSaleUuid(selectedSaleUuid === item.local_id ? null : item.local_id)}
                           >
                                <View style={{flex:1}}>
                                    <Text style={{color:'#fff', fontSize:12}}>Vente du {new Date(item.sold_at).toLocaleDateString()}</Text>
                                    <Text style={{color:'#888', fontSize:10}}>{item.local_id.substring(0,8)}...</Text>
                                </View>
                                <View style={{alignItems:'flex-end'}}>
                                    <Text style={{color:'#e74c3c', fontSize:12, fontWeight:'bold'}}>{(item.final_amount - (item.amount_paid || 0)).toFixed(2)} {currency}</Text>
                                    <Text style={{color:'#666', fontSize:10}}>Total: {item.final_amount} </Text>
                                </View>
                           </TouchableOpacity>
                        )}
                        nestedScrollEnabled
                    />
                    {!selectedSaleUuid && (
                        <Text style={{color: '#aaa', fontSize: 10, fontStyle:'italic', marginTop: 4}}>
                            * Aucun choix = Paiement réparti (plus anciennes en premier)
                        </Text>
                    )}
                </View>
            )}

            <TextInput
                style={styles.input}
                placeholder="Montant à payer *"
                placeholderTextColor="#888"
                value={paymentAmount}
                onChangeText={setPaymentAmount}
                keyboardType="decimal-pad"
                autoFocus
            />
            <TouchableOpacity style={styles.paySubmitBtn} onPress={handlePayment}>
                <Text style={styles.submitText}>
                   {selectedSaleUuid ? 'Payer cette vente' : 'Enregistrer (Global)'}
                </Text>
            </TouchableOpacity>
          </View>
      )}

      {/* List */}
      <FlatList
        data={filtered}
        keyExtractor={(c) => c.local_id || String(c.id)}
        renderItem={renderItem}
        ListEmptyComponent={<Text style={styles.empty}>Aucun client trouvé.</Text>}
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
    marginBottom: 4,
  },
  searchInput: { flex: 1, color: '#fff', fontSize: 15 },
  addBtn: {
    backgroundColor: '#10b981', padding: 6, borderRadius: 20, marginLeft: 8
  },
  statsRow: {
    flexDirection: 'row', justifyContent: 'space-between',
    paddingHorizontal: 16, marginBottom: 8,
  },
  statsText: { color: '#666', fontSize: 12 },
  addForm: {
    backgroundColor: '#1c2128', marginHorizontal: 12, marginBottom: 12, borderRadius: 12,
    padding: 16, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)',
  },
  formTitle: { color: '#fff', fontSize: 16, fontWeight: 'bold' },
  input: {
    backgroundColor: 'rgba(255,255,255,0.05)', borderRadius: 8, padding: 12,
    color: '#fff', marginBottom: 12, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)'
  },
  submitBtn: {
    backgroundColor: '#10b981', padding: 12, borderRadius: 8, alignItems: 'center'
  },
  paySubmitBtn: {
    backgroundColor: '#3498db', padding: 12, borderRadius: 8, alignItems: 'center'
  },
  submitText: { color: '#fff', fontWeight: 'bold' },
  card: {
    flexDirection: 'row', backgroundColor: '#161b22',
    marginHorizontal: 12, marginVertical: 5, borderRadius: 12,
    padding: 14, borderWidth: 1, borderColor: 'rgba(255,255,255,0.07)',
    justifyContent: 'space-between', alignItems: 'center'
  },
  cardLeft: { flex: 1 },
  name: { color: '#fff', fontWeight: '600', fontSize: 15, marginBottom: 2 },
  phone: { color: '#888', fontSize: 13 },
  cardRight: { alignItems: 'flex-end', marginLeft: 10 },
  debtLabel: { color: '#666', fontSize: 11, marginBottom: 2 },
  debtAmount: { color: '#e74c3c', fontWeight: 'bold', fontSize: 16 },
  debtZero: { color: '#2ecc71' },
  payBtn: {
      backgroundColor: '#3498db', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 6, marginTop: 6
  },
  payBtnText: { color: '#fff', fontSize: 11, fontWeight: 'bold' },
  empty: { color: '#666', textAlign: 'center', marginTop: 60, fontSize: 15 },
  saleOption: {
    flexDirection: 'row',
    backgroundColor: 'rgba(255,255,255,0.03)',
    padding: 10,
    borderRadius: 8,
    marginBottom: 6,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.05)',
  },
  saleOptionSelected: {
    borderColor: '#3498db',
    backgroundColor: 'rgba(52, 152, 219, 0.1)',
  },
});
