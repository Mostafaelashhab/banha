import React, { forwardRef, useState } from 'react';
import { StyleSheet, Text, TextInput, TextInputProps, View } from 'react-native';
import { colors, radius, spacing, typography } from '../theme';

type Props = TextInputProps & {
  label?: string;
  error?: string;
  helper?: string;
  rows?: number;
  maxLength?: number;
  counter?: boolean;
  required?: boolean;
};

export const Textarea = forwardRef<TextInput, Props>(function Textarea(
  { label, error, helper, rows = 3, maxLength, counter, required, value, onChangeText, style, onFocus, onBlur, ...rest },
  ref,
) {
  const [focused, setFocused] = useState(false);
  const [internal, setInternal] = useState(value ?? '');
  const text = value ?? internal;
  const showError = !!error;

  return (
    <View style={styles.wrap}>
      {label && (
        <Text style={styles.label}>
          {label}
          {required && <Text style={{ color: colors.blush[500] }}> *</Text>}
        </Text>
      )}
      <View
        style={[
          styles.field,
          focused && styles.fieldFocus,
          showError && styles.fieldError,
          { minHeight: 24 * rows + 24 },
        ]}
      >
        <TextInput
          ref={ref}
          multiline
          maxLength={maxLength}
          value={text}
          onChangeText={(v) => {
            setInternal(v);
            onChangeText?.(v);
          }}
          placeholderTextColor={colors.ink[400]}
          onFocus={(e) => {
            setFocused(true);
            onFocus?.(e);
          }}
          onBlur={(e) => {
            setFocused(false);
            onBlur?.(e);
          }}
          textAlignVertical="top"
          style={[styles.input, style]}
          {...rest}
        />
      </View>
      <View style={styles.metaRow}>
        <View style={{ flex: 1 }}>
          {showError ? (
            <Text style={styles.error}>{error}</Text>
          ) : helper ? (
            <Text style={styles.helper}>{helper}</Text>
          ) : null}
        </View>
        {counter && maxLength ? (
          <Text style={styles.counter}>
            {text.length} / {maxLength}
          </Text>
        ) : null}
      </View>
    </View>
  );
});

const styles = StyleSheet.create({
  wrap: { gap: spacing[1.5], width: '100%' },
  label: {
    ...typography.meta,
    color: colors.ink[700],
  },
  field: {
    backgroundColor: colors.cream[50],
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: 'rgba(11,11,12,0.06)',
    paddingHorizontal: spacing[4],
    paddingVertical: spacing[3],
  },
  fieldFocus: { borderColor: colors.coral[500], backgroundColor: colors.white },
  fieldError: { borderColor: colors.blush[500] },
  input: {
    color: colors.ink[950],
    fontSize: 14,
    fontWeight: '500',
    minHeight: 24,
  },
  metaRow: { flexDirection: 'row', alignItems: 'center', gap: spacing[2] },
  helper: { fontSize: 11, color: colors.ink[500] },
  error: { fontSize: 11, fontWeight: '700', color: colors.blush[500] },
  counter: { fontSize: 11, color: colors.ink[400], fontWeight: '700' },
});
