import type { ApiResponse } from '@/types/api'

/** Broadcast on `window` whenever the backend reports an expired/missing session. */
export const SESSION_EXPIRED_EVENT = 'ssis:session-expired'

export class ApiError extends Error {
  readonly status: number
  readonly code: string
  readonly fields: Record<string, string> | undefined

  constructor(status: number, code: string, message: string, fields?: Record<string, string>) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.code = code
    this.fields = fields
  }

  get isNetworkError(): boolean {
    return this.code === 'NETWORK_ERROR'
  }

  get isSessionExpired(): boolean {
    return this.status === 401
  }
}

type HttpMethod = 'GET' | 'POST' | 'PATCH' | 'PUT' | 'DELETE'

interface RequestOptions {
  method?: HttpMethod
  body?: unknown
  signal?: AbortSignal
  /** Internal flag preventing an infinite retry loop when refreshing the CSRF token. */
  isRetriedCsrfRequest?: boolean
}

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL ?? '/api/v1').replace(/\/+$/, '')

/** In-memory CSRF token; refreshed automatically when the backend rotates it. */
let csrfToken: string | null = null

export function setCsrfToken(token: string | null): void {
  csrfToken = token
}

export function getCsrfToken(): string | null {
  return csrfToken
}

/** Clears cached request state (used on logout / session expiry). */
export function clearRequestState(): void {
  csrfToken = null
}

function notifySessionExpired(): void {
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new Event(SESSION_EXPIRED_EVENT))
  }
}

/** Fetches the CSRF token issued by `GET /api/v1/auth/csrf`. */
export async function fetchCsrfToken(): Promise<string> {
  const response = await fetch(`${API_BASE_URL}/auth/csrf`, {
    method: 'GET',
    credentials: 'include',
    headers: { Accept: 'application/json' },
  })
  const payload = (await response.json().catch(() => null)) as ApiResponse<{ token: string }> | null

  if (!response.ok || payload === null || payload.success !== true) {
    throw new ApiError(
      response.status || 0,
      'CSRF_FETCH_FAILED',
      'Unable to secure the request. Please try again.',
    )
  }

  csrfToken = payload.data.token
  return payload.data.token
}

async function request<TData>(path: string, options: RequestOptions = {}): Promise<TData> {
  const method = options.method ?? 'GET'
  const url = `${API_BASE_URL}${path}`

  const headers: Record<string, string> = { Accept: 'application/json' }
  if (options.body !== undefined) {
    headers['Content-Type'] = 'application/json'
  }

  if (method !== 'GET') {
    const token = csrfToken ?? (await fetchCsrfToken())
    headers['X-CSRF-Token'] = token
  }

  let response: Response
  try {
    response = await fetch(url, {
      method,
      headers,
      credentials: 'include',
      body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
      signal: options.signal,
    })
  } catch (error) {
    if (error instanceof DOMException && error.name === 'AbortError') {
      throw error
    }
    throw new ApiError(
      0,
      'NETWORK_ERROR',
      'Unable to reach the SSIS server. Please check your connection and try again.',
    )
  }

  const payload = (await response.json().catch(() => null)) as ApiResponse<TData> | null

  if (!response.ok || payload === null || payload.success === false) {
    const errorPayload = payload !== null && payload.success === false ? payload.error : undefined

    // The CSRF token may rotate (for example after a new session starts):
    // refresh it once and retry the request transparently.
    if (response.status === 403 && errorPayload?.code === 'CSRF_TOKEN_INVALID' && !options.isRetriedCsrfRequest) {
      csrfToken = null
      return request<TData>(path, { ...options, isRetriedCsrfRequest: true })
    }

    if (response.status === 401 && path !== '/auth/login') {
      notifySessionExpired()
    }

    throw new ApiError(
      response.status,
      errorPayload?.code ?? (payload === null ? 'INVALID_RESPONSE' : 'REQUEST_FAILED'),
      errorPayload?.message ??
        (response.status >= 500
          ? 'The server encountered a problem. Please try again later.'
          : 'The request could not be completed.'),
      errorPayload?.fields,
    )
  }

  if (payload.success !== true) {
    throw new ApiError(response.status, 'INVALID_RESPONSE', 'The server returned an unexpected response.')
  }

  return payload.data
}

export const apiClient = {
  get: <TData>(path: string, signal?: AbortSignal): Promise<TData> => request<TData>(path, { method: 'GET', signal }),
  post: <TData>(path: string, body?: unknown): Promise<TData> => request<TData>(path, { method: 'POST', body }),
  patch: <TData>(path: string, body?: unknown): Promise<TData> => request<TData>(path, { method: 'PATCH', body }),
  put: <TData>(path: string, body?: unknown): Promise<TData> => request<TData>(path, { method: 'PUT', body }),
  delete: <TData>(path: string): Promise<TData> => request<TData>(path, { method: 'DELETE' }),
}
