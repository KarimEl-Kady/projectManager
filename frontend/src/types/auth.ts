import type { User } from './user';

export interface RegisterRequest {
  name: string;
  email: string;
  phone: string;
  password: string;
  password_confirmation: string;
  device_name?: string;
}

export interface LoginRequest {
  email: string;
  password: string;
  device_name?: string;
}

export interface AuthResponse {
  message: string;
  data: {
    user: User;
    token: string;
    token_type: string;
  };
}

export interface MessageResponse {
  message: string;
}
