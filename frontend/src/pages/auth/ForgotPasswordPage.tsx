import { ArrowLeft, KeyRound } from 'lucide-react'
import { Link } from 'react-router-dom'

import { appConfig } from '@/config/appConfig'
import { routePaths } from '@/routes/routePaths'

/**
 * Password resets are handled by Student Services staff. This deployment has
 * no outbound email capability (documented limitation of the prototype),
 * so the page provides clear self-service guidance instead of a fake reset flow.
 */
export default function ForgotPasswordPage() {
  return (
    <div>
      <span className="inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary-soft">
        <KeyRound className="h-6 w-6 text-primary" aria-hidden="true" />
      </span>

      <h1 className="mt-6 text-2xl font-semibold tracking-tight text-foreground">Forgot your password?</h1>
      <p className="mt-2 text-sm text-muted-foreground">
        For security, SSIS does not offer self-service password resets in this deployment. Password resets
        are verified and processed by Student Services.
      </p>

      <ol className="mt-6 list-decimal space-y-3 pl-5 text-sm leading-relaxed text-foreground">
        <li>Visit the Student Services desk during office hours.</li>
        <li>Bring your student ID or another valid form of identification.</li>
        <li>Staff will verify your identity and set a new password for your account.</li>
      </ol>

      <p className="mt-6 text-sm text-muted-foreground">
        Questions?{' '}
        <a href={`mailto:${appConfig.supportEmail}`} className="font-medium text-primary hover:underline">
          {appConfig.supportLabel}
        </a>
      </p>

      <Link
        to={routePaths.login}
        className="mt-8 inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline"
      >
        <ArrowLeft className="h-4 w-4" aria-hidden="true" />
        Back to login
      </Link>
    </div>
  )
}
