import { ExpoConfig, ConfigContext } from 'expo/config';

export default ({ config }: ConfigContext): ExpoConfig => ({
  ...(config as ExpoConfig),
  name: 'Banhawy',
  slug: 'banhawy',
  scheme: 'banhawy',
  version: '1.0.0',
  orientation: 'portrait',
  icon: './assets/icon.png',
  userInterfaceStyle: 'light',
  newArchEnabled: true,
  splash: {
    image: './assets/splash-icon.png',
    resizeMode: 'contain',
    backgroundColor: '#EEF2FF',
  },
  ios: {
    supportsTablet: true,
    bundleIdentifier: 'com.banhawy.app',
    config: {
      usesNonExemptEncryption: false,
    },
    infoPlist: {
      NSLocationWhenInUseUsageDescription:
        'علشان نوريك الأماكن القريبة منك في بنها',
    },
  },
  android: {
    package: 'com.banhawy.app',
    adaptiveIcon: {
      foregroundImage: './assets/adaptive-icon.png',
      backgroundColor: '#EEF2FF',
    },
    edgeToEdgeEnabled: true,
    predictiveBackGestureEnabled: false,
    permissions: ['ACCESS_FINE_LOCATION', 'ACCESS_COARSE_LOCATION'],
    config: {
      googleMaps: {
        apiKey: process.env.GOOGLE_MAPS_ANDROID_KEY ?? '',
      },
    },
  },
  web: {
    favicon: './assets/favicon.png',
    bundler: 'metro',
  },
  locales: {
    ar: './assets/locales/ar.json',
  },
  plugins: [
    'expo-router',
    'expo-font',
    'expo-localization',
    'expo-secure-store',
    [
      'expo-location',
      {
        locationAlwaysAndWhenInUsePermission:
          'علشان نوريك المحلات القريبة وتحدد موقعك على الخريطة',
      },
    ],
  ],
  extra: {
    router: {},
    apiBaseUrl: process.env.EXPO_PUBLIC_API_URL ?? 'http://10.0.2.2:8000',
  },
});
