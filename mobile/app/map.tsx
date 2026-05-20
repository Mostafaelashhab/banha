import { useEffect, useRef, useState } from 'react';
import { Platform, Pressable, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router } from 'expo-router';
import MapView, { Marker, PROVIDER_GOOGLE, Region } from 'react-native-maps';
import { Button, Card, IconTile, QueryState } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useDirectory } from '@/api/hooks';
import { BANHA_CENTER, Coords, requestCurrentLocation } from '@/lib/geo';
import { Business } from '@/api/types';

export default function MapScreen() {
  const mapRef = useRef<MapView | null>(null);
  const [region, setRegion] = useState<Region>(BANHA_CENTER);
  const [me, setMe] = useState<Coords | null>(null);
  const [selected, setSelected] = useState<Business | null>(null);

  const dir = useDirectory({
    lat: region.latitude,
    lng: region.longitude,
    radius_km: 5,
  });

  useEffect(() => {
    (async () => {
      const coords = await requestCurrentLocation();
      if (coords) {
        setMe(coords);
        const next: Region = {
          ...coords,
          latitudeDelta: 0.03,
          longitudeDelta: 0.03,
        };
        setRegion(next);
        mapRef.current?.animateToRegion(next, 600);
      }
    })();
  }, []);

  const recenter = async () => {
    const coords = me ?? (await requestCurrentLocation());
    if (!coords) return;
    setMe(coords);
    const next: Region = { ...coords, latitudeDelta: 0.03, longitudeDelta: 0.03 };
    mapRef.current?.animateToRegion(next, 500);
  };

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <Pressable onPress={() => router.back()} style={styles.iconBtn}>
          <IconTile icon="arrow-right" tone="cream" size="md" />
        </Pressable>
        <Text style={styles.title}>الخريطة</Text>
        <Pressable onPress={recenter} style={styles.iconBtn}>
          <IconTile icon="map-pin" tone="coral" size="md" />
        </Pressable>
      </View>

      <View style={styles.mapWrap}>
        <MapView
          ref={mapRef}
          provider={Platform.OS === 'android' ? PROVIDER_GOOGLE : undefined}
          style={StyleSheet.absoluteFill}
          initialRegion={BANHA_CENTER}
          showsUserLocation
          showsMyLocationButton={false}
          onRegionChangeComplete={setRegion}
        >
          {(dir.data?.data ?? [])
            .filter((b): b is Business & { lat: number; lng: number } =>
              typeof b.lat === 'number' && typeof b.lng === 'number',
            )
            .map((b) => (
              <Marker
                key={String(b.id)}
                coordinate={{ latitude: b.lat, longitude: b.lng }}
                title={b.name}
                description={b.subtitle ?? undefined}
                onPress={() => setSelected(b)}
                pinColor={colors.coral[500]}
              />
            ))}
        </MapView>

        <View style={styles.statusBar} pointerEvents="none">
          {dir.isFetching && (
            <View style={styles.badge}>
              <Text style={styles.badgeText}>بنحدّث الأماكن…</Text>
            </View>
          )}
        </View>
      </View>

      <View style={styles.sheet}>
        {selected ? (
          <Card padding="md" style={styles.sheetCard}>
            <View style={styles.row}>
              <IconTile icon="map-pin" tone="coral" size="lg" />
              <View style={{ flex: 1 }}>
                <Text style={styles.sheetTitle}>{selected.name}</Text>
                {selected.subtitle ? (
                  <Text style={styles.sheetSubtitle}>{selected.subtitle}</Text>
                ) : null}
              </View>
            </View>
            <Button
              block
              size="md"
              onPress={() => router.push(`/business/${selected.slug}`)}
            >
              افتح صفحة النشاط
            </Button>
          </Card>
        ) : (
          <QueryState
            status={dir.status}
            data={dir.data}
            error={dir.error}
            refetch={dir.refetch}
            emptyTitle="مفيش أماكن قريبة"
            emptyHint="حرّك الخريطة شوية أو قرّب أكتر"
            isEmpty={(d) => !d?.data?.length}
          >
            {(d) => (
              <Card padding="md" style={styles.sheetCard}>
                <Text style={styles.sheetMeta}>
                  {d.data.length} نشاط في المنطقة دي
                </Text>
                <Text style={styles.sheetTitle}>دوس على أي ماركر</Text>
                <Text style={styles.sheetSubtitle}>
                  علشان تشوف تفاصيل النشاط
                </Text>
              </Card>
            )}
          </QueryState>
        )}
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing[4],
    paddingBottom: spacing[2],
  },
  title: { ...typography.h3, color: colors.ink[950] },
  iconBtn: { padding: 2 },
  mapWrap: { flex: 1, borderRadius: radius['3xl'], overflow: 'hidden', marginHorizontal: spacing[3] },
  statusBar: {
    position: 'absolute',
    top: spacing[3],
    alignSelf: 'center',
    flexDirection: 'row',
  },
  badge: {
    backgroundColor: 'rgba(11,11,12,0.7)',
    paddingHorizontal: spacing[3],
    paddingVertical: spacing[1.5],
    borderRadius: radius.full,
  },
  badgeText: { color: colors.white, fontSize: 11, fontWeight: '800' },
  sheet: { padding: spacing[3] },
  sheetCard: { gap: spacing[3] },
  sheetTitle: { ...typography.h3, color: colors.ink[950] },
  sheetSubtitle: { ...typography.body, color: colors.ink[500] },
  sheetMeta: { ...typography.nano, color: colors.coral[600] },
  row: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
});
