import axios from 'axios';

// REPLACE WITH YOUR LARAVEL LOCAL IP
// const API_URL = 'http://10.255.72.76/finance/public/api';
const API_URL = 'https://brewery.quiraservices.com/api';

export const CompanyService = {
  /**
   * Fetch company settings
   */
  getSettings: async () => {
    try {
      const response = await axios.get(`${API_URL}/company/settings`);
      return response.data;
    } catch (error) {
      console.error('Error fetching company settings:', error);
      throw error;
    }
  },
};
