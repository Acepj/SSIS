import type { ReactNode } from 'react'
import { Controller, type Control, type FieldPath, type FieldValues } from 'react-hook-form'

import { FormField } from '@/components/forms/FormField'
import { Input } from '@/components/ui/input'

export interface TextFieldProps<TFieldValues extends FieldValues> {
  control: Control<TFieldValues>
  name: FieldPath<TFieldValues>
  label: string
  id?: string
  type?: 'text' | 'email' | 'password' | 'tel'
  placeholder?: string
  autoComplete?: string
  autoCapitalize?: string
  spellCheck?: boolean
  inputMode?: 'text' | 'numeric' | 'email' | 'tel'
  maxLength?: number
  required?: boolean
  hint?: string
  disabled?: boolean
  /** Rendered inside the input on the right (e.g. a password visibility toggle). */
  endAdornment?: ReactNode
  className?: string
}

/** React Hook Form connected text input with label, validation and hint/error display. */
export function TextField<TFieldValues extends FieldValues>({
  control,
  name,
  label,
  id,
  type = 'text',
  placeholder,
  autoComplete,
  autoCapitalize,
  spellCheck,
  inputMode,
  maxLength,
  required = false,
  hint,
  disabled,
  endAdornment,
  className,
}: TextFieldProps<TFieldValues>) {
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
          <div className="relative">
            <Input
              {...field}
              id={fieldId}
              type={type}
              placeholder={placeholder}
              autoComplete={autoComplete}
              autoCapitalize={autoCapitalize}
              spellCheck={spellCheck}
              inputMode={inputMode}
              maxLength={maxLength}
              disabled={disabled}
              aria-invalid={fieldState.invalid || undefined}
              aria-describedby={hint || fieldState.error ? `${fieldId}-description` : undefined}
              className={endAdornment ? 'pr-11' : undefined}
            />
            {endAdornment && (
              <div className="absolute inset-y-0 right-0 flex items-center pr-1.5">{endAdornment}</div>
            )}
          </div>
        </FormField>
      )}
    />
  )
}
