import { apiClient } from '@/lib/apiClient'
import type { NotificationsResponse, StudentNotification } from './notificationTypes'

export async function getNotifications(signal?: AbortSignal): Promise<NotificationsResponse> {
  return apiClient.get<NotificationsResponse>('/student/notifications', signal)
}

export async function markNotificationRead(id: string): Promise<{ notification: StudentNotification }> {
  return apiClient.post<{ notification: StudentNotification }>(
    `/student/notifications/${encodeURIComponent(id)}/read`,
  )
}

export async function markAllNotificationsRead(): Promise<{ updated: number }> {
  return apiClient.post<{ updated: number }>('/student/notifications/read-all')
}
