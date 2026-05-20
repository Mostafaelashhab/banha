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

// Re-exports for shorter imports
export type { Paginated, FeedItem, Notification, User };
