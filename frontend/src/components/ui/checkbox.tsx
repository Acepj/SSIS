import { forwardRef, type InputHTMLAttributes } from 'react'

import { cn } from '@/lib/utils'

export type CheckboxProps = Omit<InputHTMLAttributes<HTMLInputElement>, 'type'>

/**
 * Accessible native checkbox styled to match the design system.
 * Uses the browser's built-in keyboard and screen-reader behavior.
 */
const Checkbox = forwardRef<HTMLInputElement, CheckboxProps>(({ className, ...props }, ref) => (
  <input
    ref={ref}
    type="checkbox"
    className={cn('h-4 w-4 shrink-0 cursor-pointer rounded border-input accent-primary', className)}
    {...props}
  />
))
Checkbox.displayName = 'Checkbox'

export { Checkbox }
