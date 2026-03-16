// Core types for the e-commerce application

export interface Product {
  id: string;
  name: string;
  slug: string;
  description: string;
  price: number;
  originalPrice?: number;
  category: string;
  brand: string;
  images: string[];
  rating: number;
  reviews: number;
  stock: number;
  featured: boolean;
  isNew: boolean;
  tags: string[];
}

export interface Category {
  id: string;
  name: string;
  slug: string;
  image: string;
  productCount: number;
}

export interface Brand {
  id: string;
  name: string;
  slug: string;
}

export interface CartItem {
  product: Product;
  quantity: number;
  variant?: {
    size?: string;
    color?: string;
  };
}

export interface Order {
  id: string;
  userId: string;
  items: OrderItem[];
  subtotal: number;
  shipping: number;
  tax: number;
  total: number;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  createdAt: Date;
  updatedAt: Date;
  invoiceNumber: string;
  shippingAddress: Address;
  billingAddress: Address;
  paymentMethod: 'manual' | 'stripe' | 'paypal';
}

export interface OrderItem {
  product: Product;
  quantity: number;
  price: number;
  subtotal: number;
}

export interface Address {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  street: string;
  city: string;
  state: string;
  zipCode: string;
  country: string;
}

export interface User {
  id: string;
  email: string;
  firstName: string;
  lastName: string;
  phone?: string;
  addresses: Address[];
  orders: Order[];
  createdAt: Date;
}

export interface Review {
  id: string;
  productId: string;
  userId: string;
  userName: string;
  rating: number;
  comment: string;
  createdAt: Date;
}

export interface FilterOptions {
  categories: string[];
  brands: string[];
  priceRange: [number, number];
  minRating: number;
  inStock: boolean;
}

export interface SortOptions {
  sortBy: 'price-asc' | 'price-desc' | 'rating' | 'newest' | 'popular';
  sortOrder: 'asc' | 'desc';
}