import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { ErrorState } from '@/components/common/ErrorState';
import { PageHeader } from '@/components/common/PageHeader';
import { Skeleton } from '@/components/ui/skeleton';
import { StatusBadge } from '@/components/common/StatusBadge';
import { useAdminReportSummary } from '@/features/admin/adminHooks';
import { BarChart3, BellRing, CircleCheck, Clock, GraduationCap, Users } from 'lucide-react';
import { cn } from '@/lib/utils';

export default function AdminDashboardPage() {
  const q = useAdminReportSummary();

  if (q.isPending) return (
    <div className="space-y-4" aria-label="Loading dashboard">
      <PageHeader title="Dashboard" description="Overview of student services activity." />
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {[0, 1, 2, 3, 4, 5, 6, 7].map((i) => (
          <Skeleton key={i} className="h-32" />
        ))}
      </div>
      <Skeleton className="h-72" />
    </div>
  );

  if (q.isError || !q.data) return (
    <div className="space-y-4">
      <PageHeader title="Dashboard" />
      <Card className="mt-6">
        <CardContent className="p-2">
          <ErrorState title="Dashboard unavailable" onRetry={() => void q.refetch()} />
        </CardContent>
      </Card>
    </div>
  );

  const s = q.data.summary;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Dashboard"
        description="Overview of student services activity and system health."
      />
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <SummaryCard
          label="Total Students"
          value={String(s.totalStudents)}
          sub={`${s.activeStudents} active`}
          icon={Users}
          iconClassName="bg-primary-soft text-primary"
        />
        <SummaryCard
          label="Total Requests"
          value={String(s.totalRequests)}
          sub={`${s.pendingRequests} pending`}
          icon={Clock}
          iconClassName="bg-warning-soft text-warning-strong"
        />
        <SummaryCard
          label="Completed Requests"
          value={String(s.completedRequests)}
          sub={"Ready or finished"}
          icon={CircleCheck}
          iconClassName="bg-success-soft text-success-strong"
        />
        <SummaryCard
          label="Total Enrollments"
          value={String(s.totalEnrollments)}
          sub={`${s.enrolledStudents} enrolled`}
          icon={GraduationCap}
          iconClassName="bg-primary-soft text-primary"
        />
      </div>
      <div className="grid gap-6 xl:grid-cols-2">
        <Card className="col-span-1 xl:col-span-1">
          <div className="flex flex-col space-y-1.5 p-6">
            <h3 className="text-base font-semibold text-foreground">Requests by Status</h3>
            <p className="text-xs text-muted-foreground">Document request distribution</p>
          </div>
          <div className="p-6 pt-0">
            <div className="space-y-4">
              {Object.entries(s.requestsByStatus).map(([status, count]) => (
                <div key={status} className="flex items-center justify-between">
                  <span className="text-sm text-foreground">{status}</span>
                  <div className="flex items-center gap-2">
                    <Badge variant="outline">{status}</Badge>
                    <span className="text-sm font-medium text-foreground">{count}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </Card>
        <Card className="col-span-1 xl:col-span-1">
          <div className="flex flex-col space-y-1.5 p-6">
            <h3 className="text-base font-semibold text-foreground">Enrollments by Status</h3>
            <p className="text-xs text-muted-foreground">Student enrollment distribution</p>
          </div>
          <div className="p-6 pt-0">
            <div className="space-y-4">
              {Object.entries(s.enrollmentsByStatus).map(([status, count]) => (
                <div key={status} className="flex items-center justify-between">
                  <span className="text-sm text-foreground">{status}</span>
                  <div className="flex items-center gap-2">
                    <StatusBadge status={status as any} className="text-xs" />
                    <span className="text-sm font-medium text-foreground">{count}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </Card>
      </div>
    </div>
  );
}

function SummaryCard({
  label,
  value,
  sub,
  icon: Icon,
  iconClassName,
}: {
  label: string
  value: string
  sub: string
  icon: typeof Users
  iconClassName: string
}) {
  return (
    <Card>
      <CardContent className="p-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
              {label}
            </p>
            <h2 className="mt-1 text-2xl font-bold text-foreground">{value}</h2>
            <p className="mt-1 text-sm text-muted-foreground">{sub}</p>
          </div>
          <div className={cn('flex h-9 w-9 items-center justify-center rounded-lg', iconClassName)}>
            <Icon className="h-5 w-5" aria-hidden="true" />
          </div>
        </div>
      </CardContent>
    </Card>
  )
}