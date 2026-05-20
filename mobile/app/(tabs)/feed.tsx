import { Image, Pressable, RefreshControl, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Href, router } from 'expo-router';
import { Card, Icon, IconTile, Input, QueryState } from '@/components';
import { IconName } from '@/components';
import { ColorTone, colors, radius, shadows, spacing, typography } from '@/theme';
import { useHomeFeed } from '@/api/hooks';
import { useAuth } from '@/auth/AuthContext';
import { Business, HomeShortcut } from '@/api/types';

// Map PWA shortcut keys → mobile routes + icon tones
const shortcutTone: Record<string, { tone: ColorTone; icon: IconName; href: Href }> = {
  'craftsmen':  { tone: 'coral',  icon: 'shield',  href: '/marketplace' },
  'offers':     { tone: 'honey',  icon: 'bolt',    href: '/offers' },
  'bookings':   { tone: 'mint',   icon: 'check',   href: '/bookings' },
  'open-now':   { tone: 'mint',   icon: 'clock',   href: '/open-now' },
  'jobs':       { tone: 'coral',  icon: 'cart',    href: '/marketplace' },
  'trains':     { tone: 'honey',  icon: 'compass', href: '/marketplace' },
  'lost-found': { tone: 'blush',  icon: 'search',  href: '/marketplace' },
  'emergency':  { tone: 'blush',  icon: 'shield',  href: '/alerts' },
  'university': { tone: 'cream',  icon: 'star',    href: '/marketplace' },
  'marketplace':{ tone: 'mint',   icon: 'cart',    href: '/marketplace' },
};

export default function Feed() {
  const auth = useAuth();
  const query = useHomeFeed();

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={query.isFetching && !query.isLoading}
            onRefresh={() => query.refetch()}
          />
        }
      >
        {/* Greeting + bell */}
        <View style={styles.topRow}>
          <View style={{ flex: 1 }}>
            {auth.status === 'authenticated' && (
              <Text style={styles.greet}>أهلاً {auth.user?.username}</Text>
            )}
            <Text style={styles.h1}>بتدور على إيه في بنها؟</Text>
          </View>
          {auth.status === 'authenticated' && (
            <Pressable
              onPress={() => router.push('/notifications')}
              style={styles.bellBtn}
              accessibilityLabel="إشعارات"
            >
              <Icon name="bell" size={20} color={colors.coral[600]} />
              {(query.data?.unread_count ?? 0) > 0 && (
                <View style={styles.bellBadge}>
                  <Text style={styles.bellBadgeText}>
                    {(query.data?.unread_count ?? 0) > 9 ? '9+' : query.data?.unread_count}
                  </Text>
                </View>
              )}
            </Pressable>
          )}
        </View>

        {/* Search */}
        <Pressable onPress={() => router.push('/(tabs)/search')} style={styles.searchPress}>
          <Input
            icon="search"
            editable={false}
            placeholder="مطعم، دكتور، صيدلية، صنايعي، عرض…"
            pointerEvents="none"
          />
        </Pressable>

        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
        >
          {(d) => (
            <View style={{ gap: spacing[6] }}>
              {/* Utility shortcuts */}
              <ScrollView
                horizontal
                showsHorizontalScrollIndicator={false}
                contentContainerStyle={styles.shortcutsRow}
              >
                {d.shortcuts.map((s) => {
                  const m = shortcutTone[s.key] ?? { tone: 'coral' as const, icon: 'star' as IconName, href: '/' };
                  return (
                    <Pressable
                      key={s.key}
                      style={styles.shortcut}
                      onPress={() => router.push(m.href)}
                    >
                      <View style={styles.shortcutDisc}>
                        <Icon name={m.icon} size={20} color={colors.coral[600]} />
                      </View>
                      <Text style={styles.shortcutLabel} numberOfLines={2}>{s.label}</Text>
                    </Pressable>
                  );
                })}
              </ScrollView>

              {/* Popular searches */}
              {d.popular_searches?.length > 0 && (
                <View style={{ gap: spacing[2] }}>
                  <Text style={styles.sectionLabel}>الأكثر بحثًا</Text>
                  <ScrollView
                    horizontal
                    showsHorizontalScrollIndicator={false}
                    contentContainerStyle={styles.popularRow}
                  >
                    {d.popular_searches.map((p) => (
                      <Pressable
                        key={p}
                        style={styles.popularChip}
                        onPress={() => router.push({ pathname: '/(tabs)/search', params: { q: p } })}
                      >
                        <Icon name="search" size={12} color={colors.ink[400]} />
                        <Text style={styles.popularText}>{p}</Text>
                      </Pressable>
                    ))}
                  </ScrollView>
                </View>
              )}

              {/* Categories — circle icons row */}
              {d.categories?.length > 0 && (
                <View style={{ gap: spacing[2] }}>
                  <SectionHeader title="الفئات" />
                  <ScrollView
                    horizontal
                    showsHorizontalScrollIndicator={false}
                    contentContainerStyle={styles.catsRow}
                  >
                    {d.categories.slice(0, 12).map((c) => (
                      <Pressable
                        key={c.slug}
                        style={styles.cat}
                        onPress={() => router.push({ pathname: '/(tabs)/search', params: { q: c.label } })}
                      >
                        <View style={[styles.catDisc, c.color ? { borderColor: c.color + '33' } : null]}>
                          <Icon name={mapCatIcon(c.icon)} size={26} color={c.color ?? colors.coral[600]} />
                        </View>
                        <Text style={styles.catLabel} numberOfLines={2}>{c.label}</Text>
                      </Pressable>
                    ))}
                  </ScrollView>
                </View>
              )}

              {/* Sponsored (مميّزة الأسبوع) */}
              {d.promoted?.length > 0 && (
                <View style={{ gap: spacing[3] }}>
                  <SectionHeader title="مميّزة الأسبوع" />
                  <ScrollView
                    horizontal
                    showsHorizontalScrollIndicator={false}
                    contentContainerStyle={styles.promotedRow}
                  >
                    {d.promoted.map((b) => (
                      <Pressable
                        key={String(b.id)}
                        style={styles.promoted}
                        onPress={() => router.push(`/business/${b.slug}`)}
                      >
                        <View style={styles.promotedDisc}>
                          {b.cover_url ? (
                            <Image source={{ uri: b.cover_url }} style={styles.promotedImg} />
                          ) : (
                            <Text style={styles.promotedFallback}>{b.name.slice(0, 1)}</Text>
                          )}
                        </View>
                        <Text style={styles.promotedLabel} numberOfLines={2}>{b.name}</Text>
                      </Pressable>
                    ))}
                  </ScrollView>
                </View>
              )}

              {/* Top rated */}
              {d.top_rated?.length > 0 && (
                <View style={{ gap: spacing[3] }}>
                  <SectionHeader title="الأكتر تقييم في بنها" />
                  <View style={{ gap: spacing[3] }}>
                    {d.top_rated.slice(0, 5).map((b) => (
                      <BusinessRow key={String(b.id)} business={b} />
                    ))}
                  </View>
                </View>
              )}
            </View>
          )}
        </QueryState>
      </ScrollView>
    </SafeAreaView>
  );
}

