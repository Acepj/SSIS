import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query'

import {
  activateAdminStudent,
  archiveAdminCourse,
  createAdminCourse,
  createAdminEnrollment,
  createAdminNotification,
  createAdminStudent,
  deactivateAdminStudent,
  exportAdminRequestsCsv,
  getAdminCourses,
  getAdminDocumentRequest,
  getAdminDocumentRequests,
  getAdminEnrollment,
  getAdminEnrollments,
  getAdminNotifications,
  getAdminReportSummary,
  getAdminStudent,
  getAdminStudents,
  updateAdminCourse,
  updateAdminEnrollment,
  updateAdminRequestStatus,
  updateAdminStudent,
} from './adminApi'
import type {
  CreateCourseInput,
  CreateEnrollmentInput,
  CreateNotificationInput,
  CreateStudentInput,
  UpdateCourseInput,
  UpdateEnrollmentInput,
  UpdateRequestStatusInput,
  UpdateStudentInput,
} from './adminTypes'

// ---------------------------------------------------------------------------
// Students
// ---------------------------------------------------------------------------

export function useAdminStudents(params?: {
  search?: string
  program?: string
  status?: string
}) {
  return useQuery({
    queryKey: ['admin', 'students', params?.search ?? '', params?.program ?? '', params?.status ?? ''],
    queryFn: ({ signal }) => getAdminStudents(params, signal as AbortSignal),
    placeholderData: keepPreviousData,
  })
}

export function useAdminStudent(id: string | undefined) {
  return useQuery({
    queryKey: ['admin', 'student', id],
    queryFn: ({ signal }) => getAdminStudent(id ?? '').then(({ student }) => student),
    enabled: Boolean(id),
  })
}

export function useCreateAdminStudent() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (input: CreateStudentInput) => createAdminStudent(input),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'students'] })
    },
  })
}

export function useUpdateAdminStudent() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: ({ id, input }: { id: string; input: UpdateStudentInput }) =>
      updateAdminStudent(id, input),
    onSuccess: (_data, { id }) => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'students'] })
      void queryClient.invalidateQueries({ queryKey: ['admin', 'student', id] })
    },
  })
}

export function useActivateAdminStudent() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (id: string) => activateAdminStudent(id),
    onSuccess: (_data, id) => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'students'] })
      void queryClient.invalidateQueries({ queryKey: ['admin', 'student', id] })
    },
  })
}

export function useDeactivateAdminStudent() {
// ---------------------------------------------------------------------------
// Enrollments
// ---------------------------------------------------------------------------

export function useAdminEnrollments(studentId: string, params?: {
  search?: string
  program?: string
  yearLevel?: string
  semester?: string
  academicYear?: string
  status?: string
}) {
  return useQuery({
    queryKey: [
      'admin', 'enrollments', studentId,
      params?.search ?? '', params?.program ?? '', params?.yearLevel ?? '',
      params?.semester ?? '', params?.academicYear ?? '', params?.status ?? '',
    ],
    queryFn: ({ signal }) => getAdminEnrollments(studentId, params, signal as AbortSignal),
    placeholderData: keepPreviousData,
    enabled: Boolean(studentId),
  })
}

export function useAdminEnrollment(id: string | undefined) {
  return useQuery({
    queryKey: ['admin', 'enrollment', id],
    queryFn: ({ signal }) => getAdminEnrollment(id ?? '', signal as AbortSignal),
    enabled: Boolean(id),
  })
}

export function useCreateAdminEnrollment() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (input: CreateEnrollmentInput) => createAdminEnrollment(input),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'enrollments'] })
    },
  })
}

export function useUpdateAdminEnrollment() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: ({ id, input }: { id: string; input: UpdateEnrollmentInput }) =>
      updateAdminEnrollment(id, input),
    onSuccess: (_data, { id }) => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'enrollments'] })
// ---------------------------------------------------------------------------
// Courses
// ---------------------------------------------------------------------------

export function useAdminCourses(params?: {
  search?: string
  program?: string
  yearLevel?: string
  semester?: string
  active?: string
}) {
  return useQuery({
    queryKey: [
      'admin', 'courses',
      params?.search ?? '', params?.program ?? '', params?.yearLevel ?? '',
      params?.semester ?? '', params?.active ?? '',
    ],
    queryFn: ({ signal }) => getAdminCourses(params, signal as AbortSignal),
    placeholderData: keepPreviousData,
  })
}

export function useAdminCourse(id: string | undefined) {
  return useQuery({
    queryKey: ['admin', 'course', id],
    queryFn: ({ signal }) => getAdminCourse(id ?? '', signal as AbortSignal),
    enabled: Boolean(id),
  })
}

export function useCreateAdminCourse() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (input: CreateCourseInput) => createAdminCourse(input),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'courses'] })
    },
  })
}

export function useUpdateAdminCourse() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: ({ id, input }: { id: string; input: UpdateCourseInput }) =>
      updateAdminCourse(id, input),
    onSuccess: (_data, { id }) => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'courses'] })
      void queryClient.invalidateQueries({ queryKey: ['admin', 'course', id] })
    },
  })
}

export function useArchiveAdminCourse() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (id: string) => archiveAdminCourse(id),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'courses'] })
    },
  })
}

// ---------------------------------------------------------------------------
// Document Requests
// ---------------------------------------------------------------------------

export function useAdminDocumentRequests(params?: {
  search?: string
  status?: string
  documentType?: string
  dateFrom?: string
  dateTo?: string
}) {
  return useQuery({
    queryKey: [
      'admin', 'requests',
      params?.search ?? '', params?.status ?? '', params?.documentType ?? '',
      params?.dateFrom ?? '', params?.dateTo ?? '',
    ],
    queryFn: ({ signal }) => getAdminDocumentRequests(params, signal as AbortSignal),
    placeholderData: keepPreviousData,
  })
}

export function useAdminDocumentRequest(id: string | undefined) {
  return useQuery({
    queryKey: ['admin', 'request', id],
    queryFn: ({ signal }) => getAdminDocumentRequest(id ?? '', signal as AbortSignal),
    enabled: Boolean(id),
  })
}

export function useUpdateAdminRequestStatus() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: ({ id, input }: { id: string; input: UpdateRequestStatusInput }) =>
      updateAdminRequestStatus(id, input),
    onSuccess: (_data, { id }) => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'requests'] })
      void queryClient.invalidateQueries({ queryKey: ['admin', 'request', id] })
    },
  })
}
      void queryClient.invalidateQueries({ queryKey: ['admin', 'enrollment', id] })
    },
  })
}
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (id: string) => deactivateAdminStudent(id),
    onSuccess: (_data, id) => {
      void queryClient.invalidateQueries({ queryKey: ['admin', 'students'] })
      void queryClient.invalidateQueries({ queryKey: ['admin', 'student', id] })
    },
  })
}