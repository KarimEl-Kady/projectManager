import { createSlice, nanoid } from '@reduxjs/toolkit';

export type ToastVariant = 'success' | 'error' | 'info';

export interface Toast {
  id: string;
  message: string;
  variant: ToastVariant;
}

const initialState: Toast[] = [];

const toastSlice = createSlice({
  name: 'toast',
  initialState,
  reducers: {
    toastShown: {
      reducer: (state, action: { payload: Toast }) => {
        state.push(action.payload);
      },
      prepare: (message: string, variant: ToastVariant = 'info') => ({
        payload: { id: nanoid(), message, variant },
      }),
    },
    toastDismissed: (state, action: { payload: string }) => {
      return state.filter((toast) => toast.id !== action.payload);
    },
  },
});

export const { toastShown, toastDismissed } = toastSlice.actions;
export default toastSlice.reducer;
