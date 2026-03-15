import React, { useEffect, useState } from 'react';
import { StyleSheet, View, Text, Alert, TouchableOpacity, ActivityIndicator } from 'react-native';
import MapView, { Marker } from 'react-native-maps';
import * as Location from 'expo-location';
import { DistributorService } from '../../services/DistributorService';
import { useAuthStore } from '../../store/authStore';
import { Colors } from '../../constants/Colors';
import { Ionicons } from '@expo/vector-icons';

export default function MapScreen() {
  const [location, setLocation] = useState<Location.LocationObject | null>(null);
  const [deliveries, setDeliveries] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [selectedOrder, setSelectedOrder] = useState<any>(null);
  const [actionLoading, setActionLoading] = useState(false);
  
  const distributor = useAuthStore((state: any) => state.distributor);

  const refreshDeliveries = async () => {
    setLoading(true);
    try {
        const tokenToUse = (distributor as any)?.api_token; 
        const data = await DistributorService.getDeliveries(tokenToUse);
        setDeliveries(data);
    } catch (error) {
        console.log(error);
        Alert.alert('Erreur', 'Impossible de charger les livraisons.');
    } finally {
        setLoading(false);
        setActionLoading(false);
    }
  };

  useEffect(() => {
    (async () => {
      let { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        return;
      }
      let loc = await Location.getCurrentPositionAsync({});
      setLocation(loc);
      refreshDeliveries();
    })();
  }, []);

  const handleMarkDelivered = async (orderId: number) => {
      setActionLoading(true);
      try {
        const tokenToUse = (distributor as any)?.api_token;
        await DistributorService.markDelivered(orderId, tokenToUse);
        Alert.alert('Succès', 'Commande livrée !');
        setSelectedOrder(null);
        refreshDeliveries();
      } catch (error) {
          Alert.alert('Erreur', 'Opération échouée.');
          setActionLoading(false);
      }
  };

  return (
    <View style={styles.container}>
      <MapView 
        style={styles.map}
        initialRegion={{
            latitude: -4.325, 
            longitude: 15.322,
            latitudeDelta: 0.1,
            longitudeDelta: 0.1,
        }}
        showsUserLocation={true}
        onPress={() => setSelectedOrder(null)}
      >
        {deliveries.map((order) => (
             order.depot && order.depot.latitude && order.depot.longitude ? (
                <Marker
                    key={order.id}
                    coordinate={{
                        latitude: parseFloat(order.depot.latitude),
                        longitude: parseFloat(order.depot.longitude),
                    }}
                    title={order.depot.owner_name}
                    pinColor={Colors.primary}
                    onPress={(e) => {
                        e.stopPropagation();
                        setSelectedOrder(order);
                    }}
                />
            ) : null
        ))}
      </MapView>

       <TouchableOpacity style={styles.refreshButton} onPress={refreshDeliveries}>
         {loading ? <ActivityIndicator color="white" /> : <Ionicons name="refresh" size={24} color="white" />}
      </TouchableOpacity>
      
      {selectedOrder && (
          <View style={styles.detailCard}>
              <View style={styles.cardHeader}>
                  <Text style={styles.cardTitle}>{selectedOrder.depot?.owner_name}</Text>
                  <TouchableOpacity onPress={() => setSelectedOrder(null)}>
                      <Ionicons name="close-circle" size={28} color="#999" />
                  </TouchableOpacity>
              </View>
              
              <View style={styles.cardContent}>
                <View style={styles.row}>
                    <Ionicons name="call-outline" size={20} color="#666" />
                    <Text style={styles.infoText}>{selectedOrder.depot?.phone}</Text>
                </View>
                <View style={styles.row}>
                    <Ionicons name="location-outline" size={20} color="#666" />
                    <Text style={styles.infoText} numberOfLines={2}>{selectedOrder.depot?.address_details || 'Adresse non spécifiée'}</Text>
                </View>
                <View style={styles.divider} />
                <View style={styles.rowBetween}>
                    <Text style={styles.label}>Commande #</Text>
                    <Text style={styles.value}>{selectedOrder.id}</Text>
                </View>
                <View style={styles.rowBetween}>
                    <Text style={styles.label}>Montant Total</Text>
                    <Text style={styles.price}>{Number(selectedOrder.total_amount).toLocaleString()} FC</Text>
                </View>
                
                <TouchableOpacity 
                    style={styles.deliverButton}
                    onPress={() => handleMarkDelivered(selectedOrder.id)}
                    disabled={actionLoading}
                >
                    {actionLoading ? (
                        <ActivityIndicator color="black" />
                    ) : (
                        <Text style={styles.deliverButtonText}>CONFIRMER LA LIVRAISON</Text>
                    )}
                </TouchableOpacity>
              </View>
          </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  map: {
    width: '100%',
    height: '100%',
  },
  refreshButton: {
      position: 'absolute',
      top: 50, 
      right: 20,
      backgroundColor: 'black',
      padding: 12,
      borderRadius: 30,
      elevation: 5,
      zIndex: 10,
  },
  detailCard: {
      position: 'absolute',
      bottom: 0,
      left: 0,
      right: 0,
      backgroundColor: 'white',
      borderTopLeftRadius: 20,
      borderTopRightRadius: 20,
      padding: 20,
      elevation: 20,
      shadowColor: '#000',
      shadowOffset: { width: 0, height: -2 },
      shadowOpacity: 0.25,
      shadowRadius: 5,
      zIndex: 20,
      paddingBottom: 30, 
  },
  cardHeader: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: 15,
  },
  cardTitle: {
      fontSize: 20,
      fontWeight: 'bold',
      color: '#000',
  },
  cardContent: {
      gap: 12,
  },
  row: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 10,
  },
  rowBetween: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
  },
  infoText: {
      fontSize: 16,
      color: '#444',
      flex: 1,
  },
  label: {
      fontSize: 14,
      color: '#666',
  },
  value: {
      fontSize: 16,
      fontWeight: '600',
      color: '#000',
  },
  price: {
      fontSize: 18,
      fontWeight: 'bold',
      color: Colors.primary,
  },
  divider: {
      height: 1,
      backgroundColor: '#f0f0f0',
      marginVertical: 5,
  },
  deliverButton: {
      backgroundColor: Colors.primary,
      padding: 16,
      borderRadius: 12,
      alignItems: 'center',
      marginTop: 10,
  },
  deliverButtonText: {
      fontWeight: 'bold',
      fontSize: 16,
      color: '#000',
  }
});
