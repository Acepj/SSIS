import { apiClient } from '@/lib/apiClient'
import type { ProfileUpdateInput, StudentDashboard, StudentProfile } from './studentTypes'

export async function getDashboard(signal?: AbortSignal): Promise<StudentDashboard> {
  return apiClient.get<StudentDashboard>('/student/dashboard', signal)
}

export async function getProfile(signal?: AbortSignal): Promise<{ student: StudentProfile }> {
  return apiClient.get<{ student: StudentProfile }>('/student/profile', signal)
}

/** Only contact fields are ever sent - official academic data is read-only. */
export async function updateProfile(input: ProfileUpdateInput): Promise<{ student: StudentProfile }> {
  return apiClient.patch<{ student: StudentProfile }>('/student/profile', input)
}
