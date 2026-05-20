import { Link, router } from 'expo-router';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button, Card, IconTile } from '@/components';
import { colors, spacing, typography, shadows, radius } from '@/theme';

export default function Welcome() {
  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <ScrollView contentContainerStyle={styles.scroll}>
        <View style={styles.heroCard}>
          <IconTile icon="map-pin" tone="coral" intensity="strong" size="xl" shape="circle" />
          <Text style={styles.heroTitle}>بنهاوي</Text>
          <Text style={styles.heroSubtitle}>
            تطبيق مدينتك — مطاعم، خدمات، وظائف، وأخبار محلية في بنها والقليوبية
          </Text>
          <View style={styles.ctaRow}>
            <Button
              variant="secondary"
              size="lg"
              block
              onPress={() => router.push('/(tabs)/feed')}
            >
              ابدأ تصفّح المدينة
            </Button>
          </View>
        </View>

        <View style={styles.row}>
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
          <Link href="/(tabs)/profile" asChild>
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
    gap: spacing[4],
    paddingBottom: spacing[10],
  },
  heroCard: {
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
    fontSize: 32,
    lineHeight: 38,
  },
  heroSubtitle: {
    ...typography.body,
    color: 'rgba(255,255,255,0.85)',
    lineHeight: 22,
  },
  ctaRow: { width: '100%', marginTop: spacing[3] },
  row: { flexDirection: 'row', gap: spacing[3] },
  featureCard: { flex: 1, gap: spacing[2] },
  featureTitle: { ...typography.h3, color: colors.ink[950] },
  featureHint: { ...typography.body, color: colors.ink[500] },
  sectionLabel: {
    ...typography.nano,
    color: colors.coral[600],
  },
});
