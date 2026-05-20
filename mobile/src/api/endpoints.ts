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
