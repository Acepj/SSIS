import { useQuery, useQueryClient } from '@tanstack/react-query'
import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react'

import { logout as logoutRequest, me as meRequest } from './authApi'
import type { AuthUser } from './authTypes'
import { ApiError, clearRequestState, SESSION_EXPIRED_EVENT } from '@/lib/apiClient'

type AuthStatus = 'loading' | 'authenticated' | 'unauthenticated'

interface AuthContextValue {
  user: AuthUser | null
  status: AuthStatus
  /** True after the backend reported the session expired mid-use. */
  sessionExpired: boolean
  /** Stores the signed-in user without a refetch (called right after login). */
  signIn: (user: AuthUser) => void
  /** Signs out server-side and clears all client-side auth state. */
  signOut: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | null>(null)

const ME_QUERY_KEY = ['auth', 'me'] as const

/**
 * Authentication state for the whole app. The PHP session (cookie) is the
 * single source of truth; this provider merely mirrors `GET /auth/me`.
 */
export function AuthProvider({ children }: { children: ReactNode }) {
  const queryClient = useQueryClient()
  const [sessionExpired, setSessionExpired] = useState(false)

  const meQuery = useQuery({
    queryKey: ME_QUERY_KEY,
    queryFn: async () => {
      try {
        return await meRequest()
      } catch (error) {
        // Not signed in is a valid state, not an error. Real failures
        // (network/server) also land here and surface when login is attempted.
        if (error instanceof ApiError && (error.status === 401 || error.isNetworkError)) {
          return null
        }
        throw error
      }
    },
    retry: false,
    staleTime: Infinity,
    gcTime: Infinity,
    refetchOnWindowFocus: false,
  })

  const user = meQuery.data?.user ?? null
  const status: AuthStatus = meQuery.isPending ? 'loading' : user !== null ? 'authenticated' : 'unauthenticated'

  useEffect(() => {
    const handleSessionExpired = () => {
      setSessionExpired(true)
      queryClient.setQueryData(ME_QUERY_KEY, null)
    }
    window.addEventListener(SESSION_EXPIRED_EVENT, handleSessionExpired)
    return () => window.removeEventListener(SESSION_EXPIRED_EVENT, handleSessionExpired)
  }, [queryClient])

  const signIn = useCallback(
    (authenticatedUser: AuthUser) => {
      setSessionExpired(false)
      queryClient.setQueryData(ME_QUERY_KEY, { user: authenticatedUser })
    },
    [queryClient],
  )

  const signOut = useCallback(async () => {
    try {
      await logoutRequest()
    } catch {
      // The session may already be gone server-side; local state is cleared regardless.
    }
    clearRequestState()
    setSessionExpired(false)
    queryClient.setQueryData(ME_QUERY_KEY, null)
    queryClient.clear()
  }, [queryClient])

  const value = useMemo<AuthContextValue>(
    () => ({ user, status, sessionExpired, signIn, signOut }),
    [user, status, sessionExpired, signIn, signOut],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext)
  if (context === null) {
    throw new Error('useAuth must be used within an AuthProvider')
  }

  return context
}
