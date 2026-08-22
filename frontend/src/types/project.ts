export type ProjectStatus = 'active' | 'completed' | 'archived';

export interface Project {
  uuid: string;
  title: string;
  description: string | null;
  status: ProjectStatus;
  tasks_count?: number;
  created_at: string;
  updated_at: string;
}

export interface ProjectCreateInput {
  title: string;
  description?: string | null;
  status?: ProjectStatus;
}

export interface ProjectUpdateInput {
  title?: string;
  description?: string | null;
  status?: ProjectStatus;
}

export interface ProjectListParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: ProjectStatus;
  sort?: 'title' | 'status' | 'created_at' | 'updated_at';
  direction?: 'asc' | 'desc';
}
