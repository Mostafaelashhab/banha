import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router } from 'expo-router';
import { Button } from './Button';
import { IconTile } from './IconTile';
import { ScreenHeader } from './ScreenHeader';
import { useAuth } from '@/auth/AuthContext';
import { colors, spacing, typography } from '../theme';

type Props = {
  title?: string;
  children: React.ReactNode;
};

export function RequireAuth({ title, children }: Props) {
  const auth = useAuth();

  if (auth.status === 'loading') {
    return <SafeAreaView style={styles.safe} edges={['top']} />;
  }

  if (auth.status === 'guest') {
    return (
      <SafeAreaView style={styles.safe} edges={['top']}>
        {title ? <ScreenHeader title={title} /> : null}
        <View style={styles.body}>
          <IconTile icon="lock" tone="coral" intensity="strong" size="xl" shape="circle" />
          <Text style={styles.title}>محتاج تسجّل دخول</Text>
          <Text style={styles.hint}>افتح حساب أو سجّل دخول علشان توصل للحاجة دي</Text>
          <View style={styles.ctas}>
            <Button size="lg" block onPress={() => router.replace('/login')}>تسجيل الدخول</Button>
            <Button variant="outline" size="lg" block onPress={() => router.replace('/signup')}>
              عمل حساب جديد
            </Button>
          </View>
        </View>
      </SafeAreaView>
    );
  }

  return <>{children}</>;
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  body: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing[6],
    gap: spacing[3],
  },
  title: { ...typography.h2, color: colors.ink[950], textAlign: 'center' },
  hint: { ...typography.body, color: colors.ink[500], textAlign: 'center', marginBottom: spacing[3] },
  ctas: { gap: spacing[2], width: '100%' },
});
