import { Dimensions, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, QueryState, ScreenHeader, SmartImage } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useMarketplace } from '@/api/hooks';

const { width: SCREEN_W } = Dimensions.get('window');
const GUTTER = 16;
const GAP = 12;
const CELL_W = (SCREEN_W - GUTTER * 2 - GAP) / 2;

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
          {(d) => {
            const allPhotos = d.data.map((l) => l.photo_url).filter(Boolean) as string[];
            return (
              <View style={styles.grid}>
                {d.data.map((l, idx) => (
                  <View key={String(l.id)} style={styles.cell}>
                    <Card padding="none" style={{ overflow: 'hidden' }}>
                      <SmartImage
                        uri={l.photo_url}
                        fallbackText={l.title}
                        style={styles.image}
                        radius={0}
                        previewUris={l.photo_url ? allPhotos : undefined}
                        previewIndex={Math.max(0, allPhotos.indexOf(l.photo_url ?? ''))}
                      />
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
            );
          }}
        </QueryState>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: GUTTER, paddingBottom: spacing[10] },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: GAP },
  cell: { width: CELL_W },
  image: { width: '100%', aspectRatio: 1, borderTopLeftRadius: radius.card, borderTopRightRadius: radius.card },
  title: { ...typography.bodyStrong, color: colors.ink[950], fontSize: 13 },
  price: { ...typography.meta, color: colors.coral[600], fontSize: 12 },
});
