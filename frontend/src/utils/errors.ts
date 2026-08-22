import type { SerializedError } from '@reduxjs/toolkit';
import type { FetchBaseQueryError } from '@reduxjs/toolkit/query';
import type { ValidationErrorResponse } from '../types/api';

export type ApiError = FetchBaseQueryError | SerializedError | undefined;

export function isFetchBaseQueryError(error: ApiError): error is FetchBaseQueryError {
  return typeof error === 'object' && error != null && 'status' in error;
}

export function isValidationError(
  error: ApiError,
): error is FetchBaseQueryError & { status: 422; data: ValidationErrorResponse } {
  return isFetchBaseQueryError(error) && error.status === 422;
}

export function getFieldErrors(error: ApiError): Record<string, string> {
  if (!isValidationError(error)) return {};
  const data = error.data as ValidationErrorResponse;
  const fieldErrors: Record<string, string> = {};
  for (const [field, messages] of Object.entries(data.errors ?? {})) {
    fieldErrors[field] = messages[0];
  }
  return fieldErrors;
}

export function getErrorMessage(error: ApiError): string {
  if (!error) return 'Something went wrong. Please try again.';

  if (isFetchBaseQueryError(error)) {
    if (error.status === 'FETCH_ERROR' || error.status === 'TIMEOUT_ERROR') {
      return 'Unable to reach the server. Check your connection and try again.';
    }
    if (error.status === 'PARSING_ERROR') {
      return 'Received an unexpected response from the server.';
    }
    const data = error.data;
    if (data && typeof data === 'object' && 'message' in data && typeof data.message === 'string') {
      return data.message;
    }
    if (error.status === 429) {
      return 'Too many attempts. Please wait a moment and try again.';
    }
    return `Request failed (${String(error.status)}).`;
  }

  return error.message ?? 'Something went wrong. Please try again.';
}
