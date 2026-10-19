import type { ReactNode } from 'react'

import { Label } from '@/components/ui/label'
import { cn } from '@/lib/utils'

export interface FormFieldProps {
  id: string
  label: string
  required?: boolean
  hint?: string
  error?: string
  children: ReactNode
  className?: string
}

/** Label + control + hint/error wrapper used by all form controls. */
export function FormField({ id, label, required = false, hint, error, children, className }: FormFieldProps) {
  const showDescription = Boolean(hint || error)
  return (
    <div className={cn('space-y-2', className)}>
      <Label htmlFor={id}>
        {label}
        {required && (
          <span className="ml-0.5 text-danger" aria-hidden="true">
            *
          </span>
        )}
      </Label>
      {children}
      {showDescription && (
        <p
          id={`${id}-description`}
          className={cn('text-xs', error ? 'font-medium text-danger-strong' : 'text-muted-foreground')}
        >
          {error ?? hint}
        </p>
      )}
    </div>
  )
}
