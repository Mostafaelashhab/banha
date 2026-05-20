import { RefreshControl, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button, Card, IconTile, QueryState } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useMarkAllRead, useNotifications } from '@/api/hooks';
import { Notification } from '@/api/types';

export default function NotificationsScreen() {
  const query = useNotifications();
  const markAll = useMarkAllRead();

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>إشعاراتك</Text>
        <Button
          variant="ghost"
          size="sm"
          onPress={() => markAll.mutate()}
          loading={markAll.isPending}
        >
          علّم الكل مقروء
        </Button>
      </View>

      <ScrollView
        contentContainerStyle={styles.scroll}
        refreshControl={
          <RefreshControl
            refreshing={query.isFetching && !query.isLoading}
            onRefresh={() => query.refetch()}
          />
        }
      >
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          emptyTitle="مفيش إشعارات لسه"
          emptyHint="لما يحصل جديد هتلاقيه هنا"
          isEmpty={(d) => !d?.data?.length}
        >
          {(d) => (
            <View style={{ gap: spacing[3] }}>
              {d.data.map((n: Notification) => (
                <Card key={String(n.id)} padding="md" style={styles.row}>
                  <IconTile
                    icon="bell"
                    tone={n.read_at ? 'cream' : 'coral'}
                    size="md"
                  />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.rowTitle}>{n.title}</Text>
                    {n.body ? <Text style={styles.rowBody}>{n.body}</Text> : null}
                    <Text style={styles.rowMeta}>{relative(n.created_at)}</Text>
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

function relative(iso: string): string {
  try {
    const d = new Date(iso).getTime();
    const diffSec = (Date.now() - d) / 1000;
    if (diffSec < 60) return 'دلوقتي';
    if (diffSec < 3600) return `من ${Math.round(diffSec / 60)} دقيقة`;
    if (diffSec < 86400) return `من ${Math.round(diffSec / 3600)} ساعة`;
    return `من ${Math.round(diffSec / 86400)} يوم`;
  } catch {
    return '';
  }
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing[4],
    paddingTop: spacing[2],
    paddingBottom: spacing[3],
  },
  title: { ...typography.h2, color: colors.ink[950] },
  scroll: { paddingHorizontal: spacing[4], paddingBottom: spacing[10], gap: spacing[3] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  rowTitle: { ...typography.bodyStrong, color: colors.ink[950] },
  rowBody: { ...typography.body, color: colors.ink[500], marginTop: 2 },
  rowMeta: { fontSize: 11, color: colors.ink[400], fontWeight: '700', marginTop: 4 },
});
