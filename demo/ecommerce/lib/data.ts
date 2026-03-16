import { Product, Category, Brand, Order, User } from '@/types';

// Sample categories
export const categories: Category[] = [
  {
    id: '1',
    name: 'Electronics',
    slug: 'electronics',
    image: 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=800',
    productCount: 12
  },
  {
    id: '2',
    name: 'Clothing',
    slug: 'clothing',
    image: 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=800',
    productCount: 15
  },
  {
    id: '3',
    name: 'Home & Garden',
    slug: 'home-garden',
    image: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800',
    productCount: 10
  },
  {
    id: '4',
    name: 'Sports & Outdoors',
    slug: 'sports-outdoors',
    image: 'https://images.unsplash.com/photo-1461896836934- voices?w=800',
    productCount: 8
  },
  {
    id: '5',
    name: 'Books',
    slug: 'books',
    image: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=800',
    productCount: 6
  },
  {
    id: '6',
    name: 'Toys & Games',
    slug: 'toys-games',
    image: 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?w=800',
    productCount: 9
  }
];

// Sample brands
export const brands: Brand[] = [
  { id: '1', name: 'TechPro', slug: 'techpro' },
  { id: '2', name: 'FashionHub', slug: 'fashionhub' },
  { id: '3', name: 'HomeStyle', slug: 'homestyle' },
  { id: '4', name: 'SportMax', slug: 'sportmax' },
  { id: '5', name: 'BookWorm', slug: 'bookworm' },
  { id: '6', name: 'PlayTime', slug: 'playtime' }
];

