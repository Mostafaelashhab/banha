import { Linking, Platform, Pressable, ScrollView, Share, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { router, useLocalSearchParams } from 'expo-router';
import MapView, { Marker, PROVIDER_GOOGLE } from 'react-native-maps';
import { Button, Card, Icon, QueryState, SmartImage } from '@/components';
import { IconName } from '@/components';
import { colors, radius, shadows, spacing, typography } from '@/theme';
import { useBusiness, useToggleBookmark, useTrackBusinessClick } from '@/api/hooks';
import { Business } from '@/api/types';

export default function BusinessDetail() {
  const { slug } = useLocalSearchParams<{ slug: string }>();
  const query = useBusiness(slug ?? '');
  const track = useTrackBusinessClick();
  const bookmark = useToggleBookmark();

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <QueryState
        status={query.status}
        data={query.data}
        error={query.error}
        refetch={query.refetch}
      >
        {(b) => (
          <>
            <ScrollView
              showsVerticalScrollIndicator={false}
              contentContainerStyle={styles.scroll}
              stickyHeaderIndices={[]}
            >
              <Hero business={b} onBack={() => router.back()} />

              <View style={styles.body}>
                <Identity business={b} />

                {/* Status pills row */}
                <StatusRow business={b} />

                {/* Primary action bar */}
                <ActionBar
                  business={b}
                  onCall={() => {
                    if (!b.phone) return;
                    track.mutate(b.id);
                    Linking.openURL(`tel:${b.phone}`).catch(() => {});
                  }}
                  onWhatsApp={() => {
                    if (!b.whatsapp) return;
                    track.mutate(b.id);
                    Linking.openURL(`https://wa.me/${b.whatsapp.replace(/[^0-9]/g, '')}`).catch(() => {});
                  }}
                  onDirections={() => {
                    if (typeof b.lat !== 'number' || typeof b.lng !== 'number') return;
                    const url = Platform.select({
                      ios: `maps://?q=${b.lat},${b.lng}`,
                      android: `geo:${b.lat},${b.lng}?q=${b.lat},${b.lng}(${encodeURIComponent(b.name)})`,
                      default: `https://maps.google.com/?q=${b.lat},${b.lng}`,
                    })!;
                    Linking.openURL(url).catch(() => {});
                  }}
                  onShare={() => {
                    Share.share({
                      message: `${b.name}\nhttps://banhawy.app/biz/${b.slug}`,
                    }).catch(() => {});
                  }}
                  onBookmark={() => bookmark.mutate(b.id)}
                  bookmarkLoading={bookmark.isPending}
                />

                {/* Big primary CTAs (menu / book) */}
                <PrimaryCtaRow business={b} slug={slug ?? ''} />

                {/* About */}
                {b.description ? (
                  <Section title="نبذة">
                    <Text style={styles.aboutText}>{b.description}</Text>
                  </Section>
                ) : null}

                {/* Info rows: address, phone, hours */}
                <Section title="بيانات النشاط">
                  <Card padding="none">
                    {b.address ? (
                      <InfoRow icon="map-pin" label="العنوان" value={b.address} />
                    ) : null}
                    {b.phone ? (
                      <InfoRow icon="phone" label="التليفون" value={b.phone} isLast={!b.hours_text} />
                    ) : null}
                    {b.hours_text ? (
                      <InfoRow
                        icon="clock"
                        label="المواعيد"
                        value={b.hours_text}
                        isLast
                      />
                    ) : null}
                  </Card>
                </Section>

                {/* Features */}
                {b.features && b.features.length > 0 ? (
                  <Section title="مميزات">
                    <View style={styles.chipsWrap}>
                      {b.features.map((f) => (
                        <View key={f} style={styles.featChip}>
                          <Icon name="check" size={12} color={colors.mint[700]} />
                          <Text style={styles.featChipText}>{f}</Text>
                        </View>
                      ))}
                    </View>
                  </Section>
                ) : null}

                {/* Photos */}
                {b.photos && b.photos.length > 0 ? (
                  <Section
                    title={`الصور${b.photos_count ? ` (${b.photos_count})` : ''}`}
                    onSeeAll={() => router.push(`/business/${slug}/photos`)}
                  >
                    <ScrollView
                      horizontal
                      showsHorizontalScrollIndicator={false}
                      contentContainerStyle={styles.photosRow}
                    >
                      {b.photos.map((p, i) => (
                        <SmartImage
                          key={String(p.id)}
                          uri={p.url}
                          fallbackText={b.name}
                          style={styles.photoTile}
                          radius={radius.xl}
                          previewUris={b.photos!.map((x) => x.url)}
                          previewIndex={i}
                        />
                      ))}
                    </ScrollView>
                  </Section>
                ) : null}

                {/* Reviews preview */}
                {b.reviews && b.reviews.length > 0 ? (
                  <Section
                    title={`التقييمات (${b.reviews_count ?? 0})`}
                    onSeeAll={() => router.push(`/business/${slug}/reviews`)}
                  >
                    <View style={{ gap: spacing[2] }}>
                      {b.reviews.map((r) => (
                        <Card key={String(r.id)} padding="md" style={{ gap: spacing[2] }}>
                          <View style={styles.reviewHead}>
                            <View style={styles.starsRow}>
                              {[1, 2, 3, 4, 5].map((n) => (
                                <Icon
                                  key={n}
                                  name="star"
                                  size={12}
                                  color={n <= r.rating ? colors.honey[500] : colors.cream[200]}
                                />
                              ))}
                            </View>
                            <Text style={styles.reviewAuthor}>{r.author_name ?? 'مجهول'}</Text>
                          </View>
                          {r.body ? <Text style={styles.reviewBody}>{r.body}</Text> : null}
                        </Card>
                      ))}
                    </View>
                  </Section>
                ) : null}

                {/* Map */}
                {typeof b.lat === 'number' && typeof b.lng === 'number' ? (
                  <Section title="الموقع على الخريطة">
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
                        <Marker
                          coordinate={{ latitude: b.lat, longitude: b.lng }}
                          pinColor={colors.coral[500]}
                        />
                      </MapView>
                      {b.address ? (
                        <View style={{ padding: spacing[3] }}>
                          <Text style={styles.addressLine}>{b.address}</Text>
                        </View>
                      ) : null}
                    </Card>
                  </Section>
                ) : null}

                {/* Spacer so sticky bar doesn't cover last content */}
                <View style={{ height: 80 }} />
              </View>
            </ScrollView>

            <StickyBar
              business={b}
              onCall={() => {
                if (!b.phone) return;
                track.mutate(b.id);
                Linking.openURL(`tel:${b.phone}`).catch(() => {});
              }}
              onWhatsApp={() => {
                if (!b.whatsapp) return;
                track.mutate(b.id);
                Linking.openURL(`https://wa.me/${b.whatsapp.replace(/[^0-9]/g, '')}`).catch(() => {});
              }}
              onDirections={() => {
                if (typeof b.lat !== 'number' || typeof b.lng !== 'number') return;
                const url = Platform.select({
                  ios: `maps://?q=${b.lat},${b.lng}`,
                  android: `geo:${b.lat},${b.lng}?q=${b.lat},${b.lng}(${encodeURIComponent(b.name)})`,
                  default: `https://maps.google.com/?q=${b.lat},${b.lng}`,
                })!;
                Linking.openURL(url).catch(() => {});
              }}
            />
          </>
        )}
      </QueryState>
    </SafeAreaView>
  );
}

