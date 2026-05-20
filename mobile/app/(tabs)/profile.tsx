import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router } from 'expo-router';
import { Button, Card, IconTile, QueryState } from '@/components';
import { IconName } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useMyProfile } from '@/api/hooks';
import { useAuth } from '@/auth/AuthContext';

type Row = { icon: IconName; tone: 'coral' | 'mint' | 'honey' | 'blush' | 'cream'; label: string; onPress?: () => void };

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
            const settings: Row[] = [
              { icon: 'edit', tone: 'coral', label: 'تعديل البروفايل' },
              { icon: 'bookmark', tone: 'honey', label: 'محفوظاتي' },
              { icon: 'cart', tone: 'mint', label: 'طلباتي' },
              { icon: 'lock', tone: 'cream', label: 'الخصوصية والأمان' },
              { icon: 'settings', tone: 'cream', label: 'الإعدادات' },
              { icon: 'logout', tone: 'blush', label: 'تسجيل الخروج', onPress: () => auth.logout() },
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
                  <Button variant="outline" size="sm" pill>
                    تعديل
                  </Button>
                </Card>

                <View style={styles.statsRow}>
                  <Stat label="محفوظات" value={d.stats?.saves ?? 0} />
                  <Stat label="طلبات" value={d.stats?.orders ?? 0} />
                  <Stat label="نشاطاتي" value={d.stats?.listings ?? 0} />
                </View>

                <Card padding="none">
                  {settings.map((r, i) => (
                    <View
                      key={r.label}
                      style={[styles.settingRow, i < settings.length - 1 && styles.divider]}
                      onTouchEnd={r.onPress}
                    >
                      <IconTile icon={r.icon} tone={r.tone} size="md" />
                      <Text style={styles.settingLabel}>{r.label}</Text>
                    </View>
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

function Stat({ label, value }: { label: string; value: number }) {
  return (
    <Card padding="md" style={styles.statCard}>
      <Text style={styles.statValue}>{value}</Text>
      <Text style={styles.statLabel}>{label}</Text>
    </Card>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10] },
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
