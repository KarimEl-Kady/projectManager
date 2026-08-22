import { api } from './api';
import type { ApiResource, PaginatedResponse } from '../types/api';
import type { Task, TaskCreateInput, TaskListParams, TaskUpdateInput } from '../types/task';

export const tasksApi = api.injectEndpoints({
  endpoints: (builder) => ({
    listTasks: builder.query<PaginatedResponse<Task>, { projectUuid: string; params?: TaskListParams }>({
      query: ({ projectUuid, params }) => ({ url: `/projects/${projectUuid}/tasks`, params }),
      providesTags: (result) =>
        result
          ? [
              ...result.data.map((task) => ({ type: 'Task' as const, id: task.uuid })),
              { type: 'Task' as const, id: 'LIST' },
            ]
          : [{ type: 'Task' as const, id: 'LIST' }],
    }),
    getTask: builder.query<ApiResource<Task>, { projectUuid: string; taskUuid: string }>({
      query: ({ projectUuid, taskUuid }) => `/projects/${projectUuid}/tasks/${taskUuid}`,
      providesTags: (_result, _error, { taskUuid }) => [{ type: 'Task', id: taskUuid }],
    }),
    createTask: builder.mutation<ApiResource<Task>, { projectUuid: string; body: TaskCreateInput }>({
      query: ({ projectUuid, body }) => ({ url: `/projects/${projectUuid}/tasks`, method: 'POST', body }),
      invalidatesTags: (_result, _error, { projectUuid }) => [
        { type: 'Task', id: 'LIST' },
        { type: 'Project', id: projectUuid },
        'Dashboard',
      ],
    }),
    updateTask: builder.mutation<
      ApiResource<Task>,
      { projectUuid: string; taskUuid: string; body: TaskUpdateInput }
    >({
      query: ({ projectUuid, taskUuid, body }) => ({
        url: `/projects/${projectUuid}/tasks/${taskUuid}`,
        method: 'PATCH',
        body,
      }),
      invalidatesTags: (_result, _error, { projectUuid, taskUuid }) => [
        { type: 'Task', id: taskUuid },
        { type: 'Task', id: 'LIST' },
        { type: 'Project', id: projectUuid },
        'Dashboard',
      ],
    }),
    deleteTask: builder.mutation<void, { projectUuid: string; taskUuid: string }>({
      query: ({ projectUuid, taskUuid }) => ({
        url: `/projects/${projectUuid}/tasks/${taskUuid}`,
        method: 'DELETE',
      }),
      invalidatesTags: (_result, _error, { projectUuid, taskUuid }) => [
        { type: 'Task', id: taskUuid },
        { type: 'Task', id: 'LIST' },
        { type: 'Project', id: projectUuid },
        'Dashboard',
      ],
    }),
  }),
});

export const {
  useListTasksQuery,
  useGetTaskQuery,
  useCreateTaskMutation,
  useUpdateTaskMutation,
  useDeleteTaskMutation,
} = tasksApi;
