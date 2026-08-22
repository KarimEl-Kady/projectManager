import { useState } from 'react';
import type { FormEvent } from 'react';
import { Link, useParams, useSearchParams } from 'react-router-dom';
import { DashboardLayout } from '../../layouts/DashboardLayout';
import { useGetProjectQuery } from '../../services/projectsApi';
import { useDeleteTaskMutation, useListTasksQuery } from '../../services/tasksApi';
import type { Task, TaskPriority, TaskStatus } from '../../types/task';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/forms/Input';
import { Select } from '../../components/forms/Select';
import { Badge } from '../../components/ui/Badge';
import { Pagination } from '../../components/tables/Pagination';
import { TableSkeleton, Skeleton } from '../../components/loading/Skeleton';
import { EmptyState } from '../../components/errors/EmptyState';
import { ErrorState } from '../../components/errors/ErrorState';
import { ConfirmDialog } from '../../components/modals/ConfirmDialog';
import { ProjectFormModal } from '../../features/projects/ProjectFormModal';
import { projectStatusColor, projectStatusLabel } from '../../features/projects/projectDisplay';
import {
  TASK_PRIORITIES,
  TASK_STATUSES,
  taskPriorityColor,
  taskPriorityLabel,
  taskStatusColor,
  taskStatusLabel,
} from '../../features/tasks/taskDisplay';
import { TaskFormModal } from '../../features/tasks/TaskFormModal';
import { useToast } from '../../features/toast/useToast';
import { getErrorMessage } from '../../utils/errors';
import styles from './ProjectDetailPage.module.css';

type SortKey = 'title' | 'status' | 'priority' | 'due_date' | 'created_at' | 'updated_at';

