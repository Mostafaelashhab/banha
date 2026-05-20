import { useState } from 'react';
import { Modal, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { colors, radius, spacing, typography } from '../theme';
import { Icon } from './Icon';

type Option = { value: string | number; label: string };

type Props = {
  label?: string;
  placeholder?: string;
  value?: string | number | null;
  options: Option[];
  onChange: (v: string | number) => void;
  error?: string;
  required?: boolean;
};

export function Select({ label, placeholder, value, options, onChange, error, required }: Props) {
  const [open, setOpen] = useState(false);
  const selected = options.find((o) => String(o.value) === String(value));

  return (
    <View style={styles.wrap}>
      {label && (
        <Text style={styles.label}>
          {label}
          {required && <Text style={{ color: colors.blush[500] }}> *</Text>}
        </Text>
      )}
      <Pressable
        onPress={() => setOpen(true)}
        style={[styles.field, error && styles.fieldError]}
      >
        <Text style={[styles.value, !selected && styles.placeholder]} numberOfLines={1}>
          {selected ? selected.label : placeholder ?? '—'}
        </Text>
        <Icon name="chevron-down" size={16} color={colors.ink[500]} />
      </Pressable>
      {error ? <Text style={styles.error}>{error}</Text> : null}

      <Modal transparent visible={open} animationType="fade" onRequestClose={() => setOpen(false)}>
        <Pressable style={styles.backdrop} onPress={() => setOpen(false)}>
          <Pressable style={styles.sheet} onPress={(e) => e.stopPropagation()}>
            {label ? <Text style={styles.sheetTitle}>{label}</Text> : null}
            <ScrollView style={{ maxHeight: 360 }}>
              {options.map((opt) => {
                const active = String(opt.value) === String(value);
                return (
                  <Pressable
                    key={String(opt.value)}
                    style={styles.opt}
                    onPress={() => {
                      onChange(opt.value);
                      setOpen(false);
                    }}
                  >
                    <Text style={[styles.optLabel, active && { color: colors.coral[600] }]}>
                      {opt.label}
                    </Text>
                    {active && <Icon name="check" size={18} color={colors.coral[600]} />}
                  </Pressable>
                );
              })}
            </ScrollView>
          </Pressable>
        </Pressable>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { gap: spacing[1.5], width: '100%' },
  label: { ...typography.meta, color: colors.ink[700] },
  field: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: colors.cream[50],
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: 'rgba(11,11,12,0.06)',
    paddingHorizontal: spacing[4],
    minHeight: 44,
  },
  fieldError: { borderColor: colors.blush[500] },
  value: { flex: 1, color: colors.ink[950], fontSize: 14, fontWeight: '500' },
  placeholder: { color: colors.ink[400] },
  error: { fontSize: 11, fontWeight: '700', color: colors.blush[500] },
  backdrop: {
    flex: 1,
    backgroundColor: 'rgba(11,11,12,0.45)',
    justifyContent: 'flex-end',
  },
  sheet: {
    backgroundColor: colors.white,
    borderTopLeftRadius: radius['3xl'],
    borderTopRightRadius: radius['3xl'],
    padding: spacing[4],
    paddingBottom: spacing[8],
  },
  sheetTitle: { ...typography.h3, color: colors.ink[950], marginBottom: spacing[2] },
  opt: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: spacing[3],
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(11,11,12,0.04)',
  },
  optLabel: { ...typography.bodyStrong, color: colors.ink[950] },
});
