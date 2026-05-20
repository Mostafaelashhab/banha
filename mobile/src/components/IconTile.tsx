import React from 'react';
import { Pressable, StyleSheet, View, ViewStyle } from 'react-native';
import { colors, ColorTone, radius } from '../theme';
import { Icon, IconName } from './Icon';

type Size = 'sm' | 'md' | 'lg' | 'xl';
type Shape = 'square' | 'circle';
type Intensity = 'soft' | 'strong';

type Props = {
  icon: IconName;
  tone?: ColorTone;
  size?: Size;
  shape?: Shape;
  intensity?: Intensity;
  onPress?: () => void;
  accessibilityLabel?: string;
};

const sizeMap: Record<Size, { box: number; icon: number; r: number }> = {
  sm: { box: 24, icon: 14, r: radius.md },
  md: { box: 40, icon: 20, r: radius.xl },
  lg: { box: 48, icon: 22, r: radius.xl },
  xl: { box: 56, icon: 26, r: radius['2xl'] },
};

function tonePalette(tone: ColorTone, intensity: Intensity) {
  const soft = {
    coral: { bg: colors.coral[50], fg: colors.coral[600] },
    mint: { bg: colors.mint[100], fg: colors.mint[700] },
    honey: { bg: '#FFF6D6', fg: colors.honey[500] },
    blush: { bg: colors.blush[100], fg: colors.blush[500] },
    cream: { bg: colors.cream[100], fg: colors.ink[700] },
  } as const;
  const strong = {
    coral: { bg: colors.coral[500], fg: colors.white },
    mint: { bg: colors.mint[500], fg: colors.white },
    honey: { bg: colors.honey[500], fg: colors.ink[950] },
    blush: { bg: colors.blush[500], fg: colors.white },
    cream: { bg: colors.cream[200], fg: colors.ink[950] },
  } as const;
  return intensity === 'strong' ? strong[tone] : soft[tone];
}

export function IconTile({
  icon,
  tone = 'coral',
  size = 'md',
  shape = 'square',
  intensity = 'soft',
  onPress,
  accessibilityLabel,
}: Props) {
  const s = sizeMap[size];
  const p = tonePalette(tone, intensity);
  const containerStyle: ViewStyle = {
    width: s.box,
    height: s.box,
    backgroundColor: p.bg,
    borderRadius: shape === 'circle' ? radius.full : s.r,
    alignItems: 'center',
    justifyContent: 'center',
  };

  if (onPress) {
    return (
      <Pressable
        accessibilityRole="button"
        accessibilityLabel={accessibilityLabel}
        onPress={onPress}
        style={({ pressed }) => [containerStyle, pressed && { opacity: 0.85 }]}
      >
        <Icon name={icon} size={s.icon} color={p.fg} />
      </Pressable>
    );
  }

  return (
    <View style={containerStyle} accessibilityLabel={accessibilityLabel}>
      <Icon name={icon} size={s.icon} color={p.fg} />
    </View>
  );
}

// Keep a single ViewStyle reference for testing
StyleSheet.create({});