// Sample products
export const products: Product[] = [
  {
    id: '1',
    name: 'Wireless Bluetooth Headphones',
    slug: 'wireless-bluetooth-headphones',
    description: 'Premium wireless headphones with active noise cancellation, 30-hour battery life, and superior sound quality.',
    price: 199.99,
    originalPrice: 249.99,
    category: 'Electronics',
    brand: 'TechPro',
    images: [
      'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800',
      'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=800',
      'https://images.unsplash.com/photo-1524678606372-987d7d3d4c5c?w=800'
    ],
    rating: 4.5,
    reviews: 128,
    stock: 45,
    featured: true,
    isNew: true,
    tags: ['wireless', 'bluetooth', 'audio', 'noise-cancellation']
  },
  {
    id: '2',
    name: 'Smart Watch Pro',
    slug: 'smart-watch-pro',
    description: 'Advanced smartwatch with health monitoring, GPS, and 7-day battery life.',
    price: 349.99,
    originalPrice: 399.99,
    category: 'Electronics',
    brand: 'TechPro',
    images: [
      'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800',
      'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=800'
    ],
    rating: 4.8,
    reviews: 256,
    stock: 32,
    featured: true,
    isNew: false,
    tags: ['smartwatch', 'fitness', 'health', 'gps']
  },
  {
    id: '3',
    name: 'Premium Cotton T-Shirt',
    slug: 'premium-cotton-t-shirt',
    description: '100% organic cotton t-shirt with a comfortable fit and premium quality.',
    price: 39.99,
    originalPrice: null,
    category: 'Clothing',
    brand: 'FashionHub',
    images: [
      'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800',
      'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800'
    ],
    rating: 4.2,
    reviews: 89,
    stock: 150,
    featured: false,
    isNew: true,
    tags: ['cotton', 'organic', 't-shirt', 'apparel']
  },
  {
    id: '4',
    name: 'Running Shoes Elite',
    slug: 'running-shoes-elite',
    description: 'High-performance running shoes with advanced cushioning and breathable mesh.',
    price: 129.99,
    originalPrice: 159.99,
    category: 'Sports & Outdoors',
    brand: 'SportMax',
    images: [
      'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800',
      'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=800'
    ],
    rating: 4.7,
    reviews: 312,
    stock: 78,
    featured: true,
    isNew: false,
    tags: ['running', 'shoes', 'sports', 'fitness']
  },
  {
    id: '5',
    name: 'Modern Table Lamp',
    slug: 'modern-table-lamp',
    description: 'Elegant table lamp with adjustable brightness and USB charging port.',
    price: 79.99,
    originalPrice: 99.99,
    category: 'Home & Garden',
    brand: 'HomeStyle',
    images: [
      'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=800',
      'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=800'
    ],
    rating: 4.4,
    reviews: 67,
    stock: 23,
    featured: false,
    isNew: true,
    tags: ['lamp', 'lighting', 'home', 'decor']
  },
  {
    id: '6',
    name: 'Bestseller Novel Collection',
    slug: 'bestseller-novel-collection',
    description: 'Collection of 5 bestselling novels from top authors.',
    price: 49.99,
    originalPrice: null,
    category: 'Books',
    brand: 'BookWorm',
    images: [
      'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=800',
      'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=800'
    ],
    rating: 4.9,
    reviews: 445,
    stock: 200,
    featured: true,
    isNew: false,
    tags: ['books', 'novels', 'collection', 'reading']
  },
  {
    id: '7',
    name: 'Interactive Building Blocks',
    slug: 'interactive-building-blocks',
    description: 'Educational building blocks that teach coding and engineering concepts.',
    price: 59.99,
    originalPrice: 79.99,
    category: 'Toys & Games',
    brand: 'PlayTime',
    images: [
      'https://images.unsplash.com/photo-1587654780291-39c9404d746b?w=800',
      'https://images.unsplash.com/photo-1587654780291-39c9404d746b?w=800'
    ],
    rating: 4.6,
    reviews: 178,
    stock: 56,
    featured: true,
    isNew: true,
    tags: ['blocks', 'toys', 'educational', 'building']
  },
  {
    id: '8',
    name: 'Wireless Earbuds',
    slug: 'wireless-earbuds',
    description: 'Compact wireless earbuds with crystal clear audio and touch controls.',
    price: 149.99,
    originalPrice: 179.99,
    category: 'Electronics',
    brand: 'TechPro',
    images: [
      'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800',
      'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800'
    ],
    rating: 4.3,
    reviews: 234,
    stock: 89,
    featured: false,
    isNew: false,
    tags: ['earbuds', 'wireless', 'audio', 'bluetooth']
  },
  {
    id: '9',
    name: 'Denim Jacket',
    slug: 'denim-jacket',
    description: 'Classic denim jacket with a modern fit and premium stitching.',
    price: 89.99,
    originalPrice: null,
    category: 'Clothing',
    brand: 'FashionHub',
    images: [
      'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=800',
      'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=800'
    ],
    rating: 4.5,
    reviews: 156,
    stock: 67,
    featured: false,
    isNew: true,
    tags: ['denim', 'jacket', 'apparel', 'fashion']
  },
  {
    id: '10',
    name: 'Yoga Mat Premium',
    slug: 'yoga-mat-premium',
    description: 'Extra thick yoga mat with non-slip surface and carrying strap.',
    price: 49.99,
    originalPrice: 69.99,
    category: 'Sports & Outdoors',
    brand: 'SportMax',
    images: [
      'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=800',
      'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=800'
    ],
    rating: 4.4,
    reviews: 98,
    stock: 112,
    featured: false,
    isNew: false,
    tags: ['yoga', 'mat', 'sports', 'fitness']
  },
  {
    id: '11',
    name: 'Indoor Plant Set',
    slug: 'indoor-plant-set',
    description: 'Set of 3 low-maintenance indoor plants with decorative pots.',
    price: 69.99,
    originalPrice: 89.99,
    category: 'Home & Garden',
    brand: 'HomeStyle',
    images: [
      'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=800',
      'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=800'
    ],
    rating: 4.7,
    reviews: 134,
    stock: 45,
    featured: true,
    isNew: false,
    tags: ['plants', 'home', 'garden', 'decor']
  },
  {
    id: '12',
    name: 'Science Fiction Box Set',
    slug: 'science-fiction-box-set',
    description: 'Complete box set of 4 award-winning science fiction novels.',
    price: 59.99,
    originalPrice: null,
    category: 'Books',
    brand: 'BookWorm',
    images: [
      'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=800',
      'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=800'
    ],
    rating: 4.8,
    reviews: 267,
    stock: 88,
    featured: true,
    isNew: true,
    tags: ['books', 'scifi', 'collection', 'reading']
  }
];

