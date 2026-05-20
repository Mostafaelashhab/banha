import { api } from './client';
import {
  AuthResponse,
  Business,
  Category,
  FeedItem,
  ID,
  Notification,
  Paginated,
  Post,
  User,
} from './types';

const V1 = '/api/v1';

// ─── Auth ────────────────────────────────────────────────────────────
export async function login(payload: { phone: string; password: string }) {
  const { data } = await api.post<AuthResponse>(`${V1}/login`, {
    ...payload,
    device: 'mobile',
  });
  return data;
}

export async function signup(payload: { name: string; phone: string; password: string; password_confirmation: string }) {
  const { data } = await api.post<AuthResponse>(`${V1}/signup`, {
    ...payload,
    device: 'mobile',
  });
  return data;
}

export async function logout() {
  await api.post(`${V1}/logout`);
}

export async function me() {
  const { data } = await api.get<{ user: User }>(`${V1}/me`);
  return data.user;
}

// ─── Feed ────────────────────────────────────────────────────────────
export type FeedParams = { page?: number; category?: string };
export async function fetchFeed(params: FeedParams = {}) {
  const { data } = await api.get<Paginated<FeedItem>>(`${V1}/feed`, { params });
  return data;
}

export async function fetchFollowingFeed(params: FeedParams = {}) {
  const { data } = await api.get<Paginated<FeedItem>>(`${V1}/following`, { params });
  return data;
}

// ─── Directory / Businesses ──────────────────────────────────────────
export type DirectoryParams = {
  page?: number;
  category?: string;
  q?: string;
  lat?: number;
  lng?: number;
  radius_km?: number;
  open_now?: boolean;
};

export async function fetchDirectory(params: DirectoryParams = {}) {
  const { data } = await api.get<Paginated<Business>>(`${V1}/directory`, { params });
  return data;
}

export async function fetchBusiness(slug: string) {
  const { data } = await api.get<{ business: Business }>(`${V1}/biz/${slug}`);
  return data.business;
}

export async function trackBusinessClick(id: ID) {
  await api.post(`${V1}/track/business-click`, { business_id: id });
}

// ─── Search ──────────────────────────────────────────────────────────
export type SearchParams = { q: string; category?: string };
export async function search(params: SearchParams) {
  const { data } = await api.get<{ businesses: Business[]; posts: Post[] }>(`${V1}/search`, { params });
  return data;
}

// ─── Notifications ───────────────────────────────────────────────────
export async function fetchNotifications() {
  const { data } = await api.get<Paginated<Notification>>(`${V1}/notifications`);
  return data;
}

export async function markAllNotificationsRead() {
  await api.post(`${V1}/notifications/read-all`);
}

// ─── Profile ─────────────────────────────────────────────────────────
export async function fetchMyProfile() {
  const { data } = await api.get<{ user: User; stats: { saves: number; orders: number; listings: number } }>(
    `${V1}/profile`,
  );
  return data;
}

export async function fetchUserProfile(username: string) {
  const { data } = await api.get<{ user: User }>(`${V1}/u/${username}`);
  return data.user;
}

// ─── Categories / Open-now / Offers ──────────────────────────────────
export async function fetchCategories() {
  const { data } = await api.get<{ categories: Category[] }>(`${V1}/directory/categories`);
  return data.categories;
}

export async function fetchOpenNow(params: { lat?: number; lng?: number } = {}) {
  const { data } = await api.get<Paginated<Business>>(`${V1}/open-now`, { params });
  return data;
}

export async function fetchOffers() {
  const { data } = await api.get<Paginated<Business>>(`${V1}/offers`);
  return data;
}

// ─── Areas (geo) ─────────────────────────────────────────────────────
export async function nearestArea(payload: { lat: number; lng: number }) {
  const { data } = await api.get<{ area: { id: ID; name: string } }>(`${V1}/areas/nearest`, { params: payload });
  return data.area;
}

