import { Navigate, Outlet } from 'react-router-dom'
import type { ReactNode } from 'react'

import { useAuth } from '@/features/auth/AuthProvider'
import { routePaths } from '@/routes/routePaths'
import type { UserRole } from '@/types/common'

/**
 * Ensures the authenticated user has the required role. Mismatched roles are
 * sent to the unauthorized page - this is UX only; the PHP backend enforces
 * authorization independently on every request.
 */
export function RoleGuard({ role, children }: { role: UserRole; children?: ReactNode }) {
  const { user } = useAuth()

  if (user === null || user.role !== role) {
    return <Navigate to={routePaths.unauthorized} replace />
  }

  return <>{children ?? <Outlet />}</>
}
