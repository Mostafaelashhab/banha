import { Easing } from 'react-native';

export const motion = {
  duration: {
    fast: 150,
    base: 200,
    slow: 350,
  },
  easing: {
    spring: Easing.bezier(0.2, 0.9, 0.3, 1),
    out: Easing.out(Easing.cubic),
  },
} as const;
