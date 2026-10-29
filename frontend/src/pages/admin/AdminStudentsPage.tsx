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
import { useAdminStudents, useActivateAdminStudent, useCreateAdminStudent, useDeactivateAdminStudent } from '@/features/admin/adminHooks'
import type { AdminStudent } from '@/features/admin/adminTypes'
import { routePaths } from '@/routes/routePaths'
import { Plus, Search, User } from 'lucide-react'
import { cn } from '@/lib/utils'

export default function AdminStudentsPage() {
  const navigate = useNavigate()
  const [search, setSearch] = useState('')
  const [program, setProgram] = useState('')
  const [status, setStatus] = useState('')

  const q = useAdminStudents({ search, program, status })
  const createStudent = useCreateAdminStudent()
  const activateStudent = useActivateAdminStudent()
  const deactivateStudent = useDeactivateAdminStudent()

  const students: AdminStudent[] = q.data?.students ?? []

  const handleCreate = () => navigate(routePaths.admin.createStudent)
  const handleView = (id: string) => navigate(routePaths.admin.studentDetails(id))
  const handleToggle = (id: string, active: boolean) => {
    if (active) void deactivateStudent.mutate(id)
    else void activateStudent.mutate(id)
  }

  if (q.isPending) return (
    <div className="space-y-4">
      <PageHeader title="Students" description="Manage student accounts and records." />
      <div className="space-y-4">
        <LoadingState label="Loading students..." />
      </div>
    </div>
  )

  if (q.isError) return (
    <div className="space-y-4">
      <PageHeader title="Students" />
      <Card className="mt-6">
        <CardContent className="p-2">
          <ErrorState
            title="Unable to load students"
            onRetry={() => void q.refetch()}
          />
        </CardContent>
      </Card>
    </div>
  )

  return (
    <div className="space-y-6">
      <PageHeader
        title="Students"
        description="View, create, and manage student accounts."
        actions={
          <Button
            onClick={handleCreate}
            disabled={createStudent.isPending}
            className="ml-auto"
          >
            {createStudent.isPending ? null : <Plus className="h-4 w-4" aria-hidden="true" />}
            Create Student
          </Button>
        }
      />

      <Card>
        <CardContent className="p-6">
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div className="relative">
              <SearchInput
                value={search}
                onChange={(v) => {
                  setSearch(v)
                  void q.refetch()
                }}
                placeholder="Search by student ID or name..."
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
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </Select>
            </div>
            <div className="flex items-end">
              <Button variant="outline" onClick={() => void q.refetch()} className="w-full">
                <Search className="h-4 w-4 mr-2" aria-hidden="true" />
                Refresh
              </Button>
            </div>
          </div>

          {students.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <div className="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                <User className="h-6 w-6 text-muted-foreground" aria-hidden="true" />
              </div>
              <h3 className="mt-4 text-sm font-semibold text-foreground">No students found</h3>
              <p className="mt-1 text-sm text-muted-foreground">
                Try adjusting your search or filters.
              </p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Student ID</TableHead>
                  <TableHead className="text-left">Name</TableHead>
                  <TableHead className="text-left">Email</TableHead>
                  <TableHead className="text-left">Program</TableHead>
                  <TableHead className="text-left">Year Level</TableHead>
                  <TableHead className="text-center">Status</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {students.map((s) => (
                  <TableRow key={s.id}>
                    <TableCell className="font-medium text-foreground">{s.studentId}</TableCell>
                    <TableCell className="text-foreground">
                      <Button
                        variant="link"
                        className="text-left hover:underline text-foreground"
                        onClick={() => handleView(s.id)}
                      >
                        {s.fullName}
                      </Button>
                    </TableCell>
                    <TableCell>{s.email}</TableCell>
                    <TableCell>{s.program}</TableCell>
                    <TableCell>{s.yearLevel}</TableCell>
                    <TableCell className="text-center">
                      <StatusBadge status={s.status} />
                    </TableCell>
                    <TableCell className="text-right">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleToggle(s.id, s.status === 'Active')}
                        className="w-20"
                      >
                        {s.status === 'Active' ? 'Deactivate' : 'Activate'}
                      </Button>
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