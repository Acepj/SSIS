import { Badge } from '@/components/ui/badge'
import { cn } from '@/lib/utils'
import type { AccountStatus, EnrollmentStatus, RequestStatus } from '@/types/common'

export type StatusValue = RequestStatus | AccountStatus | EnrollmentStatus

type BadgeVariant = 'default' | 'secondary' | 'success' | 'warning' | 'destructive' | 'outline'

const statusVariant: Record<string, BadgeVariant> = {
  /* Document request statuses */
  Pending: 'warning',
  Processing: 'default',
  'Ready for Pickup': 'outline',
  Completed: 'success',
  Rejected: 'destructive',
  Cancelled: 'secondary',
  /* Account statuses */
  Active: 'success',
  Inactive: 'secondary',
  /* Enrollment statuses */
  Enrolled: 'success',
  'Not Enrolled': 'secondary',
  'On Leave': 'warning',
  Graduated: 'default',
}

function dotColor(variant: BadgeVariant): string {
  switch (variant) {
    case 'success':
      return 'bg-success'
    case 'warning':
      return 'bg-warning'
    case 'destructive':
      return 'bg-danger'
    case 'default':
    case 'outline':
      return 'bg-primary'
    default:
      return 'bg-muted-foreground'
  }
}

export interface StatusBadgeProps {
  status: StatusValue | string
  className?: string
}

/** Colored status badge with a consistent mapping for every status in the system. */
export function StatusBadge({ status, className }: StatusBadgeProps) {
  const variant = statusVariant[status] ?? 'outline'
  return (
    <Badge variant={variant} className={className}>
      <span className={cn('h-1.5 w-1.5 rounded-full', dotColor(variant))} aria-hidden="true" />
      {status}
    </Badge>
  )
}
