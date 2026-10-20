import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Eye, Inbox } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { EmptyState } from '@/components/common/EmptyState';
import { ErrorState } from '@/components/common/ErrorState';
import { LoadingState } from '@/components/common/LoadingState';
import { PageHeader } from '@/components/common/PageHeader';
import { SearchInput } from '@/components/common/SearchInput';
import { StatusBadge } from '@/components/common/StatusBadge';
import { FilterSelect } from '@/components/forms/SelectField';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useStudentRequests } from '@/features/documents/documentHooks';
import { useDebounce } from '@/hooks/useDebounce';
import { formatDate, formatRelativeTime } from '@/lib/dateUtils';
import { routePaths } from '@/routes/routePaths';

const FILTERS = ['All', 'Pending', 'Processing', 'Ready for Pickup', 'Completed', 'Rejected', 'Cancelled'];

export default function RequestStatusPage() {
  const [status, setStatus] = useState('All');
  const [search, setSearch] = useState('');
  const debounced = useDebounce(search, 300);
  const q = useStudentRequests({ status, search: debounced.trim() || undefined });
  return (
    <div className="space-y-6">
      <PageHeader title="Request Status" description="Track all your document requests." actions={<Button asChild><Link to={routePaths.student.documentRequest}>New request</Link></Button>} />
      <Card><CardContent className="pt-6">
        <div className="mb-4 flex flex-col gap-3 sm:flex-row"><SearchInput value={search} onChange={setSearch} placeholder="Search by request ID or document..." className="sm:max-w-xs" /><FilterSelect label="Filter by status" value={status} onChange={setStatus} options={FILTERS.map(f => ({ value: f, label: f === 'All' ? 'All Requests' : f }))} className="sm:w-52" /></div>
        {q.isPending ? <LoadingState label="Loading requests..." /> : q.isError || !q.data ? <ErrorState onRetry={() => void q.refetch()} /> : q.data.requests.length === 0 ? <EmptyState icon={Inbox} title="No requests found" description={search || status !== 'All' ? 'Try a different search or filter.' : 'Submit your first document request.'} /> : (
          <Table><TableHeader><TableRow><TableHead>Request ID</TableHead><TableHead>Document</TableHead><TableHead>Submitted</TableHead><TableHead>Status</TableHead><TableHead>Updated</TableHead><TableHead className="text-right">Action</TableHead></TableRow></TableHeader>
          <TableBody>{q.data.requests.map(r => (<TableRow key={r.id}><TableCell className="font-medium">{r.requestNumber}</TableCell><TableCell className="max-w-[180px] truncate">{r.documentType}</TableCell><TableCell className="whitespace-nowrap text-muted-foreground">{formatDate(r.createdAt)}</TableCell><TableCell><StatusBadge status={r.status} /></TableCell><TableCell className="whitespace-nowrap text-muted-foreground">{formatRelativeTime(r.updatedAt)}</TableCell><TableCell className="text-right"><Button variant="ghost" size="sm" asChild><Link to={routePaths.student.requestDetails(r.id)}><Eye className="h-4 w-4" aria-hidden="true" />View</Link></Button></TableCell></TableRow>))}</TableBody></Table>
        )}
      </CardContent></Card>
    </div>
  );
}
