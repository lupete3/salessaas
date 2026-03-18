import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { translations, Language } from '../constants/Translations';

interface LangState {
  lang: Language;
  setLang: (l: Language) => void;
  t: (key: string, vars?: Record<string, string | number>) => string;
}

const getInitialLang = (): Language => {
  try {
    // Standard way in modern RN (Hermes)
    const locale = Intl.DateTimeFormat().resolvedOptions().locale;
    return locale.startsWith('fr') ? 'fr' : 'en';
  } catch (e) {
    return 'fr';
  }
};

export const useLangStore = create<LangState>()(
  persist(
    (set, get) => ({
      lang: getInitialLang(),
      setLang: (l) => set({ lang: l }),
  t: (path: string, vars?: Record<string, string | number>): string => {
    const { lang } = get();
    const keys = path.split('.');
    let result: any = (translations as any)[lang];
    for (const key of keys) {
      if (result && result[key]) {
        result = result[key];
      } else {
        return path; // Fallback to key
      }
    }
    let text = typeof result === 'string' ? result : path;
    if (vars) {
      Object.entries(vars).forEach(([key, val]) => {
        text = text.replace(`{{${key}}}`, String(val));
      });
    }
    return text;
  },
    }),
    {
      name: 'lang-storage',
      storage: createJSONStorage(() => AsyncStorage),
    }
  )
);
