import { useState } from 'react';
import type { FormEvent } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { AuthLayout } from '../../layouts/AuthLayout';
import { FormField } from '../../components/forms/FormField';
import { Input } from '../../components/forms/Input';
import { Button } from '../../components/ui/Button';
import { useLoginMutation } from '../../services/authApi';
import { getErrorMessage, getFieldErrors, isValidationError } from '../../utils/errors';
import { useToast } from '../../features/toast/useToast';

export function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [login, { isLoading, error }] = useLoginMutation();
  const navigate = useNavigate();
  const notify = useToast();

  const fieldErrors = getFieldErrors(error);

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    try {
      await login({ email, password }).unwrap();
      notify('Welcome back!', 'success');
      navigate('/dashboard', { replace: true });
    } catch {
      // error state is rendered below
    }
  };

  return (
    <AuthLayout title="Log in" subtitle="Access your projects and tasks.">
      <form onSubmit={handleSubmit} noValidate>
        <FormField label="Email" htmlFor="email" error={fieldErrors.email}>
          <Input
            id="email"
            type="email"
            autoComplete="email"
            required
            hasError={Boolean(fieldErrors.email)}
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
        </FormField>
        <FormField label="Password" htmlFor="password" error={fieldErrors.password}>
          <Input
            id="password"
            type="password"
            autoComplete="current-password"
            required
            hasError={Boolean(fieldErrors.password)}
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
        </FormField>

        {error && !isValidationError(error) && (
          <p className="field-error" style={{ marginBottom: 14 }}>
            {getErrorMessage(error)}
          </p>
        )}

        <Button type="submit" loading={isLoading} style={{ width: '100%' }}>
          Log in
        </Button>
      </form>
      <p style={{ marginTop: 20, fontSize: 14, textAlign: 'center' }}>
        Don't have an account? <Link to="/register">Create one</Link>
      </p>
    </AuthLayout>
  );
}
