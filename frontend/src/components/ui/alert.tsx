import { AlertCircle, AlertTriangle, CheckCircle2, Info } from 'lucide-react'
import { forwardRef, type HTMLAttributes, type ReactNode } from 'react'

import { cn } from '@/lib/utils'

type AlertVariant = 'info' | 'success' | 'warning' | 'destructive'

const variantStyles: Record<AlertVariant, { wrapper: string; icon: ReactNode }> = {
  info: {
    wrapper: 'border-primary/20 bg-primary-soft',
    icon: <Info className="h-4 w-4 shrink-0 text-primary" aria-hidden="true" />,
  },
  success: {
    wrapper: 'border-success/20 bg-success-soft',
    icon: <CheckCircle2 className="h-4 w-4 shrink-0 text-success-strong" aria-hidden="true" />,
  },
  warning: {
    wrapper: 'border-warning/30 bg-warning-soft',
    icon: <AlertTriangle className="h-4 w-4 shrink-0 text-warning-strong" aria-hidden="true" />,
  },
  destructive: {
    wrapper: 'border-danger/20 bg-danger-soft',
    icon: <AlertCircle className="h-4 w-4 shrink-0 text-danger-strong" aria-hidden="true" />,
  },
}

export interface AlertProps extends HTMLAttributes<HTMLDivElement> {
  variant?: AlertVariant
  title?: string
}

const Alert = forwardRef<HTMLDivElement, AlertProps>(
  ({ className, variant = 'info', title, children, ...props }, ref) => {
    const styles = variantStyles[variant]
    return (
      <div
        ref={ref}
        role="alert"
        className={cn('relative rounded-lg border px-4 py-3 text-sm text-foreground', styles.wrapper, className)}
        {...props}
      >
        <div className="flex gap-3">
          {styles.icon}
          <div className="flex-1 space-y-1">
            {title && <p className="font-medium">{title}</p>}
            {children && <div className="text-sm">{children}</div>}
          </div>
        </div>
      </div>
    )
  },
)
Alert.displayName = 'Alert'

export { Alert }
