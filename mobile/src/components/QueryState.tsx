import React from 'react';
import { View } from 'react-native';
import { EmptyState } from './EmptyState';
import { Skeleton } from './Skeleton';
import { Button } from './Button';
import { spacing } from '../theme';

type Status = 'pending' | 'error' | 'success';

type Props<T> = {
  status: Status;
  data: T | undefined;
  error?: unknown;
  refetch?: () => void;
  skeleton?: React.ReactNode;
  emptyTitle?: string;
  emptyHint?: string;
  isEmpty?: (data: T) => boolean;
  children: (data: T) => React.ReactNode;
};

export function QueryState<T>({
  status,
  data,
  error,
  refetch,
  skeleton,
  emptyTitle = 'لسه مفيش هنا',
  emptyHint = 'جرّب تحدّث الصفحة بعد شوية',
  isEmpty,
  children,
}: Props<T>) {
  if (status === 'pending') {
    return <View style={{ gap: spacing[3] }}>{skeleton ?? <DefaultSkeleton />}</View>;
  }
  if (status === 'error') {
    const msg = (error as { message?: string } | undefined)?.message;
    return (
      <EmptyState
        tone="danger"
        icon="bolt"
        title="النت ضعيف شوية"
        hint={msg ?? 'حاول تاني بعد ثانية'}
      >
        {refetch && (
          <Button variant="outline" onPress={refetch} icon="bolt">
            إعادة المحاولة
          </Button>
        )}
      </EmptyState>
    );
  }
  if (!data || (isEmpty && isEmpty(data))) {
    return <EmptyState icon="search" title={emptyTitle} hint={emptyHint} />;
  }
  return <>{children(data)}</>;
}

function DefaultSkeleton() {
  return (
    <View style={{ gap: spacing[3] }}>
      <Skeleton height={72} />
      <Skeleton height={72} />
      <Skeleton height={72} />
    </View>
  );
}
