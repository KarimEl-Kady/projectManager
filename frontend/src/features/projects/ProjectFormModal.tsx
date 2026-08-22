import { useState } from 'react';
import type { FormEvent } from 'react';
import { Modal } from '../../components/modals/Modal';
import { FormField } from '../../components/forms/FormField';
import { Input } from '../../components/forms/Input';
import { TextArea } from '../../components/forms/TextArea';
import { Select } from '../../components/forms/Select';
import { Button } from '../../components/ui/Button';
import { useCreateProjectMutation, useUpdateProjectMutation } from '../../services/projectsApi';
import type { Project } from '../../types/project';
import { getErrorMessage, getFieldErrors, isValidationError } from '../../utils/errors';
import { useToast } from '../toast/useToast';
import { PROJECT_STATUSES, projectStatusLabel } from './projectDisplay';

interface ProjectFormModalProps {
  project?: Project;
  onClose: () => void;
  onSaved: () => void;
}

export function ProjectFormModal({ project, onClose, onSaved }: ProjectFormModalProps) {
  const isEdit = Boolean(project);
  const [title, setTitle] = useState(project?.title ?? '');
  const [description, setDescription] = useState(project?.description ?? '');
  const [status, setStatus] = useState(project?.status ?? 'active');

  const [createProject, createState] = useCreateProjectMutation();
  const [updateProject, updateState] = useUpdateProjectMutation();
  const notify = useToast();

  const { isLoading } = isEdit ? updateState : createState;
  const error = isEdit ? updateState.error : createState.error;
  const fieldErrors = getFieldErrors(error);

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    try {
      if (isEdit && project) {
        await updateProject({ uuid: project.uuid, body: { title, description: description || null, status } }).unwrap();
        notify('Project updated.', 'success');
      } else {
        await createProject({ title, description: description || null, status }).unwrap();
        notify('Project created.', 'success');
      }
      onSaved();
    } catch {
      // error rendered below
    }
  };

  return (
    <Modal title={isEdit ? 'Edit project' : 'New project'} onClose={onClose}>
      <form onSubmit={handleSubmit} noValidate>
        <FormField label="Title" htmlFor="title" error={fieldErrors.title}>
          <Input id="title" required hasError={Boolean(fieldErrors.title)} value={title} onChange={(e) => setTitle(e.target.value)} />
        </FormField>
        <FormField label="Description" htmlFor="description" error={fieldErrors.description}>
          <TextArea
            id="description"
            hasError={Boolean(fieldErrors.description)}
            value={description}
            onChange={(e) => setDescription(e.target.value)}
          />
        </FormField>
        <FormField label="Status" htmlFor="status" error={fieldErrors.status}>
          <Select id="status" value={status} onChange={(e) => setStatus(e.target.value as typeof status)}>
            {PROJECT_STATUSES.map((option) => (
              <option key={option} value={option}>
                {projectStatusLabel(option)}
              </option>
            ))}
          </Select>
        </FormField>

        {error && !isValidationError(error) && <p className="field-error">{getErrorMessage(error)}</p>}

        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 8 }}>
          <Button type="button" variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button type="submit" loading={isLoading}>
            {isEdit ? 'Save changes' : 'Create project'}
          </Button>
        </div>
      </form>
    </Modal>
  );
}
