import type { TaskPriority, TaskStatus } from '../../types/task';

export const TASK_STATUSES: TaskStatus[] = ['todo', 'in_progress', 'done'];
export const TASK_PRIORITIES: TaskPriority[] = ['low', 'medium', 'high'];

const STATUS_LABELS: Record<TaskStatus, string> = {
  todo: 'To do',
  in_progress: 'In progress',
  done: 'Done',
};

const STATUS_COLORS: Record<TaskStatus, 'gray' | 'blue' | 'green'> = {
  todo: 'gray',
  in_progress: 'blue',
  done: 'green',
};

const PRIORITY_LABELS: Record<TaskPriority, string> = {
  low: 'Low',
  medium: 'Medium',
  high: 'High',
};

const PRIORITY_COLORS: Record<TaskPriority, 'gray' | 'amber' | 'red'> = {
  low: 'gray',
  medium: 'amber',
  high: 'red',
};

export function taskStatusLabel(status: TaskStatus) {
  return STATUS_LABELS[status];
}

export function taskStatusColor(status: TaskStatus) {
  return STATUS_COLORS[status];
}

export function taskPriorityLabel(priority: TaskPriority) {
  return PRIORITY_LABELS[priority];
}

export function taskPriorityColor(priority: TaskPriority) {
  return PRIORITY_COLORS[priority];
}
