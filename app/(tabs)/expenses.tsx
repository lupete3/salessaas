import React, { useState, useMemo } from 'react';
import {
  View, Text, FlatList, TextInput, TouchableOpacity,
  StyleSheet, SafeAreaView, Alert
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAppStore, Expense } from '../../store/appStore';
import { useAuthStore } from '../../store/authStore';

const CATEGORIES = ['Approvisionnement', 'Transport', 'Loyer', 'Electricité/Eau', 'Salaire', 'Autres'];

export default function ExpensesScreen() {
  const { expenses, addExpense } = useAppStore();
  const { store } = useAuthStore();
  const currency = store?.currency ?? 'CDF';
  
  const [showingAdd, setShowingAdd] = useState(false);
  const [amount, setAmount] = useState('');
  const [description, setDescription] = useState('');
  const [category, setCategory] = useState(CATEGORIES[0]);

  const sortedExpenses = useMemo(() => {
    return [...expenses].sort((a, b) => new Date(b.spent_at).getTime() - new Date(a.spent_at).getTime());
  }, [expenses]);

  const totalExpenses = useMemo(() => {
    return expenses.reduce((sum, e) => sum + parseFloat(String(e.amount)), 0);
  }, [expenses]);

  const handleAdd = () => {
    const numAmount = parseFloat(amount);
    if (isNaN(numAmount) || numAmount <= 0) {
      Alert.alert('Erreur', 'Veuillez saisir un montant valide.');
      return;
    }
    if (!description.trim()) {
      Alert.alert('Erreur', 'La description est requise.');
      return;
    }

    const newExpense: Expense = {
      local_id: `loc_exp_${Date.now()}`,
      amount: numAmount,
      description: description.trim(),
      category: category,
      spent_at: new Date().toISOString(),
      is_synced: false
    };

    addExpense(newExpense);
    setShowingAdd(false);
    setAmount('');
    setDescription('');
    setCategory(CATEGORIES[0]);
  };

  const renderItem = ({ item }: { item: Expense }) => (
    <View style={styles.card}>
      <View style={styles.cardLeft}>
        <Text style={styles.desc}>{item.description}</Text>
        <Text style={styles.cat}>{item.category} • {new Date(item.spent_at).toLocaleDateString()}</Text>
      </View>
      <View style={styles.cardRight}>
        <Text style={styles.amount}>-{parseFloat(String(item.amount)).toFixed(2)} {currency}</Text>
        <Text style={[styles.syncTag, item.is_synced && styles.syncDone]}>
          {item.is_synced ? 'Synchronisé' : 'En attente'}
        </Text>
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      {/* Header / Stats */}
      <View style={styles.header}>
        <View>
          <Text style={styles.headerLabel}>Total Dépenses</Text>
          <Text style={styles.headerValue}>{totalExpenses.toFixed(2)} {currency}</Text>
        </View>
        <TouchableOpacity onPress={() => setShowingAdd(!showingAdd)} style={styles.addBtn}>
          <Ionicons name={showingAdd ? "close" : "add"} size={26} color="#fff" />
        </TouchableOpacity>
      </View>

      {/* Add Form */}
      {showingAdd && (
        <View style={styles.addForm}>
          <Text style={styles.formTitle}>Nouvelle Dépense</Text>
          
          <TextInput
            style={styles.input}
            placeholder="Montant *"
            placeholderTextColor="#888"
            value={amount}
            onChangeText={setAmount}
            keyboardType="decimal-pad"
          />
          
          <TextInput
            style={styles.input}
            placeholder="Description *"
            placeholderTextColor="#888"
            value={description}
            onChangeText={setDescription}
          />

          <Text style={styles.label}>Catégorie :</Text>
          <View style={styles.catRow}>
            {CATEGORIES.map(cat => (
              <TouchableOpacity 
                key={cat} 
                style={[styles.catBtn, category === cat && styles.catBtnActive]}
                onPress={() => setCategory(cat)}
              >
                <Text style={[styles.catText, category === cat && styles.catTextActive]}>{cat}</Text>
              </TouchableOpacity>
            ))}
          </View>

          <TouchableOpacity style={styles.submitBtn} onPress={handleAdd}>
            <Text style={styles.submitText}>Enregistrer la dépense</Text>
          </TouchableOpacity>
        </View>
      )}

      {/* List */}
      <FlatList
        data={sortedExpenses}
        keyExtractor={(e) => e.local_id}
        renderItem={renderItem}
        ListEmptyComponent={<Text style={styles.empty}>Aucune dépense enregistrée.</Text>}
        contentContainerStyle={{ padding: 12, paddingBottom: 30 }}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#0d1117' },
  header: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    padding: 20, backgroundColor: '#161b22', borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.08)'
  },
  headerLabel: { color: '#888', fontSize: 13, textTransform: 'uppercase' },
  headerValue: { color: '#e74c3c', fontSize: 24, fontWeight: 'bold' },
  addBtn: { backgroundColor: '#10b981', width: 44, height: 44, borderRadius: 22, justifyContent: 'center', alignItems: 'center' },
  addForm: {
    backgroundColor: '#161b22', margin: 12, padding: 16, borderRadius: 12,
    borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)'
  },
  formTitle: { color: '#fff', fontSize: 18, fontWeight: 'bold', marginBottom: 16 },
  label: { color: '#888', fontSize: 12, marginBottom: 8 },
  input: {
    backgroundColor: 'rgba(255,255,255,0.05)', borderRadius: 8, padding: 12,
    color: '#fff', marginBottom: 16, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)'
  },
  catRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 6, marginBottom: 20 },
  catBtn: { paddingHorizontal: 10, paddingVertical: 6, borderRadius: 16, borderWidth: 1, borderColor: 'rgba(255,255,255,0.2)' },
  catBtnActive: { backgroundColor: '#10b981', borderColor: '#10b981' },
  catText: { color: '#bbb', fontSize: 11 },
  catTextActive: { color: '#fff', fontWeight: 'bold' },
  submitBtn: { backgroundColor: '#e74c3c', padding: 15, borderRadius: 10, alignItems: 'center' },
  submitText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },
  card: {
    flexDirection: 'row', backgroundColor: '#161b22', marginBottom: 10, borderRadius: 12,
    padding: 16, borderWidth: 1, borderColor: 'rgba(255,255,255,0.07)',
    justifyContent: 'space-between', alignItems: 'center'
  },
  cardLeft: { flex: 1 },
  desc: { color: '#fff', fontWeight: '600', fontSize: 15, marginBottom: 4 },
  cat: { color: '#666', fontSize: 12 },
  cardRight: { alignItems: 'flex-end' },
  amount: { color: '#e74c3c', fontWeight: 'bold', fontSize: 16, marginBottom: 4 },
  syncTag: { fontSize: 10, color: '#f39c12' },
  syncDone: { color: '#2ecc71' },
  empty: { color: '#666', textAlign: 'center', marginTop: 60, fontSize: 15 },
});
