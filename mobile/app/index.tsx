import { useEffect } from 'react';
import { Link, Redirect, router } from 'expo-router';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button, Card, IconTile } from '@/components';
import { colors, radius, shadows, spacing, typography } from '@/theme';
import { useAuth } from '@/auth/AuthContext';

export default function Welcome() {
  const auth = useAuth();

  // Auto-redirect signed-in users straight to the home tab. OTP verification
  // is only enforced during the signup flow — login skips it.
  useEffect(() => {
    if (auth.status === 'authenticated') {
      router.replace('/(tabs)/feed');
    }
  }, [auth.status]);

  if (auth.status === 'loading') return null;
  if (auth.status === 'authenticated') return <Redirect href="/(tabs)/feed" />;

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        <View style={styles.hero}>
          <IconTile icon="map-pin" tone="coral" intensity="strong" size="xl" shape="circle" />
          <Text style={styles.heroTitle}>بنهاوي</Text>
          <Text style={styles.heroSubtitle}>
            تطبيق مدينتك — مطاعم، خدمات، وظائف، وأخبار محلية في بنها والقليوبية
          </Text>
          <View style={styles.ctaCol}>
            <Button size="lg" block onPress={() => router.push('/signup')}>
              عمل حساب جديد
            </Button>
            <Button variant="secondary" size="lg" block onPress={() => router.push('/login')}>
              عندي حساب — سجّل دخول
            </Button>
            <Button variant="ghost" size="md" block onPress={() => router.push('/(tabs)/feed')}>
              تصفّح كزائر
            </Button>
          </View>
        </View>

        <View style={styles.featureRow}>
          <Card style={styles.featureCard} padding="md">
            <IconTile icon="compass" tone="mint" size="md" />
            <Text style={styles.featureTitle}>كل حاجة في بنها</Text>
            <Text style={styles.featureHint}>مطاعم، صيدليات، خدمات، ومواصلات</Text>
          </Card>
          <Card style={styles.featureCard} padding="md">
            <IconTile icon="bolt" tone="honey" size="md" />
            <Text style={styles.featureTitle}>عروض وخصومات</Text>
            <Text style={styles.featureHint}>تنزل أول بأول من أصحاب النشاط</Text>
          </Card>
        </View>

        <Card padding="lg" style={{ gap: spacing[3] }}>
          <Text style={styles.sectionLabel}>للمحلات والخدمات</Text>
          <Text style={styles.featureTitle}>سجّل نشاطك في بنهاوي</Text>
          <Text style={styles.featureHint}>وصّل لعملاء من بنها والقليوبية مباشرة</Text>
          <Link href="/signup" asChild>
            <Button variant="primary" pill icon="arrow-left" iconEnd>
              اعرف أكتر
            </Button>
          </Link>
        </Card>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: {
    padding: spacing[4],
    paddingTop: spacing[6],
    gap: spacing[4],
    paddingBottom: spacing[10],
  },
  hero: {
    backgroundColor: colors.coral[500],
    borderRadius: radius['3xl'],
    padding: spacing[6],
    gap: spacing[3],
    alignItems: 'flex-start',
    ...shadows.glow,
  },
  heroTitle: {
    ...typography.h1,
    color: colors.white,
    fontSize: 36,
    lineHeight: 42,
  },
  heroSubtitle: {
    ...typography.body,
    color: 'rgba(255,255,255,0.88)',
    lineHeight: 22,
  },
  ctaCol: { width: '100%', marginTop: spacing[3], gap: spacing[2] },
  featureRow: { flexDirection: 'row', gap: spacing[3] },
  featureCard: { flex: 1, gap: spacing[2] },
  featureTitle: { ...typography.h3, color: colors.ink[950] },
  featureHint: { ...typography.body, color: colors.ink[500] },
  sectionLabel: { fontSize: 12, fontWeight: '900', color: colors.coral[600] },
});
