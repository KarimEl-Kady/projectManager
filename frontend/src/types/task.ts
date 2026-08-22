export type TaskStatus = 'todo' | 'in_progress' | 'done';
export type TaskPriority = 'low' | 'medium' | 'high';

export interface Task {
  uuid: string;
  project_uuid: string;
  title: string;
  description: string | null;
  status: TaskStatus;
  priority: TaskPriority;
  due_date: string | null;
  is_overdue: boolean;
  created_at: string;
  updated_at: string;
}

export interface TaskCreateInput {
  title: string;
  description?: string | null;
  status?: TaskStatus;
  priority: TaskPriority;
  due_date?: string | null;
}

export interface TaskUpdateInput {
  title?: string;
  description?: string | null;
  status?: TaskStatus;
  priority?: TaskPriority;
  due_date?: string | null;
}

export interface TaskListParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: TaskStatus;
  priority?: TaskPriority;
  sort?: 'title' | 'status' | 'priority' | 'due_date' | 'created_at' | 'updated_at';
  direction?: 'asc' | 'desc';
}
