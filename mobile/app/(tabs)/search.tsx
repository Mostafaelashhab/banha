import { useState } from 'react';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router } from 'expo-router';
import { Card, Chip, EmptyState, IconTile, Input, QueryState } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useSearch } from '@/api/hooks';

const popular = ['كشري', 'صيدلية مفتوحة', 'قهوة', 'فرن عيش', 'كوافير', 'جيم'];

export default function Search() {
  const [q, setQ] = useState('');
  const trimmed = q.trim();
  const query = useSearch(trimmed);

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>ابحث في بنها</Text>
        <Input
          icon="search"
          placeholder="إيه اللي محتاجه؟ مطعم، صيدلية، خدمة…"
          value={q}
          onChangeText={setQ}
          returnKeyType="search"
        />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        {trimmed.length === 0 ? (
          <>
            <Text style={styles.sectionLabel}>الأكثر بحثًا</Text>
            <View style={styles.chipsWrap}>
              {popular.map((p) => (
                <Chip key={p} onPress={() => setQ(p)}>
                  {p}
                </Chip>
              ))}
            </View>
            <EmptyState
              icon="search"
              title="لسه مفيش نتايج"
              hint="اكتب اسم النشاط أو الخدمة اللي بتدور عليها"
            />
          </>
        ) : (
          <QueryState
            status={query.status}
            data={query.data}
            error={query.error}
            refetch={query.refetch}
            emptyTitle="مفيش نتايج"
            emptyHint="جرّب كلمة تانية أو شيل الفلتر"
            isEmpty={(d) => !d?.businesses?.length && !d?.posts?.length}
          >
            {(d) => (
              <View style={{ gap: spacing[3] }}>
                {d.businesses.map((b) => (
                  <Card
                    key={String(b.id)}
                    padding="md"
                    style={styles.row}
                    onPress={() => router.push(`/business/${b.slug}`)}
                  >
                    <IconTile icon="map-pin" tone="coral" size="md" />
                    <View style={{ flex: 1 }}>
                      <Text style={styles.rowTitle}>{b.name}</Text>
                      {b.subtitle ? (
                        <Text style={styles.rowSubtitle}>{b.subtitle}</Text>
                      ) : null}
                    </View>
                  </Card>
                ))}
              </View>
            )}
          </QueryState>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  header: {
    paddingHorizontal: spacing[4],
    paddingTop: spacing[2],
    paddingBottom: spacing[3],
    gap: spacing[3],
  },
  title: { ...typography.h2, color: colors.ink[950] },
  scroll: {
    paddingHorizontal: spacing[4],
    paddingBottom: 120,
    gap: spacing[3],
  },
  sectionLabel: { ...typography.nano, color: colors.coral[600], marginTop: spacing[2] },
  chipsWrap: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing[2] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  rowTitle: { ...typography.bodyStrong, color: colors.ink[950] },
  rowSubtitle: { ...typography.body, color: colors.ink[500], marginTop: 2 },
});
