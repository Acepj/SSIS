import type { AccountStatus, EnrollmentStatus, RequestStatus } from '@/types/common'

/** A student record as returned to administrators. */
export type AdminStudent = {
  id: string
  studentId: string
  fullName: string
  email: string
  contactNumber: string
  program: string
  yearLevel: string
  enrollmentStatus: string
  status: AccountStatus
  createdAt: string
  updatedAt: string
}

/** Input for creating a student via the admin API. */
export type CreateStudentInput = {
  studentId: string
  fullName: string
  email: string
  contactNumber: string
  password: string
  program: string
  yearLevel: string
}

/** Input for updating a student via the admin API. */
export type UpdateStudentInput = {
  fullName?: string
  email?: string
  contactNumber?: string
  program?: string
  yearLevel?: string
  status?: AccountStatus
}

/** An enrollment record as returned to administrators. */
export type AdminEnrollment = {
  id: string
  studentId: string
  academicYear: string
  semester: string
  program: string
  yearLevel: string
  status: EnrollmentStatus
  createdAt: string
  updatedAt: string
}

/** Input for creating an enrollment via the admin API. */
export type CreateEnrollmentInput = {
  studentId: string
  academicYear: string
  semester: string
  program: string
  yearLevel: string
  status: EnrollmentStatus
}

/** Input for updating an enrollment via the admin API. */
export type UpdateEnrollmentInput = {
  program?: string
  yearLevel?: string
  status?: EnrollmentStatus
}

/** A course record as returned to administrators. */
export type AdminCourse = {
  id: string
  courseCode: string
  title: string
  description: string
  program: string
  yearLevel: string
  semester: string
  active: boolean
  createdAt: string
  updatedAt: string
}

/** Input for creating a course via the admin API. */
export type CreateCourseInput = {
  courseCode: string
  title: string
  description: string
  program: string
  yearLevel: string
  semester: string
}

/** Input for updating a course via the admin API. */
export type UpdateCourseInput = {
  title?: string
  description?: string
  program?: string
  yearLevel?: string
  semester?: string
  active?: boolean
}

/** A document request as seen by administrators. */
export type AdminDocumentRequest = {
  id: string
  requestNumber: string
  studentId: string
  studentName: string
  documentType: string
  purpose: string
  copies: number
  deliveryMethod: string
  status: RequestStatus
  processingNotes: string
  createdAt: string
  updatedAt: string
}

/** Payload for updating a document request's status. */
export type UpdateRequestStatusInput = {
  status: RequestStatus
  note?: string
}

/** A notification as seen by administrators. */
export type AdminNotification = {
  id: string
  title: string
  message: string
  recipientUserId: string
  recipientName: string
  readAt: string | null
  createdAt: string
}

/** Input for creating a notification via the admin API. */
export type CreateNotificationInput = {
  recipientUserId: string
  title: string
  message: string
}

/** Report summary statistics. */
export type ReportSummary = {
  totalStudents: number
  activeStudents: number
  totalRequests: number
  pendingRequests: number
  completedRequests: number
  totalEnrollments: number
  enrolledStudents: number
  requestsByStatus: Record<string, number>
  enrollmentsByStatus: Record<string, number>
}