// ─── Hero ────────────────────────────────────────────────────────────
function Hero({ business: b, onBack }: { business: Business; onBack: () => void }) {
  const previewUris = [b.cover_url, ...(b.photos?.map((p) => p.url) ?? [])].filter(Boolean) as string[];
  return (
    <View style={styles.hero}>
      <SmartImage
        uri={b.cover_url}
        fallbackText={b.name}
        radius={0}
        style={styles.heroImage}
        previewUris={previewUris.length > 0 ? previewUris : undefined}
      />
      <LinearGradient
        colors={['rgba(11,11,12,0.55)', 'transparent', 'rgba(11,11,12,0.55)']}
        locations={[0, 0.35, 1]}
        style={StyleSheet.absoluteFillObject}
      />
      <Pressable
        onPress={onBack}
        style={styles.backBtn}
        accessibilityRole="button"
        accessibilityLabel="رجوع"
      >
        <Icon name="arrow-right" size={20} color={colors.ink[950]} />
      </Pressable>
      {b.is_sponsored ? (
        <View style={styles.sponsorBadge}>
          <Icon name="bolt" size={11} color={colors.ink[950]} />
          <Text style={styles.sponsorText}>مميّز</Text>
        </View>
      ) : null}
    </View>
  );
}

// ─── Identity row (name + verified + category) ───────────────────────
function Identity({ business: b }: { business: Business }) {
  return (
    <View style={{ gap: spacing[1.5] }}>
      <View style={styles.nameRow}>
        <Text style={styles.name}>{b.name}</Text>
        {b.is_verified ? (
          <View style={[
            styles.verifiedDot,
            b.tier === 'gold' && { backgroundColor: colors.honey[400] },
          ]}>
            <Icon name="check" size={12} color={colors.white} />
          </View>
        ) : null}
      </View>
      <View style={styles.metaRow}>
        {b.category_label ? (
          <Text style={styles.categoryText}>{b.category_label}</Text>
        ) : null}
        {typeof b.rating === 'number' ? (
          <>
            <Text style={styles.metaDot}>·</Text>
            <View style={styles.ratingPill}>
              <Icon name="star" size={12} color={colors.honey[500]} />
              <Text style={styles.ratingText}>
                {b.rating.toFixed(1)}{b.reviews_count ? ` (${b.reviews_count})` : ''}
              </Text>
            </View>
          </>
        ) : null}
      </View>
    </View>
  );
}

