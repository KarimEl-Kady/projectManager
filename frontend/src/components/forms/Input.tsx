import type { InputHTMLAttributes } from 'react';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  hasError?: boolean;
}

export function Input({ hasError, className = '', ...rest }: InputProps) {
  const classes = ['input', hasError ? 'has-error' : '', className].filter(Boolean).join(' ');
  return <input className={classes} {...rest} />;
}
