import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';
import type { BaseQueryFn, FetchArgs, FetchBaseQueryError } from '@reduxjs/toolkit/query/react';
import { tokenStorage } from '../utils/storage';

// Dispatched as a plain action (not the authSlice action creator) to avoid a
// module import cycle with authSlice -> authApi -> api. The type string must
// stay in sync with authSlice's `credentialsCleared` reducer name.
const AUTH_CREDENTIALS_CLEARED = 'auth/credentialsCleared';

const rawBaseQuery = fetchBaseQuery({
  baseUrl: import.meta.env.VITE_API_URL,
  prepareHeaders: (headers) => {
    const token = tokenStorage.get();
    if (token) {
      headers.set('Authorization', `Bearer ${token}`);
    }
    headers.set('Accept', 'application/json');
    return headers;
  },
});

const baseQueryWithAuth: BaseQueryFn<string | FetchArgs, unknown, FetchBaseQueryError> = async (
  args,
  api,
  extraOptions,
) => {
  const result = await rawBaseQuery(args, api, extraOptions);

  if (result.error?.status === 401 && tokenStorage.get()) {
    api.dispatch({ type: AUTH_CREDENTIALS_CLEARED });
  }

  return result;
};

export const api = createApi({
  reducerPath: 'api',
  baseQuery: baseQueryWithAuth,
  tagTypes: ['User', 'Project', 'Task', 'Dashboard'],
  endpoints: () => ({}),
});
