/** Envelope returned by every PHP API endpoint. */
export interface ApiSuccess<TData> {
  success: true
  data: TData
  message?: string
}

export interface ApiErrorPayload {
  code: string
  message: string
  fields?: Record<string, string>
}

export interface ApiFailure {
  success: false
  error: ApiErrorPayload
}

export type ApiResponse<TData> = ApiSuccess<TData> | ApiFailure
