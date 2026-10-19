# SSIS — Student Services Information System

A full-stack university student services application: students access services,
submit document requests and track them; administrators manage students,
requests, enrollments, courses, notifications and reports.

## Architecture

```
React SPA (Vite, TypeScript, Tailwind)
   │  fetch, JSON envelope, session cookie, CSRF header
   ▼
Plain PHP 8.x REST API  (/api/v1, front controller + router)
   │  Controllers → Services → Repositories
   ▼
JSON file store (backend/storage/data/*.json — flock + atomic writes)
```

- **No database by design.** Data persists in JSON files behind a repository
  layer (`JsonStore`) that can be swapped for a real database later.
- **Session authentication.** PHP sessions are authoritative; roles are read
  from server-side records, never from the browser.
- **Separate portals.** `/student/*` and `/admin/*` with independent layouts
  and navigation.

### Repository layout

```
frontend/   React + TypeScript SPA
backend/    Plain PHP REST API + JSON storage (see backend/README.md)
```

## Running locally

### 1. Frontend

```bash
cd frontend
npm install
npm run dev          # http://localhost:5173, proxies /api to :8000
```

### 2. Backend (Phase 2+)

The PHP API will run with:

```bash
C:\xammp\php\php.exe -S 127.0.0.1:8000 -t backend/public backend/public/index.php
```

(PHP is not on PATH on this machine; PHP 8.2.12 lives at `C:\xammp\php\php.exe`.)

## Design system

| Token            | Value      | Use                          |
| ---------------- | ---------- | ---------------------------- |
| Primary blue     | `#1763D1`  | Actions, links, focus rings  |
| Dark navy        | `#071D35`  | Auth brand panel             |
| Sidebar navy     | `#08213B`  | Portal sidebars              |
| Background       | `#F3F6FB`  | Page background              |
| Card             | `#FFFFFF`  | Cards, forms, tables         |
| Text             | `#15243A`  | Primary text                 |
| Muted text       | `#718096`  | Secondary text               |
| Border           | `#E1E8F0`  | Borders, dividers            |
| Success/Warning/Error | `#16A36A` / `#E9A23B` / `#DC4545` | Status |

## Implementation status

- [x] Phase 1 — Frontend foundation: design system, shared components,
      split-screen login UI, router, API client, auth feature module
- [ ] Phase 2 — PHP backend foundation + JSON storage
- [ ] Phase 3 — Authentication end-to-end
- [ ] Phase 4 — Student portal
- [ ] Phase 5 — Admin portal
- [ ] Phase 6 — Integration, tests, docs

## Known limitations

- JSON file storage is for demonstration only: no transactions across files,
  O(n) scans per request, single-node only. See backend docs once Phase 2 is
  implemented.
- No outbound email: password resets are handled by Student Services staff.
- Login form is fully built but requires the Phase 2/3 backend to authenticate.

## Version control

Local Git repository. Commit per completed phase.