// ─── Status pills (open/closed, 24h, tier) ───────────────────────────
function StatusRow({ business: b }: { business: Business }) {
  const pills: { text: string; bg: string; fg: string; icon?: IconName }[] = [];

  if (b.is_24h) {
    pills.push({ text: 'مفتوح ٢٤ ساعة', bg: colors.mint[100], fg: colors.mint[700], icon: 'clock' });
  } else if (b.is_open === true) {
    pills.push({ text: 'مفتوح دلوقتي', bg: colors.mint[100], fg: colors.mint[700], icon: 'clock' });
  } else if (b.is_open === false) {
    pills.push({ text: 'مقفول دلوقتي', bg: colors.blush[100], fg: colors.blush[500], icon: 'clock' });
  }

  if (b.tier === 'gold') {
    pills.push({ text: 'موثّق ذهبي', bg: '#FFF6D6', fg: colors.honey[500], icon: 'star' });
  } else if (b.tier === 'silver') {
    pills.push({ text: 'موثّق', bg: colors.cream[200], fg: colors.ink[700], icon: 'check' });
  }

  if (b.has_menu) {
    pills.push({ text: 'منيو متاح', bg: colors.coral[50], fg: colors.coral[700], icon: 'cart' });
  }
  if (b.booking_enabled) {
    pills.push({ text: 'حجز موعد', bg: colors.coral[50], fg: colors.coral[700], icon: 'clock' });
  }

  if (pills.length === 0) return null;

  return (
    <View style={styles.statusRow}>
      {pills.map((p) => (
        <View key={p.text} style={[styles.statusPill, { backgroundColor: p.bg }]}>
          {p.icon ? <Icon name={p.icon} size={12} color={p.fg} /> : null}
          <Text style={[styles.statusText, { color: p.fg }]}>{p.text}</Text>
        </View>
      ))}
    </View>
  );
}

// ─── Action bar (4 round buttons) ────────────────────────────────────
type ActionBarProps = {
  business: Business;
  onCall: () => void;
  onWhatsApp: () => void;
  onDirections: () => void;
  onShare: () => void;
  onBookmark: () => void;
  bookmarkLoading: boolean;
};

function ActionBar({ business: b, onCall, onWhatsApp, onDirections, onShare, onBookmark, bookmarkLoading }: ActionBarProps) {
  type Action = { icon: IconName; label: string; onPress: () => void; bg: string; fg: string; show: boolean };
  const actions: Action[] = [
    { icon: 'phone',    label: 'اتصال',    onPress: onCall,       bg: colors.coral[500], fg: colors.white, show: !!b.phone },
    { icon: 'whatsapp', label: 'واتساب',   onPress: onWhatsApp,   bg: '#25D366',         fg: colors.white, show: !!b.whatsapp },
    { icon: 'compass',  label: 'الاتجاهات', onPress: onDirections, bg: colors.cream[200], fg: colors.ink[950], show: typeof b.lat === 'number' && typeof b.lng === 'number' },
    { icon: 'bookmark', label: 'حفظ',      onPress: onBookmark,   bg: colors.cream[200], fg: colors.ink[950], show: true },
    { icon: 'message',  label: 'مشاركة',   onPress: onShare,      bg: colors.cream[200], fg: colors.ink[950], show: true },
  ];
  const visible = actions.filter((a) => a.show);

  return (
    <View style={styles.actionBar}>
      {visible.map((a) => (
        <Pressable key={a.label} style={styles.actionBtn} onPress={a.onPress} disabled={bookmarkLoading && a.icon === 'bookmark'}>
          <View style={[styles.actionDisc, { backgroundColor: a.bg }]}>
            <Icon name={a.icon} size={18} color={a.fg} />
          </View>
          <Text style={styles.actionLabel}>{a.label}</Text>
        </Pressable>
      ))}
    </View>
  );
}

