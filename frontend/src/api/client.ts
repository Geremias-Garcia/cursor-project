import { apiUrl } from "./config";
import type { ApiResponse, User } from "../types/user";

const jsonHeaders = {
  Accept: "application/json",
  "Content-Type": "application/json",
  "X-Requested-With": "XMLHttpRequest",
};

function xsrfToken(): string | null {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

  if (!match) {
    return null;
  }

  return decodeURIComponent(match[1]);
}

export class ApiError extends Error {
  readonly status: number;

  constructor(message: string, status: number) {
    super(message);
    this.name = "ApiError";
    this.status = status;
  }
}

async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
  const headers = new Headers(init.headers ?? {});

  for (const [key, value] of Object.entries(jsonHeaders)) {
    if (!headers.has(key)) {
      headers.set(key, value);
    }
  }

  const token = xsrfToken();

  if (token) {
    headers.set("X-XSRF-TOKEN", token);
  }

  const response = await fetch(apiUrl(path), {
    ...init,
    headers,
    credentials: "include",
  });

  if (!response.ok) {
    let message = `Request failed: ${response.status}`;

    try {
      const body = (await response.json()) as { message?: string };
      if (body.message) {
        message = body.message;
      }
    } catch {
      // non-JSON error body
    }

    throw new ApiError(message, response.status);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return (await response.json()) as T;
}

export async function ensureCsrfCookie(): Promise<void> {
  const response = await fetch(apiUrl("/sanctum/csrf-cookie"), {
    credentials: "include",
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  });

  if (!response.ok) {
    throw new ApiError(
      `CSRF cookie request failed: ${response.status}`,
      response.status,
    );
  }
}

export async function fetchCurrentUser(): Promise<User | null> {
  try {
    const payload = await request<ApiResponse<User>>("/api/v1/user");

    return payload.data;
  } catch (error) {
    if (error instanceof ApiError && error.status === 401) {
      return null;
    }

    return null;
  }
}

export async function login(email: string, password: string): Promise<User> {
  await ensureCsrfCookie();

  const payload = await request<ApiResponse<User>>("/api/v1/login", {
    method: "POST",
    body: JSON.stringify({ email, password }),
  });

  return payload.data;
}

export async function logout(): Promise<void> {
  await ensureCsrfCookie();
  await request("/api/v1/logout", { method: "POST" });
}
