import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'

import { cancelRequest, createRequest, getRequest, getRequests } from './documentApi'
import type { CreateRequestInput } from './documentTypes'

export function useStudentRequests(params: { status?: string; search?: string }) {
  const { status, search } = params
  return useQuery({
    queryKey: ['student', 'requests', status ?? 'All', search ?? ''],
    queryFn: ({ signal }) => getRequests({ status, search }, signal),
  })
}

export function useStudentRequest(id: string | undefined) {
  return useQuery({
    queryKey: ['student', 'request', id],
    queryFn: ({ signal }) => getRequest(id ?? '', signal),
    enabled: Boolean(id),
  })
}

export function useCreateRequest() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (input: CreateRequestInput) => createRequest(input),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['student', 'requests'] })
      void queryClient.invalidateQueries({ queryKey: ['student', 'dashboard'] })
    },
  })
}

export function useCancelRequest() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: string) => cancelRequest(id),
    onSuccess: (_data, id) => {
      void queryClient.invalidateQueries({ queryKey: ['student', 'requests'] })
      void queryClient.invalidateQueries({ queryKey: ['student', 'request', id] })
      void queryClient.invalidateQueries({ queryKey: ['student', 'dashboard'] })
    },
  })
}
