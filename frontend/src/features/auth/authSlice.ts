import { createSlice } from '@reduxjs/toolkit';
import type { User } from '../../types/user';
import { tokenStorage, userStorage } from '../../utils/storage';
import { authApi } from '../../services/authApi';

interface AuthState {
  user: User | null;
  token: string | null;
}

const initialState: AuthState = {
  user: userStorage.get<User>(),
  token: tokenStorage.get(),
};

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    credentialsCleared: (state) => {
      state.user = null;
      state.token = null;
      tokenStorage.clear();
      userStorage.clear();
    },
  },
  extraReducers: (builder) => {
    builder
      .addMatcher(authApi.endpoints.login.matchFulfilled, (state, action) => {
        state.user = action.payload.data.user;
        state.token = action.payload.data.token;
        tokenStorage.set(action.payload.data.token);
        userStorage.set(action.payload.data.user);
      })
      .addMatcher(authApi.endpoints.register.matchFulfilled, (state, action) => {
        state.user = action.payload.data.user;
        state.token = action.payload.data.token;
        tokenStorage.set(action.payload.data.token);
        userStorage.set(action.payload.data.user);
      })
      .addMatcher(authApi.endpoints.me.matchFulfilled, (state, action) => {
        state.user = action.payload.data;
        userStorage.set(action.payload.data);
      })
      .addMatcher(authApi.endpoints.logout.matchFulfilled, (state) => {
        state.user = null;
        state.token = null;
        tokenStorage.clear();
        userStorage.clear();
      });
  },
});

export const { credentialsCleared } = authSlice.actions;
export default authSlice.reducer;
