import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { router } from 'expo-router';
import { Icon } from './Icon';
import { colors, spacing, typography } from '../theme';

type Props = {
  title: string;
  subtitle?: string;
  back?: boolean;
  right?: React.ReactNode;
};

export function ScreenHeader({ title, subtitle, back = true, right }: Props) {
  return (
    <View style={styles.wrap}>
      {back ? (
        <Pressable
          onPress={() => (router.canGoBack() ? router.back() : router.replace('/(tabs)/feed'))}
          accessibilityRole="button"
          accessibilityLabel="رجوع"
          style={styles.iconBtn}
        >
          <Icon name="arrow-right" size={22} color={colors.ink[950]} />
        </Pressable>
      ) : (
        <View style={styles.iconBtn} />
      )}
      <View style={{ flex: 1 }}>
        <Text style={styles.title} numberOfLines={1}>{title}</Text>
        {subtitle ? <Text style={styles.subtitle} numberOfLines={1}>{subtitle}</Text> : null}
      </View>
      <View style={styles.iconBtn}>{right}</View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing[2],
    paddingTop: spacing[1],
    paddingBottom: spacing[2],
    gap: spacing[2],
  },
  iconBtn: { width: 40, height: 40, alignItems: 'center', justifyContent: 'center' },
  title: { ...typography.h3, color: colors.ink[950] },
  subtitle: { ...typography.meta, color: colors.ink[500], marginTop: 2 },
});
