import { api } from './api';
import type { ApiResource, PaginatedResponse } from '../types/api';
import type { Project, ProjectCreateInput, ProjectListParams, ProjectUpdateInput } from '../types/project';

export const projectsApi = api.injectEndpoints({
  endpoints: (builder) => ({
    listProjects: builder.query<PaginatedResponse<Project>, ProjectListParams | void>({
      query: (params) => ({ url: '/projects', params: params ?? undefined }),
      providesTags: (result) =>
        result
          ? [
              ...result.data.map((project) => ({ type: 'Project' as const, id: project.uuid })),
              { type: 'Project' as const, id: 'LIST' },
            ]
          : [{ type: 'Project' as const, id: 'LIST' }],
    }),
    getProject: builder.query<ApiResource<Project>, string>({
      query: (uuid) => `/projects/${uuid}`,
      providesTags: (_result, _error, uuid) => [{ type: 'Project', id: uuid }],
    }),
    createProject: builder.mutation<ApiResource<Project>, ProjectCreateInput>({
      query: (body) => ({ url: '/projects', method: 'POST', body }),
      invalidatesTags: [{ type: 'Project', id: 'LIST' }, 'Dashboard'],
    }),
    updateProject: builder.mutation<ApiResource<Project>, { uuid: string; body: ProjectUpdateInput }>({
      query: ({ uuid, body }) => ({ url: `/projects/${uuid}`, method: 'PATCH', body }),
      invalidatesTags: (_result, _error, { uuid }) => [
        { type: 'Project', id: uuid },
        { type: 'Project', id: 'LIST' },
        'Dashboard',
      ],
    }),
    deleteProject: builder.mutation<void, string>({
      query: (uuid) => ({ url: `/projects/${uuid}`, method: 'DELETE' }),
      invalidatesTags: (_result, _error, uuid) => [
        { type: 'Project', id: uuid },
        { type: 'Project', id: 'LIST' },
        { type: 'Task', id: 'LIST' },
        'Dashboard',
      ],
    }),
  }),
});

export const {
  useListProjectsQuery,
  useGetProjectQuery,
  useCreateProjectMutation,
  useUpdateProjectMutation,
  useDeleteProjectMutation,
} = projectsApi;
