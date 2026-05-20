import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, IconTile, QueryState, RequireAuth, ScreenHeader } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useMyBookings } from '@/api/hooks';

export default function Bookings() {
  return (
    <RequireAuth title="حجوزاتي">
      <BookingsContent />
    </RequireAuth>
  );
}

function BookingsContent() {
  const query = useMyBookings();
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="حجوزاتي" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          emptyTitle="مفيش حجوزات"
          emptyHint="احجز موعد عند أي مكان بيدعم الحجز"
          isEmpty={(d) => !d?.data?.length}
        >
          {(d) => (
            <View style={{ gap: spacing[3] }}>
              {d.data.map((b) => (
                <Card key={String(b.id)} padding="md" style={styles.row}>
                  <IconTile icon="clock" tone="honey" size="md" />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.title}>{b.business?.name ?? `حجز #${b.id}`}</Text>
                    <Text style={styles.subtitle}>
                      {new Date(b.starts_at).toLocaleString('ar-EG', { dateStyle: 'medium', timeStyle: 'short' })}
                    </Text>
                    <Text style={styles.subtitle}>الحالة: {b.status}</Text>
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
