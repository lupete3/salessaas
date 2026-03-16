import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';

// ── Types ──────────────────────────────────────────────────────────────────────

export interface Product {
  id: number;
  store_id: number;
  category_id: number | null;
  name: string;
  barcode: string | null;
  description: string | null;
  selling_price: string;
  buying_price: string | null;
  stock: number;
  min_stock: number;
  unit: string;
}

export interface Customer {
  id?: number;
  local_id?: string;
  uuid?: string;
  name: string;
  phone: string | null;
  address?: string | null;
  total_debt: number;
  is_synced: boolean;
}

export interface CartItem {
  product: Product;
  quantity: number;
  unit_price: number;
  subtotal: number;
}

/** A sale stored locally (pending or synced) */
export interface LocalSale {
  local_id: string; // uuid generated locally
  sold_at: string;  // ISO DateTime
  payment_method: 'cash' | 'mobile_money' | 'insurance' | 'credit';
  customer_uuid?: string;
  customer_name?: string;
  customer_phone?: string;
  customer_id?: number;
  notes: string;
  discount: number;
  amount_paid: number;
  change_given: number;
  items: {
    product_id: number;
    product_name: string;
    quantity: number;
    unit_price: number;
    discount: number;
    subtotal: number;
  }[];
  total_amount: number;
  final_amount: number;
  is_synced: boolean;
  server_id?: number;
}

export interface Expense {
  local_id: string;
  amount: number;
  description: string;
  category: string;
  spent_at: string;
  is_synced: boolean;
}

export interface DebtPayment {
  local_id: string;
  customer_uuid: string;
  sale_uuid?: string; // Optional: link to a specific sale
  amount: number;
  payment_method: string;
  paid_at: string;
  is_synced: boolean;
}

interface AppState {
  products: Product[]; 
  customers: Customer[];
  expenses: Expense[];
  debtPayments: DebtPayment[];
  cart: CartItem[];
  offlineQueue: LocalSale[];   // unsynced sales
  syncedSales: LocalSale[];    // synced sales (history)
  cancelledSales: string[];    // local_ids of synced sales that were cancelled locally and need server sync
  lastSyncAt: string | null;

  // Products
  setProducts: (products: Product[]) => void;
  
  // Customers
  setCustomers: (customers: Customer[]) => void;
  addCustomer: (customer: Customer) => void;
  markCustomersSynced: (ids: { local_id: string; uuid: string }[]) => void;

  // Expenses
  setExpenses: (expenses: Expense[]) => void;
  addExpense: (expense: Expense) => void;
  markExpensesSynced: (localIds: string[]) => void;

  // Debt Payments
  setDebtPayments: (payments: DebtPayment[]) => void;
  addDebtPayment: (payment: DebtPayment) => void;
  markDebtPaymentsSynced: (localIds: string[]) => void;

  // Cart
  addToCart: (product: Product, quantity: number) => boolean;
  removeFromCart: (productId: number) => void;
  updateCartQty: (productId: number, quantity: number) => boolean;
  clearCart: () => void;
  cartTotal: () => number;

  // Sales
  queueSale: (sale: LocalSale) => void;
  cancelSale: (localId: string) => { success: boolean; message: string };
  markSalesSynced: (results: { local_id: string; server_id: number }[]) => void;
  clearQueue: () => void;
  markCancelledSalesSynced: (localIds: string[]) => void;
  setSyncedSales: (sales: LocalSale[]) => void;
  setLastSyncAt: (dt: string) => void;
}

// ── Helper ─────────────────────────────────────────────────────────────────────

const calcSubtotal = (product: Product, quantity: number): number =>
  parseFloat(product.selling_price) * quantity;

// ── Store ──────────────────────────────────────────────────────────────────────

