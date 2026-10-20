import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'

import { getDashboard, getProfile, updateProfile } from './studentApi'
import type { ProfileUpdateInput } from './studentTypes'

export function useStudentDashboard() {
  return useQuery({
    queryKey: ['student', 'dashboard'],
    queryFn: ({ signal }) => getDashboard(signal),
  })
}

export function useStudentProfile() {
  return useQuery({
    queryKey: ['student', 'profile'],
    queryFn: ({ signal }) => getProfile(signal),
  })
}

export function useUpdateProfile() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (input: ProfileUpdateInput) => updateProfile(input),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['student', 'profile'] })
    },
  })
}
