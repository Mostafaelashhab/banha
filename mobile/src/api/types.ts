export type ID = number | string;

export type Paginated<T> = {
  data: T[];
  meta?: {
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
  };
  links?: { next?: string | null; prev?: string | null };
};

export type User = {
  id: ID;
  name: string;
  username: string;
  phone?: string;
  avatar_url?: string | null;
  is_verified?: boolean;
  city?: string;
};

export type Business = {
  id: ID;
  slug: string;
  name: string;
  subtitle?: string | null;
  description?: string | null;
  category?: string;
  category_label?: string | null;
  sub_type?: string | null;
  cover_url?: string | null;
  logo_url?: string | null;
  lat?: number | null;
  lng?: number | null;
  address?: string | null;
  phone?: string | null;
  whatsapp?: string | null;
  hotline?: string | null;
  is_open?: boolean | null;
  is_24h?: boolean;
  hours_text?: string | null;
  hours_schedule?: Record<string, string | null> | null;
  rating?: number | null;
  reviews_count?: number;
  is_verified?: boolean;
  tier?: 'silver' | 'gold' | null;
  is_sponsored?: boolean;
  distance_m?: number | null;
  has_menu?: boolean;
  menu_currency?: string | null;
  booking_enabled?: boolean;
  features?: string[];
  photos?: { id: ID; url: string }[];
  photos_count?: number;
  reviews?: { id: ID; rating: number; body?: string | null; author_name?: string | null; reviewed_at: string }[];
};

export type Post = {
  id: ID;
  title?: string | null;
  body: string;
  category?: string | null;
  image_url?: string | null;
  is_announcement?: boolean;
  is_sponsored?: boolean;
  upvotes: number;
  downvotes: number;
  comments_count: number;
  author?: { id?: ID; username?: string } | null;
  zone?: string | null;
  created_at: string;
};

export type FeedItem =
  | { type: 'post'; post: Post }
  | { type: 'business'; business: Business }
  | { type: 'announcement'; id: ID; title: string; body?: string };

export type Notification = {
  id: ID;
  icon?: string;
  title: string;
  body?: string;
  link?: string | null;
  read_at?: string | null;
  created_at: string;
};

export type AuthResponse = {
  token?: string;
  user: User;
  needs_verification?: boolean;
  otp_sent?: boolean;
  debug_code?: string | null;
};

export type Zone = {
  id: ID;
  name: string;
  slug: string;
};

export type HomeShortcut = {
  key: string;
  label: string;
  icon: string;
  href: string;
};

export type HomeBanner = {
  id: ID;
  title?: string | null;
  desc?: string | null;
  cta?: string | null;
  image?: string | null;
  href?: string | null;
  bg_from?: string | null;
  bg_to?: string | null;
};

export type HomeFeed = {
  shortcuts: HomeShortcut[];
  popular_searches: string[];
  categories: (Category & { color?: string | null })[];
  promoted: Business[];
  top_rated: Business[];
  banners: HomeBanner[];
  unread_count: number;
};

export type Category = {
  slug: string;
  label: string;
  icon?: string;
  count?: number;
};

export type Alert = {
  id: ID;
  type: string;
  description: string;
  lat?: number | null;
  lng?: number | null;
  confirmations: number;
  is_verified: boolean;
  is_resolved: boolean;
  expires_at?: string | null;
  created_at: string;
  zone?: string | null;
};

export type Event = {
  id: ID;
  kind: string;
  title: string;
  description?: string | null;
  location?: string | null;
  lat?: number | null;
  lng?: number | null;
  starts_at: string;
  ends_at?: string | null;
  cover_url?: string | null;
  contact_phone?: string | null;
  attendees_count: number;
  status: string;
};

export type Listing = {
  id: ID;
  kind: string;
  category: string;
  title: string;
  description?: string | null;
  price?: number | null;
  currency: string;
  negotiable: boolean;
  photo_url?: string | null;
  lat?: number | null;
  lng?: number | null;
  contact_phone?: string | null;
  contact_whatsapp?: string | null;
  status: string;
  is_featured: boolean;
  views: number;
  expires_at?: string | null;
  created_at: string;
};

export type OrderItem = {
  id: ID;
  name: string;
  qty: number;
  unit_price: number;
  line_total: number;
};

export type Order = {
  id: ID;
  business_id: ID;
  business?: Business;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  delivery_fee: number;
  subtotal: number;
  currency: string;
  notes?: string | null;
  status: string;
  items?: OrderItem[];
  created_at: string;
};

export type Booking = {
  id: ID;
  business_id: ID;
  business?: Business;
  name: string;
  phone: string;
  starts_at: string;
  duration_minutes: number;
  status: string;
  notes?: string | null;
  created_at: string;
};

export type Review = {
  id: ID;
  rating: number;
  body?: string | null;
  author_name?: string | null;
  source?: string | null;
  reviewed_at: string;
  created_at: string;
};

export type MenuItem = {
  id: ID;
  name: string;
  description?: string | null;
  price: number;
  photo_url?: string | null;
  is_available: boolean;
};

export type MenuCategory = {
  id: ID;
  name: string;
  sort: number;
  items: MenuItem[];
};

export type Price = {
  id: ID;
  product?: string | null;
  product_id?: ID | null;
  price: number;
  shop_name?: string | null;
  photo_url?: string | null;
  notes?: string | null;
  zone?: string | null;
  reporter?: string | null;
  created_at: string;
};

export type Photo = {
  id: ID;
  url: string;
  created_at: string;
};
