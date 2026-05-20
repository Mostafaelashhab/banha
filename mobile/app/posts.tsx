import { Image, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Card, QueryState, ScreenHeader } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { usePosts } from '@/api/hooks';

export default function Posts() {
  const query = usePosts();
  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="بوستات المجتمع" subtitle="نقاشات أهل بنها" />
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
                <Card key={String(p.id)} padding="md" style={{ gap: spacing[2] }}>
                  {p.title ? <Text style={styles.title}>{p.title}</Text> : null}
                  <Text style={styles.body}>{p.body}</Text>
                  {p.image_url ? <Image source={{ uri: p.image_url }} style={styles.image} /> : null}
                  <Text style={styles.meta}>
                    {p.author?.username ?? 'مجهول'} · {p.upvotes - p.downvotes >= 0 ? '+' : ''}{p.upvotes - p.downvotes} · {p.comments_count} تعليق
                  </Text>
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
  title: { ...typography.h3, color: colors.ink[950] },
  body: { ...typography.body, color: colors.ink[700], lineHeight: 22 },
  image: { width: '100%', aspectRatio: 16 / 10, borderRadius: 12, backgroundColor: colors.cream[200] },
  meta: { ...typography.meta, color: colors.ink[500] },
});
