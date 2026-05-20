import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Href, router } from 'expo-router';
import { Button, Card, Icon, IconTile } from '@/components';
import { IconName } from '@/components';
import { ColorTone, colors, spacing, typography } from '@/theme';
import { useAuth } from '@/auth/AuthContext';

type Item = {
  icon: IconName;
  tone: ColorTone;
  title: string;
  subtitle: string;
  href: Href;
  requiresAuth?: boolean;
};

const MY_ACCOUNT: Item[] = [
  { icon: 'bookmark', tone: 'coral', title: 'محفوظاتي', subtitle: 'الأماكن اللي حفظتها', href: '/bookmarks', requiresAuth: true },
  { icon: 'cart', tone: 'mint', title: 'طلباتي', subtitle: 'طلبات الدليفري', href: '/orders', requiresAuth: true },
  { icon: 'clock', tone: 'honey', title: 'حجوزاتي', subtitle: 'مواعيد الحجز', href: '/bookings', requiresAuth: true },
  { icon: 'bell', tone: 'cream', title: 'الإشعارات', subtitle: 'كل التنبيهات', href: '/notifications', requiresAuth: true },
];

const EXPLORE: Item[] = [
  { icon: 'compass', tone: 'coral', title: 'مفتوح دلوقتي', subtitle: 'محلات شغّالة الساعة دي', href: '/open-now' },
  { icon: 'bolt', tone: 'honey', title: 'عروض اليوم', subtitle: 'تخفيضات سرايا الوقت', href: '/offers' },
  { icon: 'cart', tone: 'mint', title: 'سوق بنها', subtitle: 'بيع واشتري من سكان بنها', href: '/marketplace' },
  { icon: 'chart', tone: 'blush', title: 'أسعار السلع', subtitle: 'متابعة سعر السكر والسلع', href: '/prices' },
];

const COMMUNITY: Item[] = [
  { icon: 'shield', tone: 'blush', title: 'تنبيهات وحوادث', subtitle: 'لايف من سكان بنها', href: '/alerts' },
  { icon: 'star', tone: 'honey', title: 'فعاليات وأحداث', subtitle: 'كل اللي بيحصل في المدينة', href: '/events' },
];

export default function MoreScreen() {
  const auth = useAuth();
  const isAuth = auth.status === 'authenticated';

  const sections: { label: string; items: Item[] }[] = [];
  if (isAuth) {
    sections.push({ label: 'حسابي', items: MY_ACCOUNT });
  }
  sections.push({ label: 'استكشف بنها', items: EXPLORE });
  sections.push({ label: 'المجتمع', items: COMMUNITY });

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>كل خدمات بنهاوي</Text>
        <Text style={styles.hint}>اختار اللي محتاجه</Text>
      </View>

      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        {!isAuth && (
          <Card padding="lg" style={styles.guestCta}>
            <IconTile icon="user" tone="coral" intensity="strong" size="lg" shape="circle" />
            <View style={{ flex: 1, gap: 2 }}>
              <Text style={styles.guestTitle}>ادخل عشان تستفيد بكل المميزات</Text>
              <Text style={styles.guestHint}>محفوظات، طلبات، حجوزات، وإشعارات</Text>
            </View>
            <Button size="sm" pill onPress={() => router.push('/login')}>دخول</Button>
          </Card>
        )}

        {sections.map((sec) => (
          <View key={sec.label} style={styles.section}>
            <Text style={styles.sectionLabel}>{sec.label}</Text>
            <Card padding="none">
              {sec.items.map((it, i) => (
                <Pressable
                  key={it.title}
                  android_ripple={{ color: 'rgba(0,0,0,0.06)' }}
                  style={({ pressed }) => [
                    styles.row,
                    i < sec.items.length - 1 && styles.divider,
                    pressed && { backgroundColor: 'rgba(0,0,0,0.03)' },
                  ]}
                  onPress={() => router.push(it.href)}
                >
                  <IconTile icon={it.icon} tone={it.tone} size="md" />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.rowTitle}>{it.title}</Text>
                    <Text style={styles.rowSubtitle}>{it.subtitle}</Text>
                  </View>
                  <Icon name="chevron-left" size={18} color={colors.ink[400]} />
                </Pressable>
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
  header: {
    paddingHorizontal: spacing[4],
    paddingTop: spacing[2],
    paddingBottom: spacing[4],
    gap: spacing[1],
  },
  title: { ...typography.h2, color: colors.ink[950] },
  hint: { ...typography.body, color: colors.ink[500] },
  scroll: {
    paddingHorizontal: spacing[4],
    paddingBottom: 120,
    gap: spacing[5],
  },
  guestCta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing[3],
    marginBottom: spacing[1],
  },
  guestTitle: { ...typography.bodyStrong, color: colors.ink[950], fontSize: 14 },
  guestHint: { ...typography.body, color: colors.ink[500], fontSize: 12 },
  section: { gap: spacing[2] },
  sectionLabel: {
    fontSize: 13,
    fontWeight: '900',
    color: colors.coral[600],
    paddingHorizontal: spacing[1],
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing[3],
    paddingVertical: spacing[4],
    paddingHorizontal: spacing[4],
  },
  divider: { borderBottomWidth: 1, borderBottomColor: 'rgba(11,11,12,0.04)' },
  rowTitle: { ...typography.bodyStrong, color: colors.ink[950] },
  rowSubtitle: { ...typography.body, color: colors.ink[500], marginTop: 2, fontSize: 13 },
});

