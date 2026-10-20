import { Link } from 'react-router-dom';
import { BellRing } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { EmptyState } from '@/components/common/EmptyState';
import type { DashboardNotification } from '@/features/students/studentTypes';
import { useMarkAllNotificationsRead } from '@/features/notifications/notificationHooks';
import { formatRelativeTime } from '@/lib/dateUtils';
import { routePaths } from '@/routes/routePaths';

export interface RecentNotificationsProps {
  notifications: DashboardNotification[];
  unreadCount: number;
}

/** Latest notifications panel with a mark-all-read action. */
export function RecentNotifications({ notifications, unreadCount }: RecentNotificationsProps) {
  const markAll = useMarkAllNotificationsRead();
  return (
    <Card className="xl:col-span-2">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
        <div><CardTitle>Recent Notifications</CardTitle><CardDescription>Updates for you.</CardDescription></div>
        {unreadCount > 0 && (<Button variant="link" size="sm" className="px-0" disabled={markAll.isPending} onClick={() => markAll.mutate()}>Mark all read</Button>)}
      </CardHeader>
      <CardContent className="pt-0">
        {notifications.length === 0 ? (
          <EmptyState icon={BellRing} title="No notifications" description="Updates about your requests will appear here." />
        ) : (
          <ul className="divide-y divide-border">{notifications.map((n) => (
            <li key={n.id} className="flex gap-3 py-3 first:pt-0 last:pb-0">
              <span className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary"><BellRing className="h-4 w-4" aria-hidden="true" /></span>
              <div className="min-w-0 flex-1">
                <p className="truncate text-[13px] font-semibold">{n.title}</p>
                <p className="line-clamp-2 text-xs text-muted-foreground">{n.message}</p>
                <p className="mt-1 text-[11px] text-muted-foreground">{formatRelativeTime(n.createdAt)}</p>
              </div>
              {n.readAt === null && <span className="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary" aria-label="Unread" />}
            </li>
          ))}</ul>
        )}
        <Button variant="outline" size="sm" asChild className="mt-4 w-full"><Link to={routePaths.student.notifications}>View all</Link></Button>
      </CardContent>
    </Card>
  );
}
