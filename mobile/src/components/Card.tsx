import React from 'react';
import { View, ViewProps, ViewStyle, StyleProp, Pressable, StyleSheet } from 'react-native';
import { colors, radius, shadows, spacing } from '../theme';

type Padding = 'none' | 'sm' | 'md' | 'lg';
type Tier = 'silver' | 'gold';
type Variant = 'default' | 'sponsored' | 'announcement' | 'dark';

type Props = Omit<ViewProps, 'style'> & {
  padding?: Padding;
  tier?: Tier;
  variant?: Variant;
  onPress?: () => void;
  style?: StyleProp<ViewStyle>;
};

const padStyle: Record<Padding, ViewStyle> = {
  none: { padding: 0 },
  sm: { padding: spacing[3] },
  md: { padding: spacing[4] },
  lg: { padding: spacing[5] },
};

export function Card({
  padding = 'md',
  tier,
  variant = 'default',
  onPress,
  style,
  children,
  ...rest
}: Props) {
  const variantStyle = (() => {
    if (variant === 'sponsored') return { borderColor: colors.honey[400], borderWidth: 1.5 };
    if (variant === 'dark') return { backgroundColor: colors.ink[950] };
    if (tier === 'gold') return { borderColor: colors.honey[400], borderWidth: 1.5 };
    if (tier === 'silver') return { borderColor: colors.ink[300], borderWidth: 1.5 };
    return {};
  })();

  const composed: StyleProp<ViewStyle> = [styles.card, padStyle[padding], variantStyle, style];

  if (onPress) {
    return (
      <Pressable onPress={onPress} style={({ pressed }) => [composed, pressed && { opacity: 0.85 }]}>
        {children}
      </Pressable>
    );
  }

  return (
    <View style={composed} {...rest}>
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.white,
    borderRadius: radius.card,
    borderWidth: 1,
    borderColor: 'rgba(11,11,12,0.06)',
    ...shadows.card,
  },
});
