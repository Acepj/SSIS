import { useEffect, useState } from 'react'

/** Delays fast-changing values (search input) before they trigger API queries. */
export function useDebounce<TValue>(value: TValue, delayMs = 300): TValue {
  const [debounced, setDebounced] = useState(value)

  useEffect(() => {
    const timer = window.setTimeout(() => setDebounced(value), delayMs)
    return () => window.clearTimeout(timer)
  }, [value, delayMs])

  return debounced
}
