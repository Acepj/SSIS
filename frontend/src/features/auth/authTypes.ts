import type { UserRole } from '@/types/common'

export interface AuthUser {
  id: string
  role: UserRole
  displayName: string
  /** Login identifier (student ID for students, username for administrators). */
  identifier: string
  active: boolean
}

export interface LoginPayload {
  identifier: string
  password: string
  remember: boolean
}

export interface LoginResponse {
  user: AuthUser
}
