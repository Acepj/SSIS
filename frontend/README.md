# SSIS Frontend — Student Services Information System

React + TypeScript + Vite single-page application for SSIS. Talks to the plain
PHP REST API in `../backend` over JSON (session cookie auth + CSRF).

## Stack

- React 19 + TypeScript (strict) + Vite
- Tailwind CSS v4 (design tokens in `src/index.css`)
- React Router v7 (central routes in `src/routes/`)
- React Hook Form + Zod (validation schemas live in `src/features/*`)
- TanStack Query (server state; used by portal pages)
- Lucide React (icons), date-fns (dates), shadcn/ui-style components in `src/components/ui/`

## Setup

```bash
cd frontend
npm install
cp .env.example .env        # optional in dev - defaults are fine
npm run dev                 # http://localhost:5173
```

In development, Vite proxies `/api/*` to the PHP server at
`http://127.0.0.1:8000` (override with the `VITE_PROXY_TARGET` environment
variable). Start the PHP backend first — see `../backend/README.md`.

## Scripts

| Command         | Purpose                                    |
| --------------- | ------------------------------------------ |
| `npm run dev`   | Start dev server with hot reload           |
| `npm run build` | Type-check (`tsc -b`) + production build   |
| `npm run lint`  | ESLint (flat config)                       |
| `npm run preview` | Serve the production build               |

## Environment Variables

Only non-sensitive values belong here (see `.env.example`):

- `VITE_API_BASE_URL` — API prefix, default `/api/v1`
- `VITE_PROXY_TARGET` — dev proxy target, default `http://127.0.0.1:8000`

Never put secrets in frontend env vars — everything in the bundle is public.

## How it talks to the backend

1. `src/lib/apiClient.ts` is the single `fetch` wrapper: JSON envelope parsing,
   `credentials: 'include'` (session cookie), automatic CSRF token handling for
   state-changing requests, typed `ApiError` (status/code/message/field errors),
   and a global `ssis:session-expired` event on 401.
2. Feature modules in `src/features/<domain>/` expose typed API functions and
   Zod schemas. Pages never call `fetch` directly.
3. Roles come from the server session (`GET /auth/me` / login response) — the
  frontend never decides authorization; route guards are UX only.

## Structure (abbreviated)

```
src/
  components/   ui/ (shadcn-style), forms/, common/, layout/
  layouts/      AuthLayout (split-screen auth shell)
  pages/        auth/, student/, admin/, errors/
  features/     auth/ (students, documents, ... added in later phases)
  lib/          apiClient, queryClient, dateUtils, utils
  routes/       AppRouter, routePaths
  types/        shared API + domain types
  config/       appConfig (non-sensitive settings)
```
