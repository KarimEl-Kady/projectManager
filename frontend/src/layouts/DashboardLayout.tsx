import type { ReactNode } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import styles from './DashboardLayout.module.css';
import { useAppSelector } from '../app/hooks';
import { useLogoutMutation } from '../services/authApi';
import { Button } from '../components/ui/Button';
import { useToast } from '../features/toast/useToast';

const navItems = [
  { to: '/dashboard', label: 'Dashboard' },
  { to: '/projects', label: 'Projects' },
];

export function DashboardLayout({ children, breadcrumb }: { children: ReactNode; breadcrumb?: string }) {
  const user = useAppSelector((state) => state.auth.user);
  const [logout, { isLoading }] = useLogoutMutation();
  const navigate = useNavigate();
  const notify = useToast();

  const handleLogout = async () => {
    try {
      await logout().unwrap();
    } catch {
      // token is cleared client-side regardless via the auth slice matcher
    } finally {
      notify('Logged out successfully.', 'success');
      navigate('/login', { replace: true });
    }
  };

  return (
    <div className={styles.shell}>
      <aside className={styles.sidebar}>
        <div className={styles.brand}>Project Manager</div>
        {navItems.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            className={({ isActive }) => `${styles.navLink} ${isActive ? styles.navLinkActive : ''}`}
          >
            {item.label}
          </NavLink>
        ))}
      </aside>
      <div className={styles.main}>
        <header className={styles.header}>
          <div>{breadcrumb && <p className={styles.breadcrumbs}>{breadcrumb}</p>}</div>
          <div className={styles.headerUser}>
            {user && (
              <span>
                <span className={styles.userName}>{user.name}</span>
              </span>
            )}
            <Button variant="secondary" size="sm" onClick={handleLogout} loading={isLoading}>
              Log out
            </Button>
          </div>
        </header>
        <main className={styles.content}>{children}</main>
      </div>
    </div>
  );
}
