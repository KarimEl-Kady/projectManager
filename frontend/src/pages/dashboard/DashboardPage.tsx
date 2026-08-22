import { DashboardLayout } from '../../layouts/DashboardLayout';
import { useGetDashboardQuery } from '../../services/dashboardApi';
import { Skeleton } from '../../components/loading/Skeleton';
import { ErrorState } from '../../components/errors/ErrorState';
import styles from './DashboardPage.module.css';

const STATS: { key: 'total_projects' | 'active_projects' | 'total_tasks' | 'completed_tasks' | 'pending_tasks' | 'overdue_tasks'; label: string }[] = [
  { key: 'total_projects', label: 'Total projects' },
  { key: 'active_projects', label: 'Active projects' },
  { key: 'total_tasks', label: 'Total tasks' },
  { key: 'completed_tasks', label: 'Completed tasks' },
  { key: 'pending_tasks', label: 'Pending tasks' },
  { key: 'overdue_tasks', label: 'Overdue tasks' },
];

export function DashboardPage() {
  const { data, isLoading, error, refetch } = useGetDashboardQuery();

  return (
    <DashboardLayout breadcrumb="Dashboard">
      {error ? (
        <ErrorState error={error} onRetry={refetch} />
      ) : (
        <div className={styles.grid}>
          {STATS.map((stat) => (
            <div key={stat.key} className={`card ${styles.stat}`}>
              <p className={styles.statLabel}>{stat.label}</p>
              {isLoading || !data ? (
                <Skeleton width={60} height={28} />
              ) : (
                <p className={styles.statValue}>{data.data[stat.key]}</p>
              )}
            </div>
          ))}
        </div>
      )}
    </DashboardLayout>
  );
}
