import { zodResolver } from '@hookform/resolvers/zod';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Link } from 'react-router-dom';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FormError } from '@/components/forms/FormError';
import { PageHeader } from '@/components/common/PageHeader';
import { SelectField } from '@/components/forms/SelectField';
import { TextField } from '@/components/forms/TextField';
import { TextareaField } from '@/components/forms/TextareaField';
import { useCreateRequest } from '@/features/documents/documentHooks';
import { DELIVERY_METHODS, DOCUMENT_TYPES, type RequestDetails } from '@/features/documents/documentTypes';
import { documentRequestSchema, type DocumentRequestFormValues } from '@/features/documents/documentSchemas';
import { ApiError } from '@/lib/apiClient';
import { routePaths } from '@/routes/routePaths';

export default function DocumentRequestPage() {
  const create = useCreateRequest();
  const [done, setDone] = useState<RequestDetails | null>(null);
  const form = useForm<DocumentRequestFormValues>({ resolver: zodResolver(documentRequestSchema), defaultValues: { documentType: undefined as never, purpose: '', copies: 1, deliveryMethod: 'Pickup', additionalInstructions: '' } });
  const submit = (v: DocumentRequestFormValues) => { setDone(null); create.mutate({ documentType: v.documentType, purpose: v.purpose.trim(), copies: v.copies, deliveryMethod: v.deliveryMethod, additionalInstructions: v.additionalInstructions?.trim() || undefined }, { onSuccess: (r) => { setDone(r.request); form.reset(); } }); };
  const apiErr = create.error instanceof ApiError ? create.error : null;
  if (done) return (<div className="space-y-6"><PageHeader title="Document Request" /><Card><CardContent className="pt-6"><Alert variant="success" title="Request submitted">Your request <strong>{done.requestNumber}</strong> is now Pending. Track it on the Request Status page.</Alert><div className="mt-4 flex flex-wrap gap-2"><Button asChild><Link to={routePaths.student.requestDetails(done.id)}>View request</Link></Button><Button variant="outline" onClick={() => setDone(null)}>Submit another</Button></div></CardContent></Card></div>);
  return (
    <div className="space-y-6"><PageHeader title="Request Document" description="Submit a request for official documents." />
      <Card><CardHeader><CardTitle>New request</CardTitle></CardHeader><CardContent>
        <form onSubmit={form.handleSubmit(submit)} className="grid gap-4 sm:grid-cols-2" noValidate>
          <SelectField control={form.control} name="documentType" label="Document type" required placeholder="Select a document" options={DOCUMENT_TYPES.map(t => ({ value: t, label: t }))} />
          <TextField control={form.control} name="copies" label="Number of copies" required type="text" inputMode="numeric" />
          <div className="sm:col-span-2"><TextField control={form.control} name="purpose" label="Purpose of request" required placeholder="e.g. Scholarship application" /></div>
          <SelectField control={form.control} name="deliveryMethod" label="Delivery preference" required options={DELIVERY_METHODS.map(m => ({ value: m, label: m }))} />
          <div className="sm:col-span-2"><TextareaField control={form.control} name="additionalInstructions" label="Additional instructions" hint="Optional, up to 1000 characters." rows={3} maxLength={1000} /></div>
          {apiErr && <div className="sm:col-span-2"><FormError message={apiErr.fields ? 'Please review the highlighted fields.' : apiErr.message} /></div>}
          <div className="sm:col-span-2"><Button type="submit" loading={create.isPending}>Submit request</Button></div>
        </form>
      </CardContent></Card></div>
  );
}
