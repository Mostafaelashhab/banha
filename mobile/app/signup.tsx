import { useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Link, router } from 'expo-router';
import { Button, IconTile, Input, Select } from '@/components';
import { colors, spacing, typography } from '@/theme';
import { useAuth } from '@/auth/AuthContext';
import { useZones } from '@/api/hooks';
import { ApiError } from '@/api/client';

export default function Signup() {
  const auth = useAuth();
  const zonesQuery = useZones();
  const [phone, setPhone] = useState('');
  const [username, setUsername] = useState('');
  const [zoneId, setZoneId] = useState<number | null>(null);
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [agree, setAgree] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const submit = async () => {
    const next: Record<string, string> = {};
    if (!/^01[0125][0-9]{8}$/.test(phone)) next.phone = 'لازم رقم موبايل مصري صحيح';
    if (!username || username.length < 3) next.username = 'الاسم لازم ٣ حروف على الأقل';
    if (!zoneId) next.zone_id = 'اختار منطقتك';
    if (!password || password.length < 6) next.password = 'الباسورد ٦ حروف على الأقل';
    if (password !== confirm) next.confirm = 'الباسورد مش متطابق';
    if (!agree) next.agree = 'لازم توافق على الشروط';

    if (Object.keys(next).length) {
      setErrors(next);
      return;
    }
    setErrors({});
    setSubmitting(true);
    try {
      const res = await auth.signup({
        phone,
        username,
        zone_id: zoneId!,
        password,
        password_confirmation: confirm,
        agree: true,
      });
      if (res.needs_verification) {
        router.replace({ pathname: '/verify', params: { debug: res.debug_code ?? '' } });
      } else {
        router.replace('/(tabs)/feed');
      }
    } catch (e) {
      if (e instanceof ApiError && e.data && typeof e.data === 'object') {
        const apiErrors = (e.data as { errors?: Record<string, string[]> }).errors;
        if (apiErrors) {
          const mapped: Record<string, string> = {};
          Object.entries(apiErrors).forEach(([k, v]) => {
            mapped[k] = Array.isArray(v) ? v[0] : String(v);
          });
          setErrors(mapped);
        } else {
          setErrors({ phone: e.message });
        }
      } else {
        setErrors({ phone: 'حصل خطأ، حاول تاني' });
      }
    } finally {
      setSubmitting(false);
    }
  };

  const zoneOptions = (zonesQuery.data ?? []).map((z) => ({
    value: Number(z.id),
    label: z.name,
  }));

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={{ flex: 1 }}
      >
        <ScrollView contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
          <View style={styles.hero}>
            <IconTile icon="user" tone="coral" intensity="strong" size="xl" shape="circle" />
            <Text style={styles.title}>حساب جديد</Text>
            <Text style={styles.hint}>دقيقة واحدة وتبقى جوّة بنهاوي</Text>
          </View>

          <View style={{ gap: spacing[3] }}>
            <Input
              label="رقم الموبايل (واتساب)"
              icon="phone"
              keyboardType="phone-pad"
              autoCapitalize="none"
              maxLength={11}
              placeholder="01XXXXXXXXX"
              value={phone}
              onChangeText={setPhone}
              error={errors.phone}
              required
            />
            <Input
              label="اسم اليوزر"
              icon="user"
              autoCapitalize="none"
              placeholder="حروف، أرقام و _"
              value={username}
              onChangeText={setUsername}
              error={errors.username}
              required
            />
            <Select
              label="منطقتك في بنها"
              placeholder={zonesQuery.isLoading ? 'بنحمّل…' : 'اختار منطقتك'}
              value={zoneId}
              options={zoneOptions}
              onChange={(v) => setZoneId(Number(v))}
              error={errors.zone_id}
              required
            />
            <Input
              label="الباسورد"
              icon="lock"
              secureTextEntry
              placeholder="٦ حروف على الأقل"
              value={password}
              onChangeText={setPassword}
              error={errors.password}
              required
            />
            <Input
              label="تأكيد الباسورد"
              icon="lock"
              secureTextEntry
              placeholder="••••••••"
              value={confirm}
              onChangeText={setConfirm}
              error={errors.confirm}
              required
            />

            <Pressable
              onPress={() => setAgree((v) => !v)}
              style={styles.agreeRow}
              accessibilityRole="checkbox"
              accessibilityState={{ checked: agree }}
            >
              <View style={[styles.checkbox, agree && styles.checkboxOn]}>
                {agree ? <Text style={styles.checkmark}>✓</Text> : null}
              </View>
              <Text style={styles.agreeText}>
                موافق على <Text style={styles.agreeLink}>شروط الاستخدام</Text>
              </Text>
            </Pressable>
            {errors.agree ? <Text style={styles.error}>{errors.agree}</Text> : null}
          </View>

          <Button size="lg" block loading={submitting} onPress={submit}>
            افتح حساب
          </Button>

          <View style={styles.bottom}>
            <Text style={styles.bottomText}>عندك حساب؟</Text>
            <Link href="/login">
              <Text style={styles.link}>ادخل</Text>
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
  hero: { alignItems: 'center', gap: spacing[2], paddingVertical: spacing[5] },
  title: { ...typography.h1, color: colors.ink[950], textAlign: 'center' },
  hint: { ...typography.body, color: colors.ink[500], textAlign: 'center' },
  agreeRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[2], marginTop: spacing[1] },
  checkbox: {
    width: 22,
    height: 22,
    borderRadius: 6,
    borderWidth: 2,
    borderColor: colors.ink[300],
    alignItems: 'center',
    justifyContent: 'center',
  },
  checkboxOn: { backgroundColor: colors.coral[500], borderColor: colors.coral[500] },
  checkmark: { color: colors.white, fontSize: 14, fontWeight: '900' },
  agreeText: { ...typography.body, color: colors.ink[700], flex: 1 },
  agreeLink: { color: colors.coral[600], fontWeight: '800' },
  error: { fontSize: 11, fontWeight: '700', color: colors.blush[500] },
  bottom: { flexDirection: 'row', justifyContent: 'center', gap: spacing[2], marginTop: spacing[4] },
  bottomText: { ...typography.body, color: colors.ink[500] },
  link: { ...typography.bodyStrong, color: colors.coral[600] },
});
