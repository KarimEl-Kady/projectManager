import { api } from './api';
import type { ApiResource } from '../types/api';
import type { AuthResponse, LoginRequest, MessageResponse, RegisterRequest } from '../types/auth';
import type { User } from '../types/user';

export const authApi = api.injectEndpoints({
  endpoints: (builder) => ({
    register: builder.mutation<AuthResponse, RegisterRequest>({
      query: (body) => ({ url: '/auth/register', method: 'POST', body }),
      invalidatesTags: ['User'],
    }),
    login: builder.mutation<AuthResponse, LoginRequest>({
      query: (body) => ({ url: '/auth/login', method: 'POST', body }),
      invalidatesTags: ['User'],
    }),
    me: builder.query<ApiResource<User>, void>({
      query: () => '/auth/me',
      providesTags: ['User'],
    }),
    logout: builder.mutation<MessageResponse, void>({
      query: () => ({ url: '/auth/logout', method: 'POST' }),
      // Reset the whole cache instead of invalidating tags: invalidation would
      // refetch any still-mounted queries (e.g. the page being logged out from)
      // after the token is already cleared, producing spurious 401s.
      onQueryStarted: async (_arg, { dispatch, queryFulfilled }) => {
        await queryFulfilled;
        dispatch(api.util.resetApiState());
      },
    }),
  }),
});

export const { useRegisterMutation, useLoginMutation, useMeQuery, useLogoutMutation } = authApi;