// ─── Primary CTA row (menu + book) ───────────────────────────────────
function PrimaryCtaRow({ business: b, slug }: { business: Business; slug: string }) {
  if (!b.has_menu && !b.booking_enabled) return null;
  return (
    <View style={styles.ctaRow}>
      {b.has_menu ? (
        <View style={styles.ctaSlot}>
          <Button block icon="menu" onPress={() => router.push(`/business/${slug}/menu`)} size="lg">
            افتح المنيو
          </Button>
        </View>
      ) : null}
      {b.booking_enabled ? (
        <View style={styles.ctaSlot}>
          <Button
            block
            variant={b.has_menu ? 'outline' : 'primary'}
            icon="clock"
            size="lg"
            onPress={() => {/* booking sheet coming next */}}
          >
            احجز موعد
          </Button>
        </View>
      ) : null}
    </View>
  );
}

// ─── Section helper ──────────────────────────────────────────────────
function Section({ title, onSeeAll, children }: { title: string; onSeeAll?: () => void; children: React.ReactNode }) {
  return (
    <View style={{ gap: spacing[2] }}>
      <View style={styles.sectionHead}>
        <Text style={styles.sectionTitle}>{title}</Text>
        {onSeeAll ? (
          <Pressable onPress={onSeeAll} style={styles.sectionLinkBtn}>
            <Text style={styles.sectionLink}>شوف الكل</Text>
            <Icon name="chevron-left" size={16} color={colors.coral[600]} />
          </Pressable>
        ) : null}
      </View>
      {children}
    </View>
  );
}

// ─── Info row ────────────────────────────────────────────────────────
function InfoRow({ icon, label, value, isLast }: { icon: IconName; label: string; value: string; isLast?: boolean }) {
  return (
    <View style={[styles.infoRow, !isLast && styles.divider]}>
      <View style={styles.infoIcon}>
        <Icon name={icon} size={18} color={colors.coral[600]} />
      </View>
      <View style={{ flex: 1 }}>
        <Text style={styles.infoLabel}>{label}</Text>
        <Text style={styles.infoValue}>{value}</Text>
      </View>
    </View>
  );
}

// ─── Sticky bottom CTA ───────────────────────────────────────────────
type StickyAction = { key: string; label: string; icon: IconName; bg: string; fg: string; onPress: () => void };

function StickyBar({ business: b, onCall, onWhatsApp, onDirections }: {
  business: Business;
  onCall: () => void;
  onWhatsApp: () => void;
  onDirections: () => void;
}) {
  const actions: StickyAction[] = [];
  if (b.phone) {
    actions.push({ key: 'call', label: 'اتصال', icon: 'phone', bg: colors.coral[500], fg: colors.white, onPress: onCall });
  }
  if (b.whatsapp) {
    actions.push({ key: 'wa', label: 'واتساب', icon: 'whatsapp', bg: '#25D366', fg: colors.white, onPress: onWhatsApp });
  }
  if (typeof b.lat === 'number' && typeof b.lng === 'number') {
    actions.push({ key: 'dir', label: 'الاتجاهات', icon: 'compass', bg: colors.cream[200], fg: colors.ink[950], onPress: onDirections });
  }
  if (actions.length === 0) return null;

  return (
    <SafeAreaView edges={['bottom']} style={styles.stickyWrap}>
      <View style={styles.sticky}>
        {actions.map((a) => (
          <Pressable
            key={a.key}
            onPress={a.onPress}
            android_ripple={{ color: 'rgba(0,0,0,0.12)' }}
            style={({ pressed }) => [
              styles.stickyBtn,
              { backgroundColor: a.bg },
              pressed && { opacity: 0.85 },
            ]}
          >
            <Icon name={a.icon} size={18} color={a.fg} />
            <Text style={[styles.stickyLabel, { color: a.fg }]} numberOfLines={1}>{a.label}</Text>
          </Pressable>
        ))}
      </View>
    </SafeAreaView>
  );
}

