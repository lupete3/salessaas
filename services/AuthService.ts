import axios from 'axios';

// REPLACE with your actual LAN IP
// const API_URL = 'http://10.255.72.76/finance/public/api'; 
const API_URL = 'https://brewery.quiraservices.com/api'; 


export const AuthService = {
  login: async (depotId: string, pinCode: string) => {
    try {
      const response = await axios.post(`${API_URL}/depot/login`, {
        depot_id: depotId,
        pin_code: pinCode,
      });
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error) && error.response) {
        throw new Error(error.response.data.message || 'Echec de connexion');
      }
      throw new Error('Erreur réseau');
    }
  },
};
