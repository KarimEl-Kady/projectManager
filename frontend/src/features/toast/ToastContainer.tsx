import { useAppDispatch, useAppSelector } from '../../app/hooks';
import { toastDismissed } from './toastSlice';

export function ToastContainer() {
  const toasts = useAppSelector((state) => state.toast);
  const dispatch = useAppDispatch();

  if (toasts.length === 0) return null;

  return (
    <div className="toast-container">
      {toasts.map((toast) => (
        <div key={toast.id} className={`toast toast-${toast.variant}`}>
          <span>{toast.message}</span>
          <button className="toast-dismiss" onClick={() => dispatch(toastDismissed(toast.id))} aria-label="Dismiss">
            ✕
          </button>
        </div>
      ))}
    </div>
  );
}
