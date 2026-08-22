import type { TextareaHTMLAttributes } from 'react';

interface TextAreaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
  hasError?: boolean;
}

export function TextArea({ hasError, className = '', ...rest }: TextAreaProps) {
  const classes = ['textarea', hasError ? 'has-error' : '', className].filter(Boolean).join(' ');
  return <textarea className={classes} {...rest} />;
}
