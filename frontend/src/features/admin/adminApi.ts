import { apiClient } from '@/lib/apiClient'
import type {
  AdminDocumentRequest,
  AdminEnrollment,
  AdminNotification,
  AdminStudent,
  CreateEnrollmentInput,
  CreateNotificationInput,
  CreateStudentInput,
  CreateCourseInput,
  ReportSummary,
  UpdateCourseInput,
  UpdateEnrollmentInput,
  UpdateRequestStatusInput,
  UpdateStudentInput,
} from './adminTypes'

// ---------------------------------------------------------------------------
// Students
// ---------------------------------------------------------------------------

export async function getAdminStudents(params?: {
  search?: string
  program?: string
  status?: string
}): Promise<{ students: AdminStudent[] }> {
  const query = new URLSearchParams()
  if (params?.search) query.set('search', params.search)
  if (params?.program) query.set('program', params.program)
  if (params?.status) query.set('status', params.status)
  const qs = query.toString()
  return apiClient.get<{ students: AdminStudent[] }>(
    `/admin/students${qs ? `?${qs}` : ''}`,
  )
}

export async function createAdminStudent(input: CreateStudentInput): Promise<{ student: AdminStudent }> {
  return apiClient.post<{ student: AdminStudent }>('/admin/students', input)
}

export async function getAdminStudent(id: string): Promise<{ student: AdminStudent }> {
  return apiClient.get<{ student: AdminStudent }>(`/admin/students/${encodeURIComponent(id)}`)
}

export async function updateAdminStudent(id: string, input: UpdateStudentInput): Promise<{ student: AdminStudent }> {
  return apiClient.patch<{ student: AdminStudent }>(`/admin/students/${encodeURIComponent(id)}`, input)
}

export async function activateAdminStudent(id: string): Promise<{ student: AdminStudent }> {
  return apiClient.post<{ student: AdminStudent }>(`/admin/students/${encodeURIComponent(id)}/activate`)
}

export async function deactivateAdminStudent(id: string): Promise<{ student: AdminStudent }> {
  return apiClient.post<{ student: AdminStudent }>(`/admin/students/${encodeURIComponent(id)}/deactivate`)
}

// ---------------------------------------------------------------------------
// Enrollments
// ---------------------------------------------------------------------------

export async function getAdminEnrollments(studentId: string, params?: {
  search?: string
  program?: string
  yearLevel?: string
  semester?: string
  academicYear?: string
  status?: string
}): Promise<{ enrollments: AdminEnrollment[] }> {
  const query = new URLSearchParams()
  if (params?.search) query.set('search', params.search)
  if (params?.program) query.set('program', params.program)
  if (params?.yearLevel) query.set('yearLevel', params.yearLevel)
  if (params?.semester) query.set('semester', params.semester)
  if (params?.academicYear) query.set('academicYear', params.academicYear)
  if (params?.status) query.set('status', params.status)
  const qs = query.toString()
  return apiClient.get<{ enrollments: AdminEnrollment[] }>(
    `/admin/students/${encodeURIComponent(studentId)}/enrollments${qs ? `?${qs}` : ''}`,
  )
}

export async function createAdminEnrollment(input: CreateEnrollmentInput): Promise<{ enrollment: AdminEnrollment }> {
  return apiClient.post<{ enrollment: AdminEnrollment }>('/admin/enrollments', input)
}

export async function getAdminEnrollment(id: string): Promise<{ enrollment: AdminEnrollment }> {
  return apiClient.get<{ enrollment: AdminEnrollment }>(`/admin/enrollments/${encodeURIComponent(id)}`)
}

export async function updateAdminEnrollment(
  id: string,
  input: UpdateEnrollmentInput,
): Promise<{ enrollment: AdminEnrollment }> {
  return apiClient.patch<{ enrollment: AdminEnrollment }>(
    `/admin/enrollments/${encodeURIComponent(id)}`,
    input,
  )
}

// ---------------------------------------------------------------------------
// Courses
// ---------------------------------------------------------------------------

export async function getAdminCourses(params?: {
  search?: string
  program?: string
  yearLevel?: string
  semester?: string
  active?: string
}): Promise<{ courses: AdminCourse[] }> {
  const query = new URLSearchParams()
  if (params?.search) query.set('search', params.search)
  if (params?.program) query.set('program', params.program)
  if (params?.yearLevel) query.set('yearLevel', params.yearLevel)
  if (params?.semester) query.set('semester', params.semester)
  if (params?.active) query.set('active', params.active)
  const qs = query.toString()
  return apiClient.get<{ courses: AdminCourse[] }>(
    `/admin/courses${qs ? `?${qs}` : ''}`,
  )
}