// ─── Community: alerts / events / posts ──────────────────────────────
import {
  Alert as AlertT,
  Event as EventT,
  Listing,
  Order,
  Booking,
  Review,
  MenuCategory,
  Price,
  Photo,
} from './types';

export async function fetchAlerts() {
  const { data } = await api.get<Paginated<AlertT>>(`${V1}/alerts`);
  return data;
}

export async function fetchEvents() {
  const { data } = await api.get<Paginated<EventT>>(`${V1}/events`);
  return data;
}

export async function fetchPosts() {
  const { data } = await api.get<Paginated<Post>>(`${V1}/posts`);
  return data;
}

// ─── Marketplace ─────────────────────────────────────────────────────
export type MarketplaceParams = { page?: number; kind?: string; category?: string; q?: string };
export async function fetchMarketplace(params: MarketplaceParams = {}) {
  const { data } = await api.get<Paginated<Listing>>(`${V1}/marketplace`, { params });
  return data;
}

export async function fetchListing(id: ID) {
  const { data } = await api.get<{ listing: Listing }>(`${V1}/marketplace/${id}`);
  return data.listing;
}

// ─── Business sub-resources ──────────────────────────────────────────
export async function fetchMenu(slug: string) {
  const { data } = await api.get<{ business: { id: ID; name: string; currency: string }; menu: MenuCategory[] }>(
    `${V1}/biz/${slug}/menu`,
  );
  return data;
}

export async function fetchReviews(slug: string) {
  const { data } = await api.get<Paginated<Review> & { meta: { rating_avg?: number; ratings_count?: number } }>(
    `${V1}/biz/${slug}/reviews`,
  );
  return data;
}

export async function submitReview(slug: string, payload: { rating: number; body?: string }) {
  const { data } = await api.post<{ review: Review }>(`${V1}/biz/${slug}/reviews`, payload);
  return data.review;
}

export async function fetchBusinessPhotos(slug: string) {
  const { data } = await api.get<{ photos: Photo[] }>(`${V1}/biz/${slug}/photos`);
  return data.photos;
}

// ─── Prices ──────────────────────────────────────────────────────────
export async function fetchPrices() {
  const { data } = await api.get<Paginated<Price>>(`${V1}/prices`);
  return data;
}

// ─── Bookmarks ───────────────────────────────────────────────────────
export async function fetchBookmarks() {
  const { data } = await api.get<{ data: Business[] }>(`${V1}/bookmarks`);
  return data;
}

export async function toggleBookmark(business_id: ID) {
  const { data } = await api.post<{ bookmarked: boolean }>(`${V1}/bookmarks/toggle`, { business_id });
  return data.bookmarked;
}

// ─── Orders ──────────────────────────────────────────────────────────
export async function fetchMyOrders() {
  const { data } = await api.get<Paginated<Order>>(`${V1}/orders`);
  return data;
}

export async function fetchOrder(id: ID) {
  const { data } = await api.get<{ order: Order }>(`${V1}/orders/${id}`);
  return data.order;
}

export type CreateOrderPayload = {
  business_id: ID;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  notes?: string;
  area_id?: ID;
  items: { menu_item_id: ID; qty: number }[];
};

export async function createOrder(payload: CreateOrderPayload) {
  const { data } = await api.post<{ order: Order }>(`${V1}/orders`, payload);
  return data.order;
}

// ─── Bookings ────────────────────────────────────────────────────────
export async function fetchMyBookings() {
  const { data } = await api.get<Paginated<Booking>>(`${V1}/bookings`);
  return data;
}

export type CreateBookingPayload = {
  business_id: ID;
  name: string;
  phone: string;
  starts_at: string;
  duration_minutes?: number;
  notes?: string;
};

export async function createBooking(payload: CreateBookingPayload) {
  const { data } = await api.post<{ booking: Booking }>(`${V1}/bookings`, payload);
  return data.booking;
}
