import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { ErrorState } from '@/components/common/ErrorState';
import { Skeleton } from '@/components/ui/skeleton';
import { useAuth } from '@/features/auth/AuthProvider';
import { useStudentDashboard } from '@/features/students/studentHooks';
import { BellRing, CircleCheck, Clock, GraduationCap } from 'lucide-react';
import { QuickActions } from './components/QuickActions';
import { RecentNotifications } from './components/RecentNotifications';
import { RecentRequests } from './components/RecentRequests';
import { SummaryCard } from './components/SummaryCard';

export default function StudentDashboardPage() {
  const { user } = useAuth();
  const q = useStudentDashboard();
  const first = (user?.displayName ?? 'Student').split(' ')[0];
  if (q.isPending) return (<div className="space-y-4" aria-label="Loading dashboard"><Skeleton className="h-16" /><div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{[0, 1, 2, 3].map((i) => (<Skeleton key={i} className="h-28" />))}</div><Skeleton className="h-72" /></div>);
  if (q.isError || !q.data) return (<Card><CardContent className="p-2"><ErrorState title="Dashboard unavailable" onRetry={() => void q.refetch()} /></CardContent></Card>);
  const d = q.data;
  const term = d.enrollment?.term ?? (d.academic.semester ? `${d.academic.semester} ${d.academic.year}` : '');
  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div><h1 className="text-xl font-semibold sm:text-2xl">Welcome back, {first}!</h1><p className="mt-1 text-sm text-muted-foreground">Manage your student services and requests in one place.</p></div>
        {term !== '' && <Badge>{term}</Badge>}
      </div>
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <SummaryCard label="Enrollment Status" value={d.enrollment?.status ?? 'Not Enrolled'} sub={term || 'No active term'} icon={GraduationCap} iconClassName="bg-primary-soft text-primary" />
        <SummaryCard label="Pending Requests" value={String(d.counts.pending)} sub="Awaiting processing" icon={Clock} iconClassName="bg-warning-soft text-warning-strong" />
        <SummaryCard label="Completed Requests" value={String(d.counts.completed)} sub="Ready or finished" icon={CircleCheck} iconClassName="bg-success-soft text-success-strong" />
        <SummaryCard label="Notifications" value={String(d.counts.unreadNotifications)} sub="Unread messages" icon={BellRing} iconClassName="bg-primary-soft text-primary" />
      </div>
      <QuickActions />
      <div className="grid gap-6 xl:grid-cols-5">
        <RecentRequests requests={d.recentRequests} />
        <RecentNotifications notifications={d.recentNotifications} unreadCount={d.counts.unreadNotifications} />
      </div>
    </div>
  );
}