function SectionHeader({ title }: { title: string }) {
  return (
    <View style={styles.sectionHeader}>
      <Text style={styles.sectionTitle}>{title}</Text>
      <Pressable onPress={() => router.push('/(tabs)/search')} style={styles.sectionLinkBtn}>
        <Text style={styles.sectionLink}>شوف الكل</Text>
        <Icon name="chevron-left" size={18} color={colors.ink[700]} />
      </Pressable>
    </View>
  );
}

function BusinessRow({ business: b }: { business: Business }) {
  return (
    <Card padding="md" style={styles.bizRow} onPress={() => router.push(`/business/${b.slug}`)}>
      <View style={styles.bizThumb}>
        {b.cover_url ? (
          <Image source={{ uri: b.cover_url }} style={{ width: '100%', height: '100%', borderRadius: radius.lg }} />
        ) : (
          <Text style={styles.bizThumbFallback}>{b.name.slice(0, 1)}</Text>
        )}
      </View>
      <View style={{ flex: 1 }}>
        <Text style={styles.bizName} numberOfLines={1}>{b.name}</Text>
        {b.subtitle ? <Text style={styles.bizSub} numberOfLines={1}>{b.subtitle}</Text> : null}
        {typeof b.rating === 'number' && (
          <View style={styles.ratingRow}>
            <Icon name="star" size={12} color={colors.honey[500]} />
            <Text style={styles.ratingText}>
              {b.rating.toFixed(1)} {b.reviews_count ? `(${b.reviews_count})` : ''}
            </Text>
          </View>
        )}
      </View>
    </Card>
  );
}

