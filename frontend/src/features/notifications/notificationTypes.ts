export interface StudentNotification {
  id: string
  title: string
  message: string
  readAt: string | null
  createdAt: string
}

export interface NotificationsResponse {
  notifications: StudentNotification[]
  unreadCount: number
}
