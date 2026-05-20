import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, IconTile, QueryState, ScreenHeader } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { usePrices } from '@/api/hooks';

export default function Prices() {
  const query = usePrices();
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="أسعار السلع" subtitle="من سكان بنها لايف" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          isEmpty={(d) => !d?.data?.length}
        >
          {(d) => (
            <View style={{ gap: spacing[3] }}>
              {d.data.map((p) => (
                <Card key={String(p.id)} padding="md" style={styles.row}>
                  <IconTile icon="chart" tone="mint" size="md" />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.title}>{p.product ?? 'سلعة'}</Text>
                    {p.shop_name ? <Text style={styles.subtitle}>{p.shop_name}{p.zone ? ` · ${p.zone}` : ''}</Text> : null}
                  </View>
                  <Text style={styles.price}>{p.price.toFixed(0)} ج.م</Text>
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
  price: { ...typography.h3, color: colors.coral[600] },
});
