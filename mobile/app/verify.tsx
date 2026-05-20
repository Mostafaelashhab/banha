import { useEffect, useRef, useState } from 'react';
import { Keyboard, KeyboardAvoidingView, Platform, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { router, useLocalSearchParams } from 'expo-router';
import { Button, IconTile } from '@/components';
import { colors, radius, spacing, typography } from '@/theme';
import { useAuth } from '@/auth/AuthContext';
import { ApiError } from '@/api/client';
import { sendOtp, verifyOtp } from '@/api/endpoints';

const CODE_LENGTH = 6;

export default function Verify() {
  const auth = useAuth();
  const params = useLocalSearchParams<{ debug?: string }>();
  const [code, setCode] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [resending, setResending] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [notice, setNotice] = useState<string | null>(
    params.debug ? `[Local Debug] الكود: ${params.debug}` : null,
  );
  const inputRef = useRef<TextInput | null>(null);

  useEffect(() => {
    setTimeout(() => inputRef.current?.focus(), 200);
  }, []);

  useEffect(() => {
    if (code.length === CODE_LENGTH && !submitting) {
      void submit(code);
    }
  }, [code]);

  const submit = async (value?: string) => {
    const c = (value ?? code).trim();
    if (c.length !== CODE_LENGTH) {
      setError('الكود لازم ٦ أرقام');
      return;
    }
    setError(null);
    setSubmitting(true);
    Keyboard.dismiss();
    try {
      await verifyOtp(c);
      await auth.refreshUser();
      router.replace('/(tabs)/feed');
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'حصل خطأ، حاول تاني');
      setCode('');
      inputRef.current?.focus();
    } finally {
      setSubmitting(false);
    }
  };

  const resend = async () => {
    setError(null);
    setResending(true);
    try {
      const res = await sendOtp();
      setNotice(
        res.debug_code
          ? `[Local Debug] الكود الجديد: ${res.debug_code}`
          : 'بعتنالك كود جديد على واتساب',
      );
    } catch (e) {
      setError(e instanceof ApiError ? e.message : 'حصل خطأ');
    } finally {
      setResending(false);
    }
  };

  const phone = auth.user?.phone;
  const slots = Array.from({ length: CODE_LENGTH }, (_, i) => code[i] ?? '');

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={{ flex: 1 }}
      >
        <View style={styles.scroll}>
          <View style={styles.hero}>
            <IconTile icon="whatsapp" tone="mint" intensity="strong" size="xl" shape="circle" />
            <Text style={styles.title}>فعّل حسابك</Text>
            <Text style={styles.hint}>
              بعتنالك كود تفعيل على واتساب الرقم
            </Text>
            {phone ? <Text style={styles.phone}>{phone}</Text> : null}
          </View>

          {notice && (
            <View style={styles.notice}>
              <Text style={styles.noticeText}>{notice}</Text>
            </View>
          )}

          <Pressable onPress={() => inputRef.current?.focus()} style={styles.codeRow}>
            {slots.map((digit, i) => {
              const filled = !!digit;
              const isActive = i === code.length;
              return (
                <View
                  key={i}
                  style={[
                    styles.codeSlot,
                    filled && styles.codeSlotFilled,
                    isActive && !filled && styles.codeSlotActive,
                  ]}
                >
                  <Text style={styles.codeDigit}>{digit || '·'}</Text>
                </View>
              );
            })}
          </Pressable>

          {/* Hidden input that drives the slots */}
          <TextInput
            ref={inputRef}
            value={code}
            onChangeText={(v) => setCode(v.replace(/\D/g, '').slice(0, CODE_LENGTH))}
            keyboardType="number-pad"
            maxLength={CODE_LENGTH}
            style={styles.hiddenInput}
            autoFocus
          />

          {error ? <Text style={styles.error}>{error}</Text> : null}

          <Button size="lg" block loading={submitting} onPress={() => submit()}>
            تأكيد التفعيل
          </Button>

          <Pressable onPress={resend} disabled={resending} style={styles.resendBtn}>
            <Text style={[styles.resendText, resending && { opacity: 0.5 }]}>
              {resending ? 'بنبعت…' : 'مفيش كود؟ ابعت تاني'}
            </Text>
          </Pressable>

          <View style={styles.warning}>
            <Text style={styles.warningText}>
              <Text style={styles.warningBold}>⚠️ الكود سرّي: </Text>
              متشاركهوش مع حد. فريق بنهاوي مش هيطلبه منك أبداً. الكود صالح ٥ دقايق فقط.
            </Text>
          </View>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const SLOT_SIZE = 48;

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.cream[100] },
  scroll: { padding: spacing[5], gap: spacing[4] },
  hero: { alignItems: 'center', gap: spacing[2], paddingVertical: spacing[6] },
  title: { ...typography.h1, color: colors.ink[950], textAlign: 'center' },
  hint: { ...typography.body, color: colors.ink[500], textAlign: 'center' },
  phone: { ...typography.h3, color: colors.ink[950], marginTop: 4, writingDirection: 'ltr' },
  notice: {
    backgroundColor: '#FFF6D6',
    borderColor: colors.honey[400],
    borderWidth: 1,
    borderRadius: radius.xl,
    padding: spacing[3],
  },
  noticeText: { ...typography.meta, color: colors.ink[700] },
  codeRow: {
    flexDirection: 'row',
    gap: spacing[2],
    justifyContent: 'center',
    paddingVertical: spacing[2],
  },
  codeSlot: {
    width: SLOT_SIZE,
    height: SLOT_SIZE + 6,
    borderRadius: radius.xl,
    borderWidth: 2,
    borderColor: 'rgba(11,11,12,0.08)',
    backgroundColor: colors.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  codeSlotFilled: { borderColor: colors.coral[500], backgroundColor: colors.coral[50] },
  codeSlotActive: { borderColor: colors.coral[500] },
  codeDigit: { fontSize: 24, fontWeight: '900', color: colors.ink[950] },
  hiddenInput: {
    position: 'absolute',
    width: 1,
    height: 1,
    opacity: 0,
  },
  error: { fontSize: 13, fontWeight: '700', color: colors.blush[500], textAlign: 'center' },
  resendBtn: { alignItems: 'center', paddingVertical: spacing[2] },
  resendText: { ...typography.bodyStrong, color: colors.coral[600] },
  warning: {
    backgroundColor: colors.coral[50],
    borderColor: 'rgba(45,91,255,0.2)',
    borderWidth: 1,
    borderRadius: radius.xl,
    padding: spacing[3],
  },
  warningText: { ...typography.meta, color: colors.ink[500], lineHeight: 18 },
  warningBold: { color: colors.ink[950], fontWeight: '900' },
});
