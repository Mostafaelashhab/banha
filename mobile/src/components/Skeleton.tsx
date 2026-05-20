import React, { useEffect, useRef } from 'react';
import { Animated, StyleSheet, View, ViewStyle } from 'react-native';
import { colors, radius, spacing } from '../theme';

type Variant = 'block' | 'circle' | 'text';

type Props = {
  variant?: Variant;
  lines?: number;
  width?: ViewStyle['width'];
  height?: ViewStyle['height'];
  style?: ViewStyle;
};

export function Skeleton({ variant = 'block', lines = 1, width, height, style }: Props) {
  const pulse = useRef(new Animated.Value(0.5)).current;

  useEffect(() => {
    const loop = Animated.loop(
      Animated.sequence([
        Animated.timing(pulse, { toValue: 1, duration: 700, useNativeDriver: true }),
        Animated.timing(pulse, { toValue: 0.5, duration: 700, useNativeDriver: true }),
      ]),
    );
    loop.start();
    return () => loop.stop();
  }, [pulse]);

  const shared: ViewStyle = {
    backgroundColor: colors.cream[200],
    overflow: 'hidden',
  };

  if (variant === 'text') {
    return (
      <View style={{ gap: spacing[2] }}>
        {Array.from({ length: lines }).map((_, i) => (
          <Animated.View
            key={i}
            style={[
              shared,
              {
                opacity: pulse,
                height: 12,
                width: i === lines - 1 ? '60%' : '100%',
                borderRadius: radius.md,
              },
            ]}
          />
        ))}
      </View>
    );
  }

  return (
    <Animated.View
      style={[
        shared,
        {
          opacity: pulse,
          width: width ?? '100%',
          height: height ?? 16,
          borderRadius: variant === 'circle' ? radius.full : radius.lg,
        },
        style,
      ]}
    />
  );
}
