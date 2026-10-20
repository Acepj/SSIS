import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'

import { ProtectedRoute } from '@/features/auth/ProtectedRoute'
import { RoleGuard } from '@/features/auth/RoleGuard'
import AuthLayout from '@/layouts/AuthLayout'
import StudentLayout from '@/layouts/StudentLayout'
import ForgotPasswordPage from '@/pages/auth/ForgotPasswordPage'
import LoginPage from '@/pages/auth/LoginPage'
import UnauthorizedPage from '@/pages/auth/UnauthorizedPage'
import NotFoundPage from '@/pages/errors/NotFoundPage'
import CourseDetailsPage from '@/pages/student/CourseDetailsPage'
import CoursesPage from '@/pages/student/CoursesPage'
import DocumentRequestPage from '@/pages/student/DocumentRequestPage'
import EnrollmentPage from '@/pages/student/EnrollmentPage'
import HelpSupportPage from '@/pages/student/HelpSupportPage'
import NotificationsPage from '@/pages/student/NotificationsPage'
import RequestDetailsPage from '@/pages/student/RequestDetailsPage'
import RequestStatusPage from '@/pages/student/RequestStatusPage'
import StudentDashboardPage from '@/pages/student/StudentDashboardPage'
import StudentProfilePage from '@/pages/student/StudentProfilePage'
import { PendingPhasePage } from './PendingPhasePage'
import { routePaths } from './routePaths'

const ADMIN_PHASE = 'Phase 5 - Admin Portal'

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
            <Route element={<StudentLayout />}>
              <Route path="/student/dashboard" element={<StudentDashboardPage />} />
              <Route path="/student/enrollment" element={<EnrollmentPage />} />
              <Route path="/student/document-request" element={<DocumentRequestPage />} />
              <Route path="/student/request-status" element={<RequestStatusPage />} />
              <Route path="/student/requests/:id" element={<RequestDetailsPage />} />
              <Route path="/student/courses" element={<CoursesPage />} />
              <Route path="/student/courses/:id" element={<CourseDetailsPage />} />
              <Route path="/student/profile" element={<StudentProfilePage />} />
              <Route path="/student/notifications" element={<NotificationsPage />} />
              <Route path="/student/help" element={<HelpSupportPage />} />
            </Route>
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
