import { useAuth } from '@/features/auth/AuthProvider'
import type { AuthUser } from '@/features/auth/authTypes'

interface CurrentUser {
  user: AuthUser | null
  isLoading: boolean
  isAuthenticated: boolean
}

/** Provides the authenticated user (mirrored from the server session). */
export function useCurrentUser(): CurrentUser {
  const { user, status } = useAuth()

  return {
    user,
    isLoading: status === 'loading',
    isAuthenticated: status === 'authenticated',
  }
}
