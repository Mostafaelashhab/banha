import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig } from 'axios';
import * as SecureStore from 'expo-secure-store';
import { API_BASE_URL, API_TIMEOUT_MS, TOKEN_STORE_KEY } from './config';

export class ApiError<T = unknown> extends Error {
  status: number;
  data: T | undefined;
  constructor(message: string, status: number, data?: T) {
    super(message);
    this.status = status;
    this.data = data;
  }
}

let cachedToken: string | null | undefined;

async function readToken(): Promise<string | null> {
  if (cachedToken !== undefined) return cachedToken;
  try {
    cachedToken = (await SecureStore.getItemAsync(TOKEN_STORE_KEY)) ?? null;
  } catch {
    cachedToken = null;
  }
  return cachedToken;
}

export async function setAuthToken(token: string | null) {
  cachedToken = token;
  if (token) {
    await SecureStore.setItemAsync(TOKEN_STORE_KEY, token);
  } else {
    await SecureStore.deleteItemAsync(TOKEN_STORE_KEY);
  }
}

type ClientEvents = {
  unauthorized: () => void;
};

const listeners: { [K in keyof ClientEvents]: ClientEvents[K][] } = {
  unauthorized: [],
};

export function onUnauthorized(cb: () => void) {
  listeners.unauthorized.push(cb);
  return () => {
    listeners.unauthorized = listeners.unauthorized.filter((l) => l !== cb);
  };
}

function emit<K extends keyof ClientEvents>(event: K) {
  listeners[event].forEach((cb) => cb());
}

export const api: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  timeout: API_TIMEOUT_MS,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

api.interceptors.request.use(async (cfg: InternalAxiosRequestConfig) => {
  const token = await readToken();
  if (token && cfg.headers) {
    cfg.headers.set('Authorization', `Bearer ${token}`);
  }
  return cfg;
});

api.interceptors.response.use(
  (res) => res,
  (err: AxiosError) => {
    const status = err.response?.status ?? 0;
    if (status === 401 || status === 419) {
      emit('unauthorized');
    }
    const data = err.response?.data as { message?: string } | undefined;
    const message = data?.message ?? err.message ?? 'Network error';
    return Promise.reject(new ApiError(message, status, err.response?.data));
  },
);

