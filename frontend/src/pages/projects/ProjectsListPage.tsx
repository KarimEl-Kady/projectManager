import { useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { DashboardLayout } from '../../layouts/DashboardLayout';
import { useDeleteProjectMutation, useListProjectsQuery } from '../../services/projectsApi';
import type { Project, ProjectStatus } from '../../types/project';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/forms/Input';
import { Select } from '../../components/forms/Select';
import { Badge } from '../../components/ui/Badge';
import { Pagination } from '../../components/tables/Pagination';
import { TableSkeleton } from '../../components/loading/Skeleton';
import { EmptyState } from '../../components/errors/EmptyState';
import { ErrorState } from '../../components/errors/ErrorState';
import { ConfirmDialog } from '../../components/modals/ConfirmDialog';
import { ProjectFormModal } from '../../features/projects/ProjectFormModal';
import { PROJECT_STATUSES, projectStatusColor, projectStatusLabel } from '../../features/projects/projectDisplay';
import { useToast } from '../../features/toast/useToast';
import { getErrorMessage } from '../../utils/errors';

type SortKey = 'title' | 'status' | 'created_at' | 'updated_at';

export function ProjectsListPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const navigate = useNavigate();
  const notify = useToast();

  const page = Number(searchParams.get('page') ?? '1');
  const search = searchParams.get('search') ?? '';
  const status = (searchParams.get('status') as ProjectStatus | null) ?? undefined;
  const sort = (searchParams.get('sort') as SortKey | null) ?? 'created_at';
  const direction = (searchParams.get('direction') as 'asc' | 'desc' | null) ?? 'desc';

  const [searchInput, setSearchInput] = useState(search);
  const [editingProject, setEditingProject] = useState<Project | null>(null);
  const [creating, setCreating] = useState(false);
  const [deletingProject, setDeletingProject] = useState<Project | null>(null);

  const { data, isLoading, isFetching, error, refetch } = useListProjectsQuery({
    page,
    search: search || undefined,
    status,
    sort,
    direction,
  });

  const [deleteProject, { isLoading: isDeleting }] = useDeleteProjectMutation();

  const updateParams = (updates: Record<string, string | undefined>) => {
    const next = new URLSearchParams(searchParams);
    for (const [key, value] of Object.entries(updates)) {
      if (value) next.set(key, value);
      else next.delete(key);
    }
    if (!('page' in updates)) next.delete('page');
    setSearchParams(next);
  };

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    updateParams({ search: searchInput || undefined });
  };

  const toggleSort = (key: SortKey) => {
    if (sort === key) {
      updateParams({ sort: key, direction: direction === 'asc' ? 'desc' : 'asc' });
    } else {
      updateParams({ sort: key, direction: 'asc' });
    }
  };

  const handleDelete = async () => {
    if (!deletingProject) return;
    try {
      await deleteProject(deletingProject.uuid).unwrap();
      notify('Project deleted.', 'success');
      setDeletingProject(null);
    } catch (err) {
      notify(getErrorMessage(err as Parameters<typeof getErrorMessage>[0]), 'error');
    }
  };

  const sortIndicator = (key: SortKey) => (sort === key ? (direction === 'asc' ? ' ↑' : ' ↓') : '');

  return (
    <DashboardLayout breadcrumb="Projects">
      <div className="toolbar">
        <form onSubmit={handleSearchSubmit} style={{ display: 'flex', gap: 8 }}>
          <Input
            placeholder="Search projects..."
            value={searchInput}
            onChange={(e) => setSearchInput(e.target.value)}
            style={{ width: 240 }}
          />
          <Button type="submit" variant="secondary">
            Search
          </Button>
        </form>
        <Select
          value={status ?? ''}
          onChange={(e) => updateParams({ status: e.target.value || undefined })}
          style={{ width: 160 }}
        >
          <option value="">All statuses</option>
          {PROJECT_STATUSES.map((option) => (
            <option key={option} value={option}>
              {projectStatusLabel(option)}
            </option>
          ))}
        </Select>
        <div className="toolbar-spacer" />
        <Button onClick={() => setCreating(true)}>New project</Button>
      </div>

      {error ? (
        <ErrorState error={error} onRetry={refetch} />
      ) : isLoading ? (
        <TableSkeleton columns={4} />
      ) : !data || data.data.length === 0 ? (
        <EmptyState
          title="No projects yet"
          description={search || status ? 'No projects match your filters.' : 'Create your first project to get started.'}
          action={!search && !status ? <Button onClick={() => setCreating(true)}>New project</Button> : undefined}
        />
      ) : (
        <div className="table-wrap" style={{ opacity: isFetching ? 0.6 : 1 }}>
          <table className="table">
            <thead>
              <tr>
                <th className="table-sortable" onClick={() => toggleSort('title')}>
                  Title{sortIndicator('title')}
                </th>
                <th className="table-sortable" onClick={() => toggleSort('status')}>
                  Status{sortIndicator('status')}
                </th>
                <th>Tasks</th>
                <th className="table-sortable" onClick={() => toggleSort('updated_at')}>
                  Updated{sortIndicator('updated_at')}
                </th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {data.data.map((project) => (
                <tr key={project.uuid} onClick={() => navigate(`/projects/${project.uuid}`)}>
                  <td>{project.title}</td>
                  <td>
                    <Badge color={projectStatusColor(project.status)}>{projectStatusLabel(project.status)}</Badge>
                  </td>
                  <td>{project.tasks_count ?? '-'}</td>
                  <td>{new Date(project.updated_at).toLocaleDateString()}</td>
                  <td onClick={(e) => e.stopPropagation()}>
                    <div style={{ display: 'flex', gap: 6 }}>
                      <Button variant="ghost" size="sm" onClick={() => setEditingProject(project)}>
                        Edit
                      </Button>
                      <Button variant="ghost" size="sm" onClick={() => setDeletingProject(project)}>
                        Delete
                      </Button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          <Pagination meta={data.meta} onPageChange={(next) => updateParams({ page: String(next) })} />
        </div>
      )}

      {creating && (
        <ProjectFormModal
          onClose={() => setCreating(false)}
          onSaved={() => setCreating(false)}
        />
      )}
      {editingProject && (
        <ProjectFormModal
          project={editingProject}
          onClose={() => setEditingProject(null)}
          onSaved={() => setEditingProject(null)}
        />
      )}
      {deletingProject && (
        <ConfirmDialog
          title="Delete project"
          message={`Delete "${deletingProject.title}" and all of its tasks? This can't be undone from here.`}
          confirmLabel="Delete"
          danger
          loading={isDeleting}
          onConfirm={handleDelete}
          onCancel={() => setDeletingProject(null)}
        />
      )}
    </DashboardLayout>
  );
}
