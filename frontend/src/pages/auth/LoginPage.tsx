import { zodResolver } from '@hookform/resolvers/zod'
import { Eye, EyeOff } from 'lucide-react'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, Navigate, useLocation, useNavigate } from 'react-router-dom'

import { appConfig } from '@/config/appConfig'
import { FormError } from '@/components/forms/FormError'
import { TextField } from '@/components/forms/TextField'
import { Alert } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { useAuth } from '@/features/auth/AuthProvider'
import { login } from '@/features/auth/authApi'
import { loginSchema, type LoginFormValues } from '@/features/auth/authSchemas'
import { ApiError } from '@/lib/apiClient'
import { routePaths } from '@/routes/routePaths'

/**
 * Only the identifier is remembered for convenience. The PHP session cookie
 * remains the single authoritative authentication state - nothing security
 * related is stored in browser storage.
 */
const REMEMBERED_IDENTIFIER_KEY = 'ssis.remembered-identifier'

function resolveLoginError(error: unknown): string {
  if (error instanceof ApiError) {
    switch (error.code) {
      case 'INVALID_CREDENTIALS':
        return 'The identifier or password you entered is incorrect. Please try again.'
      case 'ACCOUNT_INACTIVE':
        return 'Your account has been deactivated. Please contact Student Services for assistance.'
      case 'RATE_LIMITED':
        return 'Too many sign-in attempts. Please wait a moment and try again.'
      case 'CSRF_FETCH_FAILED':
      case 'NETWORK_ERROR':
        return 'Unable to reach the SSIS server. Please make sure the server is running and try again.'
      case 'SERVICE_UNAVAILABLE':
        return 'The authentication service is currently unavailable. Please try again later.'
      default:
        return error.message
    }
  }
  return 'An unexpected error occurred. Please try again.'
}

export default function LoginPage() {
  const navigate = useNavigate()
  const location = useLocation()
  const auth = useAuth()
  const [showPassword, setShowPassword] = useState(false)
  const [submitError, setSubmitError] = useState<string | null>(null)

  // Deep link the user tried to reach before being sent to login (if any).
  const locationState = location.state as { from?: string } | null
  const redirectTo =
    typeof locationState?.from === 'string' && locationState.from.startsWith('/') ? locationState.from : null

  const rememberedIdentifier =
    typeof window !== 'undefined' ? window.localStorage.getItem(REMEMBERED_IDENTIFIER_KEY) : null

  const form = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      identifier: rememberedIdentifier ?? '',
      password: '',
      remember: rememberedIdentifier !== null,
    },
  })

  // Already signed in? Leave the login page for the user's own area.
  if (auth.status === 'authenticated' && auth.user !== null) {
    return <Navigate to={redirectTo ?? routePaths.defaultForRole(auth.user.role)} replace />
  }

  const onSubmit = async (values: LoginFormValues) => {
    setSubmitError(null)
    try {
      const { user } = await login({
        identifier: values.identifier,
        password: values.password,
        remember: values.remember,
      })

      if (values.remember) {
        window.localStorage.setItem(REMEMBERED_IDENTIFIER_KEY, values.identifier)
      } else {
        window.localStorage.removeItem(REMEMBERED_IDENTIFIER_KEY)
      }

      auth.signIn(user)
      navigate(redirectTo ?? routePaths.defaultForRole(user.role), { replace: true })
    } catch (error) {
      setSubmitError(resolveLoginError(error))
    }
  }

  return (
    <div>
      <header>
        <h1 className="text-2xl font-semibold tracking-tight text-foreground">Welcome Back!</h1>
        <p className="mt-1.5 text-sm text-muted-foreground">Sign in to access your student services.</p>
      </header>

      {auth.sessionExpired && (
        <Alert variant="warning" title="Session expired" className="mt-5">
          Your session has expired or you were signed out. Please sign in again to continue.
        </Alert>
      )}

      <form
        onSubmit={form.handleSubmit(onSubmit)}
        noValidate
        className="mt-8 space-y-5"
        aria-busy={form.formState.isSubmitting}
      >
        <TextField
          control={form.control}
          name="identifier"
          label="Student ID or administrator username"
          placeholder="e.g. 2026-00123"
          autoComplete="username"
          autoCapitalize="none"
          spellCheck={false}
          required
        />

        <TextField
          control={form.control}
          name="password"
          label="Password"
          type={showPassword ? 'text' : 'password'}
          autoComplete="current-password"
          required
          endAdornment={
            <button
              type="button"
              onClick={() => setShowPassword((visible) => !visible)}
              className="rounded-md p-1.5 text-muted-foreground transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              aria-label={showPassword ? 'Hide password' : 'Show password'}
            >
              {showPassword ? (
                <EyeOff className="h-4 w-4" aria-hidden="true" />
              ) : (
                <Eye className="h-4 w-4" aria-hidden="true" />
              )}
            </button>
          }
        />

        <div className="flex items-center justify-between gap-4">
          <label htmlFor="remember" className="flex cursor-pointer items-center gap-2 text-sm text-foreground">
            <Checkbox id="remember" {...form.register('remember')} />
            Remember me
          </label>
          <Link
            to={routePaths.forgotPassword}
            className="text-sm font-medium text-primary hover:underline"
          >
            Forgot password?
          </Link>
        </div>

        {submitError && <FormError title="Unable to sign in" message={submitError} />}

        <Button type="submit" className="w-full" loading={form.formState.isSubmitting}>
          Login
        </Button>
      </form>

      <p className="mt-8 text-center text-sm text-muted-foreground">
        Having trouble signing in?{' '}
        <a href={`mailto:${appConfig.supportEmail}`} className="font-medium text-primary hover:underline">
          {appConfig.supportLabel}
        </a>
      </p>
    </div>
  )
}
