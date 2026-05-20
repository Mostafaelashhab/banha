import { Dimensions, Pressable, RefreshControl, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { Href, router } from 'expo-router';
import { Card, Icon, QueryState, SmartImage } from '@/components';
import { IconName } from '@/components';
import { colors, radius, shadows, spacing, typography } from '@/theme';
import { useHomeFeed } from '@/api/hooks';
import { useAuth } from '@/auth/AuthContext';
import { Business } from '@/api/types';

const { width: SCREEN_W } = Dimensions.get('window');
const HERO_W = SCREEN_W - 32;

type QuickAction = { key: string; label: string; icon: IconName; color: string; href: Href };

const QUICK_ACTIONS: QuickAction[] = [
  { key: 'open-now',    label: 'مفتوح دلوقتي',  icon: 'clock',   color: '#1FA857', href: '/open-now' },
  { key: 'offers',      label: 'عروض اليوم',    icon: 'bolt',    color: '#F5BA12', href: '/offers' },
  { key: 'marketplace', label: 'سوق بنها',      icon: 'cart',    color: '#2D5BFF', href: '/marketplace' },
  { key: 'alerts',      label: 'لايف بنها',     icon: 'bell',    color: '#E64646', href: '/alerts' },
];

// Matches the slugs in App\Models\Business::CATEGORIES
const CATEGORY_PALETTE: Record<string, { bg: string; fg: string; icon: IconName }> = {
  food:        { bg: '#FFE9DD', fg: '#FF7A4D', icon: 'cart' },
  hotels:      { bg: '#EFE3FF', fg: '#9333EA', icon: 'star' },
  medical:     { bg: '#D8F5E2', fg: '#0D8A3F', icon: 'shield' },
  shops:       { bg: '#FFF6D6', fg: '#B58300', icon: 'cart' },
  craftsmen:   { bg: '#FFE6F0', fg: '#D6336C', icon: 'settings' },
  services:    { bg: '#E9EBF1', fg: '#5C5C66', icon: 'settings' },
  companies:   { bg: '#DCE4FF', fg: '#1736B0', icon: 'chart' },
  government:  { bg: '#DCE4FF', fg: '#1736B0', icon: 'shield' },
  education:   { bg: '#DCE4FF', fg: '#1736B0', icon: 'edit' },
  transport:   { bg: '#D6F4E9', fg: '#0D8A3F', icon: 'compass' },
  religious:   { bg: '#D8F5E2', fg: '#0D8A3F', icon: 'star' },
  banks:       { bg: '#FFF6D6', fg: '#B58300', icon: 'chart' },
  tourist:     { bg: '#FFE9DD', fg: '#FF7A4D', icon: 'map-pin' },
  emergency:   { bg: '#FCE0E0', fg: '#E64646', icon: 'bolt' },
};

export default function Home() {
  const auth = useAuth();
  const query = useHomeFeed();

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}
        stickyHeaderIndices={[0]}
        refreshControl={
          <RefreshControl
            refreshing={query.isFetching && !query.isLoading}
            onRefresh={() => query.refetch()}
            tintColor={colors.coral[500]}
          />
        }
      >
        {/* Sticky header — search + bell */}
        <View style={styles.stickyHeader}>
          <View style={styles.headerRow}>
            <Pressable style={styles.searchBar} onPress={() => router.push('/(tabs)/search')}>
              <Icon name="search" size={18} color={colors.ink[400]} />
              <Text style={styles.searchPlaceholder} numberOfLines={1}>
                بتدور على إيه في بنها؟
              </Text>
            </Pressable>
            {auth.status === 'authenticated' && (
              <Pressable
                onPress={() => router.push('/notifications')}
                style={styles.bellBtn}
                accessibilityLabel="إشعارات"
              >
                <Icon name="bell" size={20} color={colors.ink[950]} />
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
        </View>

        {/* Greeting */}
        <View style={styles.greetWrap}>
          <Text style={styles.greetSmall}>
            {auth.status === 'authenticated'
              ? `أهلاً، ${auth.user?.username}`
              : 'أهلاً بيك في بنهاوي'}
          </Text>
          <Text style={styles.greetBig}>اكتشف أحلى الأماكن في بنها</Text>
        </View>

        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
        >
          {(d) => (
            <View style={{ gap: spacing[6] }}>
              {/* Quick actions — 4 big tappable tiles */}
              <View style={styles.quickGrid}>
                {QUICK_ACTIONS.map((a) => (
                  <Pressable
                    key={a.key}
                    style={styles.quickTile}
                    onPress={() => router.push(a.href)}
                  >
                    <View style={[styles.quickIconBg, { backgroundColor: a.color + '18' }]}>
                      <Icon name={a.icon} size={22} color={a.color} />
                    </View>
                    <Text style={styles.quickLabel}>{a.label}</Text>
                  </Pressable>
                ))}
              </View>

              {/* Hero carousel — promoted businesses with big cover */}
              {d.promoted.length > 0 && (
                <View style={{ gap: spacing[3] }}>
                  <SectionHeader title="مميّزة الأسبوع" badge={`${d.promoted.length}`} />
                  <ScrollView
                    horizontal
                    showsHorizontalScrollIndicator={false}
                    contentContainerStyle={styles.heroRow}
                    decelerationRate="fast"
                    snapToInterval={HERO_W + spacing[3]}
                    snapToAlignment="start"
                  >
                    {d.promoted.map((b) => (
                      <HeroCard key={String(b.id)} business={b} />
                    ))}
                  </ScrollView>
                </View>
              )}

              {/* Categories grid */}
              {d.categories.length > 0 && (
                <View style={{ gap: spacing[3] }}>
                  <SectionHeader title="ابحث حسب الفئة" />
                  <View style={styles.catGrid}>
                    {d.categories.slice(0, 8).map((c) => {
                      const palette = CATEGORY_PALETTE[c.slug] ?? { bg: colors.cream[200], fg: colors.coral[600], icon: 'compass' as IconName };
                      return (
                        <Pressable
                          key={c.slug}
                          style={styles.catTile}
                          onPress={() => router.push({ pathname: '/(tabs)/search', params: { q: c.label } })}
                        >
                          <View style={[styles.catIconBg, { backgroundColor: palette.bg }]}>
                            <Icon name={palette.icon} size={22} color={palette.fg} />
                          </View>
                          <Text style={styles.catLabel} numberOfLines={1}>{c.label}</Text>
                          {c.count ? (
                            <Text style={styles.catCount}>{c.count} مكان</Text>
                          ) : null}
                        </Pressable>
                      );
                    })}
                  </View>
                </View>
              )}

              {/* Top rated — big cards with cover photos */}
              {d.top_rated.length > 0 && (
                <View style={{ gap: spacing[3] }}>
                  <SectionHeader title="الأكتر تقييم في بنها" onSeeAll={() => router.push('/(tabs)/search')} />
                  <View style={{ gap: spacing[3] }}>
                    {d.top_rated.slice(0, 6).map((b) => (
                      <BusinessCard key={String(b.id)} business={b} />
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

function SectionHeader({ title, badge, onSeeAll }: { title: string; badge?: string; onSeeAll?: () => void }) {
  return (
    <View style={styles.sectionHeader}>
      <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing[2] }}>
        <Text style={styles.sectionTitle}>{title}</Text>
        {badge ? (
          <View style={styles.sectionBadge}>
            <Text style={styles.sectionBadgeText}>{badge}</Text>
          </View>
        ) : null}
      </View>
      {onSeeAll && (
        <Pressable onPress={onSeeAll} style={styles.sectionLinkBtn}>
          <Text style={styles.sectionLink}>شوف الكل</Text>
          <Icon name="chevron-left" size={16} color={colors.coral[600]} />
        </Pressable>
      )}
    </View>
  );
}

function HeroCard({ business: b }: { business: Business }) {
  return (
    <Pressable
      style={styles.heroCard}
      onPress={() => router.push(`/business/${b.slug}`)}
    >
      <SmartImage
        uri={b.cover_url}
        fallbackText={b.name}
        radius={radius['3xl']}
        style={styles.heroImage}
      />
      <LinearGradient
        colors={['transparent', 'rgba(11,11,12,0.15)', 'rgba(11,11,12,0.85)']}
        locations={[0, 0.5, 1]}
        style={styles.heroOverlay}
      />
      <View style={styles.heroBadge}>
        <Icon name="bolt" size={11} color={colors.ink[950]} />
        <Text style={styles.heroBadgeText}>مميّز</Text>
      </View>
      <View style={styles.heroBody}>
        <Text style={styles.heroTitle} numberOfLines={1}>{b.name}</Text>
        {b.subtitle ? (
          <Text style={styles.heroSubtitle} numberOfLines={1}>{b.subtitle}</Text>
        ) : null}
        {typeof b.rating === 'number' && (
          <View style={styles.heroRating}>
            <Icon name="star" size={12} color={colors.honey[500]} />
            <Text style={styles.heroRatingText}>
              {b.rating.toFixed(1)}{b.reviews_count ? ` · ${b.reviews_count} تقييم` : ''}
            </Text>
          </View>
        )}
      </View>
    </Pressable>
  );
}

function BusinessCard({ business: b }: { business: Business }) {
  return (
    <Card padding="none" style={styles.bizCard} onPress={() => router.push(`/business/${b.slug}`)}>
      <SmartImage
        uri={b.cover_url}
        fallbackText={b.name}
        radius={0}
        style={styles.bizImage}
      />
      <View style={styles.bizBody}>
        <View style={{ flex: 1, gap: 4 }}>
          <Text style={styles.bizName} numberOfLines={1}>{b.name}</Text>
          {b.subtitle ? <Text style={styles.bizSub} numberOfLines={2}>{b.subtitle}</Text> : null}
          <View style={styles.bizMeta}>
            {typeof b.rating === 'number' && (
              <View style={styles.metaPill}>
                <Icon name="star" size={11} color={colors.honey[500]} />
                <Text style={styles.metaText}>
                  {b.rating.toFixed(1)} {b.reviews_count ? `(${b.reviews_count})` : ''}
                </Text>
              </View>
            )}
            {b.is_verified && (
              <View style={[styles.metaPill, { backgroundColor: colors.mint[100] }]}>
                <Icon name="check" size={11} color={colors.mint[700]} />
                <Text style={[styles.metaText, { color: colors.mint[700] }]}>موثّق</Text>
              </View>
            )}
          </View>
        </View>
        <View style={styles.bizArrow}>
          <Icon name="chevron-left" size={20} color={colors.ink[400]} />
        </View>
      </View>
    </Card>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { paddingBottom: 120 },

  // ─── Sticky header ───
  stickyHeader: {
    backgroundColor: colors.cream[100],
    paddingHorizontal: spacing[4],
    paddingTop: spacing[2],
    paddingBottom: spacing[3],
  },
  headerRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[2] },
  searchBar: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing[2],
    height: 48,
    paddingHorizontal: spacing[4],
    borderRadius: radius.full,
    backgroundColor: colors.white,
    borderWidth: 1,
    borderColor: 'rgba(11,11,12,0.06)',
    ...shadows.card,
  },
  searchPlaceholder: { ...typography.body, color: colors.ink[400], flex: 1 },
  bellBtn: {
    width: 48,
    height: 48,
    borderRadius: radius.full,
    backgroundColor: colors.white,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: 'rgba(11,11,12,0.06)',
    ...shadows.card,
  },
  bellBadge: {
    position: 'absolute',
    top: 6,
    insetInlineEnd: 6,
    minWidth: 18,
    height: 18,
    paddingHorizontal: 4,
    borderRadius: 999,
    backgroundColor: colors.coral[500],
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: colors.white,
  },
  bellBadgeText: { color: colors.white, fontSize: 9, fontWeight: '900' },

  // ─── Greeting ───
  greetWrap: { paddingHorizontal: spacing[4], paddingBottom: spacing[4], gap: 2 },
  greetSmall: { ...typography.meta, color: colors.ink[500], fontSize: 13 },
  greetBig: { ...typography.h1, color: colors.ink[950], fontSize: 26 },

  // ─── Quick actions ───
  quickGrid: {
    flexDirection: 'row',
    paddingHorizontal: spacing[4],
    gap: spacing[3],
  },
  quickTile: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: spacing[3],
    paddingHorizontal: spacing[2],
    borderRadius: radius['2xl'],
    backgroundColor: colors.white,
    gap: spacing[2],
    ...shadows.card,
  },
  quickIconBg: {
    width: 44,
    height: 44,
    borderRadius: radius.xl,
    alignItems: 'center',
    justifyContent: 'center',
  },
  quickLabel: {
    fontSize: 11,
    fontWeight: '800',
    color: colors.ink[950],
    textAlign: 'center',
  },

  // ─── Section header ───
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing[4],
  },
  sectionTitle: { ...typography.h2, fontSize: 19, color: colors.ink[950] },
  sectionBadge: {
    paddingHorizontal: spacing[2],
    paddingVertical: 2,
    borderRadius: radius.full,
    backgroundColor: colors.coral[100],
  },
  sectionBadgeText: { fontSize: 11, fontWeight: '900', color: colors.coral[700] },
  sectionLinkBtn: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  sectionLink: { fontSize: 13, fontWeight: '800', color: colors.coral[600] },

  // ─── Hero carousel ───
  heroRow: { gap: spacing[3], paddingHorizontal: spacing[4] },
  heroCard: {
    width: HERO_W,
    height: 200,
    borderRadius: radius['3xl'],
    overflow: 'hidden',
    backgroundColor: colors.cream[200],
    ...shadows.soft,
  },
  heroImage: { ...StyleSheet.absoluteFillObject },
  heroOverlay: { ...StyleSheet.absoluteFillObject },
  heroBadge: {
    position: 'absolute',
    top: spacing[3],
    insetInlineEnd: spacing[3],
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: spacing[2],
    paddingVertical: 4,
    borderRadius: radius.full,
    backgroundColor: colors.honey[400],
  },
  heroBadgeText: { fontSize: 11, fontWeight: '900', color: colors.ink[950] },
  heroBody: {
    position: 'absolute',
    insetInlineStart: spacing[4],
    insetInlineEnd: spacing[4],
    bottom: spacing[4],
    gap: 4,
  },
  heroTitle: { fontSize: 22, fontWeight: '900', color: colors.white },
  heroSubtitle: { fontSize: 13, fontWeight: '600', color: 'rgba(255,255,255,0.9)' },
  heroRating: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: spacing[1],
    alignSelf: 'flex-start',
    paddingHorizontal: spacing[2],
    paddingVertical: 4,
    borderRadius: radius.full,
    backgroundColor: 'rgba(255,255,255,0.95)',
  },
  heroRatingText: { fontSize: 11, fontWeight: '900', color: colors.ink[950] },

  // ─── Category grid ───
  catGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: spacing[3],
    paddingHorizontal: spacing[4],
  },
  catTile: {
    width: (SCREEN_W - spacing[4] * 2 - spacing[3] * 3) / 4,
    alignItems: 'center',
    paddingVertical: spacing[3],
    paddingHorizontal: spacing[1],
    borderRadius: radius['2xl'],
    backgroundColor: colors.white,
    gap: 6,
    ...shadows.card,
  },
  catIconBg: {
    width: 48,
    height: 48,
    borderRadius: radius.xl,
    alignItems: 'center',
    justifyContent: 'center',
  },
  catLabel: { fontSize: 11, fontWeight: '800', color: colors.ink[950], textAlign: 'center' },
  catCount: { fontSize: 9, fontWeight: '700', color: colors.ink[400] },

  // ─── Business card ───
  bizCard: {
    marginHorizontal: spacing[4],
    overflow: 'hidden',
  },
  bizImage: {
    width: '100%',
    height: 150,
    borderTopLeftRadius: radius.card,
    borderTopRightRadius: radius.card,
  },
  bizBody: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: spacing[4],
    gap: spacing[3],
  },
  bizName: { ...typography.h3, color: colors.ink[950], fontSize: 16 },
  bizSub: { ...typography.body, color: colors.ink[500], fontSize: 13 },
  bizMeta: { flexDirection: 'row', gap: spacing[2], marginTop: spacing[1] },
  metaPill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: spacing[2],
    paddingVertical: 4,
    borderRadius: radius.full,
    backgroundColor: '#FFF6D6',
  },
  metaText: { fontSize: 11, fontWeight: '800', color: colors.honey[500] },
  bizArrow: { width: 32, alignItems: 'center', justifyContent: 'center' },
});
