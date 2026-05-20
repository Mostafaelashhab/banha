import { Image, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, IconTile, QueryState, ScreenHeader } from '@/components';
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
          {(d) => (
            <View style={{ gap: spacing[3] }}>
              {d.data.map((e) => (
                <Card key={String(e.id)} padding="none" style={{ overflow: 'hidden' }}>
                  {e.cover_url ? (
                    <Image source={{ uri: e.cover_url }} style={styles.cover} />
                  ) : (
                    <View style={[styles.cover, styles.coverFallback]}>
                      <IconTile icon="star" tone="honey" size="xl" shape="circle" />
                    </View>
                  )}
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
          )}
        </QueryState>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10] },
  cover: { width: '100%', height: 160, backgroundColor: colors.cream[200], borderTopLeftRadius: radius.card, borderTopRightRadius: radius.card },
  coverFallback: { alignItems: 'center', justifyContent: 'center' },
  title: { ...typography.h3, color: colors.ink[950] },
  meta: { ...typography.meta, color: colors.coral[600] },
  subtitle: { ...typography.body, color: colors.ink[500] },
});
