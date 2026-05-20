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
  category?: string;
  cover_url?: string | null;
  logo_url?: string | null;
  lat?: number | null;
  lng?: number | null;
  address?: string | null;
  phone?: string | null;
  whatsapp?: string | null;
  is_open?: boolean;
  rating?: number | null;
  reviews_count?: number;
  is_verified?: boolean;
  tier?: 'silver' | 'gold' | null;
  is_sponsored?: boolean;
  distance_m?: number | null;
};

export type Post = {
  id: ID;
  body: string;
  created_at: string;
  author: User;
  business?: Business | null;
  images?: string[];
  likes_count?: number;
  comments_count?: number;
  liked?: boolean;
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
};

export type Category = {
  slug: string;
  label: string;
  icon?: string;
  count?: number;
};
