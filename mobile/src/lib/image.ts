import { API_BASE_URL } from '@/api/config';

/**
 * Resolve an image URL coming from the API.
 *  - Absolute http(s) URLs are returned as-is.
 *  - Paths that start with "/" are prefixed with API_BASE_URL.
 *  - Falsy / empty values return null so callers can render a fallback.
 */
export function imageUrl(input: string | null | undefined): string | null {
  if (!input) return null;
  const s = String(input).trim();
  if (!s) return null;
  if (/^https?:\/\//i.test(s)) return s;
  if (s.startsWith('//')) return `https:${s}`;
  if (s.startsWith('/')) return `${API_BASE_URL.replace(/\/+$/, '')}${s}`;
  return `${API_BASE_URL.replace(/\/+$/, '')}/${s}`;
}

/**
 * Pick a deterministic brand-ish colour from a string (for fallback chips).
 */
const PALETTE = ['#2D5BFF', '#1FA857', '#F5BA12', '#E64646', '#6A5ACD', '#FF7A4D', '#1736B0', '#0D8A3F'];
export function colorFromName(name?: string | null): string {
  if (!name) return PALETTE[0];
  let h = 0;
  for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) >>> 0;
  return PALETTE[h % PALETTE.length];
}