// Sample orders
export const orders: Order[] = [
  {
    id: 'ORD-001',
    userId: 'user-1',
    items: [
      {
        product: products[0],
        quantity: 1,
        price: 199.99,
        subtotal: 199.99
      }
    ],
    subtotal: 199.99,
    shipping: 12.99,
    tax: 16.80,
    total: 229.78,
    status: 'delivered',
    createdAt: new Date('2024-01-15'),
    updatedAt: new Date('2024-01-20'),
    invoiceNumber: 'INV-2024-001',
    shippingAddress: {
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      phone: '+1-555-0101',
      street: '123 Main Street',
      city: 'New York',
      state: 'NY',
      zipCode: '10001',
      country: 'USA'
    },
    billingAddress: {
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      phone: '+1-555-0101',
      street: '123 Main Street',
      city: 'New York',
      state: 'NY',
      zipCode: '10001',
      country: 'USA'
    },
    paymentMethod: 'manual'
  },
  {
    id: 'ORD-002',
    userId: 'user-2',
    items: [
      {
        product: products[1],
        quantity: 1,
        price: 349.99,
        subtotal: 349.99
      },
      {
        product: products[2],
        quantity: 2,
        price: 39.99,
        subtotal: 79.98
      }
    ],
    subtotal: 429.97,
    shipping: 14.99,
    tax: 34.40,
    total: 479.36,
    status: 'processing',
    createdAt: new Date('2024-02-01'),
    updatedAt: new Date('2024-02-02'),
    invoiceNumber: 'INV-2024-002',
    shippingAddress: {
      firstName: 'Jane',
      lastName: 'Smith',
      email: 'jane.smith@example.com',
      phone: '+1-555-0202',
      street: '456 Oak Avenue',
      city: 'Los Angeles',
      state: 'CA',
      zipCode: '90001',
      country: 'USA'
    },
    billingAddress: {
      firstName: 'Jane',
      lastName: 'Smith',
      email: 'jane.smith@example.com',
      phone: '+1-555-0202',
      street: '456 Oak Avenue',
      city: 'Los Angeles',
      state: 'CA',
      zipCode: '90001',
      country: 'USA'
    },
    paymentMethod: 'manual'
  },
  {
    id: 'ORD-003',
    userId: 'user-1',
    items: [
      {
        product: products[3],
        quantity: 1,
        price: 129.99,
        subtotal: 129.99
      },
      {
        product: products[4],
        quantity: 1,
        price: 79.99,
        subtotal: 79.99
      },
      {
        product: products[5],
        quantity: 1,
        price: 49.99,
        subtotal: 49.99
      }
    ],
    subtotal: 259.97,
    shipping: 12.99,
    tax: 20.80,
    total: 293.76,
    status: 'shipped',
    createdAt: new Date('2024-02-10'),
    updatedAt: new Date('2024-02-15'),
    invoiceNumber: 'INV-2024-003',
    shippingAddress: {
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      phone: '+1-555-0101',
      street: '123 Main Street',
      city: 'New York',
      state: 'NY',
      zipCode: '10001',
      country: 'USA'
    },
    billingAddress: {
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      phone: '+1-555-0101',
      street: '123 Main Street',
      city: 'New York',
      state: 'NY',
      zipCode: '10001',
      country: 'USA'
    },
    paymentMethod: 'manual'
  }
];

// Sample user
export const user: User = {
  id: 'user-1',
  email: 'john.doe@example.com',
  firstName: 'John',
  lastName: 'Doe',
  phone: '+1-555-0101',
  addresses: [
    {
      firstName: 'John',
      lastName: 'Doe',
      email: 'john.doe@example.com',
      phone: '+1-555-0101',
      street: '123 Main Street',
      city: 'New York',
      state: 'NY',
      zipCode: '10001',
      country: 'USA'
    }
  ],
  orders: orders.filter(order => order.userId === 'user-1'),
  createdAt: new Date('2024-01-01')
};