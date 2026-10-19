import { QueryClient } from '@tanstack/react-query'

import { ApiError } from './apiClient'

/**
 * Shared TanStack Query configuration: short stale time for fast-moving
 * dashboards, no retrying on client errors (4xx), limited retry on server
 * errors and network failures.
 */
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 30_000,
      refetchOnWindowFocus: false,
      retry: (failureCount, error) => {
        if (error instanceof ApiError && error.status >= 400 && error.status < 500) {
          return false
        }
        return failureCount < 2
      },
    },
    mutations: {
      retry: false,
    },
  },
})
