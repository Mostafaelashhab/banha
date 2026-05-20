import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { colors, spacing, typography } from '../theme';
import { IconTile } from './IconTile';
import { IconName } from './Icon';

type Tone = 'default' | 'danger';

type Props = {
  icon: IconName;
  title: string;
  hint?: string;
  tone?: Tone;
  children?: React.ReactNode;
};

export function EmptyState({ icon, title, hint, tone = 'default', children }: Props) {
  return (
    <View style={styles.wrap}>
      <IconTile icon={icon} tone={tone === 'danger' ? 'blush' : 'coral'} size="xl" shape="circle" />
      <Text style={styles.title}>{title}</Text>
      {hint && <Text style={styles.hint}>{hint}</Text>}
      {children && <View style={styles.cta}>{children}</View>}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing[3],
    paddingHorizontal: spacing[6],
    paddingVertical: spacing[10],
  },
  title: {
    ...typography.h3,
    color: colors.ink[950],
    textAlign: 'center',
  },
  hint: {
    ...typography.body,
    color: colors.ink[500],
    textAlign: 'center',
    lineHeight: 22,
  },
  cta: { marginTop: spacing[2] },
});
