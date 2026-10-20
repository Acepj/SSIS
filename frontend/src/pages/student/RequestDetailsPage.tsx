import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, History } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ConfirmDialog } from '@/components/common/ConfirmDialog';
import { ErrorState } from '@/components/common/ErrorState';
import { LoadingState } from '@/components/common/LoadingState';
import { PageHeader } from '@/components/common/PageHeader';
import { StatusBadge } from '@/components/common/StatusBadge';
import { useCancelRequest, useStudentRequest } from '@/features/documents/documentHooks';
import { ApiError } from '@/lib/apiClient';
import { formatDateTime } from '@/lib/dateUtils';
import { routePaths } from '@/routes/routePaths';

export default function RequestDetailsPage() {
  const { id } = useParams<{ id: string }>();
  const q = useStudentRequest(id);
  const cancel = useCancelRequest();
  const [confirm, setConfirm] = useState(false);
  if (q.isPending) return (<div><PageHeader title="Request Details" /><Card className="mt-6"><CardContent><LoadingState label="Loading request..." /></CardContent></Card></div>);
  if (q.isError || !q.data) { const s = q.error instanceof ApiError ? q.error.status : 0; return (<div className="space-y-6"><PageHeader title="Request Details" breadcrumb={[{ label: 'Request Status', href: routePaths.student.requestStatus }, { label: 'Details' }]} /><Card><CardContent className="p-2"><ErrorState title={s === 404 ? 'Request not found' : 'Something went wrong'} message={s === 404 ? 'This request does not exist or is not yours.' : undefined} onRetry={() => void q.refetch()} /></CardContent></Card></div>); }
  const r = q.data.request;
  const info: Array<[string, string]> = [['Request ID', r.requestNumber], ['Document', r.documentType], ['Purpose', r.purpose], ['Copies', String(r.copies)], ['Delivery', r.deliveryMethod], ['Instructions', r.additionalInstructions || '—'], ['Submitted', formatDateTime(r.createdAt)], ['Last updated', formatDateTime(r.updatedAt)]];
  return (
    <div className="space-y-6">
      <PageHeader title={r.requestNumber} description={r.documentType} breadcrumb={[{ label: 'Request Status', href: routePaths.student.requestStatus }, { label: r.requestNumber }]} actions={<>{r.canCancel && <Button variant="outline" onClick={() => setConfirm(true)}>Cancel request</Button>}<Button variant="outline" asChild><Link to={routePaths.student.requestStatus}><ArrowLeft className="h-4 w-4" aria-hidden="true" />Back</Link></Button></>} />
      <div className="grid gap-6 lg:grid-cols-2">
        <Card><CardHeader><CardTitle>Request information</CardTitle></CardHeader><CardContent><div className="mb-3"><StatusBadge status={r.status} /></div><dl className="space-y-3">{info.map(([k, v]) => (<div key={k} className="grid grid-cols-3 gap-2 text-sm"><dt className="text-muted-foreground">{k}</dt><dd className="col-span-2 font-medium">{v}</dd></div>))}</dl>{r.processingNotes !== '' && <div className="mt-4 rounded-lg bg-muted p-3 text-sm"><p className="font-semibold">Note from Student Services</p><p className="mt-1 text-muted-foreground">{r.processingNotes}</p></div>}</CardContent></Card>
        <Card><CardHeader><CardTitle className="flex items-center gap-2"><History className="h-5 w-5 text-primary" aria-hidden="true" />Status history</CardTitle></CardHeader><CardContent>{r.statusHistory.length === 0 ? <p className="text-sm text-muted-foreground">No status changes recorded yet.</p> : (<ol className="space-y-4">{r.statusHistory.map((h, i) => (<li key={i} className="border-l-2 border-border pl-4 text-sm"><p className="font-medium">{h.previousStatus ?? 'Created'} → {h.newStatus}</p><p className="text-xs text-muted-foreground">{h.changedByName} · {formatDateTime(h.changedAt)}</p>{h.note !== '' && <p className="mt-1 text-muted-foreground">{h.note}</p>}</li>))}</ol>)}</CardContent></Card>
      </div>
      <ConfirmDialog open={confirm} onOpenChange={setConfirm} title="Cancel this request?" description="Cancelled requests cannot be reopened. This is recorded through the backend, not deleted." confirmLabel="Yes, cancel" tone="danger" loading={cancel.isPending} onConfirm={() => cancel.mutate(r.id, { onSuccess: () => setConfirm(false) })} />
    </div>
  );
}
