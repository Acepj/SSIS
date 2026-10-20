import { apiClient } from '@/lib/apiClient'
import type { EnrollmentResponse } from './enrollmentTypes'

export async function getEnrollment(signal?: AbortSignal): Promise<EnrollmentResponse> {
  return apiClient.get<EnrollmentResponse>('/student/enrollment', signal)
}
