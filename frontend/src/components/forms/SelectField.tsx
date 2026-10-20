import { useState } from 'react'
import type { Control, FieldPath, FieldValues } from 'react-hook-form'
import { Controller } from 'react-hook-form'

import { FormField } from '@/components/forms/FormField'
import { Select } from '@/components/ui/select'

export interface SelectOption {
  value: string
  label: string
}

export interface SelectFieldProps<TFieldValues extends FieldValues> {
  control: Control<TFieldValues>
  name: FieldPath<TFieldValues>
  label: string
  id?: string
  options: SelectOption[]
  placeholder?: string
  required?: boolean
  hint?: string
  disabled?: boolean
  className?: string
}

/** React Hook Form connected dropdown built on the native select element. */
export function SelectField<TFieldValues extends FieldValues>({
  control,
  name,
  label,
  id,
  options,
  placeholder,
  required = false,
  hint,
  disabled,
  className,
}: SelectFieldProps<TFieldValues>) {
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
          <Select
            id={fieldId}
            value={field.value ?? ''}
            onChange={(event) => field.onChange(event.target.value)}
            onBlur={field.onBlur}
            name={field.name}
            disabled={disabled}
            aria-invalid={fieldState.invalid || undefined}
            aria-describedby={hint || fieldState.error ? `${fieldId}-description` : undefined}
          >
            {placeholder && (
              <option value="" disabled>
                {placeholder}
              </option>
            )}
            {options.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </Select>
        </FormField>
      )}
    />
  )
}

/** Small uncontrolled filter dropdown used above tables (search/filter bars). */
export function FilterSelect({
  label,
  value,
  onChange,
  options,
  className,
}: {
  label: string
  value: string
  onChange: (value: string) => void
  options: SelectOption[]
  className?: string
}) {
  const [selectId] = useState(() => `filter-${Math.random().toString(36).slice(2, 9)}`)
  return (
    <div className={className}>
      <label htmlFor={selectId} className="sr-only">
        {label}
      </label>
      <Select id={selectId} value={value} onChange={(event) => onChange(event.target.value)} aria-label={label}>
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </Select>
    </div>
  )
}
