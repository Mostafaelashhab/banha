import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useLocalSearchParams } from 'expo-router';
import { Card, Icon, IconTile, QueryState, ScreenHeader } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useReviews } from '@/api/hooks';

export default function BusinessReviews() {
  const { slug } = useLocalSearchParams<{ slug: string }>();
  const query = useReviews(slug ?? '');

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="التقييمات" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          isEmpty={(d) => !d?.data?.length}
          emptyTitle="مفيش تقييمات لسه"
          emptyHint="كن أول واحد يقيّم"
        >
          {(d) => (
            <View style={{ gap: spacing[3] }}>
              {typeof d.meta?.rating_avg === 'number' && (
                <Card padding="md" style={styles.summary}>
                  <IconTile icon="star" tone="honey" intensity="strong" size="lg" />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.avg}>{d.meta.rating_avg.toFixed(1)} / 5</Text>
                    <Text style={styles.avgMeta}>
                      من {d.meta.ratings_count ?? 0} تقييم
                    </Text>
                  </View>
                </Card>
              )}
              {d.data.map((r) => (
                <Card key={String(r.id)} padding="md" style={{ gap: spacing[2] }}>
                  <View style={styles.row}>
                    <View style={styles.starsRow}>
                      {[1, 2, 3, 4, 5].map((n) => (
                        <Icon
                          key={n}
                          name="star"
                          size={14}
                          color={n <= r.rating ? colors.honey[500] : colors.cream[200]}
                        />
                      ))}
                    </View>
                    <Text style={styles.author}>{r.author_name ?? 'مجهول'}</Text>
                  </View>
                  {r.body ? <Text style={styles.body}>{r.body}</Text> : null}
                </Card>
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
  summary: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  avg: { ...typography.h2, color: colors.ink[950] },
  avgMeta: { ...typography.meta, color: colors.ink[500] },
  row: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  starsRow: { flexDirection: 'row', gap: 2 },
  author: { ...typography.meta, color: colors.ink[500] },
  body: { ...typography.body, color: colors.ink[700], lineHeight: 22 },
});
