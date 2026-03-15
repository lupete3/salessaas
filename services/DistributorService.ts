import axios from 'axios';
import { AuthService } from './AuthService'; // Re-use base URL logic if possible, or copy it.

// const API_URL = 'http://10.255.72.76/finance/public/api';
const API_URL = 'https://brewery.quiraservices.com/api';

export const DistributorService = {
  login: async (phone: string, password: string) => {
    try {
      const response = await axios.post(`${API_URL}/distributor/login`, {
        phone,
        password,
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  getDeliveries: async (token: string) => {
    try {
      const response = await axios.get(`${API_URL}/distributor/deliveries`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  markDelivered: async (orderId: number, token: string) => {
     try {
      const response = await axios.post(`${API_URL}/distributor/orders/${orderId}/deliver`, {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  }
};
