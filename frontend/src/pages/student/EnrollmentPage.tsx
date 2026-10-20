import { GraduationCap } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { EmptyState } from '@/components/common/EmptyState';
import { ErrorState } from '@/components/common/ErrorState';
import { LoadingState } from '@/components/common/LoadingState';
import { PageHeader } from '@/components/common/PageHeader';
import { StatusBadge } from '@/components/common/StatusBadge';
import { useStudentEnrollment } from '@/features/enrollment/enrollmentHooks';
import { formatDate } from '@/lib/dateUtils';

export default function EnrollmentPage() {
  const q = useStudentEnrollment();
  if (q.isPending) return (<div><PageHeader title="Enrollment" description="Your official enrollment record." /><Card className="mt-6"><CardContent><LoadingState label="Loading enrollment..." /></CardContent></Card></div>);
  if (q.isError || !q.data) return (<div><PageHeader title="Enrollment" /><Card className="mt-6"><CardContent className="p-2"><ErrorState onRetry={() => void q.refetch()} /></CardContent></Card></div>);
  const e = q.data.enrollment;
  const term = q.data.academic.semester ? `${q.data.academic.semester} ${q.data.academic.year}` : '';
  if (!e) return (<div><PageHeader title="Enrollment" description={term || 'Enrollment information.'} /><Card className="mt-6"><CardContent className="p-2"><EmptyState icon={GraduationCap} title="No enrollment record" description="No official enrollment found. Contact Student Services if this looks wrong." /></CardContent></Card></div>);
  const rows: Array<[string, string]> = [['Academic Year', e.academicYear], ['Semester', e.semester], ['Program', e.program], ['Year Level', e.yearLevel], ['Date Enrolled', formatDate(e.dateEnrolled)]];
  return (
    <div className="space-y-6">
      <PageHeader title="Enrollment" description={term || 'Your official enrollment record.'} />
      <Card><CardHeader><CardTitle className="flex items-center gap-2"><GraduationCap className="h-5 w-5 text-primary" aria-hidden="true" />Current Enrollment</CardTitle></CardHeader>
        <CardContent><div className="mb-4"><StatusBadge status={e.status} /></div>
          <dl className="grid gap-x-8 gap-y-4 sm:grid-cols-2">{rows.map(([k, v]) => (<div key={k}><dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{k}</dt><dd className="mt-1 text-sm font-medium">{v || '—'}</dd></div>))}</dl>
          <p className="mt-6 rounded-lg bg-muted p-3 text-xs text-muted-foreground">Official enrollment data is read-only. To request changes, contact Student Services.</p>
        </CardContent></Card>
    </div>
  );
}