function mapCatIcon(icon?: string): IconName {
  switch (icon) {
    case 'utensils':       return 'cart';
    case 'briefcase':      return 'shield';
    case 'stethoscope':    return 'shield';
    case 'bag':            return 'cart';
    case 'wrench':         return 'shield';
    case 'tag':            return 'bolt';
    case 'graduation':     return 'star';
    case 'train':          return 'compass';
    default:               return 'compass';
  }
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10], gap: spacing[4] },
  topRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  greet: { fontSize: 11, fontWeight: '700', color: colors.ink[500] },
  h1: { ...typography.h2, color: colors.ink[950] },
  bellBtn: {
    width: 40,
    height: 40,
    borderRadius: 999,
    backgroundColor: colors.coral[50],
    alignItems: 'center',
    justifyContent: 'center',
  },
  bellBadge: {
    position: 'absolute',
    top: -2,
    insetInlineEnd: -2,
    minWidth: 18,
    height: 18,
    paddingHorizontal: 4,
    borderRadius: 999,
    backgroundColor: colors.coral[500],
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: colors.cream[100],
  },
  bellBadgeText: { color: colors.white, fontSize: 9, fontWeight: '900' },
  searchPress: { width: '100%' },
  shortcutsRow: { gap: spacing[2], paddingVertical: 2 },
  shortcut: {
    width: 78,
    paddingVertical: spacing[3],
    paddingHorizontal: spacing[1],
    borderRadius: radius['2xl'],
    backgroundColor: colors.coral[50],
    alignItems: 'center',
    gap: spacing[1.5],
  },
  shortcutDisc: {
    width: 44,
    height: 44,
    borderRadius: 999,
    backgroundColor: colors.white,
    alignItems: 'center',
    justifyContent: 'center',
    ...shadows.card,
  },
  shortcutLabel: {
    fontSize: 10,
    fontWeight: '900',
    color: colors.ink[950],
    textAlign: 'center',
    lineHeight: 12,
    paddingHorizontal: 4,
  },
  popularRow: { gap: spacing[2] },
  popularChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: spacing[3],
    paddingVertical: 6,
    borderRadius: radius.full,
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: 'rgba(11,11,12,0.06)',
  },
  popularText: { fontSize: 12, fontWeight: '800', color: colors.ink[950] },
  sectionLabel: { fontSize: 11, fontWeight: '700', color: colors.ink[500], paddingHorizontal: spacing[1] },
  sectionHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: spacing[1] },
  sectionTitle: { ...typography.h2, fontSize: 18, color: colors.ink[950] },
  sectionLinkBtn: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  sectionLink: { fontSize: 13, fontWeight: '800', color: colors.ink[700] },
  catsRow: { gap: spacing[3], paddingVertical: spacing[2] },
  cat: { width: 72, alignItems: 'center', gap: 6 },
  catDisc: {
    width: 64,
    height: 64,
    borderRadius: 999,
    backgroundColor: colors.white,
    borderWidth: 2,
    borderColor: colors.cream[200],
    alignItems: 'center',
    justifyContent: 'center',
  },
  catLabel: { fontSize: 11, fontWeight: '800', color: colors.ink[950], textAlign: 'center', lineHeight: 14 },
  promotedRow: { gap: spacing[3], paddingVertical: spacing[2] },
  promoted: { width: 84, alignItems: 'center', gap: 6 },
  promotedDisc: {
    width: 72,
    height: 72,
    borderRadius: 999,
    backgroundColor: colors.white,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    borderWidth: 2,
    borderColor: colors.honey[400],
  },
  promotedImg: { width: '100%', height: '100%' },
  promotedFallback: { fontSize: 26, fontWeight: '900', color: colors.coral[600] },
  promotedLabel: { fontSize: 11, fontWeight: '800', color: colors.ink[950], textAlign: 'center', lineHeight: 14 },
  bizRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[3] },
  bizThumb: {
    width: 56,
    height: 56,
    borderRadius: radius.lg,
    backgroundColor: colors.cream[200],
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  bizThumbFallback: { fontSize: 22, fontWeight: '900', color: colors.coral[600] },
  bizName: { ...typography.bodyStrong, color: colors.ink[950], fontSize: 15 },
  bizSub: { ...typography.body, color: colors.ink[500], marginTop: 2, fontSize: 12 },
  ratingRow: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 4 },
  ratingText: { fontSize: 11, fontWeight: '800', color: colors.ink[700] },
});
