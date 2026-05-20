import { Image, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, IconTile, QueryState, ScreenHeader } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useMarketplace } from '@/api/hooks';

export default function Marketplace() {
  const query = useMarketplace();
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="سوق بنها" subtitle="بيع واشتري من أهل المدينة" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          emptyTitle="مفيش إعلانات لسه"
          emptyHint="ابدأ بإضافة إعلان"
          isEmpty={(d) => !d?.data?.length}
        >
          {(d) => (
            <View style={styles.grid}>
              {d.data.map((l) => (
                <View key={String(l.id)} style={styles.cell}>
                  <Card padding="none" style={{ overflow: 'hidden' }}>
                    {l.photo_url ? (
                      <Image source={{ uri: l.photo_url }} style={styles.image} />
                    ) : (
                      <View style={[styles.image, styles.imageFallback]}>
                        <IconTile icon="image" tone="cream" size="lg" />
                      </View>
                    )}
                    <View style={{ padding: spacing[3], gap: 4 }}>
                      <Text style={styles.title} numberOfLines={2}>{l.title}</Text>
                      {typeof l.price === 'number' && (
                        <Text style={styles.price}>
                          {l.price.toFixed(0)} {l.currency}{l.negotiable ? ' · قابل للتفاوض' : ''}
                        </Text>
                      )}
                    </View>
                  </Card>
                </View>
              ))}
            </View>
          )}
        </QueryState>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10] },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing[3] },
  cell: { width: '48%' },
  image: { width: '100%', aspectRatio: 1, backgroundColor: colors.cream[200], borderTopLeftRadius: radius.card, borderTopRightRadius: radius.card },
  imageFallback: { alignItems: 'center', justifyContent: 'center' },
  title: { ...typography.bodyStrong, color: colors.ink[950], fontSize: 13 },
  price: { ...typography.meta, color: colors.coral[600], fontSize: 12 },
});
