import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useLocalSearchParams } from 'expo-router';
import { Card, QueryState, ScreenHeader, SmartImage } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useMenu } from '@/api/hooks';

export default function BusinessMenu() {
  const { slug } = useLocalSearchParams<{ slug: string }>();
  const query = useMenu(slug ?? '');

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader
        title="قائمة الطعام"
        subtitle={query.data?.business.name}
      />
      <ScrollView contentContainerStyle={styles.scroll}>
        <QueryState
          status={query.status}
          data={query.data}
          error={query.error}
          refetch={query.refetch}
          emptyTitle="لسه مفيش قائمة"
          emptyHint="المالك لسه ما ضافش الأصناف"
          isEmpty={(d) => !d?.menu?.length}
        >
          {(d) => (
            <View style={{ gap: spacing[4] }}>
              {d.menu.map((cat) => (
                <View key={String(cat.id)} style={{ gap: spacing[2] }}>
                  <Text style={styles.categoryLabel}>{cat.name}</Text>
                  <Card padding="none">
                    {cat.items.map((item, i) => (
                      <View
                        key={String(item.id)}
                        style={[styles.itemRow, i < cat.items.length - 1 && styles.divider]}
                      >
                        <SmartImage uri={item.photo_url} fallbackText={item.name} style={styles.photo} radius={8} />
                        <View style={{ flex: 1, gap: 2 }}>
                          <Text style={styles.itemName}>{item.name}</Text>
                          {item.description ? (
                            <Text style={styles.itemDesc} numberOfLines={2}>{item.description}</Text>
                          ) : null}
                          <Text style={styles.itemPrice}>
                            {item.price.toFixed(0)} {d.business.currency}
                          </Text>
                        </View>
                      </View>
                    ))}
                  </Card>
                </View>
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
  categoryLabel: { ...typography.nano, color: colors.coral[600] },
  itemRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[3], padding: spacing[3] },
  divider: { borderBottomWidth: 1, borderBottomColor: 'rgba(11,11,12,0.04)' },
  photo: { width: 64, height: 64 },
  itemName: { ...typography.bodyStrong, color: colors.ink[950] },
  itemDesc: { ...typography.body, color: colors.ink[500] },
  itemPrice: { ...typography.meta, color: colors.coral[600], marginTop: 2 },
});
