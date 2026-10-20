import { apiClient } from '@/lib/apiClient'
import type { Course, CourseCatalogResponse, CourseQuery } from './courseTypes'

export async function getCourses(query: CourseQuery, signal?: AbortSignal): Promise<CourseCatalogResponse> {
  const params = new URLSearchParams()
  if (query.search) params.set('search', query.search)
  if (query.program) params.set('program', query.program)
  if (query.yearLevel) params.set('yearLevel', query.yearLevel)
  if (query.semester) params.set('semester', query.semester)
  const qs = params.toString()

  return apiClient.get<CourseCatalogResponse>(`/student/courses${qs ? `?${qs}` : ''}`, signal)
}

export async function getCourse(id: string, signal?: AbortSignal): Promise<{ course: Course }> {
  return apiClient.get<{ course: Course }>(`/student/courses/${encodeURIComponent(id)}`, signal)
}