export async function createAdminCourse(input: CreateCourseInput): Promise<{ course: AdminCourse }> {
  return apiClient.post<{ course: AdminCourse }>('/admin/courses', input)
}

export async function getAdminCourse(id: string): Promise<{ course: AdminCourse }> {
  return apiClient.get<{ course: AdminCourse }>(`/admin/courses/${encodeURIComponent(id)}`)
}

export async function updateAdminCourse(id: string, input: UpdateCourseInput): Promise<{ course: AdminCourse }> {
  return apiClient.put<{ course: AdminCourse }>(`/admin/courses/${encodeURIComponent(id)}`, input)
}

export async function archiveAdminCourse(id: string): Promise<null> {
  return apiClient.delete<null>(`/admin/courses/${encodeURIComponent(id)}`)
}

// ---------------------------------------------------------------------------
// Document Requests
// ---------------------------------------------------------------------------

export async function getAdminDocumentRequests(params?: {
  search?: string
  status?: string
  documentType?: string
  dateFrom?: string
  dateTo?: string
}): Promise<{ requests: AdminDocumentRequest[] }> {
  const query = new URLSearchParams()
  if (params?.search) query.set('search', params.search)
  if (params?.status) query.set('status', params.status)
  if (params?.documentType) query.set('documentType', params.documentType)
  if (params?.dateFrom) query.set('dateFrom', params.dateFrom)
  if (params?.dateTo) query.set('dateTo', params.dateTo)
  const qs = query.toString()
  return apiClient.get<{ requests: AdminDocumentRequest[] }>(
    `/admin/requests${qs ? `?${qs}` : ''}`,
  )
}

export async function getAdminDocumentRequest(id: string): Promise<{ request: AdminDocumentRequest }> {
  return apiClient.get<{ request: AdminDocumentRequest }>(`/admin/requests/${encodeURIComponent(id)}`)
}

export async function updateAdminRequestStatus(
  id: string,
  input: UpdateRequestStatusInput,
): Promise<{ request: AdminDocumentRequest }> {
  return apiClient.patch<{ request: AdminDocumentRequest }>(
    `/admin/requests/${encodeURIComponent(id)}/status`,
    input,
  )
}

// ---------------------------------------------------------------------------
// Notifications
// ---------------------------------------------------------------------------

export async function getAdminNotifications(params?: {
  search?: string
  recipient?: string
}): Promise<{ notifications: AdminNotification[] }> {
  const query = new URLSearchParams()
  if (params?.search) query.set('search', params.search)
  if (params?.recipient) query.set('recipient', params.recipient)
  const qs = query.toString()
  return apiClient.get<{ notifications: AdminNotification[] }>(
    `/admin/notifications${qs ? `?${qs}` : ''}`,
  )
}

export async function createAdminNotification(
  input: CreateNotificationInput,
): Promise<{ notification: AdminNotification }> {
  return apiClient.post<{ notification: AdminNotification }>('/admin/notifications', input)
}

// ---------------------------------------------------------------------------
// Reports
// ---------------------------------------------------------------------------

export async function getAdminReportSummary(params?: {
  dateFrom?: string
  dateTo?: string
}): Promise<{ summary: ReportSummary }> {
  const query = new URLSearchParams()
  if (params?.dateFrom) query.set('dateFrom', params.dateFrom)
  if (params?.dateTo) query.set('dateTo', params.dateTo)
  const qs = query.toString()
  return apiClient.get<{ summary: ReportSummary }>(
    `/admin/reports/summary${qs ? `?${qs}` : ''}`,
  )
}

export async function exportAdminRequestsCsv(params?: {
  dateFrom?: string
  dateTo?: string
}): Promise<Blob> {
  const query = new URLSearchParams()
  if (params?.dateFrom) query.set('dateFrom', params.dateFrom)
  if (params?.dateTo) query.set('dateTo', params.dateTo)
  const qs = query.toString()
  const response = await fetch(
    `${(import.meta.env.VITE_API_BASE_URL ?? '/api/v1').replace(/\/+$/, '')}/admin/reports/requests.csv${qs ? `?${qs}` : ''}`,
    {
      method: 'GET',
      credentials: 'include',
      headers: { Accept: 'text/csv' },
    },
  )
  if (!response.ok) {
    throw new Error(`CSV export failed: ${response.status}`)
  }
  return response.blob()
}