export function ProjectDetailPage() {
  const { uuid } = useParams<{ uuid: string }>();
  const projectUuid = uuid!;
  const [searchParams, setSearchParams] = useSearchParams();
  const notify = useToast();

  const page = Number(searchParams.get('page') ?? '1');
  const search = searchParams.get('search') ?? '';
  const status = (searchParams.get('status') as TaskStatus | null) ?? undefined;
  const priority = (searchParams.get('priority') as TaskPriority | null) ?? undefined;
  const sort = (searchParams.get('sort') as SortKey | null) ?? 'created_at';
  const direction = (searchParams.get('direction') as 'asc' | 'desc' | null) ?? 'desc';

  const [searchInput, setSearchInput] = useState(search);
  const [editingProject, setEditingProject] = useState(false);
  const [creatingTask, setCreatingTask] = useState(false);
  const [editingTask, setEditingTask] = useState<Task | null>(null);
  const [deletingTask, setDeletingTask] = useState<Task | null>(null);

  const project = useGetProjectQuery(projectUuid);
  const tasks = useListTasksQuery({
    projectUuid,
    params: { page, search: search || undefined, status, priority, sort, direction },
  });
  const [deleteTask, { isLoading: isDeleting }] = useDeleteTaskMutation();

  const updateParams = (updates: Record<string, string | undefined>) => {
    const next = new URLSearchParams(searchParams);
    for (const [key, value] of Object.entries(updates)) {
      if (value) next.set(key, value);
      else next.delete(key);
    }
    if (!('page' in updates)) next.delete('page');
    setSearchParams(next);
  };

  const handleSearchSubmit = (e: FormEvent) => {
    e.preventDefault();
    updateParams({ search: searchInput || undefined });
  };

  const toggleSort = (key: SortKey) => {
    if (sort === key) updateParams({ sort: key, direction: direction === 'asc' ? 'desc' : 'asc' });
    else updateParams({ sort: key, direction: 'asc' });
  };
  const sortIndicator = (key: SortKey) => (sort === key ? (direction === 'asc' ? ' ↑' : ' ↓') : '');

  const handleDeleteTask = async () => {
    if (!deletingTask) return;
    try {
      await deleteTask({ projectUuid, taskUuid: deletingTask.uuid }).unwrap();
      notify('Task deleted.', 'success');
      setDeletingTask(null);
    } catch (err) {
      notify(getErrorMessage(err as Parameters<typeof getErrorMessage>[0]), 'error');
    }
  };

  if (project.error) {
    return (
      <DashboardLayout breadcrumb="Projects">
        <ErrorState error={project.error} onRetry={project.refetch} />
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout breadcrumb={project.data ? `Projects / ${project.data.data.title}` : 'Projects'}>
      <p style={{ marginTop: -8, marginBottom: 16 }}>
        <Link to="/projects">← Back to projects</Link>
      </p>

      <div className={`card ${styles.header}`} style={{ padding: 24 }}>
        {project.isLoading || !project.data ? (
          <Skeleton width={240} height={24} />
        ) : (
          <div>
            <h1 className={styles.title}>{project.data.data.title}</h1>
            {project.data.data.description && <p className={styles.description}>{project.data.data.description}</p>}
            <div className={styles.metaRow}>
              <Badge color={projectStatusColor(project.data.data.status)}>
                {projectStatusLabel(project.data.data.status)}
              </Badge>
              <span style={{ fontSize: 13, color: 'var(--color-text-muted)' }}>
                {project.data.data.tasks_count ?? 0} task{project.data.data.tasks_count === 1 ? '' : 's'}
              </span>
            </div>
          </div>
        )}
        <Button variant="secondary" onClick={() => setEditingProject(true)}>
          Edit project
        </Button>
      </div>

      <h2 className={styles.sectionTitle}>Tasks</h2>

      <div className="toolbar">
        <form onSubmit={handleSearchSubmit} style={{ display: 'flex', gap: 8 }}>
          <Input
            placeholder="Search tasks..."
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            style={{ width: 220 }}
          />
          <Button type="submit" variant="secondary">
            Search
          </Button>
        </form>
        <Select value={status ?? ''} onChange={(e) => updateParams({ status: e.target.value || undefined })} style={{ width: 150 }}>
          <option value="">All statuses</option>
          {TASK_STATUSES.map((option) => (
            <option key={option} value={option}>
              {taskStatusLabel(option)}
            </option>
          ))}
        </Select>
        <Select value={priority ?? ''} onChange={(e) => updateParams({ priority: e.target.value || undefined })} style={{ width: 150 }}>
          <option value="">All priorities</option>
          {TASK_PRIORITIES.map((option) => (
            <option key={option} value={option}>
              {taskPriorityLabel(option)}
            </option>
          ))}
        </Select>
        <div className="toolbar-spacer" />
        <Button onClick={() => setCreatingTask(true)}>New task</Button>
      </div>

      {tasks.error ? (
        <ErrorState error={tasks.error} onRetry={tasks.refetch} />
      ) : tasks.isLoading ? (
        <TableSkeleton columns={5} />
      ) : !tasks.data || tasks.data.data.length === 0 ? (
        <EmptyState
          title="No tasks yet"
          description={search || status || priority ? 'No tasks match your filters.' : 'Add the first task for this project.'}
          action={!search && !status && !priority ? <Button onClick={() => setCreatingTask(true)}>New task</Button> : undefined}
        />
      ) : (
        <div className="table-wrap" style={{ opacity: tasks.isFetching ? 0.6 : 1 }}>
          <table className="table">
            <thead>
              <tr>
                <th className="table-sortable" onClick={() => toggleSort('title')}>
                  Title{sortIndicator('title')}
                </th>
                <th className="table-sortable" onClick={() => toggleSort('status')}>
                  Status{sortIndicator('status')}
                </th>
                <th className="table-sortable" onClick={() => toggleSort('priority')}>
                  Priority{sortIndicator('priority')}
                </th>
                <th className="table-sortable" onClick={() => toggleSort('due_date')}>
                  Due date{sortIndicator('due_date')}
                </th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {tasks.data.data.map((task) => (
                <tr key={task.uuid}>
                  <td>{task.title}</td>
                  <td>
                    <Badge color={taskStatusColor(task.status)}>{taskStatusLabel(task.status)}</Badge>
                  </td>
                  <td>
                    <Badge color={taskPriorityColor(task.priority)}>{taskPriorityLabel(task.priority)}</Badge>
                  </td>
                  <td>
                    {task.due_date ? new Date(task.due_date).toLocaleDateString() : '—'}
                    {task.is_overdue && (
                      <span style={{ marginLeft: 6 }}>
                        <Badge color="red">Overdue</Badge>
                      </span>
                    )}
                  </td>
                  <td>
                    <div style={{ display: 'flex', gap: 6 }}>
                      <Button variant="ghost" size="sm" onClick={() => setEditingTask(task)}>
                        Edit
                      </Button>
                      <Button variant="ghost" size="sm" onClick={() => setDeletingTask(task)}>
                        Delete
                      </Button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          <Pagination meta={tasks.data.meta} onPageChange={(next) => updateParams({ page: String(next) })} />
        </div>
      )}

      {editingProject && project.data && (
        <ProjectFormModal project={project.data.data} onClose={() => setEditingProject(false)} onSaved={() => setEditingProject(false)} />
      )}
      {creatingTask && (
        <TaskFormModal projectUuid={projectUuid} onClose={() => setCreatingTask(false)} onSaved={() => setCreatingTask(false)} />
      )}
      {editingTask && (
        <TaskFormModal
          projectUuid={projectUuid}
          task={editingTask}
          onClose={() => setEditingTask(null)}
          onSaved={() => setEditingTask(null)}
        />
      )}
      {deletingTask && (
        <ConfirmDialog
          title="Delete task"
          message={`Delete "${deletingTask.title}"? This can't be undone from here.`}
          confirmLabel="Delete"
          danger
          loading={isDeleting}
          onConfirm={handleDeleteTask}
          onCancel={() => setDeletingTask(null)}
        />
      )}
    </DashboardLayout>
  );
}
