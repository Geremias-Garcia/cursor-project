export type UserRole = "user" | "admin";

export interface User {
  uuid: string;
  name: string;
  email: string;
  role: UserRole;
}

export interface ApiResponse<T> {
  data: T;
}
