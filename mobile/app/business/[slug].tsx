import { Linking, Platform, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router, useLocalSearchParams } from 'expo-router';
import MapView, { Marker, PROVIDER_GOOGLE } from 'react-native-maps';
import { Button, Card, IconTile, QueryState } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useBusiness, useTrackBusinessClick } from '@/api/hooks';

export default function BusinessDetail() {
  const { slug } = useLocalSearchParams<{ slug: string }>();
  const query = useBusiness(slug ?? '');
  const track = useTrackBusinessClick();

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <Button variant="ghost" icon="arrow-right" onPress={() => router.back()}>
          رجوع
        </Button>
      </View>
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
        >
          {(b) => (
            <View style={{ gap: spacing[4] }}>
              <Card padding="lg" style={{ gap: spacing[2] }}>
                <View style={styles.row}>
                  <IconTile icon="map-pin" tone="coral" intensity="strong" size="lg" />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.title}>{b.name}</Text>
                    {b.subtitle ? <Text style={styles.subtitle}>{b.subtitle}</Text> : null}
                  </View>
                </View>
                {b.address ? <Text style={styles.meta}>{b.address}</Text> : null}
              </Card>

              <View style={styles.ctaRow}>
                {b.phone && (
                  <Button
                    variant="primary"
                    icon="phone"
                    onPress={() => {
                      track.mutate(b.id);
                      Linking.openURL(`tel:${b.phone}`).catch(() => {});
                    }}
                  >
                    اتصال
                  </Button>
                )}
                {b.whatsapp && (
                  <Button
                    variant="whatsapp"
                    icon="whatsapp"
                    onPress={() => {
                      track.mutate(b.id);
                      Linking.openURL(`https://wa.me/${b.whatsapp}`).catch(() => {});
                    }}
                  >
                    واتساب
                  </Button>
                )}
              </View>

              {typeof b.lat === 'number' && typeof b.lng === 'number' && (
                <Card padding="none" style={styles.mapCard}>
                  <MapView
                    provider={Platform.OS === 'android' ? PROVIDER_GOOGLE : undefined}
                    style={styles.miniMap}
                    initialRegion={{
                      latitude: b.lat,
                      longitude: b.lng,
                      latitudeDelta: 0.01,
                      longitudeDelta: 0.01,
                    }}
                    pointerEvents="none"
                  >
                    <Marker coordinate={{ latitude: b.lat, longitude: b.lng }} pinColor={colors.coral[500]} />
                  </MapView>
                  <View style={{ padding: spacing[3] }}>
                    <Button
                      variant="outline"
                      block
                      icon="compass"
                      onPress={() => {
                        const url = Platform.select({
                          ios: `maps://?q=${b.lat},${b.lng}`,
                          android: `geo:${b.lat},${b.lng}?q=${b.lat},${b.lng}(${encodeURIComponent(b.name)})`,
                          default: `https://maps.google.com/?q=${b.lat},${b.lng}`,
                        })!;
                        Linking.openURL(url).catch(() => {});
                      }}
                    >
                      افتح في خرايط جوجل
                    </Button>
                  </View>
                </Card>
              )}
            </View>
          )}
        </QueryState>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  header: { paddingHorizontal: spacing[2], paddingTop: spacing[1] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  title: { ...typography.h2, color: colors.ink[950] },
  subtitle: { ...typography.body, color: colors.ink[500], marginTop: 2 },
  meta: { ...typography.meta, color: colors.ink[500] },
  ctaRow: { flexDirection: 'row', gap: spacing[2], flexWrap: 'wrap' },
  mapCard: { overflow: 'hidden' },
  miniMap: { width: '100%', height: 200, borderTopLeftRadius: radius.card, borderTopRightRadius: radius.card },
});
