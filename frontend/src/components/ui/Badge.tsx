import type { ReactNode } from 'react';

type BadgeColor = 'neutral' | 'blue' | 'green' | 'amber' | 'red' | 'gray';

export function Badge({ color = 'neutral', children }: { color?: BadgeColor; children: ReactNode }) {
  return <span className={`badge badge-${color}`}>{children}</span>;
}
