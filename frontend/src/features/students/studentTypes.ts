import type { RequestStatus } from '@/types/common'

export interface StudentProfile {
  id: string
  studentId: string
  fullName: string
  email: string
  contactNumber: string
  program: string
  yearLevel: string
  enrollmentStatus: string
}

export interface ProfileUpdateInput {
  email: string
  contactNumber: string
}

export interface DashboardRequest {
  id: string
  requestNumber: string
  documentType: string
  status: RequestStatus
  createdAt: string
}

export interface DashboardNotification {
  id: string
  title: string
  message: string
  readAt: string | null
  createdAt: string
}

export interface StudentDashboard {
  academic: { year: string; semester: string }
  enrollment: { status: string; term: string } | null
  counts: { pending: number; completed: number; unreadNotifications: number }
  recentRequests: DashboardRequest[]
  recentNotifications: DashboardNotification[]
}
