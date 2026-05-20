import { ScrollView, StyleSheet, Switch, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, ScreenHeader } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useState } from 'react';

const rows = [
  { key: 'show_phone',   title: 'إظهار رقمي للمحلات',   subtitle: 'يقدر المحل يتواصل معاك مباشرة' },
  { key: 'show_location',title: 'مشاركة موقعي',         subtitle: 'علشان نوريك أقرب الأماكن' },
  { key: 'show_orders',  title: 'إظهار طلباتي للأصحاب', subtitle: 'يقدروا يشوفوا تقييماتك' },
  { key: 'analytics',    title: 'مشاركة بيانات الاستخدام', subtitle: 'يساعدنا نحسّن التطبيق' },
];

export default function PrivacySettings() {
  const [vals, setVals] = useState<Record<string, boolean>>({
    show_phone: false,
    show_location: true,
    show_orders: false,
    analytics: true,
  });

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="الخصوصية والأمان" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <Card padding="none">
          {rows.map((r, i) => (
            <View key={r.key} style={[styles.row, i < rows.length - 1 && styles.divider]}>
              <View style={{ flex: 1, gap: 2 }}>
                <Text style={styles.title}>{r.title}</Text>
                <Text style={styles.subtitle}>{r.subtitle}</Text>
              </View>
              <Switch
                value={vals[r.key]}
                onValueChange={(v) => setVals((s) => ({ ...s, [r.key]: v }))}
                trackColor={{ false: colors.cream[200], true: colors.coral[500] }}
                thumbColor={colors.white}
              />
            </View>
          ))}
        </Card>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3], padding: spacing[4] },
  divider: { borderBottomWidth: 1, borderBottomColor: 'rgba(11,11,12,0.04)' },
  title: { ...typography.bodyStrong, color: colors.ink[950] },
  subtitle: { ...typography.body, color: colors.ink[500], fontSize: 12, marginTop: 2 },
});
