export interface Service {
  id: string;
  name: string;
  category: string;
  duration: number;
  price: number;
  description: string;
  image: string;
}

export interface Professional {
  id: string;
  name: string;
  specialties: string[];
  available: boolean;
}

export interface Appointment {
  id: string;
  clientName: string;
  clientWhatsApp: string;
  clientEmail: string;
  serviceId: string;
  professionalId?: string;
  date: string;
  time: string;
  allergies?: string;
  status: 'pending' | 'confirmed' | 'cancelled';
  notes?: string;
}

export interface Client {
  id: string;
  name: string;
  whatsapp: string;
  email: string;
  appointments: Appointment[];
  allergies?: string;
  notes?: string;
}

export interface Review {
  id: string;
  clientName: string;
  service: string;
  rating: number;
  comment: string;
  date: string;
}