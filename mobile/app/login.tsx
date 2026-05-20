import { useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Link, router } from 'expo-router';
import { Button, IconTile, Input } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useAuth } from '@/auth/AuthContext';
import { ApiError } from '@/api/client';

export default function Login() {
  const auth = useAuth();
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const submit = async () => {
    if (!phone || !password) {
      setError('اكتب رقم تليفونك وكلمة السر');
      return;
    }
    setError(null);
    setSubmitting(true);
    try {
      const res = await auth.login({ phone, password });
      if (res.needs_verification) {
        router.replace('/verify');
      } else {
        router.replace('/(tabs)/feed');
      }
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'حصل خطأ، حاول تاني');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={{ flex: 1 }}
      >
        <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
          <View style={styles.hero}>
            <IconTile icon="lock" tone="coral" intensity="strong" size="xl" shape="circle" />
            <Text style={styles.title}>أهلاً تاني</Text>
            <Text style={styles.hint}>سجّل دخولك علشان تكمّل من حيث وقفت</Text>
          </View>

          <View style={{ gap: spacing[3] }}>
            <Input
              label="رقم التليفون"
              icon="phone"
              keyboardType="phone-pad"
              autoCapitalize="none"
              placeholder="01XXXXXXXXX"
              value={phone}
              onChangeText={setPhone}
            />
            <Input
              label="كلمة السر"
              icon="lock"
              secureTextEntry
              placeholder="••••••••"
              value={password}
              onChangeText={setPassword}
              error={error ?? undefined}
            />
          </View>

          <Button size="lg" block loading={submitting} onPress={submit}>
            تسجيل الدخول
          </Button>

          <View style={styles.bottom}>
            <Text style={styles.bottomText}>ملكش حساب؟</Text>
            <Link href="/signup">
              <Text style={styles.link}>عمل حساب جديد</Text>
            </Link>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[5], gap: spacing[4] },
  hero: { alignItems: 'center', gap: spacing[2], paddingVertical: spacing[6] },
  title: { ...typography.h1, color: colors.ink[950] },
  hint: { ...typography.body, color: colors.ink[500], textAlign: 'center' },
  bottom: { flexDirection: 'row', justifyContent: 'center', gap: spacing[2], marginTop: spacing[4] },
  bottomText: { ...typography.body, color: colors.ink[500] },
  link: { ...typography.bodyStrong, color: colors.coral[600] },
});
