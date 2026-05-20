export const colors = {
  coral: {
    50: '#EEF2FF',
    100: '#DCE4FF',
    200: '#B6C5FF',
    300: '#8AA1FF',
    400: '#5C7DFF',
    500: '#2D5BFF',
    600: '#1F46DB',
    700: '#1736B0',
    800: '#0F2787',
  },
  honey: {
    300: '#FFE082',
    400: '#FFD440',
    500: '#F5BA12',
  },
  cream: {
    50: '#FAFAFC',
    100: '#F4F5F8',
    200: '#E9EBF1',
  },
  ink: {
    300: '#B5B5BC',
    400: '#84848E',
    500: '#5C5C66',
    700: '#232328',
    800: '#1A1A1E',
    900: '#131316',
    950: '#0B0B0C',
  },
  mint: {
    100: '#D8F5E2',
    500: '#1FA857',
    700: '#0D8A3F',
  },
  blush: {
    100: '#FCE0E0',
    500: '#E64646',
    600: '#CC3535',
  },
  white: '#FFFFFF',
  black: '#000000',
  transparent: 'transparent',
} as const;

export type ColorTone = 'coral' | 'mint' | 'honey' | 'blush' | 'cream';
