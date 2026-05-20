import React, { useState } from 'react';
import { Image, ImageStyle, StyleProp, StyleSheet, Text, View, ViewStyle } from 'react-native';
import { colors, radius, typography } from '../theme';
import { colorFromName, imageUrl } from '../lib/image';

type Props = {
  uri?: string | null;
  fallbackText?: string | null;
  style?: StyleProp<ViewStyle>;
  imageStyle?: StyleProp<ImageStyle>;
  radius?: number;
  shape?: 'square' | 'circle';
};

export function SmartImage({ uri, fallbackText, style, imageStyle, radius: r, shape = 'square' }: Props) {
  const resolved = imageUrl(uri);
  const [failed, setFailed] = useState(false);
  const borderRadius = shape === 'circle' ? 9999 : r ?? radius.lg;

  if (resolved && !failed) {
    return (
      <View style={[styles.wrap, { borderRadius }, style]}>
        <Image
          source={{ uri: resolved }}
          style={[StyleSheet.absoluteFillObject, { borderRadius }, imageStyle]}
          resizeMode="cover"
          onError={() => setFailed(true)}
        />
      </View>
    );
  }

  const initial = (fallbackText ?? '').trim().slice(0, 1) || '·';
  const bg = colorFromName(fallbackText);

  return (
    <View style={[styles.wrap, styles.fallback, { backgroundColor: bg, borderRadius }, style]}>
      <Text style={styles.initial}>{initial}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    overflow: 'hidden',
    backgroundColor: colors.cream[200],
  },
  fallback: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  initial: {
    ...typography.h1,
    color: '#FFFFFF',
    fontSize: 28,
  },
});
