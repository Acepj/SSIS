import { apiClient } from '@/lib/apiClient'
import type { AuthUser, LoginPayload } from './authTypes'

/** Every auth endpoint returns the authenticated user inside `data.user`. */
export interface AuthenticatedUserResponse {
  user: AuthUser
}

/**
 * Authenticates against the PHP API. The server creates the authoritative
 * session cookie and returns the user's role from its own records - never
 * from the request payload.
 */
export async function login(payload: LoginPayload): Promise<AuthenticatedUserResponse> {
  return apiClient.post<AuthenticatedUserResponse>('/auth/login', payload)
}

/** Restores the current session (401 means simply "not signed in"). */
export async function me(): Promise<AuthenticatedUserResponse> {
  return apiClient.get<AuthenticatedUserResponse>('/auth/me')
}

/** Destroys the server-side session. */
export async function logout(): Promise<void> {
  await apiClient.post<null>('/auth/logout')
}
