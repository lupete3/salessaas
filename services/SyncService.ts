import axios from 'axios';
import { useAuthStore } from '../store/authStore';
import { useAppStore } from '../store/appStore';
import { useLangStore } from '../store/langStore';
import { StorageService } from './StorageService';

// ── Helpers ────────────────────────────────────────────────────────────────────

/** Returns an Axios instance pre-configured with the stored API URL and token. */
const apiClient = () => {
  const { apiUrl, token } = useAuthStore.getState();
  const { lang } = useLangStore.getState();
  return axios.create({
    baseURL: `${apiUrl.trim().replace(/\/+$/, '')}/api`,
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'Accept-Language': lang,
    },
    timeout: 15000,
  });
};

// ── Auth ───────────────────────────────────────────────────────────────────────

export const SyncService = {
  /**
   * Authenticate against the backend and store the token + store info.
   */
  login: async (apiUrl: string, email: string, password: string): Promise<string | null> => {
    const clean = apiUrl.trim().replace(/\/+$/, '');
    useAuthStore.getState().setApiUrl(clean);

    try {
      const response = await axios.post(
        `${clean}/api/auth/login`,
        { email, password },
        { headers: { Accept: 'application/json' }, timeout: 15000 }
      );
      const { token, user, store } = response.data;
      useAuthStore.getState().loginSuccess(token, user, store);
      return null;
    } catch (error: any) {
      if (error.response?.status === 422) {
        const msgs = error.response?.data?.errors;
        const first = msgs ? Object.values(msgs).flat()[0] : null;
        return (first as string) ?? error.response?.data?.message ?? 'Identifiants invalides.';
      }
      if (error.response?.status === 401) return 'Identifiants invalides.';
      const detail = error.response
        ? `Erreur ${error.response.status}: ${JSON.stringify(error.response.data)}`
        : `Réseau: ${error.message}`;
      return `Connexion échouée — ${detail}`;
    }
  },

  logout: async (): Promise<void> => {
    try { await apiClient().post('/auth/logout'); } catch (_) { /* ignore */ }
    useAuthStore.getState().logout();
    useAppStore.getState().clearQueue();
  },

  // ── Pull Data ──────────────────────────────────────────────────────────────

  /**
   * Fetch products and customers from the server and persist locally.
   */
  pullData: async (): Promise<boolean> => {
    try {
      const pRes = await apiClient().get('/products');
      useAppStore.getState().setProducts(pRes.data.products);
      if (pRes.data.store) {
        useAuthStore.getState().setStoreInfo(pRes.data.store);
      }
      
      const cRes = await apiClient().get('/customers');
      useAppStore.getState().setCustomers(cRes.data.customers);
      if (cRes.data.debt_payments) {
        useAppStore.getState().setDebtPayments(cRes.data.debt_payments);
      }
      
      useAppStore.getState().setLastSyncAt(new Date().toISOString());
      console.log(`✅ Données synchronisées.`);
      return true;
    } catch (error) {
      console.warn('⚠️ Pull data échoué (mode hors-ligne):', error);
      return false;
    }
  },

  // ── Push Data (Sync) ──────────────────────────────────────────────────────

  /**
   * Push all unsynced local data to the central `/sync` endpoint.
   */
  pushData: async (): Promise<{ success: boolean; error?: string }> => {
    const { offlineQueue, customers, expenses, debtPayments } = useAppStore.getState();
    const pendingSales = offlineQueue.filter((s) => !s.is_synced);
    const pendingCustomers = customers.filter((c) => !c.is_synced);
    const pendingExpenses = expenses.filter((e) => !e.is_synced);
    const pendingDebtPayments = debtPayments.filter((p) => !p.is_synced);

    if (
        pendingSales.length === 0 && 
        pendingCustomers.length === 0 && 
        pendingExpenses.length === 0 && 
        pendingDebtPayments.length === 0
    ) return { success: true };

    try {
      const payload = {
        sales: pendingSales.map(sale => ({
           uuid: sale.local_id,
           sale_date: sale.sold_at,
           total_amount: sale.total_amount,
           final_amount: sale.final_amount,
           amount_paid: sale.amount_paid, 
           change_given: sale.change_given,
           discount: sale.discount,
           payment_method: sale.payment_method,
           customer_uuid: sale.customer_uuid,
           items: sale.items.map(i => ({
               product_id: i.product_id,
               quantity: i.quantity,
               unit_price: i.unit_price,
               subtotal: i.quantity * i.unit_price
           }))
        })),
        customers: pendingCustomers.map(c => ({
           uuid: c.local_id,
           name: c.name,
           phone: c.phone,
           address: c.address
        })),
        expenses: pendingExpenses.map(e => ({
            uuid: e.local_id,
            amount: e.amount,
            description: e.description,
            category: e.category,
            spent_at: e.spent_at
        })),
        debt_payments: pendingDebtPayments.map(p => ({
            uuid: p.local_id,
            customer_uuid: p.customer_uuid,
            sale_uuid: p.sale_uuid,
            amount: p.amount,
            payment_method: p.payment_method,
            paid_at: p.paid_at
        }))
      };

      const res = await apiClient().post('/sync', { changes: payload });
      
      if (res.data.success) {
          const store = useAppStore.getState();
          
          // Refresh store info if returned
          if (res.data.store) {
            useAuthStore.getState().setStoreInfo(res.data.store); // We need to add this action to useAuthStore
          }
          
          // Confirm synchronization locally
          if (res.data.results) {
             const results = res.data.results;
             if (results.sales) store.markSalesSynced(results.sales);
             if (results.customers) store.markCustomersSynced(results.customers);
             if (results.expenses) store.markExpensesSynced(results.expenses.map((r: any) => r.local_id));
             if (results.debt_payments) store.markDebtPaymentsSynced(results.debt_payments.map((r: any) => r.local_id));
          } else {
             // Fallback for older API versions if necessary
             if (pendingSales.length > 0) {
               store.markSalesSynced(pendingSales.map(s => ({ local_id: s.local_id, server_id: 1 })));
             }
             if (pendingCustomers.length > 0) {
               store.markCustomersSynced(pendingCustomers.map(c => ({ local_id: c.local_id!, uuid: c.local_id! })));
             }
          }
          if (pendingExpenses.length > 0) {
            store.markExpensesSynced(pendingExpenses.map(e => e.local_id));
          }
          if (pendingDebtPayments.length > 0) {
            store.markDebtPaymentsSynced(pendingDebtPayments.map(p => p.local_id));
          }

          return { success: true };
      }
      return { success: false, error: res.data.message || 'Le serveur a refusé la synchronisation.' };

    } catch (error: any) {
      console.error('❌ Sync push échoué:', error);
      return { success: false, error: error.response?.data?.message || error.message };
    }
  },
};
