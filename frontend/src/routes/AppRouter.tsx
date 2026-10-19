import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'

import AuthLayout from '@/layouts/AuthLayout'
import ForgotPasswordPage from '@/pages/auth/ForgotPasswordPage'
import LoginPage from '@/pages/auth/LoginPage'
import UnauthorizedPage from '@/pages/auth/UnauthorizedPage'
import NotFoundPage from '@/pages/errors/NotFoundPage'
import { routePaths } from './routePaths'

/**
 * Central route definitions.
 * Student and admin routes are added in their respective implementation phases,
 * wrapped in session/role guards (see features/auth).
 */
export default function AppRouter() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path={routePaths.root} element={<Navigate to={routePaths.login} replace />} />

        <Route element={<AuthLayout />}>
          <Route path={routePaths.login} element={<LoginPage />} />
          <Route path={routePaths.forgotPassword} element={<ForgotPasswordPage />} />
        </Route>

        <Route path={routePaths.unauthorized} element={<UnauthorizedPage />} />

        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </BrowserRouter>
  )
}
