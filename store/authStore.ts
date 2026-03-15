import { create } from 'zustand';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { createJSONStorage, persist } from 'zustand/middleware';

// ── Types ──────────────────────────────────────────────────────────────────────

export interface StoreInfo {
  id: number;
  name: string;
  currency?: string;
  address?: string | null;
  phone?: string | null;
  email?: string | null;
  logo?: string | null;
  license_number?: string | null;
}

export interface UserInfo {
  id: number;
  name: string;
  email: string;
  role: string;
  locale?: string;
}

interface AuthState {
  /** The Sanctum API token */
  token: string | null;
  /** The configured API base URL (e.g. "https://mypharma.com/api") */
  apiUrl: string;
  user: UserInfo | null;
  store: StoreInfo | null;
  isAuthenticated: boolean;

  // Actions
  setApiUrl: (url: string) => void;
  loginSuccess: (token: string, user: UserInfo, store: StoreInfo) => void;
  setStoreInfo: (store: StoreInfo) => void;
  logout: () => void;
}

// ── Store ──────────────────────────────────────────────────────────────────────

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      token: null,
      apiUrl: '',
      user: null,
      store: null,
      isAuthenticated: false,

      setApiUrl: (url) => set({ apiUrl: url.trim().replace(/\/+$/, '') }),

      loginSuccess: (token, user, store) =>
        set({ token, user, store, isAuthenticated: true }),

      setStoreInfo: (store) => set({ store }),

      logout: () =>
        set({ token: null, user: null, store: null, isAuthenticated: false }),
    }),
    {
      name: 'auth-storage',
      storage: createJSONStorage(() => AsyncStorage),
    }
  )
);
