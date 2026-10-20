import { apiClient } from '@/lib/apiClient'
import type { CreateRequestInput, RequestDetails, RequestSummary } from './documentTypes'

export async function getRequests(params: { status?: string; search?: string }, signal?: AbortSignal): Promise<{ requests: RequestSummary[] }> {
  const query = new URLSearchParams()
  if (params.status && params.status !== 'All') query.set('status', params.status)
  if (params.search) query.set('search', params.search)
  const qs = query.toString()

  return apiClient.get<{ requests: RequestSummary[] }>(`/student/requests${qs ? `?${qs}` : ''}`, signal)
}

export async function createRequest(input: CreateRequestInput): Promise<{ request: RequestDetails }> {
  return apiClient.post<{ request: RequestDetails }>('/student/requests', input)
}

export async function getRequest(id: string, signal?: AbortSignal): Promise<{ request: RequestDetails }> {
  return apiClient.get<{ request: RequestDetails }>(`/student/requests/${encodeURIComponent(id)}`, signal)
}

export async function cancelRequest(id: string): Promise<{ request: RequestDetails }> {
  return apiClient.post<{ request: RequestDetails }>(`/student/requests/${encodeURIComponent(id)}/cancel`)
}
