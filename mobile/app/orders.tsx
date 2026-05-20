import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, IconTile, QueryState, RequireAuth, ScreenHeader } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useMyOrders } from '@/api/hooks';

const statusLabels: Record<string, { label: string; tone: 'coral' | 'mint' | 'honey' | 'blush' | 'cream' }> = {
  pending:   { label: 'قيد المراجعة', tone: 'honey' },
  confirmed: { label: 'تأكدت',         tone: 'mint' },
  preparing: { label: 'بنحضّر',        tone: 'honey' },
  on_the_way:{ label: 'في الطريق',     tone: 'coral' },
  delivered: { label: 'تم التوصيل',    tone: 'mint' },
  cancelled: { label: 'متلغي',         tone: 'blush' },
};

export default function Orders() {
  return (
    <RequireAuth title="طلباتي">
      <OrdersContent />
    </RequireAuth>
  );
}

function OrdersContent() {
  const query = useMyOrders();
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="طلباتي" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          emptyTitle="لسه مطلبتش حاجة"
          emptyHint="اطلب من قائمة طعام أي مطعم"
          isEmpty={(d) => !d?.data?.length}
        >
          {(d) => (
            <View style={{ gap: spacing[3] }}>
              {d.data.map((o) => {
                const s = statusLabels[o.status] ?? { label: o.status, tone: 'cream' as const };
                return (
                  <Card key={String(o.id)} padding="md" style={{ gap: spacing[2] }}>
                    <View style={styles.row}>
                      <IconTile icon="cart" tone={s.tone} size="md" />
                      <View style={{ flex: 1 }}>
                        <Text style={styles.title}>{o.business?.name ?? `طلب #${o.id}`}</Text>
                        <Text style={styles.subtitle}>
                          {o.items?.length ?? 0} أصناف · {o.subtotal.toFixed(0)} {o.currency}
                        </Text>
                      </View>
                      <View style={[styles.statusPill, { backgroundColor: tonePillBg(s.tone) }]}>
                        <Text style={[styles.statusText, { color: tonePillFg(s.tone) }]}>{s.label}</Text>
                      </View>
                    </View>
                  </Card>
                );
              })}
            </View>
          )}
        </QueryState>
      </ScrollView>
    </SafeAreaView>
  );
}

function tonePillBg(tone: string) {
  if (tone === 'mint') return colors.mint[100];
  if (tone === 'honey') return '#FFF6D6';
  if (tone === 'blush') return colors.blush[100];
  if (tone === 'coral') return colors.coral[100];
  return colors.cream[200];
}
function tonePillFg(tone: string) {
  if (tone === 'mint') return colors.mint[700];
  if (tone === 'honey') return colors.honey[500];
  if (tone === 'blush') return colors.blush[500];
  if (tone === 'coral') return colors.coral[700];
  return colors.ink[700];
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  title: { ...typography.bodyStrong, color: colors.ink[950] },
  subtitle: { ...typography.body, color: colors.ink[500], marginTop: 2 },
  statusPill: { paddingHorizontal: spacing[2], paddingVertical: spacing[1], borderRadius: radius.full },
  statusText: { fontSize: 11, fontWeight: '800' },
});
