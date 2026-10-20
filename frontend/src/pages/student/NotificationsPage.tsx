import { BellRing, CheckCheck } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/common/EmptyState';
import { ErrorState } from '@/components/common/ErrorState';
import { LoadingState } from '@/components/common/LoadingState';
import { PageHeader } from '@/components/common/PageHeader';
import { useMarkAllNotificationsRead, useMarkNotificationRead, useNotifications } from '@/features/notifications/notificationHooks';
import { formatRelativeTime } from '@/lib/dateUtils';
import { cn } from '@/lib/utils';

export default function NotificationsPage() {
  const q = useNotifications();
  const markOne = useMarkNotificationRead();
  const markAll = useMarkAllNotificationsRead();
  return (
    <div className="space-y-6">
      <PageHeader title="Notifications" description="Updates from Student Services." actions={q.data && q.data.unreadCount > 0 ? <Button variant="outline" disabled={markAll.isPending} onClick={() => markAll.mutate()}><CheckCheck className="h-4 w-4" aria-hidden="true" />Mark all read</Button> : undefined} />
      <Card><CardContent className="pt-6">
        {q.isPending ? <LoadingState label="Loading notifications..." /> : q.isError || !q.data ? <ErrorState onRetry={() => void q.refetch()} /> : q.data.notifications.length === 0 ? <EmptyState icon={BellRing} title="No notifications" description="Important updates about your requests will appear here." /> : (
          <ul className="divide-y divide-border">{q.data.notifications.map(n => (<li key={n.id} className={cn('flex gap-3 py-4 first:pt-0 last:pb-0', n.readAt === null && 'rounded-lg bg-primary-soft/40 px-3')}><span className="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary"><BellRing className="h-4 w-4" aria-hidden="true" /></span><div className="min-w-0 flex-1"><p className="flex items-center gap-2 text-sm font-semibold">{n.title}{n.readAt === null && <span className="rounded-full bg-primary px-2 py-0.5 text-[10px] font-semibold text-white">New</span>}</p><p className="mt-0.5 text-sm text-muted-foreground">{n.message}</p><p className="mt-1 text-xs text-muted-foreground">{formatRelativeTime(n.createdAt)}</p></div>{n.readAt === null && <Button variant="ghost" size="sm" className="shrink-0" disabled={markOne.isPending} onClick={() => markOne.mutate(n.id)}>Mark read</Button>}</li>))}</ul>
        )}
      </CardContent></Card>
    </div>
  );
}
