import React from 'react';
import { ActivityIndicator, Pressable, StyleSheet, Text, View, ViewStyle, TextStyle } from 'react-native';
import { colors, radius, spacing, typography } from '../theme';
import { Icon, IconName } from './Icon';

export type ButtonVariant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' | 'whatsapp';
export type ButtonSize = 'sm' | 'md' | 'lg';

type Props = {
  children: React.ReactNode;
  variant?: ButtonVariant;
  size?: ButtonSize;
  icon?: IconName;
  iconEnd?: boolean;
  onPress?: () => void;
  loading?: boolean;
  disabled?: boolean;
  block?: boolean;
  pill?: boolean;
  style?: ViewStyle;
};

const sizeStyles: Record<ButtonSize, { padV: number; padH: number; height: number; textSize: number; textWeight: TextStyle['fontWeight']; iconSize: number }> = {
  sm: { padV: spacing[1.5], padH: spacing[3], height: 32, textSize: 12, textWeight: '800', iconSize: 14 },
  md: { padV: spacing[2.5], padH: spacing[4], height: 40, textSize: 14, textWeight: '800', iconSize: 16 },
  lg: { padV: spacing[3.5], padH: spacing[5], height: 52, textSize: 14, textWeight: '900', iconSize: 18 },
};

function variantPalette(v: ButtonVariant) {
  switch (v) {
    case 'primary':
      return { bg: colors.coral[500], bgPressed: colors.coral[600], text: colors.white, border: colors.transparent };
    case 'secondary':
      return { bg: colors.cream[100], bgPressed: colors.cream[200], text: colors.ink[950], border: colors.transparent };
    case 'outline':
      return { bg: colors.white, bgPressed: colors.coral[50], text: colors.coral[600], border: 'rgba(45,91,255,0.3)' };
    case 'ghost':
      return { bg: colors.transparent, bgPressed: colors.cream[100], text: colors.ink[700], border: colors.transparent };
    case 'danger':
      return { bg: colors.blush[500], bgPressed: colors.blush[600], text: colors.white, border: colors.transparent };
    case 'whatsapp':
      return { bg: '#25D366', bgPressed: '#1FB959', text: colors.white, border: colors.transparent };
  }
}

export function Button({
  children,
  variant = 'primary',
  size = 'md',
  icon,
  iconEnd,
  onPress,
  loading,
  disabled,
  block,
  pill,
  style,
}: Props) {
  const p = variantPalette(variant);
  const s = sizeStyles[size];
  const isDisabled = disabled || loading;

  return (
    <Pressable
      accessibilityRole="button"
      onPress={onPress}
      disabled={isDisabled}
      style={({ pressed }) => [
        styles.base,
        {
          backgroundColor: pressed && !isDisabled ? p.bgPressed : p.bg,
          borderColor: p.border,
          paddingVertical: s.padV,
          paddingHorizontal: s.padH,
          minHeight: s.height,
          borderRadius: pill ? radius.full : radius.xl,
          alignSelf: block ? 'stretch' : 'flex-start',
          opacity: isDisabled ? 0.6 : 1,
        },
        style,
      ]}
    >
      <View style={[styles.row, { flexDirection: iconEnd ? 'row-reverse' : 'row' }]}>
        {loading ? (
          <ActivityIndicator color={p.text} size="small" />
        ) : (
          <>
            {icon && <Icon name={icon} size={s.iconSize} color={p.text} />}
            <Text
              style={[
                styles.text,
                { color: p.text, fontSize: s.textSize, fontWeight: s.textWeight },
              ]}
              numberOfLines={1}
            >
              {children}
            </Text>
          </>
        )}
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: {
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  row: {
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing[2],
  },
  text: {
    ...typography.bodyStrong,
  },
});
