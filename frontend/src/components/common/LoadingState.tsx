import { Loader2 } from 'lucide-react'

import { cn } from '@/lib/utils'

export interface LoadingStateProps {
  label?: string
  className?: string
}

/** Centered spinner with an accessible status label. */
export function LoadingState({ label = 'Loading...', className }: LoadingStateProps) {
  return (
    <div
      role="status"
      className={cn('flex flex-col items-center justify-center gap-3 py-12 text-muted-foreground', className)}
    >
      <Loader2 className="h-6 w-6 animate-spin text-primary" aria-hidden="true" />
      <p className="text-sm">{label}</p>
    </div>
  )
}
