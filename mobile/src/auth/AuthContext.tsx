import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import * as SecureStore from 'expo-secure-store';
import { onUnauthorized, setAuthToken } from '@/api/client';
import * as endpoints from '@/api/endpoints';
import { USER_STORE_KEY } from '@/api/config';
import { User } from '@/api/types';

type AuthState =
  | { status: 'loading'; user: null }
  | { status: 'authenticated'; user: User }
  | { status: 'guest'; user: null };

type AuthValue = AuthState & {
  login: (payload: { phone: string; password: string }) => Promise<{ needs_verification: boolean }>;
  signup: (payload: endpoints.SignupPayload) => Promise<{ needs_verification: boolean; debug_code?: string | null }>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
};

const AuthContext = createContext<AuthValue | undefined>(undefined);

async function readStoredUser(): Promise<User | null> {
  try {
    const raw = await SecureStore.getItemAsync(USER_STORE_KEY);
    return raw ? (JSON.parse(raw) as User) : null;
  } catch {
    return null;
  }
}

async function writeStoredUser(user: User | null) {
  if (user) {
    await SecureStore.setItemAsync(USER_STORE_KEY, JSON.stringify(user));
  } else {
    await SecureStore.deleteItemAsync(USER_STORE_KEY);
  }
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [state, setState] = useState<AuthState>({ status: 'loading', user: null });

  useEffect(() => {
    (async () => {
      const user = await readStoredUser();
      if (user) {
        setState({ status: 'authenticated', user });
        // refresh in background — don't block
        endpoints.me().then(
          (fresh) => {
            setState({ status: 'authenticated', user: fresh });
            writeStoredUser(fresh).catch(() => {});
          },
          () => {
            // keep cached user; interceptor will fire onUnauthorized if session is dead
          },
        );
      } else {
        setState({ status: 'guest', user: null });
      }
    })();
  }, []);

  useEffect(() => {
    return onUnauthorized(() => {
      setAuthToken(null).catch(() => {});
      writeStoredUser(null).catch(() => {});
      setState({ status: 'guest', user: null });
    });
  }, []);

  const login = useCallback(async (payload: { phone: string; password: string }) => {
    const res = await endpoints.login(payload);
    if (res.token) await setAuthToken(res.token);
    await writeStoredUser(res.user);
    setState({ status: 'authenticated', user: res.user });
    return { needs_verification: !!res.needs_verification };
  }, []);

  const signup = useCallback(async (payload: endpoints.SignupPayload) => {
    const res = await endpoints.signup(payload);
    if (res.token) await setAuthToken(res.token);
    await writeStoredUser(res.user);
    setState({ status: 'authenticated', user: res.user });
    return {
      needs_verification: !!res.needs_verification,
      debug_code: res.debug_code ?? null,
    };
  }, []);

  const logout = useCallback(async () => {
    try {
      await endpoints.logout();
    } catch {
      // ignore
    }
    await setAuthToken(null);
    await writeStoredUser(null);
    setState({ status: 'guest', user: null });
  }, []);

  const refreshUser = useCallback(async () => {
    try {
      const user = await endpoints.me();
      await writeStoredUser(user);
      setState({ status: 'authenticated', user });
    } catch {
      // ignore
    }
  }, []);

  const value = useMemo<AuthValue>(
    () => ({ ...state, login, signup, logout, refreshUser }),
    [state, login, signup, logout, refreshUser],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthValue {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used inside AuthProvider');
  return ctx;
}
