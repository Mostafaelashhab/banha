import { Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Href, router } from 'expo-router';
import { Button, Card, Icon, IconTile, QueryState } from '@/components';
import { IconName } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useMyProfile } from '@/api/hooks';
import { useAuth } from '@/auth/AuthContext';

type Row = {
  icon: IconName;
  tone: 'coral' | 'mint' | 'honey' | 'blush' | 'cream';
  label: string;
  href?: Href;
  onPress?: () => void;
};

export default function Profile() {
  const auth = useAuth();
  const query = useMyProfile();

  if (auth.status === 'guest') {
    return (
      <SafeAreaView style={styles.safe} edges={['top']}>
        <View style={styles.signedOut}>
          <IconTile icon="user" tone="coral" size="xl" shape="circle" />
          <Text style={styles.h2}>سجّل دخول للمتابعة</Text>
          <Text style={styles.hint}>هتحتاجه علشان تحفظ نشاطك وتطلب من المحلات</Text>
          <View style={{ gap: spacing[2], width: '100%' }}>
            <Button size="lg" block onPress={() => router.push('/login')}>
              تسجيل الدخول
            </Button>
            <Button variant="outline" size="lg" block onPress={() => router.push('/signup')}>
              عمل حساب جديد
            </Button>
          </View>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
        >
          {(d) => {
            const confirmLogout = () =>
              Alert.alert(
                'تسجيل الخروج',
                'متأكد إنك عاوز تطلع؟',
                [
                  { text: 'إلغاء', style: 'cancel' },
                  { text: 'خروج', style: 'destructive', onPress: () => auth.logout() },
                ],
              );
            const settings: Row[] = [
              { icon: 'edit', tone: 'coral', label: 'تعديل البروفايل', href: '/settings/profile' },
              { icon: 'bookmark', tone: 'honey', label: 'محفوظاتي', href: '/bookmarks' },
              { icon: 'cart', tone: 'mint', label: 'طلباتي', href: '/orders' },
              { icon: 'clock', tone: 'honey', label: 'حجوزاتي', href: '/bookings' },
              { icon: 'bell', tone: 'cream', label: 'الإشعارات', href: '/notifications' },
              { icon: 'lock', tone: 'cream', label: 'الخصوصية والأمان', href: '/settings/privacy' },
              { icon: 'settings', tone: 'cream', label: 'الإعدادات', href: '/settings/app' },
              { icon: 'logout', tone: 'blush', label: 'تسجيل الخروج', onPress: confirmLogout },
            ];
            return (
              <View style={{ gap: spacing[3] }}>
                <Card padding="lg" style={styles.heroCard}>
                  <View style={styles.avatar}>
                    <Text style={styles.avatarText}>
                      {(d.user.name ?? '?').slice(0, 1)}
                    </Text>
                  </View>
                  <View style={{ flex: 1 }}>
                    <Text style={styles.name}>{d.user.name}</Text>
                    <Text style={styles.handle}>
                      {d.user.city ? `${d.user.city} · ` : ''}
                      {d.user.username ? `@${d.user.username}` : ''}
                    </Text>
                  </View>
                  <Button variant="outline" size="sm" pill onPress={() => router.push('/settings/profile')}>
                    تعديل
                  </Button>
                </Card>

                <View style={styles.statsRow}>
                  <Stat label="محفوظات" value={d.stats?.saves ?? 0} href="/bookmarks" />
                  <Stat label="طلبات" value={d.stats?.orders ?? 0} href="/orders" />
                  <Stat label="نشاطاتي" value={d.stats?.listings ?? 0} />
                </View>

                <Card padding="none">
                  {settings.map((r, i) => (
                    <Pressable
                      key={r.label}
                      android_ripple={{ color: 'rgba(0,0,0,0.06)' }}
                      style={({ pressed }) => [
                        styles.settingRow,
                        i < settings.length - 1 && styles.divider,
                        pressed && { backgroundColor: 'rgba(0,0,0,0.03)' },
                      ]}
                      onPress={() => {
                        if (r.onPress) return r.onPress();
                        if (r.href) router.push(r.href);
                      }}
                    >
                      <IconTile icon={r.icon} tone={r.tone} size="md" />
                      <Text style={styles.settingLabel}>{r.label}</Text>
                      <Icon name="chevron-left" size={18} color={colors.ink[400]} />
                    </Pressable>
                  ))}
                </Card>
              </View>
            );
          }}
        </QueryState>
      </ScrollView>
    </SafeAreaView>
  );
}

function Stat({ label, value, href }: { label: string; value: number; href?: Href }) {
  return (
    <Card
      padding="md"
      style={styles.statCard}
      onPress={href ? () => router.push(href) : undefined}
    >
      <Text style={styles.statValue}>{value}</Text>
      <Text style={styles.statLabel}>{label}</Text>
    </Card>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: 120 },
  signedOut: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing[3],
    paddingHorizontal: spacing[6],
  },
  h2: { ...typography.h2, color: colors.ink[950], textAlign: 'center' },
  hint: { ...typography.body, color: colors.ink[500], textAlign: 'center' },
  heroCard: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  avatar: {
    width: 56,
    height: 56,
    borderRadius: 999,
    backgroundColor: colors.coral[100],
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: { ...typography.h2, color: colors.coral[700] },
  name: { ...typography.h3, color: colors.ink[950] },
  handle: { ...typography.meta, color: colors.ink[500], marginTop: 2 },
  statsRow: { flexDirection: 'row', gap: spacing[2] },
  statCard: { flex: 1, alignItems: 'center', gap: 4 },
  statValue: { ...typography.h2, color: colors.coral[600] },
  statLabel: { fontSize: 11, fontWeight: '700', color: colors.ink[500] },
  settingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing[3],
    padding: spacing[4],
  },
  divider: { borderBottomWidth: 1, borderBottomColor: 'rgba(11,11,12,0.04)' },
  settingLabel: { ...typography.bodyStrong, color: colors.ink[950], flex: 1 },
});
