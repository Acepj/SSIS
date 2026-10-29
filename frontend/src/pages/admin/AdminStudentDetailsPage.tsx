import { useParams } from 'react-router-dom'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ErrorState } from '@/components/common/ErrorState'
import { LoadingState } from '@/components/common/LoadingState'
import { PageHeader } from '@/components/common/PageHeader'
import { StatusBadge } from '@/components/common/StatusBadge'
import { useAdminStudent, useActivateAdminStudent, useDeactivateAdminStudent } from '@/features/admin/adminHooks'
import { routePaths } from '@/routes/routePaths'
import { Edit, User } from 'lucide-react'

export default function AdminStudentDetailsPage() {
  const { id } = useParams()
  const studentId = id ?? ''

  const q = useAdminStudent(studentId ? studentId : undefined)
  const activateStudent = useActivateAdminStudent()
  const deactivateStudent = useDeactivateAdminStudent()

  if (q.isPending) return (
    <div className="space-y-4">
      <PageHeader title="Student Details" />
      <div className="space-y-4">
        <LoadingState label="Loading student..." />
      </div>
    </div>
  )

  if (q.isError || !q.data) return (
    <div className="space-y-4">
      <PageHeader title="Student Details" />
      <Card className="mt-6">
        <CardContent className="p-2">
          <ErrorState title="Student not found" />
        </CardContent>
      </Card>
    </div>
  )

  const s = q.data
  const isActive = s.status === 'Active'

  return (
    <div className="space-y-6">
      <PageHeader title="Student Details" description={`Account for ${s.studentId}`} />
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <User className="h-5 w-5 text-primary" aria-hidden="true" />
            {s.fullName}
          </CardTitle>
        </CardHeader>
        <CardContent className="pt-0">
          <dl className="grid gap-x-8 gap-y-4 sm:grid-cols-3">
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Student ID</dt>
              <dd className="mt-1 text-sm font-medium text-foreground">{s.studentId}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Full Name</dt>
              <dd className="mt-1 text-sm text-foreground">{s.fullName}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Email</dt>
              <dd className="mt-1 text-sm text-foreground">{s.email}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Contact Number</dt>
              <dd className="mt-1 text-sm text-foreground">{s.contactNumber}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Program</dt>
              <dd className="mt-1 text-sm text-foreground">{s.program}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Year Level</dt>
              <dd className="mt-1 text-sm text-foreground">{s.yearLevel}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Enrollment Status</dt>
              <dd className="mt-1 text-sm text-foreground">{s.enrollmentStatus}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Account Status</dt>
              <dd className="mt-1 text-sm text-foreground">
                <StatusBadge status={s.status} />
              </dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Member Since</dt>
              <dd className="mt-1 text-sm text-foreground">{new Date(s.createdAt).toLocaleDateString()}</dd>
            </div>
          </dl>

          <div className="mt-6 flex items-center gap-3">
            <Button
              variant="outline"
              size="sm"
              asChild
              className="w-32"
            >
              <a href={routePaths.admin.editStudent(s.id)}>
                <Edit className="h-4 w-4 mr-2" aria-hidden="true" />
                Edit Student
              </a>
            </Button>
            <Button
              size="sm"
              variant={isActive ? 'danger' : 'default'}
              onClick={() => (isActive ? void deactivateStudent.mutate(s.id) : void activateStudent.mutate(s.id))}
              disabled={activateStudent.isPending || deactivateStudent.isPending}
            >
              {isActive ? 'Deactivate Account' : 'Activate Account'}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}