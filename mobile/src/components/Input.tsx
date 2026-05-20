import React, { forwardRef, useState } from 'react';
import { StyleSheet, Text, TextInput, TextInputProps, View } from 'react-native';
import { colors, radius, spacing, typography } from '../theme';
import { Icon, IconName } from './Icon';

type Props = TextInputProps & {
  label?: string;
  error?: string;
  helper?: string;
  icon?: IconName;
  suffix?: string;
  required?: boolean;
};

export const Input = forwardRef<TextInput, Props>(function Input(
  { label, error, helper, icon, suffix, required, style, onFocus, onBlur, ...rest },
  ref,
) {
  const [focused, setFocused] = useState(false);
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
        ]}
      >
        {icon && (
          <View style={styles.iconBox}>
            <Icon name={icon} size={16} color={focused ? colors.coral[500] : colors.ink[400]} />
          </View>
        )}
        <TextInput
          ref={ref}
          placeholderTextColor={colors.ink[400]}
          onFocus={(e) => {
            setFocused(true);
            onFocus?.(e);
          }}
          onBlur={(e) => {
            setFocused(false);
            onBlur?.(e);
          }}
          style={[styles.input, style]}
          {...rest}
        />
        {suffix && <Text style={styles.suffix}>{suffix}</Text>}
      </View>
      {showError ? (
        <Text style={styles.error}>{error}</Text>
      ) : helper ? (
        <Text style={styles.helper}>{helper}</Text>
      ) : null}
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
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.cream[50],
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: 'rgba(11,11,12,0.06)',
    paddingHorizontal: spacing[4],
    minHeight: 44,
  },
  fieldFocus: {
    borderColor: colors.coral[500],
    backgroundColor: colors.white,
  },
  fieldError: {
    borderColor: colors.blush[500],
  },
  iconBox: { marginEnd: spacing[2] },
  input: {
    flex: 1,
    color: colors.ink[950],
    fontSize: 14,
    fontWeight: '500',
    paddingVertical: spacing[3],
  },
  suffix: {
    ...typography.meta,
    color: colors.ink[500],
    marginStart: spacing[2],
  },
  helper: {
    fontSize: 11,
    color: colors.ink[500],
  },
  error: {
    fontSize: 11,
    fontWeight: '700',
    color: colors.blush[500],
  },
});
