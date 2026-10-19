import { SearchX } from 'lucide-react'
import { useNavigate } from 'react-router-dom'
import { Link } from 'react-router-dom'

import { Button, buttonVariants } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import { routePaths } from '@/routes/routePaths'

export default function NotFoundPage() {
  const navigate = useNavigate()

  return (
    <main className="flex min-h-screen items-center justify-center bg-background px-6 py-12">
      <div className="w-full max-w-md rounded-xl border border-border bg-card p-8 text-center shadow-[0_1px_2px_rgba(21,36,58,0.05)]">
        <span className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-muted">
          <SearchX className="h-6 w-6 text-muted-foreground" aria-hidden="true" />
        </span>
        <p className="mt-5 text-sm font-semibold text-primary">404</p>
        <h1 className="mt-1 text-xl font-semibold tracking-tight text-foreground">Page not found</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          The page you are looking for does not exist or may have been moved.
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
