import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';

export interface Product {
  id: number;
  name: string;
  image_url: string | null;
  crate_quantity: number;
  crate_price: string; // Decimal comes as string from JSON often
  piece_price: string | null;
  is_active: number;
}

export interface CartItem {
  product: Product;
  quantity_crates: number;
  quantity_pieces: number;
  subtotal: number;
}

interface ProductState {
  products: Product[];
  cart: CartItem[];
  offlineQueue: any[];
  setProducts: (products: Product[]) => void;
  addToCart: (product: Product, isCrate: boolean, change: number) => void;
  removeProduct: (productId: number) => void;
  clearCart: () => void;
  cartTotal: () => number;
  queueOrder: (order: any) => void;
  clearQueue: () => void;
}

export const useProductStore = create<ProductState>()(
  persist(
    (set, get) => ({
      products: [],
      cart: [],
      offlineQueue: [],
      setProducts: (products) => set({ products }),
      
      addToCart: (product, isCrate, change) => {
        const { cart } = get();
        const existingItemIndex = cart.findIndex((item) => item.product.id === product.id);
        
        let newCart = [...cart];

        if (existingItemIndex > -1) {
          const item = newCart[existingItemIndex];
          if (isCrate) {
            item.quantity_crates = Math.max(0, item.quantity_crates + change);
          } else {
             // Logic: pieces
             item.quantity_pieces = Math.max(0, item.quantity_pieces + change);
          }
          
          // Compute Subtotal
          const cratePrice = parseFloat(product.crate_price);
          // Assuming piece price logic if exists, or derived? 
          // Backend has piece_price.
          const piecePrice = product.piece_price ? parseFloat(product.piece_price) : (cratePrice / product.crate_quantity);

          item.subtotal = (item.quantity_crates * cratePrice) + (item.quantity_pieces * piecePrice);

          // Remove if 0
          if (item.quantity_crates === 0 && item.quantity_pieces === 0) {
             newCart.splice(existingItemIndex, 1);
          }
        } else if (change > 0) {
          // Add new item
           const cratePrice = parseFloat(product.crate_price);
           const piecePrice = product.piece_price ? parseFloat(product.piece_price) : (cratePrice / product.crate_quantity);
           
           newCart.push({
             product,
             quantity_crates: isCrate ? change : 0,
             quantity_pieces: !isCrate ? change : 0,
             subtotal: isCrate ? cratePrice : piecePrice
           });
        }

        set({ cart: newCart });
      },

      removeProduct: (productId: number) => {
        const { cart } = get();
        const newCart = cart.filter(item => item.product.id !== productId);
        set({ cart: newCart });
      },

      clearCart: () => set({ cart: [] }),
      
      cartTotal: () => {
        return get().cart.reduce((sum, item) => sum + item.subtotal, 0);
      },
      
      queueOrder: (order) => {
        set((state) => ({ offlineQueue: [...state.offlineQueue, order] }));
      },

      clearQueue: () => set({ offlineQueue: [] }),
    }),
    {
      name: 'product-storage',
      storage: createJSONStorage(() => AsyncStorage),
    }
  )
);
