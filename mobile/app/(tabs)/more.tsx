import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Href, router } from 'expo-router';
import { Card, IconTile } from '@/components';
import { IconName } from '@/components';
import { ColorTone, colors, spacing, typography } from '@/theme';

type Item = { icon: IconName; tone: ColorTone; title: string; subtitle: string; href: Href };

const sections: { label: string; items: Item[] }[] = [
  {
    label: 'حسابي',
    items: [
      { icon: 'bookmark', tone: 'coral', title: 'محفوظاتي', subtitle: 'الأماكن اللي حفظتها', href: '/bookmarks' },
      { icon: 'cart', tone: 'mint', title: 'طلباتي', subtitle: 'طلبات الدليفري', href: '/orders' },
      { icon: 'clock', tone: 'honey', title: 'حجوزاتي', subtitle: 'مواعيد الحجز', href: '/bookings' },
      { icon: 'bell', tone: 'cream', title: 'الإشعارات', subtitle: 'كل التنبيهات', href: '/notifications' },
    ],
  },
  {
    label: 'استكشف بنها',
    items: [
      { icon: 'compass', tone: 'coral', title: 'مفتوح دلوقتي', subtitle: 'محلات شغّالة الساعة دي', href: '/open-now' },
      { icon: 'bolt', tone: 'honey', title: 'عروض اليوم', subtitle: 'تخفيضات سرايا الوقت', href: '/offers' },
      { icon: 'cart', tone: 'mint', title: 'سوق بنها', subtitle: 'بيع واشتري من سكان بنها', href: '/marketplace' },
      { icon: 'chart', tone: 'blush', title: 'أسعار السلع', subtitle: 'متابعة سعر السكر والسلع', href: '/prices' },
    ],
  },
  {
    label: 'المجتمع',
    items: [
      { icon: 'shield', tone: 'blush', title: 'تنبيهات وحوادث', subtitle: 'لايف من سكان بنها', href: '/alerts' },
      { icon: 'star', tone: 'honey', title: 'فعاليات وأحداث', subtitle: 'كل اللي بيحصل في المدينة', href: '/events' },
      { icon: 'message', tone: 'coral', title: 'بوستات المجتمع', subtitle: 'نقاشات أهل بنها', href: '/posts' },
    ],
  },
];

export default function MoreScreen() {
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>كل خدمات بنهاوي</Text>
        <Text style={styles.hint}>اختار اللي محتاجه</Text>
      </View>
      <ScrollView contentContainerStyle={styles.scroll}>
        {sections.map((sec) => (
          <View key={sec.label} style={{ gap: spacing[2] }}>
            <Text style={styles.sectionLabel}>{sec.label}</Text>
            <Card padding="none">
              {sec.items.map((it, i) => (
                <View
                  key={it.title}
                  style={[styles.row, i < sec.items.length - 1 && styles.divider]}
                  onTouchEnd={() => router.push(it.href)}
                >
                  <IconTile icon={it.icon} tone={it.tone} size="md" />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.rowTitle}>{it.title}</Text>
                    <Text style={styles.rowSubtitle}>{it.subtitle}</Text>
                  </View>
                </View>
              ))}
            </Card>
          </View>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  header: { paddingHorizontal: spacing[4], paddingTop: spacing[2], paddingBottom: spacing[3], gap: spacing[1] },
  title: { ...typography.h2, color: colors.ink[950] },
  hint: { ...typography.body, color: colors.ink[500] },
  scroll: { paddingHorizontal: spacing[4], paddingBottom: spacing[10], gap: spacing[4] },
  sectionLabel: { ...typography.nano, color: colors.coral[600] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3], padding: spacing[4] },
  divider: { borderBottomWidth: 1, borderBottomColor: 'rgba(11,11,12,0.04)' },
  rowTitle: { ...typography.bodyStrong, color: colors.ink[950] },
  rowSubtitle: { ...typography.body, color: colors.ink[500], marginTop: 2 },
});
