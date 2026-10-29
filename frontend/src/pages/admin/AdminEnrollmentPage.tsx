import { useState } from 'react'
import { useNavigate } from 'react-router-dom'

import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { ErrorState } from '@/components/common/ErrorState'
import { LoadingState } from '@/components/common/LoadingState'
import { PageHeader } from '@/components/common/PageHeader'
import { StatusBadge } from '@/components/common/StatusBadge'
import { SearchInput } from '@/components/common/SearchInput'
import { useAdminEnrollments } from '@/features/admin/adminHooks'
import { useAdminStudents } from '@/features/admin/adminHooks'
import type { AdminEnrollment, AdminStudent } from '../../features/admin/adminTypes'
import { routePaths } from '@/routes/routePaths'
import { Search, GraduationCap, UserSearch } from 'lucide-react'
import { cn } from '@/lib/utils'

export default function AdminEnrollmentPage() {
  const navigate = useNavigate()
  const [studentId, setStudentId] = useState('')
  const [search, setSearch] = useState('')
  const [program, setProgram] = useState('')
  const [status, setStatus] = useState('')

  const studentsQ = useAdminStudents()
  const students: AdminStudent[] = studentsQ.data?.students ?? []

  const q = useAdminEnrollments(studentId, { search, program, status })
  const enrollments: AdminEnrollment[] = q.data?.enrollments ?? []

  if (studentsQ.isPending || q.isPending) return (
    <div className="space-y-4">
      <PageHeader title="Enrollment Management" description="Manage student enrollment records." />
      <LoadingState label="Loading enrollment data..." />
    </div>
  )

  if (studentsQ.isError || q.isError) return (
    <div className="space-y-4">
      <PageHeader title="Enrollment Management" />
      <Card className="mt-6">
        <CardContent className="p-2">
          <ErrorState title="Unable to load enrollment data" onRetry={() => { void studentsQ.refetch(); void q.refetch() }} />
        </CardContent>
      </Card>
    </div>
  )

  if (!studentId) {
    return (
      <div className="space-y-6">
        <PageHeader
          title="Enrollment Management"
          description="Select a student to view and manage their enrollment records."
        />
        <Card>
          <CardContent className="p-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <div className="relative w-full sm:w-80">
                <UserSearch className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                <Select
                  value={studentId}
                  onValueChange={setStudentId}
                  placeholder="Select a student..."
                >
                  <option value="">Choose a student</option>
                  {students.map((s) => (
                    <option key={s.id} value={s.id}>
                      {s.studentId} — {s.fullName} ({s.program})
                    </option>
                  ))}
                </Select>
              </div>
              <p className="text-sm text-muted-foreground">
                Enrollment records are managed per student. Select a student above to view their enrollments.
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    )
  }

  const selectedStudent = students.find((s) => s.id === studentId)

  return (
    <div className="space-y-6">
      <PageHeader
        title="Enrollment Management"
        description={`Enrollments for ${selectedStudent?.fullName ?? ''} (${selectedStudent?.studentId ?? ''})`}
        actions={
          <div className="flex items-center gap-2">
            <Button
              onClick={() => setStudentId('')}
              variant="outline"
              size="sm"
            >
              <Search className="h-4 w-4 mr-2" aria-hidden="true" />
              Choose another student
            </Button>
            <Button
              onClick={() => navigate(routePaths.admin.students)}
              variant="outline"
              size="sm"
            >
              <GraduationCap className="h-4 w-4 mr-2" aria-hidden="true" />
              Manage Students
            </Button>
          </div>
        }
      />

      <Card>
        <CardContent className="p-6">
          <div className="grid gap-4 sm:grid-cols-3 lg:grid-cols-4 mb-6">
            <div className="relative">
              <SearchInput
                value={search}
                onChange={(v) => {
                  setSearch(v)
                  void q.refetch()
                }}
                placeholder="Search by program..."
              />
            </div>
            <div className="relative">
              <Select
                value={program}
                onValueChange={(v) => setProgram(v ?? '')}
                onChange={(): void => void q.refetch()}
              >
                <option value="">All Programs</option>
                <option value="BSIT">BS Information Technology</option>
                <option value="BSIS">BS Information Systems</option>
                <option value="BSCS">BS Computer Science</option>
                <option value="BSHM">BS Hospitality Management</option>
              </Select>
            </div>
            <div className="relative">
              <Select
                value={status}
                onValueChange={(v) => setStatus(v ?? '')}
                onChange={(): void => void q.refetch()}
              >
                <option value="">All Statuses</option>
                <option value="Enrolled">Enrolled</option>
                <option value="Not Enrolled">Not Enrolled</option>
                <option value="On Leave">On Leave</option>
                <option value="Graduated">Graduated</option>
              </Select>
            </div>
          </div>

          {enrollments.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <p className="text-sm text-muted-foreground">No enrollment records found for this student.</p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Student ID</TableHead>
                  <TableHead className="text-left">Academic Year</TableHead>
                  <TableHead className="text-left">Semester</TableHead>
                  <TableHead className="text-left">Program</TableHead>
                  <TableHead className="text-left">Year Level</TableHead>
                  <TableHead className="text-center">Status</TableHead>
                  <TableHead className="text-right">Enrolled</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {enrollments.map((e) => (
                  <TableRow key={e.id}>
                    <TableCell className="font-medium text-foreground">{e.studentId}</TableCell>
                    <TableCell>{e.academicYear}</TableCell>
                    <TableCell>{e.semester}</TableCell>
                    <TableCell>{e.program}</TableCell>
                    <TableCell>{e.yearLevel}</TableCell>
                    <TableCell className="text-center">
                      <StatusBadge status={e.status} />
                    </TableCell>
                    <TableCell className="text-right text-sm text-muted-foreground">
                      {new Date(e.createdAt).toLocaleDateString()}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>
    </div>
  )
}