import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, QueryState, ScreenHeader, SmartImage } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useEvents } from '@/api/hooks';

export default function Events() {
  const query = useEvents();
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="فعاليات وأحداث" subtitle="كل اللي بيحصل في بنها" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          emptyTitle="مفيش فعاليات قريبة"
          emptyHint="هنبلغك أول ما حد يضيف"
          isEmpty={(d) => !d?.data?.length}
        >
          {(d) => {
            const covers = d.data.map((e) => e.cover_url).filter(Boolean) as string[];
            return (
              <View style={{ gap: spacing[3] }}>
                {d.data.map((e) => (
                  <Card key={String(e.id)} padding="none" style={{ overflow: 'hidden' }}>
                    <SmartImage
                      uri={e.cover_url}
                      fallbackText={e.title}
                      style={styles.cover}
                      radius={0}
                      previewUris={e.cover_url ? covers : undefined}
                      previewIndex={Math.max(0, covers.indexOf(e.cover_url ?? ''))}
                    />
                    <View style={{ padding: spacing[4], gap: spacing[1] }}>
                      <Text style={styles.title}>{e.title}</Text>
                      <Text style={styles.meta}>
                        {new Date(e.starts_at).toLocaleString('ar-EG', { dateStyle: 'medium', timeStyle: 'short' })}
                      </Text>
                      {e.location ? <Text style={styles.subtitle}>{e.location}</Text> : null}
                    </View>
                  </Card>
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
  scroll: { padding: spacing[4], paddingBottom: spacing[10] },
  cover: { width: '100%', height: 160, borderTopLeftRadius: radius.card, borderTopRightRadius: radius.card },
  title: { ...typography.h3, color: colors.ink[950] },
  meta: { ...typography.meta, color: colors.coral[600] },
  subtitle: { ...typography.body, color: colors.ink[500] },
});
