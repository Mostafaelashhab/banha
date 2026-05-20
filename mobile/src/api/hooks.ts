import { useQuery, useMutation, useQueryClient, UseQueryOptions } from '@tanstack/react-query';
import * as endpoints from './endpoints';
import { Business, FeedItem, Notification, Paginated, User } from './types';

export const queryKeys = {
  feed: (category?: string) => ['feed', category ?? 'all'] as const,
  followingFeed: () => ['feed', 'following'] as const,
  directory: (params?: endpoints.DirectoryParams) => ['directory', params ?? {}] as const,
  business: (slug: string) => ['business', slug] as const,
  search: (q: string, category?: string) => ['search', q, category ?? 'all'] as const,
  notifications: () => ['notifications'] as const,
  myProfile: () => ['profile', 'me'] as const,
  userProfile: (username: string) => ['profile', username] as const,
  categories: () => ['categories'] as const,
  openNow: (lat?: number, lng?: number) => ['open-now', lat, lng] as const,
  offers: () => ['offers'] as const,
  alerts: () => ['alerts'] as const,
  events: () => ['events'] as const,
  posts: () => ['posts'] as const,
  marketplace: (params?: endpoints.MarketplaceParams) => ['marketplace', params ?? {}] as const,
  listing: (id: number | string) => ['listing', String(id)] as const,
  menu: (slug: string) => ['menu', slug] as const,
  reviews: (slug: string) => ['reviews', slug] as const,
  businessPhotos: (slug: string) => ['business-photos', slug] as const,
  prices: () => ['prices'] as const,
  bookmarks: () => ['bookmarks'] as const,
  orders: () => ['orders'] as const,
  order: (id: number | string) => ['order', String(id)] as const,
  bookings: () => ['bookings'] as const,
};

export function useFeed(category?: string) {
  return useQuery({
    queryKey: queryKeys.feed(category),
    queryFn: () => endpoints.fetchFeed({ category }),
  });
}

export function useFollowingFeed() {
  return useQuery({
    queryKey: queryKeys.followingFeed(),
    queryFn: () => endpoints.fetchFollowingFeed(),
  });
}

export function useDirectory(params: endpoints.DirectoryParams = {}) {
  return useQuery({
    queryKey: queryKeys.directory(params),
    queryFn: () => endpoints.fetchDirectory(params),
  });
}

export function useBusiness(slug: string, options?: Partial<UseQueryOptions<Business>>) {
  return useQuery<Business>({
    queryKey: queryKeys.business(slug),
    queryFn: () => endpoints.fetchBusiness(slug),
    enabled: !!slug,
    ...options,
  });
}

export function useSearch(q: string, category?: string) {
  return useQuery({
    queryKey: queryKeys.search(q, category),
    queryFn: () => endpoints.search({ q, category }),
    enabled: q.trim().length > 0,
  });
}

export function useNotifications() {
  return useQuery({
    queryKey: queryKeys.notifications(),
    queryFn: () => endpoints.fetchNotifications(),
  });
}

export function useMarkAllRead() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: endpoints.markAllNotificationsRead,
    onSuccess: () => qc.invalidateQueries({ queryKey: queryKeys.notifications() }),
  });
}

export function useMyProfile() {
  return useQuery({
    queryKey: queryKeys.myProfile(),
    queryFn: () => endpoints.fetchMyProfile(),
  });
}

export function useCategories() {
  return useQuery({
    queryKey: queryKeys.categories(),
    queryFn: () => endpoints.fetchCategories(),
    staleTime: 30 * 60_000,
  });
}

export function useOpenNow(lat?: number, lng?: number) {
  return useQuery({
    queryKey: queryKeys.openNow(lat, lng),
    queryFn: () => endpoints.fetchOpenNow({ lat, lng }),
  });
}

export function useOffers() {
  return useQuery({
    queryKey: queryKeys.offers(),
    queryFn: () => endpoints.fetchOffers(),
  });
}

export function useTrackBusinessClick() {
  return useMutation({
    mutationFn: (id: Business['id']) => endpoints.trackBusinessClick(id),
  });
}

// ─── Community ───────────────────────────────────────────────────────
export function useAlerts() {
  return useQuery({ queryKey: queryKeys.alerts(), queryFn: endpoints.fetchAlerts });
}

export function useEvents() {
  return useQuery({ queryKey: queryKeys.events(), queryFn: endpoints.fetchEvents });
}

export function usePosts() {
  return useQuery({ queryKey: queryKeys.posts(), queryFn: endpoints.fetchPosts });
}

// ─── Marketplace ─────────────────────────────────────────────────────
export function useMarketplace(params: endpoints.MarketplaceParams = {}) {
  return useQuery({
    queryKey: queryKeys.marketplace(params),
    queryFn: () => endpoints.fetchMarketplace(params),
  });
}

export function useListing(id: number | string) {
  return useQuery({
    queryKey: queryKeys.listing(id),
    queryFn: () => endpoints.fetchListing(id),
    enabled: !!id,
  });
}

// ─── Business sub-resources ──────────────────────────────────────────
export function useMenu(slug: string) {
  return useQuery({
    queryKey: queryKeys.menu(slug),
    queryFn: () => endpoints.fetchMenu(slug),
    enabled: !!slug,
  });
}

export function useReviews(slug: string) {
  return useQuery({
    queryKey: queryKeys.reviews(slug),
    queryFn: () => endpoints.fetchReviews(slug),
    enabled: !!slug,
  });
}

export function useSubmitReview(slug: string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: { rating: number; body?: string }) =>
      endpoints.submitReview(slug, payload),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: queryKeys.reviews(slug) });
      qc.invalidateQueries({ queryKey: queryKeys.business(slug) });
    },
  });
}

export function useBusinessPhotos(slug: string) {
  return useQuery({
    queryKey: queryKeys.businessPhotos(slug),
    queryFn: () => endpoints.fetchBusinessPhotos(slug),
    enabled: !!slug,
  });
}

// ─── Prices ──────────────────────────────────────────────────────────
export function usePrices() {
  return useQuery({ queryKey: queryKeys.prices(), queryFn: endpoints.fetchPrices });
}

// ─── Bookmarks ───────────────────────────────────────────────────────
export function useBookmarks() {
  return useQuery({ queryKey: queryKeys.bookmarks(), queryFn: endpoints.fetchBookmarks });
}

export function useToggleBookmark() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (business_id: Business['id']) => endpoints.toggleBookmark(business_id),
    onSuccess: () => qc.invalidateQueries({ queryKey: queryKeys.bookmarks() }),
  });
}

// ─── Orders ──────────────────────────────────────────────────────────
export function useMyOrders() {
  return useQuery({ queryKey: queryKeys.orders(), queryFn: endpoints.fetchMyOrders });
}

export function useOrder(id: number | string) {
  return useQuery({
    queryKey: queryKeys.order(id),
    queryFn: () => endpoints.fetchOrder(id),
    enabled: !!id,
  });
}

export function useCreateOrder() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: endpoints.createOrder,
    onSuccess: () => qc.invalidateQueries({ queryKey: queryKeys.orders() }),
  });
}

// ─── Bookings ────────────────────────────────────────────────────────
export function useMyBookings() {
  return useQuery({ queryKey: queryKeys.bookings(), queryFn: endpoints.fetchMyBookings });
}

export function useCreateBooking() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: endpoints.createBooking,
    onSuccess: () => qc.invalidateQueries({ queryKey: queryKeys.bookings() }),
  });
}

// Re-exports for shorter imports
export type { Paginated, FeedItem, Notification, User };
