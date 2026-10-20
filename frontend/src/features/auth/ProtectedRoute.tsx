import { Navigate, Outlet, useLocation } from 'react-router-dom'
import type { ReactNode } from 'react'

import { LoadingState } from '@/components/common/LoadingState'
import { useAuth } from '@/features/auth/AuthProvider'
import { routePaths } from '@/routes/routePaths'

/**
 * Blocks unauthenticated access. While the session is being verified the app
 * shows a loading state; unauthenticated visitors are redirected to /login
 * with the attempted location preserved for post-login redirection.
 *
 * Backend endpoints remain independently protected regardless of this guard.
 */
export function ProtectedRoute({ children }: { children?: ReactNode }) {
  const { status } = useAuth()
  const location = useLocation()

  if (status === 'loading') {
    return (
      <div className="min-h-screen">
        <LoadingState label="Checking your session..." />
      </div>
    )
  }

  if (status !== 'authenticated') {
    return <Navigate to={routePaths.login} state={{ from: location.pathname + location.search }} replace />
  }

  return <>{children ?? <Outlet />}</>
}
