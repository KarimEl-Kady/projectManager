import type { ProjectStatus } from '../../types/project';

export const PROJECT_STATUSES: ProjectStatus[] = ['active', 'completed', 'archived'];

const LABELS: Record<ProjectStatus, string> = {
  active: 'Active',
  completed: 'Completed',
  archived: 'Archived',
};

const COLORS: Record<ProjectStatus, 'blue' | 'green' | 'gray'> = {
  active: 'blue',
  completed: 'green',
  archived: 'gray',
};

export function projectStatusLabel(status: ProjectStatus) {
  return LABELS[status];
}

export function projectStatusColor(status: ProjectStatus) {
  return COLORS[status];
}
