import { format, formatDistanceToNow, isValid, parseISO } from 'date-fns'

export const DATE_FORMAT = 'MMM d, yyyy'
export const DATE_TIME_FORMAT = 'MMM d, yyyy h:mm a'

function toDate(value: string | Date | null | undefined): Date | null {
  if (value === null || value === undefined || value === '') {
    return null
  }
  const parsed = typeof value === 'string' ? parseISO(value) : value
  return isValid(parsed) ? parsed : null
}

/** Formats a date as e.g. "Oct 19, 2026". Returns an em dash when unavailable. */
export function formatDate(value: string | Date | null | undefined): string {
  const date = toDate(value)
  return date ? format(date, DATE_FORMAT) : '\u2014'
}

/** Formats a date and time as e.g. "Oct 19, 2026 3:05 PM". */
export function formatDateTime(value: string | Date | null | undefined): string {
  const date = toDate(value)
  return date ? format(date, DATE_TIME_FORMAT) : '\u2014'
}

/** Formats a date relative to now as e.g. "5 minutes ago". */
export function formatRelativeTime(value: string | Date | null | undefined): string {
  const date = toDate(value)
  return date ? formatDistanceToNow(date, { addSuffix: true }) : '\u2014'
}
