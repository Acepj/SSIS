import { ShieldX } from 'lucide-react'
import { useNavigate } from 'react-router-dom'
import { Link } from 'react-router-dom'

import { appConfig } from '@/config/appConfig'
import { Button, buttonVariants } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import { routePaths } from '@/routes/routePaths'

export default function UnauthorizedPage() {
  const navigate = useNavigate()

  return (
    <main className="flex min-h-screen items-center justify-center bg-background px-6 py-12">
      <div className="w-full max-w-md rounded-xl border border-border bg-card p-8 text-center shadow-[0_1px_2px_rgba(21,36,58,0.05)]">
        <span className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-danger-soft">
          <ShieldX className="h-6 w-6 text-danger-strong" aria-hidden="true" />
        </span>
        <h1 className="mt-5 text-xl font-semibold tracking-tight text-foreground">Access denied</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          You do not have permission to view this page. If you believe this is a mistake, please{' '}
          <a href={`mailto:${appConfig.supportEmail}`} className="font-medium text-primary hover:underline">
            {appConfig.supportLabel}
          </a>
          .
        </p>
        <div className="mt-6 flex flex-wrap justify-center gap-3">
          <Button variant="outline" onClick={() => navigate(-1)}>
            Go back
          </Button>
          <Link to={routePaths.login} className={cn(buttonVariants())}>
            Back to login
          </Link>
        </div>
      </div>
    </main>
  )
}
