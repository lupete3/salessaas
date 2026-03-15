import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { translations, Language } from '../constants/Translations';

interface LangState {
  lang: Language;
  setLang: (l: Language) => void;
  t: (key: string) => string;
}

export const useLangStore = create<LangState>()(
  persist(
    (set, get) => ({
      lang: 'fr',
      setLang: (l) => set({ lang: l }),
      t: (path: string) => {
        const { lang } = get();
        const keys = path.split('.');
        let result: any = translations[lang];
        for (const key of keys) {
          if (result && result[key]) {
            result = result[key];
          } else {
            return path; // Fallback to key
          }
        }
        return typeof result === 'string' ? result : path;
      },
    }),
    {
      name: 'lang-storage',
      storage: createJSONStorage(() => AsyncStorage),
    }
  )
);
