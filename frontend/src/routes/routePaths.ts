import type { UserRole } from '@/types/common'

/** Central route constants for the whole application. */
export const routePaths = {
  root: '/',
  login: '/login',
  forgotPassword: '/forgot-password',
  unauthorized: '/unauthorized',
  student: {
    dashboard: '/student/dashboard',
    enrollment: '/student/enrollment',
    documentRequest: '/student/document-request',
    requestStatus: '/student/request-status',
    requestDetails: (id: string) => `/student/requests/${encodeURIComponent(id)}`,
    courses: '/student/courses',
    courseDetails: (id: string) => `/student/courses/${encodeURIComponent(id)}`,
    profile: '/student/profile',
    notifications: '/student/notifications',
    help: '/student/help',
  },
  admin: {
    dashboard: '/admin/dashboard',
    students: '/admin/students',
    createStudent: '/admin/students/new',
    studentDetails: (id: string) => `/admin/students/${encodeURIComponent(id)}`,
    editStudent: (id: string) => `/admin/students/${encodeURIComponent(id)}/edit`,
    requests: '/admin/requests',
    requestDetails: (id: string) => `/admin/requests/${encodeURIComponent(id)}`,
    enrollment: '/admin/enrollment',
    courses: '/admin/courses',
    createCourse: '/admin/courses/new',
    editCourse: (id: string) => `/admin/courses/${encodeURIComponent(id)}/edit`,
    notifications: '/admin/notifications',
    reports: '/admin/reports',
    profile: '/admin/profile',
  },
  defaultForRole(role: UserRole): string {
    return role === 'admin' ? this.admin.dashboard : this.student.dashboard
  },
} as const
