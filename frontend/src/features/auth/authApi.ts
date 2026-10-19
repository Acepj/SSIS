import { apiClient } from '@/lib/apiClient'
import type { LoginPayload, LoginResponse } from './authTypes'

/**
 * Authenticates against the PHP API. The server creates the authoritative
 * session cookie and returns the user's role from its own records - never
 * from the request payload.
 */
export async function login(payload: LoginPayload): Promise<LoginResponse> {
  return apiClient.post<LoginResponse>('/auth/login', payload)
}
