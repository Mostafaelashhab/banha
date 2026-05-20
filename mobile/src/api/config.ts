import Constants from 'expo-constants';

const extra = (Constants.expoConfig?.extra ?? {}) as { apiBaseUrl?: string };

export const API_BASE_URL =
  extra.apiBaseUrl ?? process.env.EXPO_PUBLIC_API_URL ?? 'http://localhost:8000';

export const API_TIMEOUT_MS = 15_000;

export const TOKEN_STORE_KEY = 'banhawy.auth.token';
export const USER_STORE_KEY = 'banhawy.auth.user';
