import { useState } from 'react';
import type { FormEvent } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { AuthLayout } from '../../layouts/AuthLayout';
import { FormField } from '../../components/forms/FormField';
import { Input } from '../../components/forms/Input';
import { Button } from '../../components/ui/Button';
import { useRegisterMutation } from '../../services/authApi';
import { getErrorMessage, getFieldErrors, isValidationError } from '../../utils/errors';
import { useToast } from '../../features/toast/useToast';

export function RegisterPage() {
  const [form, setForm] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
  });
  const [register, { isLoading, error }] = useRegisterMutation();
  const navigate = useNavigate();
  const notify = useToast();

  const fieldErrors = getFieldErrors(error);

  const update = (field: keyof typeof form) => (e: React.ChangeEvent<HTMLInputElement>) =>
    setForm((prev) => ({ ...prev, [field]: e.target.value }));

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    try {
      await register(form).unwrap();
      notify('Account created successfully.', 'success');
      navigate('/dashboard', { replace: true });
    } catch {
      // error state is rendered below
    }
  };

  return (
    <AuthLayout title="Create an account" subtitle="Start organizing your projects and tasks.">
      <form onSubmit={handleSubmit} noValidate>
        <FormField label="Name" htmlFor="name" error={fieldErrors.name}>
          <Input id="name" required hasError={Boolean(fieldErrors.name)} value={form.name} onChange={update('name')} />
        </FormField>
        <FormField label="Email" htmlFor="email" error={fieldErrors.email}>
          <Input
            id="email"
            type="email"
            required
            hasError={Boolean(fieldErrors.email)}
            value={form.email}
            onChange={update('email')}
          />
        </FormField>
        <FormField label="Phone" htmlFor="phone" error={fieldErrors.phone}>
          <Input id="phone" required hasError={Boolean(fieldErrors.phone)} value={form.phone} onChange={update('phone')} />
        </FormField>
        <FormField label="Password" htmlFor="password" error={fieldErrors.password} hint="At least 8 characters, letters and numbers.">
          <Input
            id="password"
            type="password"
            required
            hasError={Boolean(fieldErrors.password)}
            value={form.password}
            onChange={update('password')}
          />
        </FormField>
        <FormField label="Confirm password" htmlFor="password_confirmation">
          <Input
            id="password_confirmation"
            type="password"
            required
            value={form.password_confirmation}
            onChange={update('password_confirmation')}
          />
        </FormField>

        {error && !isValidationError(error) && (
          <p className="field-error" style={{ marginBottom: 14 }}>
            {getErrorMessage(error)}
          </p>
        )}

        <Button type="submit" loading={isLoading} style={{ width: '100%' }}>
          Create account
        </Button>
      </form>
      <p style={{ marginTop: 20, fontSize: 14, textAlign: 'center' }}>
        Already have an account? <Link to="/login">Log in</Link>
      </p>
    </AuthLayout>
  );
}
