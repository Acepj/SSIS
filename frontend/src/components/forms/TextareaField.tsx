import { Controller, type Control, type FieldPath, type FieldValues } from 'react-hook-form'

import { FormField } from '@/components/forms/FormField'
import { Textarea } from '@/components/ui/textarea'

export interface TextareaFieldProps<TFieldValues extends FieldValues> {
  control: Control<TFieldValues>
  name: FieldPath<TFieldValues>
  label: string
  id?: string
  placeholder?: string
  required?: boolean
  hint?: string
  disabled?: boolean
  rows?: number
  maxLength?: number
  className?: string
}

/** React Hook Form connected multi-line text input. */
export function TextareaField<TFieldValues extends FieldValues>({
  control,
  name,
  label,
  id,
  placeholder,
  required = false,
  hint,
  disabled,
  rows = 4,
  maxLength,
  className,
}: TextareaFieldProps<TFieldValues>) {
  const fieldId = id ?? name
  return (
    <Controller
      control={control}
      name={name}
      render={({ field, fieldState }) => (
        <FormField
          id={fieldId}
          label={label}
          required={required}
          hint={hint}
          error={fieldState.error?.message}
          className={className}
        >
          <Textarea
            id={fieldId}
            value={field.value ?? ''}
            onChange={(event) => field.onChange(event.target.value)}
            onBlur={field.onBlur}
            name={field.name}
            placeholder={placeholder}
            disabled={disabled}
            rows={rows}
            maxLength={maxLength}
            aria-invalid={fieldState.invalid || undefined}
            aria-describedby={hint || fieldState.error ? `${fieldId}-description` : undefined}
          />
        </FormField>
      )}
    />
  )
}
