import { useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Link, router } from 'expo-router';
import { Button, IconTile, Input } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useAuth } from '@/auth/AuthContext';
import { ApiError } from '@/api/client';

export default function Signup() {
  const auth = useAuth();
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const submit = async () => {
    if (password !== confirm) {
      setError('كلمتين السر مش زي بعض');
      return;
    }
    if (!name || !phone || !password) {
      setError('املأ كل البيانات');
      return;
    }
    setError(null);
    setSubmitting(true);
    try {
      await auth.signup({ name, phone, password, password_confirmation: confirm });
      router.replace('/(tabs)/feed');
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
            <IconTile icon="user" tone="coral" intensity="strong" size="xl" shape="circle" />
            <Text style={styles.title}>أهلاً بيك في بنهاوي</Text>
            <Text style={styles.hint}>ثواني وتبقى عندك حساب</Text>
          </View>

          <View style={{ gap: spacing[3] }}>
            <Input
              label="اسمك"
              icon="user"
              placeholder="مثلاً: محمد علي"
              value={name}
              onChangeText={setName}
            />
            <Input
              label="رقم التليفون"
              icon="phone"
              keyboardType="phone-pad"
              autoCapitalize="none"
              placeholder="01XXXXXXXXX"
              value={phone}
              onChangeText={setPhone}
              helper="هنبعت كود تأكيد على واتساب"
            />
            <Input
              label="كلمة السر"
              icon="lock"
              secureTextEntry
              placeholder="٨ حروف على الأقل"
              value={password}
              onChangeText={setPassword}
            />
            <Input
              label="تأكيد كلمة السر"
              icon="lock"
              secureTextEntry
              placeholder="••••••••"
              value={confirm}
              onChangeText={setConfirm}
              error={error ?? undefined}
            />
          </View>

          <Button size="lg" block loading={submitting} onPress={submit}>
            عمل حساب
          </Button>

          <View style={styles.bottom}>
            <Text style={styles.bottomText}>عندك حساب؟</Text>
            <Link href="/login">
              <Text style={styles.link}>سجّل دخول</Text>
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
  title: { ...typography.h1, color: colors.ink[950], textAlign: 'center' },
  hint: { ...typography.body, color: colors.ink[500], textAlign: 'center' },
  bottom: { flexDirection: 'row', justifyContent: 'center', gap: spacing[2], marginTop: spacing[4] },
  bottomText: { ...typography.body, color: colors.ink[500] },
  link: { ...typography.bodyStrong, color: colors.coral[600] },
});
