import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router } from 'expo-router';
import { Card, IconTile, QueryState, RequireAuth, ScreenHeader } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useBookmarks } from '@/api/hooks';

export default function Bookmarks() {
  return (
    <RequireAuth title="محفوظاتي">
      <BookmarksContent />
    </RequireAuth>
  );
}

function BookmarksContent() {
  const query = useBookmarks();
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="محفوظاتي" subtitle="الأماكن اللي حفظتها" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          emptyTitle="مفيش محفوظات لسه"
          emptyHint="دوس على القلب في أي محل علشان تحفظه"
          isEmpty={(d) => !d?.data?.length}
        >
          {(d) => (
            <View style={{ gap: spacing[3] }}>
              {d.data.map((b) => (
                <Card
                  key={String(b.id)}
                  padding="md"
                  style={styles.row}
                  onPress={() => router.push(`/business/${b.slug}`)}
                >
                  <IconTile icon="bookmark" tone="coral" size="md" />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.title}>{b.name}</Text>
                    {b.subtitle ? <Text style={styles.subtitle}>{b.subtitle}</Text> : null}
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
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  title: { ...typography.bodyStrong, color: colors.ink[950] },
  subtitle: { ...typography.body, color: colors.ink[500], marginTop: 2 },
});
