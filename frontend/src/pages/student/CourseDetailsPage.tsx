import { Link, useParams } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ErrorState } from '@/components/common/ErrorState';
import { LoadingState } from '@/components/common/LoadingState';
import { PageHeader } from '@/components/common/PageHeader';
import { useCourse } from '@/features/courses/courseHooks';
import { ApiError } from '@/lib/apiClient';
import { routePaths } from '@/routes/routePaths';

export default function CourseDetailsPage() {
  const { id } = useParams<{ id: string }>();
  const q = useCourse(id);
  if (q.isPending) return (<div><PageHeader title="Course Details" /><Card className="mt-6"><CardContent><LoadingState label="Loading course..." /></CardContent></Card></div>);
  if (q.isError || !q.data) { const s = q.error instanceof ApiError ? q.error.status : 0; return (<div className="space-y-6"><PageHeader title="Course Details" breadcrumb={[{ label: 'Courses', href: routePaths.student.courses }, { label: 'Details' }]} /><Card><CardContent className="p-2"><ErrorState title={s === 404 ? 'Course not found' : undefined} onRetry={() => void q.refetch()} /></CardContent></Card></div>); }
  const c = q.data.course;
  const rows: Array<[string, string]> = [['Program', c.program], ['Year Level', c.yearLevel], ['Semester', c.semester]];
  return (
    <div className="space-y-6">
      <PageHeader title={c.courseCode} description={c.title} breadcrumb={[{ label: 'Courses', href: routePaths.student.courses }, { label: c.courseCode }]} actions={<Button variant="outline" asChild><Link to={routePaths.student.courses}><ArrowLeft className="h-4 w-4" aria-hidden="true" />Back</Link></Button>} />
      <Card><CardHeader><CardTitle>Course information</CardTitle></CardHeader><CardContent><div className="mb-4 flex gap-2">{c.active ? <Badge variant="success">Active</Badge> : <Badge variant="secondary">Archived</Badge>}</div><dl className="grid gap-x-8 gap-y-4 sm:grid-cols-2">{rows.map(([k, v]) => (<div key={k}><dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{k}</dt><dd className="mt-1 text-sm font-medium">{v || '—'}</dd></div>))}</dl><div className="mt-4"><p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Description</p><p className="mt-1 text-sm">{c.description || 'No description available.'}</p></div></CardContent></Card>
    </div>
  );
}
