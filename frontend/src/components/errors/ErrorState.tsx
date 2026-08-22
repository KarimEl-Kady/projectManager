import type { ApiError } from '../../utils/errors';
import { getErrorMessage } from '../../utils/errors';
import { Button } from '../ui/Button';

interface ErrorStateProps {
  error?: ApiError;
  message?: string;
  onRetry?: () => void;
}

export function ErrorState({ error, message, onRetry }: ErrorStateProps) {
  return (
    <div className="state-block state-error">
      <h3>Something went wrong</h3>
      <p>{message ?? getErrorMessage(error)}</p>
      {onRetry && (
        <Button variant="secondary" size="sm" onClick={onRetry}>
          Try again
        </Button>
      )}
    </div>
  );
}
