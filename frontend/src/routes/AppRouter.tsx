import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'

import { ProtectedRoute } from '@/features/auth/ProtectedRoute'
import { RoleGuard } from '@/features/auth/RoleGuard'
import AuthLayout from '@/layouts/AuthLayout'
import ForgotPasswordPage from '@/pages/auth/ForgotPasswordPage'
import LoginPage from '@/pages/auth/LoginPage'
import UnauthorizedPage from '@/pages/auth/UnauthorizedPage'
import NotFoundPage from '@/pages/errors/NotFoundPage'
import { PendingPhasePage } from './PendingPhasePage'
import { routePaths } from './routePaths'

const STUDENT_PHASE = 'Phase 4 - Student Portal'
const ADMIN_PHASE = 'Phase 5 - Admin Portal'

/** [path pattern, page title] for portal routes whose pages arrive later. */
const pendingStudentRoutes: Array<[string, string]> = [
  ['/student/dashboard', 'Dashboard'],
  ['/student/enrollment', 'Enrollment'],
  ['/student/document-request', 'Document Request'],
  ['/student/request-status', 'Request Status'],
  ['/student/requests/:id', 'Request Details'],
  ['/student/courses', 'Courses'],
  ['/student/courses/:id', 'Course Details'],
  ['/student/profile', 'Profile'],
  ['/student/notifications', 'Notifications'],
  ['/student/help', 'Help & Support'],
]

const pendingAdminRoutes: Array<[string, string]> = [
  ['/admin/dashboard', 'Dashboard'],
  ['/admin/students', 'Student Management'],
  ['/admin/students/new', 'Create Student'],
  ['/admin/students/:id', 'Student Details'],
  ['/admin/students/:id/edit', 'Edit Student'],
  ['/admin/requests', 'Document Requests'],
  ['/admin/requests/:id', 'Request Details'],
  ['/admin/enrollment', 'Enrollment Management'],
  ['/admin/courses', 'Course Management'],
  ['/admin/courses/new', 'Create Course'],
  ['/admin/courses/:id/edit', 'Edit Course'],
  ['/admin/notifications', 'Notifications'],
  ['/admin/reports', 'Reports'],
  ['/admin/profile', 'Admin Profile'],
]

/**
 * Central route definitions. Private routes are wrapped in session and role
 * guards (frontend UX only - the PHP backend enforces both independently).
 */
export default function AppRouter() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path={routePaths.root} element={<Navigate to={routePaths.login} replace />} />

        <Route element={<AuthLayout />}>
          <Route path={routePaths.login} element={<LoginPage />} />
          <Route path={routePaths.forgotPassword} element={<ForgotPasswordPage />} />
        </Route>

        <Route path={routePaths.unauthorized} element={<UnauthorizedPage />} />

        <Route element={<ProtectedRoute />}>
          <Route element={<RoleGuard role="student" />}>
            {pendingStudentRoutes.map(([path, title]) => (
              <Route key={path} path={path} element={<PendingPhasePage title={title} phase={STUDENT_PHASE} />} />
            ))}
          </Route>

          <Route element={<RoleGuard role="admin" />}>
            {pendingAdminRoutes.map(([path, title]) => (
              <Route key={path} path={path} element={<PendingPhasePage title={title} phase={ADMIN_PHASE} />} />
            ))}
          </Route>
        </Route>

        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </BrowserRouter>
  )
}
