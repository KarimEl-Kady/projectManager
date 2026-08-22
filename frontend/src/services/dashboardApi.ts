import { api } from './api';
import type { ApiResource } from '../types/api';
import type { DashboardMetrics } from '../types/dashboard';

export const dashboardApi = api.injectEndpoints({
  endpoints: (builder) => ({
    getDashboard: builder.query<ApiResource<DashboardMetrics>, void>({
      query: () => '/dashboard',
      providesTags: ['Dashboard'],
    }),
  }),
});

export const { useGetDashboardQuery } = dashboardApi;
