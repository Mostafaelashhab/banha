import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { colors, radius, spacing } from '../theme';
import { Icon, IconName } from './Icon';

type Props = {
  children: React.ReactNode;
  active?: boolean;
  icon?: IconName;
  count?: number;
  onPress?: () => void;
};

export function Chip({ children, active, icon, count, onPress }: Props) {
  const bg = active ? colors.coral[500] : colors.coral[50];
  const fg = active ? colors.white : colors.coral[700];

  const Container: any = onPress ? Pressable : View;

  return (
    <Container
      onPress={onPress}
      style={({ pressed }: { pressed: boolean }) => [
        styles.chip,
        { backgroundColor: bg, opacity: pressed ? 0.85 : 1 },
      ]}
    >
      {icon && <Icon name={icon} size={14} color={fg} />}
      <Text style={[styles.label, { color: fg }]} numberOfLines={1}>
        {children}
      </Text>
      {typeof count === 'number' && (
        <View style={[styles.count, { backgroundColor: active ? 'rgba(255,255,255,0.18)' : colors.coral[100] }]}>
          <Text style={[styles.countText, { color: fg }]}>{count}</Text>
        </View>
      )}
    </Container>
  );
}

const styles = StyleSheet.create({
  chip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing[1.5],
    paddingHorizontal: spacing[3],
    paddingVertical: spacing[1.5],
    borderRadius: radius.full,
  },
  label: {
    fontSize: 12,
    fontWeight: '800',
  },
  count: {
    minWidth: 20,
    paddingHorizontal: 6,
    height: 18,
    borderRadius: radius.full,
    alignItems: 'center',
    justifyContent: 'center',
    marginStart: spacing[1],
  },
  countText: {
    fontSize: 10,
    fontWeight: '800',
  },
});
