import { I18nManager, Platform } from 'react-native';

export function ensureRTL() {
  if (!I18nManager.isRTL) {
    try {
      I18nManager.allowRTL(true);
      I18nManager.forceRTL(true);
    } catch {
      // no-op
    }
  }
  return I18nManager.isRTL || Platform.OS === 'web';
}
