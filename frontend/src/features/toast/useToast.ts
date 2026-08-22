import { useCallback, useEffect, useRef } from 'react';
import { useAppDispatch } from '../../app/hooks';
import type { ToastVariant } from './toastSlice';
import { toastDismissed, toastShown } from './toastSlice';

const AUTO_DISMISS_MS = 4000;

export function useToast() {
  const dispatch = useAppDispatch();
  const timers = useRef<ReturnType<typeof setTimeout>[]>([]);

  useEffect(() => {
    const activeTimers = timers.current;
    return () => {
      activeTimers.forEach(clearTimeout);
    };
  }, []);

  return useCallback(
    (message: string, variant: ToastVariant = 'info') => {
      const action = toastShown(message, variant);
      dispatch(action);
      const timer = setTimeout(() => dispatch(toastDismissed(action.payload.id)), AUTO_DISMISS_MS);
      timers.current.push(timer);
    },
    [dispatch],
  );
}
