import { TextStyle } from 'react-native';

export const fontFamily = {
  regular: 'Cairo_500Medium',
  medium: 'Cairo_500Medium',
  bold: 'Cairo_700Bold',
  extrabold: 'Cairo_800ExtraBold',
  black: 'Cairo_900Black',
} as const;

export const fontWeight = {
  medium: '500',
  bold: '700',
  extrabold: '800',
  black: '900',
} as const satisfies Record<string, TextStyle['fontWeight']>;

type TextStylePreset = {
  fontSize: number;
  fontFamily: string;
  fontWeight: TextStyle['fontWeight'];
  lineHeight?: number;
  letterSpacing?: number;
  textTransform?: TextStyle['textTransform'];
};

export const typography: Record<string, TextStylePreset> = {
  h1: {
    fontSize: 24,
    fontFamily: fontFamily.black,
    fontWeight: fontWeight.black,
    lineHeight: 32,
  },
  h2: {
    fontSize: 20,
    fontFamily: fontFamily.extrabold,
    fontWeight: fontWeight.extrabold,
    lineHeight: 28,
  },
  h3: {
    fontSize: 16,
    fontFamily: fontFamily.extrabold,
    fontWeight: fontWeight.extrabold,
    lineHeight: 22,
  },
  body: {
    fontSize: 14,
    fontFamily: fontFamily.medium,
    fontWeight: fontWeight.medium,
    lineHeight: 22,
  },
  bodyStrong: {
    fontSize: 14,
    fontFamily: fontFamily.bold,
    fontWeight: fontWeight.bold,
    lineHeight: 22,
  },
  meta: {
    fontSize: 12,
    fontFamily: fontFamily.bold,
    fontWeight: fontWeight.bold,
    lineHeight: 16,
  },
  micro: {
    fontSize: 11,
    fontFamily: fontFamily.extrabold,
    fontWeight: fontWeight.extrabold,
    lineHeight: 14,
  },
  nano: {
    fontSize: 10,
    fontFamily: fontFamily.extrabold,
    fontWeight: fontWeight.extrabold,
    lineHeight: 12,
    letterSpacing: 0.6,
    textTransform: 'uppercase',
  },
};
