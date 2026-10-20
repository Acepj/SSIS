import { zodResolver } from '@hookform/resolvers/zod';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { UserRound } from 'lucide-react';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FormError } from '@/components/forms/FormError';
import { PageHeader } from '@/components/common/PageHeader';
import { TextField } from '@/components/forms/TextField';
import { ErrorState } from '@/components/common/ErrorState';
import { LoadingState } from '@/components/common/LoadingState';
import { StatusBadge } from '@/components/common/StatusBadge';
import { useStudentProfile, useUpdateProfile } from '@/features/students/studentHooks';
import { profileUpdateSchema, type ProfileUpdateFormValues } from '@/features/students/studentSchemas';
import { ApiError } from '@/lib/apiClient';

export default function StudentProfilePage() {
  const q = useStudentProfile();
  const update = useUpdateProfile();
  const form = useForm<ProfileUpdateFormValues>({ resolver: zodResolver(profileUpdateSchema), defaultValues: { email: '', contactNumber: '' } });
  useEffect(() => { if (q.data) form.reset({ email: q.data.student.email, contactNumber: q.data.student.contactNumber }); }, [q.data, form]);
  if (q.isPending) return (<div><PageHeader title="Profile" /><Card className="mt-6"><CardContent><LoadingState label="Loading profile..." /></CardContent></Card></div>);
  if (q.isError || !q.data) return (<div><PageHeader title="Profile" /><Card className="mt-6"><CardContent className="p-2"><ErrorState onRetry={() => void q.refetch()} /></CardContent></Card></div>);
  const s = q.data.student;
  const apiErr = update.error instanceof ApiError ? update.error : null;
  const official: Array<[string, string]> = [['Student ID', s.studentId], ['Full Name', s.fullName], ['Program', s.program], ['Year Level', s.yearLevel]];
  return (
    <div className="space-y-6">
      <PageHeader title="Profile" description="View your record and keep contact details current." />
      <Card><CardHeader><CardTitle className="flex items-center gap-2"><UserRound className="h-5 w-5 text-primary" aria-hidden="true" />{s.fullName}</CardTitle></CardHeader>
        <CardContent><div className="mb-4"><StatusBadge status={s.enrollmentStatus} /></div><dl className="grid gap-x-8 gap-y-4 sm:grid-cols-2">{official.map(([k, v]) => (<div key={k}><dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{k}</dt><dd className="mt-1 text-sm font-medium">{v || '—'}</dd></div>))}</dl></CardContent></Card>
      <Card><CardHeader><CardTitle>Contact information</CardTitle></CardHeader><CardContent>
        {update.isSuccess && <div className="mb-4"><Alert variant="success" title="Saved">Your contact details were updated.</Alert></div>}
        <form onSubmit={form.handleSubmit((v) => update.mutate(v))} className="grid gap-4 sm:grid-cols-2" noValidate>
          <TextField control={form.control} name="email" label="Email address" required type="email" autoComplete="email" />
          <TextField control={form.control} name="contactNumber" label="Contact number" required type="tel" autoComplete="tel" />
          {apiErr && <div className="sm:col-span-2"><FormError message={apiErr.fields ? 'Please review the highlighted fields.' : apiErr.message} /></div>}
          <div className="sm:col-span-2"><Button type="submit" loading={update.isPending}>Save changes</Button></div>
        </form>
        <p className="mt-4 text-xs text-muted-foreground">Program, year level and enrollment status are official records and cannot be changed here.</p>
      </CardContent></Card>
    </div>
  );
}
