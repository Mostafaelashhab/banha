import { useEffect, useState } from 'react';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router } from 'expo-router';
import { Card, IconTile, QueryState, ScreenHeader } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useOpenNow } from '@/api/hooks';
import { Coords, requestCurrentLocation } from '@/lib/geo';

export default function OpenNow() {
  const [coords, setCoords] = useState<Coords | null>(null);

  useEffect(() => {
    requestCurrentLocation().then((c) => c && setCoords(c));
  }, []);

  const query = useOpenNow(coords?.latitude, coords?.longitude);

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="مفتوح دلوقتي" subtitle="محلات شغّالة الساعة دي" />
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
              {d.data.map((b) => {
                const dist = typeof b.distance_m === 'number'
                  ? b.distance_m < 1000 ? `${b.distance_m | 0} م` : `${(b.distance_m / 1000).toFixed(1)} كم`
                  : null;
                return (
                  <Card
                    key={String(b.id)}
                    padding="md"
                    style={styles.row}
                    onPress={() => router.push(`/business/${b.slug}`)}
                  >
                    <IconTile icon="clock" tone="mint" size="md" />
                    <View style={{ flex: 1 }}>
                      <Text style={styles.title}>{b.name}</Text>
                      {b.subtitle ? <Text style={styles.subtitle}>{b.subtitle}</Text> : null}
                    </View>
                    {dist && (
                      <View style={styles.distPill}>
                        <Text style={styles.distText}>{dist}</Text>
                      </View>
                    )}
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

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  title: { ...typography.bodyStrong, color: colors.ink[950] },
  subtitle: { ...typography.body, color: colors.ink[500], marginTop: 2 },
  distPill: { paddingHorizontal: spacing[2], paddingVertical: spacing[1], borderRadius: radius.full, backgroundColor: colors.cream[100] },
  distText: { fontSize: 11, fontWeight: '800', color: colors.ink[700] },
});
