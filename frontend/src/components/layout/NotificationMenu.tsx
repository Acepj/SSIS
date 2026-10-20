import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Bell, CheckCheck } from 'lucide-react'

import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { useMarkAllNotificationsRead, useNotifications } from '@/features/notifications/notificationHooks'
import { formatRelativeTime } from '@/lib/dateUtils'
import { cn } from '@/lib/utils'
import { routePaths } from '@/routes/routePaths'

/** Header bell with unread count and a preview of the latest notifications. */
export function NotificationMenu() {
  const { data, isPending } = useNotifications()
  const markAllRead = useMarkAllNotificationsRead()
  const [open, setOpen] = useState(false)

  const unreadCount = data?.unreadCount ?? 0
  const preview = (data?.notifications ?? []).slice(0, 5)

  return (
    <DropdownMenu open={open} onOpenChange={setOpen}>
      <DropdownMenuTrigger asChild>
        <Button
          type="button"
          variant="ghost"
          size="icon"
          aria-label={unreadCount > 0 ? `Notifications, ${unreadCount} unread` : 'Notifications'}
          className="relative text-muted-foreground hover:text-foreground"
        >
          <Bell className="h-5 w-5" aria-hidden="true" />
          {unreadCount > 0 && (
            <span
              aria-hidden="true"
              className="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-danger px-1 text-[11px] font-semibold text-white"
            >
              {unreadCount > 9 ? '9+' : unreadCount}
            </span>
          )}
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-[320px] max-w-[calc(100vw-2rem)]">
        <div className="flex items-center justify-between px-2.5 py-1.5">
          <DropdownMenuLabel className="px-0 py-0">Notifications</DropdownMenuLabel>
          {unreadCount > 0 && (
            <button
              type="button"
              onClick={() => markAllRead.mutate()}
              disabled={markAllRead.isPending}
              className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-60"
            >
              <CheckCheck className="h-3.5 w-3.5" aria-hidden="true" />
              Mark all read
            </button>
          )}
        </div>
        <DropdownMenuSeparator />
        {isPending ? (
          <p className="px-2.5 py-6 text-center text-xs text-muted-foreground">Loading notifications...</p>
        ) : preview.length === 0 ? (
          <p className="px-2.5 py-6 text-center text-xs text-muted-foreground">You have no notifications yet.</p>
        ) : (
          preview.map((notification) => (
            <DropdownMenuItem key={notification.id} asChild className="cursor-pointer">
              <Link
                to={routePaths.student.notifications}
                onClick={() => setOpen(false)}
                className={cn('flex-col items-start gap-1', notification.readAt === null && 'bg-primary-soft/50')}
              >
                <span className="flex w-full items-center justify-between gap-2">
                  <span className="truncate text-[13px] font-semibold text-foreground">{notification.title}</span>
                  {notification.readAt === null && (
                    <span className="h-2 w-2 shrink-0 rounded-full bg-primary" aria-label="Unread" />
                  )}
                </span>
                <span className="line-clamp-2 text-xs text-muted-foreground">{notification.message}</span>
                <span className="text-[11px] text-muted-foreground">{formatRelativeTime(notification.createdAt)}</span>
              </Link>
            </DropdownMenuItem>
          ))
        )}
        <DropdownMenuSeparator />
        <DropdownMenuItem asChild className="justify-center">
          <Link to={routePaths.student.notifications} onClick={() => setOpen(false)} className="text-primary">
            View all notifications
          </Link>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
