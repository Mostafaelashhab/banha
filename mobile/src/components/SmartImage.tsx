import React, { useState } from 'react';
import { Pressable, StyleProp, StyleSheet, Text, View, ViewStyle } from 'react-native';
import { Image, ImageStyle } from 'expo-image';
import { colors, radius, typography } from '../theme';
import { colorFromName, imageUrl } from '../lib/image';
import { PhotoViewer } from './PhotoViewer';

type Props = {
  uri?: string | null;
  fallbackText?: string | null;
  style?: StyleProp<ViewStyle>;
  imageStyle?: StyleProp<ImageStyle>;
  radius?: number;
  shape?: 'square' | 'circle';
  /**
   * When provided, tapping the image opens a full-screen viewer with pinch
   * zoom + horizontal swipe between the URIs in the array.
   */
  previewUris?: (string | null | undefined)[];
  /** Which index in `previewUris` to start the viewer on (defaults to 0). */
  previewIndex?: number;
};

export function SmartImage({
  uri,
  fallbackText,
  style,
  imageStyle,
  radius: r,
  shape = 'square',
  previewUris,
  previewIndex = 0,
}: Props) {
  const resolved = imageUrl(uri);
  const [failed, setFailed] = useState(false);
  const [viewerOpen, setViewerOpen] = useState(false);
  const borderRadius = shape === 'circle' ? 9999 : r ?? radius.lg;

  const previewable = !!previewUris && previewUris.some((u) => !!imageUrl(u));

  const content = resolved && !failed ? (
    <View style={[styles.wrap, { borderRadius }, style]}>
      <Image
        source={{ uri: resolved }}
        style={[StyleSheet.absoluteFillObject, { borderRadius }, imageStyle]}
        contentFit="cover"
        transition={150}
        cachePolicy="memory-disk"
        recyclingKey={resolved}
        onError={() => setFailed(true)}
      />
    </View>
  ) : (
    <View style={[styles.wrap, styles.fallback, { backgroundColor: colorFromName(fallbackText), borderRadius }, style]}>
      <Text style={styles.initial}>
        {(fallbackText ?? '').trim().slice(0, 1) || '·'}
      </Text>
    </View>
  );

  if (!previewable) return content;

  return (
    <>
      <Pressable onPress={() => setViewerOpen(true)} style={({ pressed }) => (pressed ? { opacity: 0.92 } : null)}>
        {content}
      </Pressable>
      <PhotoViewer
        visible={viewerOpen}
        uris={previewUris!}
        initialIndex={previewIndex}
        onClose={() => setViewerOpen(false)}
      />
    </>
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
