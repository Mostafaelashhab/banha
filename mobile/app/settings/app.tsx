import { Linking, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, Icon, IconTile, ScreenHeader } from '@/components';
import { IconName } from '@/components';
import { ColorTone, colors, spacing, typography } from '@/theme';
import Constants from 'expo-constants';

type Row = { icon: IconName; tone: ColorTone; label: string; meta?: string; onPress?: () => void };

const items: Row[] = [
  { icon: 'bell',   tone: 'coral',  label: 'الإشعارات', meta: 'مفعّل' },
  { icon: 'map-pin', tone: 'mint',  label: 'الموقع',    meta: 'مفعّل' },
  { icon: 'shield', tone: 'honey',  label: 'الأمان',    meta: 'حماية الحساب' },
  { icon: 'message', tone: 'cream', label: 'تواصل معانا', onPress: () => Linking.openURL('https://wa.me/201000000000').catch(() => {}) },
  { icon: 'star',   tone: 'honey',  label: 'قيّم التطبيق' },
];

export default function AppSettings() {
  const version = Constants.expoConfig?.version ?? '—';
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="الإعدادات" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <Card padding="none">
          {items.map((r, i) => (
            <View key={r.label} style={[styles.row, i < items.length - 1 && styles.divider]} onTouchEnd={r.onPress}>
              <IconTile icon={r.icon} tone={r.tone} size="md" />
              <View style={{ flex: 1 }}>
                <Text style={styles.title}>{r.label}</Text>
              </View>
              {r.meta ? <Text style={styles.meta}>{r.meta}</Text> : null}
              <Icon name="chevron-left" size={16} color={colors.ink[400]} />
            </View>
          ))}
        </Card>

        <Text style={styles.version}>الإصدار {version}</Text>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10], gap: spacing[4] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3], padding: spacing[4] },
  divider: { borderBottomWidth: 1, borderBottomColor: 'rgba(11,11,12,0.04)' },
  title: { ...typography.bodyStrong, color: colors.ink[950] },
  meta: { ...typography.meta, color: colors.ink[500] },
  version: { ...typography.meta, color: colors.ink[400], textAlign: 'center' },
});
