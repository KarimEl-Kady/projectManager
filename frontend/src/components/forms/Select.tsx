import type { SelectHTMLAttributes } from 'react';

interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
  hasError?: boolean;
}

export function Select({ hasError, className = '', children, ...rest }: SelectProps) {
  const classes = ['select', hasError ? 'has-error' : '', className].filter(Boolean).join(' ');
  return (
    <select className={classes} {...rest}>
      {children}
    </select>
  );
}
