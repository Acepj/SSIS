import { Link } from 'react-router-dom';
import { Eye, Inbox } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { EmptyState } from '@/components/common/EmptyState';
import { StatusBadge } from '@/components/common/StatusBadge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { DashboardRequest } from '@/features/students/studentTypes';
import { formatDate } from '@/lib/dateUtils';
import { routePaths } from '@/routes/routePaths';

/** Latest requests table with a link to the full tracking page. */
export function RecentRequests({ requests }: { requests: DashboardRequest[] }) {
  return (
    <Card className="xl:col-span-3">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
        <div><CardTitle>Recent Requests</CardTitle><CardDescription>Your latest requests.</CardDescription></div>
        <Button variant="link" size="sm" asChild className="px-0"><Link to={routePaths.student.requestStatus}>View all</Link></Button>
      </CardHeader>
      <CardContent className="pt-0">
        {requests.length === 0 ? (
          <EmptyState icon={Inbox} title="No requests yet" description="Submit your first document request." action={<Button size="sm" asChild><Link to={routePaths.student.documentRequest}>Request a document</Link></Button>} />
        ) : (
          <Table>
            <TableHeader><TableRow><TableHead>Document</TableHead><TableHead>Request ID</TableHead><TableHead>Date</TableHead><TableHead>Status</TableHead><TableHead className="text-right">Action</TableHead></TableRow></TableHeader>
            <TableBody>{requests.map((r) => (
              <TableRow key={r.id}>
                <TableCell className="max-w-[160px] truncate font-medium">{r.documentType}</TableCell>
                <TableCell className="whitespace-nowrap text-muted-foreground">{r.requestNumber}</TableCell>
                <TableCell className="whitespace-nowrap text-muted-foreground">{formatDate(r.createdAt)}</TableCell>
                <TableCell><StatusBadge status={r.status} /></TableCell>
                <TableCell className="text-right"><Button variant="ghost" size="sm" asChild><Link to={routePaths.student.requestDetails(r.id)}><Eye className="h-4 w-4" aria-hidden="true" />View</Link></Button></TableCell>
              </TableRow>
            ))}</TableBody>
          </Table>
        )}
      </CardContent>
    </Card>
  );
}
