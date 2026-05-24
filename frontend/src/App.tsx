import { useEffect, useState, type FormEvent } from "react";
import { apiBaseUrl, usesViteProxy } from "./api/config";
import { ApiError, fetchCurrentUser, login, logout } from "./api/client";
import type { User } from "./types/user";

function App() {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchCurrentUser()
      .then(setUser)
      .finally(() => setLoading(false));
  }, []);

  async function handleLogin(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);

    const form = new FormData(event.currentTarget);

    try {
      const authenticated = await login(
        String(form.get("email")),
        String(form.get("password")),
      );
      setUser(authenticated);
    } catch (err) {
      const hint =
        err instanceof ApiError && err.status === 419
          ? "CSRF/session error — use http://localhost:8080 or ensure Vite proxy is running."
          : "Login failed. Check credentials and API connectivity.";
      setError(hint);
    }
  }

  async function handleLogout() {
    await logout();
    setUser(null);
  }

  return (
    <main className="mx-auto flex min-h-screen max-w-lg flex-col gap-8 px-6 py-12">
      <header>
        <p className="text-sm font-medium uppercase tracking-wide text-slate-500">
          Phase 1 — Foundation
        </p>
        <h1 className="mt-2 text-3xl font-semibold text-slate-900">
          Auction Platform
        </h1>
        <p className="mt-2 text-slate-600">
          Sanctum SPA via{" "}
          {apiBaseUrl() || (usesViteProxy() ? "Vite proxy" : "same-origin")}{" "}
          → Laravel <code className="text-sm">/api/v1</code>
        </p>
      </header>

      {loading ? (
        <p className="text-slate-500">Loading session…</p>
      ) : user ? (
        <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
          <p className="text-sm text-slate-500">Signed in as</p>
          <p className="mt-1 text-lg font-medium text-slate-900">{user.name}</p>
          <p className="text-slate-600">{user.email}</p>
          <p className="mt-2 inline-block rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700">
            Role: {user.role}
          </p>
          <button
            type="button"
            onClick={handleLogout}
            className="mt-6 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
          >
            Log out
          </button>
        </section>
      ) : (
        <form
          onSubmit={handleLogin}
          className="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
        >
          <h2 className="text-lg font-medium text-slate-900">Sign in</h2>
          <label className="flex flex-col gap-1 text-sm text-slate-700">
            Email
            <input
              name="email"
              type="email"
              required
              defaultValue="user@example.com"
              className="rounded-lg border border-slate-300 px-3 py-2"
            />
          </label>
          <label className="flex flex-col gap-1 text-sm text-slate-700">
            Password
            <input
              name="password"
              type="password"
              required
              defaultValue="password"
              className="rounded-lg border border-slate-300 px-3 py-2"
            />
          </label>
          {error ? <p className="text-sm text-red-600">{error}</p> : null}
          <button
            type="submit"
            className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
          >
            Log in
          </button>
        </form>
      )}
    </main>
  );
}

export default App;
