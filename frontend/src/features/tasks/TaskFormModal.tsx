import { useState } from 'react';
import type { FormEvent } from 'react';
import { Modal } from '../../components/modals/Modal';
import { FormField } from '../../components/forms/FormField';
import { Input } from '../../components/forms/Input';
import { TextArea } from '../../components/forms/TextArea';
import { Select } from '../../components/forms/Select';
import { Button } from '../../components/ui/Button';
import { useCreateTaskMutation, useUpdateTaskMutation } from '../../services/tasksApi';
import type { Task } from '../../types/task';
import { getErrorMessage, getFieldErrors, isValidationError } from '../../utils/errors';
import { useToast } from '../toast/useToast';
import { TASK_PRIORITIES, TASK_STATUSES, taskPriorityLabel, taskStatusLabel } from './taskDisplay';

interface TaskFormModalProps {
  projectUuid: string;
  task?: Task;
  onClose: () => void;
  onSaved: () => void;
}

export function TaskFormModal({ projectUuid, task, onClose, onSaved }: TaskFormModalProps) {
  const isEdit = Boolean(task);
  const [title, setTitle] = useState(task?.title ?? '');
  const [description, setDescription] = useState(task?.description ?? '');
  const [status, setStatus] = useState(task?.status ?? 'todo');
  const [priority, setPriority] = useState(task?.priority ?? 'medium');
  const [dueDate, setDueDate] = useState(task?.due_date ?? '');

  const [createTask, createState] = useCreateTaskMutation();
  const [updateTask, updateState] = useUpdateTaskMutation();
  const notify = useToast();

  const { isLoading } = isEdit ? updateState : createState;
  const error = isEdit ? updateState.error : createState.error;
  const fieldErrors = getFieldErrors(error);

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    const body = {
      title,
      description: description || null,
      status,
      priority,
      due_date: dueDate || null,
    };
    try {
      if (isEdit && task) {
        await updateTask({ projectUuid, taskUuid: task.uuid, body }).unwrap();
        notify('Task updated.', 'success');
      } else {
        await createTask({ projectUuid, body }).unwrap();
        notify('Task created.', 'success');
      }
      onSaved();
    } catch {
      // error rendered below
    }
  };

  return (
    <Modal title={isEdit ? 'Edit task' : 'New task'} onClose={onClose}>
      <form onSubmit={handleSubmit} noValidate>
        <FormField label="Title" htmlFor="task-title" error={fieldErrors.title}>
          <Input id="task-title" required hasError={Boolean(fieldErrors.title)} value={title} onChange={(e) => setTitle(e.target.value)} />
        </FormField>
        <FormField label="Description" htmlFor="task-description" error={fieldErrors.description}>
          <TextArea
            id="task-description"
            hasError={Boolean(fieldErrors.description)}
            value={description}
            onChange={(e) => setDescription(e.target.value)}
          />
        </FormField>
        <div style={{ display: 'flex', gap: 12 }}>
          <div style={{ flex: 1 }}>
            <FormField label="Status" htmlFor="task-status" error={fieldErrors.status}>
              <Select id="task-status" value={status} onChange={(e) => setStatus(e.target.value as typeof status)}>
                {TASK_STATUSES.map((option) => (
                  <option key={option} value={option}>
                    {taskStatusLabel(option)}
                  </option>
                ))}
              </Select>
            </FormField>
          </div>
          <div style={{ flex: 1 }}>
            <FormField label="Priority" htmlFor="task-priority" error={fieldErrors.priority}>
              <Select id="task-priority" required value={priority} onChange={(e) => setPriority(e.target.value as typeof priority)}>
                {TASK_PRIORITIES.map((option) => (
                  <option key={option} value={option}>
                    {taskPriorityLabel(option)}
                  </option>
                ))}
              </Select>
            </FormField>
          </div>
        </div>
        <FormField label="Due date" htmlFor="task-due-date" error={fieldErrors.due_date}>
          <Input
            id="task-due-date"
            type="date"
            hasError={Boolean(fieldErrors.due_date)}
            value={dueDate ?? ''}
            onChange={(e) => setDueDate(e.target.value)}
          />
        </FormField>

        {error && !isValidationError(error) && <p className="field-error">{getErrorMessage(error)}</p>}

        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 8 }}>
          <Button type="button" variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button type="submit" loading={isLoading}>
            {isEdit ? 'Save changes' : 'Create task'}
          </Button>
        </div>
      </form>
    </Modal>
  );
}
