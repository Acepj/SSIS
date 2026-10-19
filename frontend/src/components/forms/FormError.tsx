import { Alert } from '@/components/ui/alert'

export interface FormErrorProps {
  title?: string
  message: string
}

/** Inline error message for form-level (non field-level) failures. */
export function FormError({ title = 'Something went wrong', message }: FormErrorProps) {
  return (
    <Alert variant="destructive" title={title}>
      {message}
    </Alert>
  )
}