// ─── Styles ──────────────────────────────────────────────────────────
const HERO_HEIGHT = 240;

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { paddingBottom: spacing[10] },

  // Hero
  hero: { width: '100%', height: HERO_HEIGHT, backgroundColor: colors.cream[200] },
  heroImage: { ...StyleSheet.absoluteFillObject },
  backBtn: {
    position: 'absolute',
    top: spacing[3],
    insetInlineStart: spacing[4],
    width: 40,
    height: 40,
    borderRadius: 999,
    backgroundColor: 'rgba(255,255,255,0.92)',
    alignItems: 'center',
    justifyContent: 'center',
    ...shadows.card,
  },
  sponsorBadge: {
    position: 'absolute',
    top: spacing[3],
    insetInlineEnd: spacing[4],
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: spacing[2],
    paddingVertical: 6,
    borderRadius: radius.full,
    backgroundColor: colors.honey[400],
  },
  sponsorText: { fontSize: 11, fontWeight: '900', color: colors.ink[950] },

  // Body wrapper
  body: { padding: spacing[4], gap: spacing[4] },
  aboutText: { ...typography.body, color: colors.ink[700], lineHeight: 24 },

  // Identity
  nameRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[2] },
  name: { ...typography.h1, color: colors.ink[950], fontSize: 24, flex: 1 },
  verifiedDot: {
    width: 22,
    height: 22,
    borderRadius: 999,
    backgroundColor: colors.coral[500],
    alignItems: 'center',
    justifyContent: 'center',
  },
  metaRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[2] },
  categoryText: { ...typography.meta, color: colors.ink[500] },
  metaDot: { color: colors.ink[400], fontWeight: '900' },
  ratingPill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: radius.full,
    backgroundColor: '#FFF6D6',
  },
  ratingText: { fontSize: 12, fontWeight: '900', color: colors.ink[950] },

  // Status pills
  statusRow: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing[2] },
  statusPill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: spacing[2],
    paddingVertical: 6,
    borderRadius: radius.full,
  },
  statusText: { fontSize: 11, fontWeight: '800' },

  // Action bar
  actionBar: { flexDirection: 'row', justifyContent: 'space-between', paddingHorizontal: spacing[2] },
  actionBtn: { alignItems: 'center', gap: 6, flex: 1 },
  actionDisc: {
    width: 50,
    height: 50,
    borderRadius: 999,
    alignItems: 'center',
    justifyContent: 'center',
    ...shadows.card,
  },
  actionLabel: { fontSize: 11, fontWeight: '800', color: colors.ink[700] },

  // CTA row
  ctaRow: { flexDirection: 'row', gap: spacing[2] },
  ctaSlot: { flex: 1 },

  // Sections
  sectionHead: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  sectionTitle: { ...typography.h3, color: colors.ink[950], fontSize: 16 },
  sectionLinkBtn: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  sectionLink: { fontSize: 13, fontWeight: '800', color: colors.coral[600] },

  // Info rows
  infoRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[3], padding: spacing[4] },
  divider: { borderBottomWidth: 1, borderBottomColor: 'rgba(11,11,12,0.04)' },
  infoIcon: {
    width: 36,
    height: 36,
    borderRadius: 999,
    backgroundColor: colors.coral[50],
    alignItems: 'center',
    justifyContent: 'center',
  },
  infoLabel: { fontSize: 11, fontWeight: '700', color: colors.ink[500] },
  infoValue: { ...typography.bodyStrong, color: colors.ink[950], marginTop: 2 },

  // Features chips
  chipsWrap: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing[2] },
  featChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: spacing[2],
    paddingVertical: 6,
    borderRadius: radius.full,
    backgroundColor: colors.mint[100],
  },
  featChipText: { fontSize: 12, fontWeight: '800', color: colors.mint[700] },

  // Photos
  photosRow: { gap: spacing[2] },
  photoTile: { width: 110, height: 110 },

  // Reviews
  reviewHead: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  starsRow: { flexDirection: 'row', gap: 2 },
  reviewAuthor: { ...typography.meta, color: colors.ink[500] },
  reviewBody: { ...typography.body, color: colors.ink[700], lineHeight: 22 },

  // Map
  mapCard: { overflow: 'hidden' },
  miniMap: { width: '100%', height: 200 },
  addressLine: { ...typography.body, color: colors.ink[700] },

  // Sticky bar
  stickyWrap: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: colors.white,
    borderTopWidth: 1,
    borderTopColor: 'rgba(11,11,12,0.06)',
    ...shadows.soft,
  },
  sticky: {
    flexDirection: 'row',
    gap: spacing[2],
    paddingHorizontal: spacing[3],
    paddingVertical: spacing[3],
  },
  stickyBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing[1.5],
    height: 48,
    borderRadius: radius.xl,
  },
  stickyLabel: { fontSize: 14, fontWeight: '900' },
});

