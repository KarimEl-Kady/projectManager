import type { PaginatedResponse } from '../../types/api';
import { Button } from '../ui/Button';

interface PaginationProps {
  meta: PaginatedResponse<unknown>['meta'];
  onPageChange: (page: number) => void;
}

export function Pagination({ meta, onPageChange }: PaginationProps) {
  const { current_page, last_page, total, from, to } = meta;

  return (
    <div className="pagination">
      <span className="pagination-info">
        {total === 0 ? 'No results' : `Showing ${from}–${to} of ${total}`}
      </span>
      <div className="pagination-controls">
        <Button
          variant="secondary"
          size="sm"
          disabled={current_page <= 1}
          onClick={() => onPageChange(current_page - 1)}
        >
          Previous
        </Button>
        <Button
          variant="secondary"
          size="sm"
          disabled={current_page >= last_page}
          onClick={() => onPageChange(current_page + 1)}
        >
          Next
        </Button>
      </div>
    </div>
  );
}
