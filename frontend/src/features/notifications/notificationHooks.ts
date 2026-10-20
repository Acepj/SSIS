import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'

import { getNotifications, markAllNotificationsRead, markNotificationRead } from './notificationApi'

export function useNotifications() {
  return useQuery({
    queryKey: ['student', 'notifications'],
    queryFn: ({ signal }) => getNotifications(signal),
  })
}

export function useMarkNotificationRead() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: (id: string) => markNotificationRead(id),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['student', 'notifications'] })
      void queryClient.invalidateQueries({ queryKey: ['student', 'dashboard'] })
    },
  })
}

export function useMarkAllNotificationsRead() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: () => markAllNotificationsRead(),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['student', 'notifications'] })
      void queryClient.invalidateQueries({ queryKey: ['student', 'dashboard'] })
    },
  })
}