export const useAppStore = create<AppState>()(
  persist(
    (set, get) => ({
      products: [],
      customers: [],
      expenses: [],
      debtPayments: [],
      cart: [],
      offlineQueue: [],
      syncedSales: [],
      cancelledSales: [],
      lastSyncAt: null,

      // ── Products ──────────────────────────────────────────────────
      setProducts: (products) => set({ products }),
      
      // ── Customers ─────────────────────────────────────────────────────────────
      setCustomers: (serverCustomers) => {
        const { customers } = get();
        
        // 1. Identify unsynced (local-only) customers
        const unsynced = customers.filter(c => !c.is_synced);
        
        // 2. Identify synced customers that might have "pending" local debt 
        // (debt that was added locally but server version hasn't reflected yet)
        const localSyncedMap = new Map();
        customers.filter(c => c.is_synced).forEach(c => {
           if (c.uuid) localSyncedMap.set(c.uuid, c.total_debt);
        });

        const synced = serverCustomers.map(sc => {
          const localDebt = localSyncedMap.get(sc.uuid);
          // If local debt is higher, it probably contains pending sales not yet 
          // fully processed/returned by the server list.
          const finalDebt = (localDebt !== undefined && localDebt > sc.total_debt) 
              ? localDebt 
              : sc.total_debt;
              
          return { ...sc, total_debt: finalDebt, is_synced: true };
        });
        
        // 3. Merge
        const syncedUuids = new Set(synced.map(c => c.uuid));
        const filteredUnsynced = unsynced.filter(c => !c.local_id || !syncedUuids.has(c.local_id));

        set({ customers: [...synced, ...filteredUnsynced] });
      },
      addCustomer: (customer) => set({ customers: [...get().customers, customer] }),
      markCustomersSynced: (ids) => {
        const idMap = new Map(ids.map(i => [i.local_id, i.uuid]));
        set({
           customers: get().customers.map(c => 
             c.local_id && idMap.has(c.local_id) 
               ? { ...c, is_synced: true, uuid: idMap.get(c.local_id) } 
               : c
           )
        });
      },

      // ── Expenses ─────────────────────────────────────────────────────────────
      setExpenses: (serverExpenses) => {
        const { expenses } = get();
        const unsynced = expenses.filter(e => !e.is_synced);
        const incomingIds = new Set(serverExpenses.map(e => e.local_id));
        const filteredUnsynced = unsynced.filter(e => !incomingIds.has(e.local_id));
        set({ expenses: [...serverExpenses, ...filteredUnsynced] });
      },
      addExpense: (expense) => set({ expenses: [...get().expenses, expense] }),
      markExpensesSynced: (localIds) => {
        const idSet = new Set(localIds);
        set({
           expenses: get().expenses.map(e => idSet.has(e.local_id) ? { ...e, is_synced: true } : e)
        });
      },

      // ── Debt Payments ────────────────────────────────────────────────────────
      setDebtPayments: (payments) => {
        const unsynced = get().debtPayments.filter(p => !p.is_synced);
        // Avoid duplicates
        const unsyncedIds = new Set(unsynced.map(p => p.local_id));
        const synced = payments.map(p => ({ ...p, is_synced: true }));
        const filteredUnsynced = unsynced.filter(p => !unsyncedIds.has(p.local_id)); // Wait, this logic is a bit circular.
        
        // Correct logic: take all incoming (synced) + local unsynced that aren't already in incoming
        const incomingIds = new Set(payments.map(p => p.local_id));
        const localStillUnsynced = unsynced.filter(p => !incomingIds.has(p.local_id));
        
        set({ debtPayments: [...synced, ...localStillUnsynced] });
      },
      addDebtPayment: (payment) => {
        const { customers, debtPayments, offlineQueue, syncedSales } = get();
        
        // 1. Update Customer Aggregate Debt
        const updatedCustomers = customers.map(c => {
           if (c.uuid === payment.customer_uuid || c.local_id === payment.customer_uuid) {
             return { ...c, total_debt: Math.max(0, parseFloat(String(c.total_debt)) - payment.amount) };
           }
           return c;
        });

        // 2. Update Individual Sales
        let remainingPayment = payment.amount;

        const updateSale = (sale: LocalSale) => {
          if (remainingPayment <= 0) return sale;

          const isTargeted = payment.sale_uuid ? (sale.local_id === payment.sale_uuid) : true;
          const isMatch = (sale.customer_uuid === payment.customer_uuid);
          const hasDebt = sale.final_amount > (sale.amount_paid || 0);

          if (isMatch && hasDebt && isTargeted) {
             const currentDebt = sale.final_amount - (sale.amount_paid || 0);
             const amountToApply = Math.min(remainingPayment, currentDebt);
             
             remainingPayment -= amountToApply;
             return { 
               ...sale, 
               amount_paid: (sale.amount_paid || 0) + amountToApply 
             };
          }
          return sale;
        };

        const updatedQueue = offlineQueue.map(updateSale);
        const updatedSynced = syncedSales.map(updateSale);
        
        set({ 
          debtPayments: [...debtPayments, payment],
          customers: updatedCustomers,
          offlineQueue: updatedQueue,
          syncedSales: updatedSynced
        });
      },
      markDebtPaymentsSynced: (localIds) => {
        const idSet = new Set(localIds);
        set({
            debtPayments: get().debtPayments.map(p => idSet.has(p.local_id) ? { ...p, is_synced: true } : p)
        });
      },

      // ── Cart ──────────────────────────────────────────────────────────────────
      addToCart: (product, quantity) => {
        if (quantity <= 0) return false;
        const cart = [...get().cart];
        const idx = cart.findIndex((c) => c.product.id === product.id);
        
        let newQty = quantity;
        if (idx > -1) {
          newQty = cart[idx].quantity + quantity;
        }

        // Check stock limit
        if (newQty > product.stock) {
          return false;
        }

        if (idx > -1) {
          cart[idx].quantity = newQty;
          cart[idx].subtotal = calcSubtotal(product, newQty);
        } else {
          cart.push({
            product,
            quantity: newQty,
            unit_price: parseFloat(product.selling_price),
            subtotal: calcSubtotal(product, newQty),
          });
        }
        set({ cart });
        return true;
      },

      removeFromCart: (productId) =>
        set({ cart: get().cart.filter((c) => c.product.id !== productId) }),

      updateCartQty: (productId, quantity) => {
        const cart = get().cart;
        const item = cart.find(c => c.product.id === productId);
        if (!item) return false;

        if (quantity <= 0) {
          get().removeFromCart(productId);
          return true;
        }

        if (quantity > item.product.stock) {
          return false;
        }

        const newCart = cart.map((c) =>
          c.product.id === productId
            ? { ...c, quantity, subtotal: calcSubtotal(c.product, quantity) }
            : c
        );
        set({ cart: newCart });
        return true;
      },

      clearCart: () => set({ cart: [] }),

      cartTotal: () =>
        get().cart.reduce((sum, item) => sum + item.subtotal, 0),

      // ── Sales / Offline Queue ─────────────────────────────────────────────────
      queueSale: (sale) => {
        const { customers, offlineQueue } = get();
        let updatedCustomers = customers;

        // Ensure we work with numbers to avoid string concatenation or NaN issues
        const finalAmt = Number(sale.final_amount) || 0;
        const paidAmt = Number(sale.amount_paid) || 0;
        const debtAmount = Math.max(0, finalAmt - paidAmt);

        // Precision check: only update if debt is significant (> 0.01)
        if (debtAmount > 0.009 && sale.customer_uuid) {
          updatedCustomers = customers.map(c => {
            const isMatch = (c.uuid && c.uuid === sale.customer_uuid) || 
                            (c.local_id && c.local_id === sale.customer_uuid);
            if (isMatch) {
              const currentDebt = Number(c.total_debt) || 0;
              return { ...c, total_debt: currentDebt + debtAmount };
            }
            return c;
          });
        }

        set({ 
          offlineQueue: [...offlineQueue, sale],
          customers: updatedCustomers
        });
      },

      markSalesSynced: (results) => {
        const idMap = new Map(results.map((r) => [r.local_id, r.server_id]));
        const updated = get().offlineQueue.map((s) =>
          idMap.has(s.local_id)
            ? { ...s, is_synced: true, server_id: idMap.get(s.local_id) }
            : s
        );
        const nowSynced = updated.filter((s) => s.is_synced);
        const stillPending = updated.filter((s) => !s.is_synced);
        set({
          offlineQueue: stillPending,
          syncedSales: [...get().syncedSales, ...nowSynced],
        });
      },

      clearQueue: () => set({ offlineQueue: [] }),

      markCancelledSalesSynced: (localIds: string[]) => {
        const idSet = new Set(localIds);
        set({
          cancelledSales: get().cancelledSales.filter(id => !idSet.has(id))
        });
      },

      setSyncedSales: (sales) => {
        const { offlineQueue } = get();
        const pendingIds = new Set(offlineQueue.map(s => s.local_id));
        const filteredServerSales = sales.filter(s => !pendingIds.has(s.local_id));
        
        set({ syncedSales: filteredServerSales });
      },

      setLastSyncAt: (dt) => set({ lastSyncAt: dt }),

      cancelSale: (localId) => {
        const { offlineQueue, syncedSales, products } = get();
        const sale = offlineQueue.find(s => s.local_id === localId) || syncedSales.find(s => s.local_id === localId);
        
        if (!sale) return { success: false, message: "Vente introuvable." };

        const diff = Date.now() - new Date(sale.sold_at).getTime();
        const mins = diff / (1000 * 60);

        if (mins > 20) {
            return { success: false, message: "Délai de 20 minutes dépassé. Impossible d'annuler." };
        }

        // Restore Stock
        const newProducts = products.map((p: Product) => {
            const item = sale.items.find(i => i.product_id === p.id);
            if (item) {
                return { ...p, stock: p.stock + item.quantity };
            }
            return p;
        });

        // Update queues
        const isAlreadySynced = syncedSales.some(s => s.local_id === localId);
        
        set({
            products: newProducts,
            offlineQueue: offlineQueue.filter(s => s.local_id !== localId),
            syncedSales: syncedSales.filter(s => s.local_id !== localId),
            cancelledSales: isAlreadySynced ? [...get().cancelledSales, localId] : get().cancelledSales
        });

        return { success: true, message: "Vente annulée et stock restauré." };
      },
    }),
    {
      name: 'salessaas-storage',
      storage: createJSONStorage(() => AsyncStorage),
    }
  )
);
