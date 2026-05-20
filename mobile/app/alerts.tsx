import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, IconTile, QueryState, ScreenHeader } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useAlerts } from '@/api/hooks';

const typeIcon: Record<string, 'shield' | 'bolt' | 'map-pin' | 'bell'> = {
  accident: 'shield',
  traffic:  'map-pin',
  emergency:'bolt',
  power:    'bolt',
  water:    'shield',
};

export default function Alerts() {
  const query = useAlerts();
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="تنبيهات وحوادث" subtitle="من سكان بنها لايف" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          emptyTitle="مفيش تنبيهات دلوقتي"
          emptyHint="الحمد لله — الدنيا هادية"
          isEmpty={(d) => !d?.data?.length}
        >
          {(d) => (
            <View style={{ gap: spacing[3] }}>
              {d.data.map((a) => (
                <Card key={String(a.id)} padding="md" style={styles.row}>
                  <IconTile icon={typeIcon[a.type] ?? 'bell'} tone="blush" size="md" />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.title}>{a.description}</Text>
                    <View style={styles.metaRow}>
                      {a.zone ? <Text style={styles.meta}>{a.zone}</Text> : null}
                      {a.confirmations > 0 ? (
                        <View style={styles.confPill}>
                          <Text style={styles.confText}>✓ {a.confirmations}</Text>
                        </View>
                      ) : null}
                    </View>
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
  row: { flexDirection: 'row', alignItems: 'flex-start', gap: spacing[3] },
  title: { ...typography.bodyStrong, color: colors.ink[950] },
  metaRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[2], marginTop: 6 },
  meta: { fontSize: 11, fontWeight: '700', color: colors.ink[500] },
  confPill: { paddingHorizontal: spacing[2], paddingVertical: 2, borderRadius: radius.full, backgroundColor: colors.mint[100] },
  confText: { fontSize: 10, fontWeight: '800', color: colors.mint[700] },
});
