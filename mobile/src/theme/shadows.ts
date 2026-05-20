import { Platform, ViewStyle } from 'react-native';

const make = (
  ios: { offset: [number, number]; opacity: number; radius: number; color?: string },
  androidElevation: number,
): ViewStyle =>
  Platform.select<ViewStyle>({
    ios: {
      shadowColor: ios.color ?? '#0F0F14',
      shadowOffset: { width: ios.offset[0], height: ios.offset[1] },
      shadowOpacity: ios.opacity,
      shadowRadius: ios.radius,
    },
    android: { elevation: androidElevation },
    default: {},
  }) as ViewStyle;

export const shadows = {
  card: make({ offset: [0, 6], opacity: 0.08, radius: 14 }, 2),
  soft: make({ offset: [0, 10], opacity: 0.12, radius: 22 }, 4),
  glow: make({ offset: [0, 24], opacity: 0.32, radius: 32, color: '#2D5BFF' }, 8),
} as const;
