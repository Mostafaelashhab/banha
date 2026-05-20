import { useState } from 'react';
import { RefreshControl, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router } from 'expo-router';
import { Card, Chip, IconTile, QueryState } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useFeed } from '@/api/hooks';
import { Business, FeedItem } from '@/api/types';

const fallbackCategories = [
  { key: 'all', label: 'الكل' },
  { key: 'food', label: 'مطاعم' },
  { key: 'cafes', label: 'قهاوي' },
  { key: 'shops', label: 'محلات' },
  { key: 'services', label: 'خدمات' },
  { key: 'jobs', label: 'وظائف' },
];

export default function Feed() {
  const [category, setCategory] = useState<string>('all');
  const query = useFeed(category === 'all' ? undefined : category);

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.greet}>مرحبًا 👋</Text>
        <Text style={styles.title}>إيه الجديد في بنها؟</Text>
      </View>

      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.chipsRow}
      >
        {fallbackCategories.map((c) => (
          <Chip key={c.key} active={c.key === category} onPress={() => setCategory(c.key)}>
            {c.label}
          </Chip>
        ))}
      </ScrollView>

      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={query.isFetching && !query.isLoading}
            onRefresh={() => query.refetch()}
          />
        }
      >
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>قريب منك</Text>
          <Text style={styles.sectionLink} onPress={() => router.push('/map')}>
            افتح الخريطة
          </Text>
        </View>

        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          emptyTitle="لسه مفيش محتوى"
          emptyHint="جرّب تغيّر التصنيف أو حدّث الصفحة"
          isEmpty={(d) => !d?.data?.length}
        >
          {(d) => (
            <View style={{ gap: spacing[3] }}>
              {d.data.map((item, i) => (
                <FeedRow key={`${item.type}-${i}`} item={item} />
              ))}
            </View>
          )}
        </QueryState>
      </ScrollView>
    </SafeAreaView>
  );
}

function FeedRow({ item }: { item: FeedItem }) {
  if (item.type === 'business') {
    return <BusinessRow business={item.business} />;
  }
  if (item.type === 'announcement') {
    return (
      <Card padding="md" variant="sponsored">
        <Text style={styles.rowTitle}>{item.title}</Text>
        {item.body && <Text style={styles.rowSubtitle}>{item.body}</Text>}
      </Card>
    );
  }
  return (
    <Card padding="md" style={{ gap: spacing[2] }}>
      <Text style={styles.rowTitle}>{item.post.author?.username ?? 'مجهول'}</Text>
      <Text style={styles.rowSubtitle}>{item.post.body}</Text>
    </Card>
  );
}

function BusinessRow({ business }: { business: Business }) {
  const distance =
    typeof business.distance_m === 'number'
      ? business.distance_m < 1000
        ? `${business.distance_m | 0} م`
        : `${(business.distance_m / 1000).toFixed(1)} كم`
      : null;

  return (
    <Card
      padding="md"
      style={styles.row}
      onPress={() => router.push(`/business/${business.slug}`)}
    >
      <IconTile icon="map-pin" tone="coral" size="lg" />
      <View style={{ flex: 1 }}>
        <Text style={styles.rowTitle}>{business.name}</Text>
        {business.subtitle ? (
          <Text style={styles.rowSubtitle}>{business.subtitle}</Text>
        ) : null}
      </View>
      {distance && (
        <View style={styles.distPill}>
          <Text style={styles.distText}>{distance}</Text>
        </View>
      )}
    </Card>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  header: {
    paddingHorizontal: spacing[4],
    paddingTop: spacing[2],
    paddingBottom: spacing[3],
    gap: spacing[1],
  },
  greet: { ...typography.meta, color: colors.ink[500] },
  title: { ...typography.h2, color: colors.ink[950] },
  chipsRow: {
    paddingHorizontal: spacing[4],
    paddingBottom: spacing[3],
    gap: spacing[2],
  },
  scroll: {
    paddingHorizontal: spacing[4],
    paddingBottom: spacing[10],
    gap: spacing[3],
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingTop: spacing[2],
  },
  sectionTitle: { ...typography.h3, color: colors.ink[950] },
  sectionLink: { ...typography.meta, color: colors.coral[600] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  rowTitle: { ...typography.bodyStrong, color: colors.ink[950], fontSize: 15 },
  rowSubtitle: { ...typography.body, color: colors.ink[500], marginTop: 2 },
  distPill: {
    paddingHorizontal: spacing[2],
    paddingVertical: spacing[1],
    borderRadius: radius.full,
    backgroundColor: colors.cream[100],
  },
  distText: { fontSize: 11, fontWeight: '800', color: colors.ink[700] },
});
