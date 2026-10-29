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
import { routePaths } from './routePaths'
import AdminLayout from '@/layouts/AdminLayout'
import AdminDashboardPage from '@/pages/admin/AdminDashboardPage'
import AdminStudentsPage from '@/pages/admin/AdminStudentsPage'
import AdminStudentDetailsPage from '@/pages/admin/AdminStudentDetailsPage'
import AdminEnrollmentPage from '@/pages/admin/AdminEnrollmentPage'
import AdminCoursesPage from '@/pages/admin/AdminCoursesPage'
import AdminDocumentRequestsPage from '@/pages/admin/AdminDocumentRequestsPage'
import AdminNotificationsPage from '@/pages/admin/AdminNotificationsPage'
import AdminReportsPage from '@/pages/admin/AdminReportsPage'
import AdminProfilePage from '@/pages/admin/AdminProfilePage'
import { PendingPhasePage } from './PendingPhasePage'

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
            <Route element={<AdminLayout />}>
              <Route path={routePaths.admin.dashboard} element={<AdminDashboardPage />} />
              <Route path={routePaths.admin.students} element={<AdminStudentsPage />} />
              <Route path={routePaths.admin.createStudent} element={<PendingPhasePage title="Create Student" phase="Phase 5 - Admin Portal" />} />
              <Route path={routePaths.admin.studentDetails(':id')} element={<AdminStudentDetailsPage />} />
              <Route path={routePaths.admin.editStudent(':id')} element={<PendingPhasePage title="Edit Student" phase="Phase 5 - Admin Portal" />} />
              <Route path={routePaths.admin.enrollment} element={<AdminEnrollmentPage />} />
              <Route path={routePaths.admin.courses} element={<AdminCoursesPage />} />
              <Route path={routePaths.admin.createCourse} element={<PendingPhasePage title="Create Course" phase="Phase 5 - Admin Portal" />} />
              <Route path={routePaths.admin.editCourse(':id')} element={<PendingPhasePage title="Edit Course" phase="Phase 5 - Admin Portal" />} />
              <Route path={routePaths.admin.requests} element={<AdminDocumentRequestsPage />} />
              <Route path={routePaths.admin.requestDetails(':id')} element={<PendingPhasePage title="Request Details" phase="Phase 5 - Admin Portal" />} />
              <Route path={routePaths.admin.notifications} element={<AdminNotificationsPage />} />
              <Route path={routePaths.admin.reports} element={<AdminReportsPage />} />
              <Route path={routePaths.admin.profile} element={<AdminProfilePage />} />
            </Route>
          </Route>
        </Route>

        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </BrowserRouter>
  )
}
