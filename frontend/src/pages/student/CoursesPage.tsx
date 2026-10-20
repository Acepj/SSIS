import { useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { BookOpen } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { EmptyState } from '@/components/common/EmptyState';
import { ErrorState } from '@/components/common/ErrorState';
import { LoadingState } from '@/components/common/LoadingState';
import { PageHeader } from '@/components/common/PageHeader';
import { SearchInput } from '@/components/common/SearchInput';
import { FilterSelect } from '@/components/forms/SelectField';
import { useCourses } from '@/features/courses/courseHooks';
import { useDebounce } from '@/hooks/useDebounce';
import { routePaths } from '@/routes/routePaths';

export default function CoursesPage() {
  const [search, setSearch] = useState('');
  const [program, setProgram] = useState('');
  const [yearLevel, setYearLevel] = useState('');
  const [semester, setSemester] = useState('');
  const debounced = useDebounce(search, 300);
  const query = useMemo(() => ({ search: debounced.trim() || undefined, program: program || undefined, yearLevel: yearLevel || undefined, semester: semester || undefined }), [debounced, program, yearLevel, semester]);
  const q = useCourses(query);
  const filters = q.data?.filters;
  return (
    <div className="space-y-6">
      <PageHeader title="Courses" description="Read-only course catalog for your program." />
      <Card><CardContent className="pt-6">
        <div className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><SearchInput value={search} onChange={setSearch} placeholder="Search code or title..." className="sm:col-span-2 lg:col-span-1" /><FilterSelect label="Program" value={program} onChange={setProgram} options={[{ value: '', label: 'All programs' }, ...((filters?.programs ?? []).map(p => ({ value: p, label: p })) )]} /><FilterSelect label="Year level" value={yearLevel} onChange={setYearLevel} options={[{ value: '', label: 'All year levels' }, ...((filters?.yearLevels ?? []).map(y => ({ value: y, label: y })) )]} /><FilterSelect label="Semester" value={semester} onChange={setSemester} options={[{ value: '', label: 'All semesters' }, ...((filters?.semesters ?? []).map(s => ({ value: s, label: s })) )]} /></div>
        {q.isPending ? <LoadingState label="Loading courses..." /> : q.isError || !q.data ? <ErrorState onRetry={() => void q.refetch()} /> : q.data.courses.length === 0 ? <EmptyState icon={BookOpen} title="No courses found" description="Try different search terms or filters." /> : (
          <ul className="grid gap-4 md:grid-cols-2">{q.data.courses.map(c => (<li key={c.id}><Link to={routePaths.student.courseDetails(c.id)} className="block rounded-xl border border-border bg-card p-4 transition-colors hover:border-primary/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"><div className="flex items-start justify-between gap-2"><p className="text-sm font-semibold">{c.courseCode}</p><Badge variant="secondary">{c.semester}</Badge></div><p className="mt-1 text-sm text-foreground">{c.title}</p><p className="mt-1 line-clamp-2 text-xs text-muted-foreground">{c.description || 'No description available.'}</p><p className="mt-2 text-xs text-muted-foreground">{c.program} · {c.yearLevel}</p></Link></li>))}</ul>
        )}
      </CardContent></Card>
    </div>
  );
}
