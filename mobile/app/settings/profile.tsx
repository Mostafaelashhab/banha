import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button, Card, Input, ScreenHeader } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useAuth } from '@/auth/AuthContext';
import { useState } from 'react';

export default function ProfileSettings() {
  const auth = useAuth();
  const [username, setUsername] = useState(auth.user?.username ?? '');
  const [city, setCity] = useState(auth.user?.city ?? '');

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScreenHeader title="تعديل البروفايل" />
      <ScrollView contentContainerStyle={styles.scroll}>
        <Card padding="lg" style={{ gap: spacing[3] }}>
          <Input label="اسم اليوزر" value={username} onChangeText={setUsername} />
          <Input label="المنطقة" value={city} onChangeText={setCity} editable={false} />
          <Text style={styles.hint}>تعديل البيانات هيتفعل قريب.</Text>
        </Card>

        <View style={{ height: spacing[3] }} />

        <Button block size="lg" disabled>
          حفظ التعديلات
        </Button>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[4], paddingBottom: spacing[10] },
  hint: { ...typography.meta, color: colors.ink[500] },
});
