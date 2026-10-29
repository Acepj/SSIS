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
import { SearchInput } from '@/components/common/SearchInput'
import { useAdminCourses } from '@/features/admin/adminHooks'
import type { AdminCourse } from '@/features/admin/adminTypes'
import { routePaths } from '@/routes/routePaths'
import { Search, BookOpen } from 'lucide-react'

export default function AdminCoursesPage() {
  const navigate = useNavigate()
  const [search, setSearch] = useState('')
  const [program, setProgram] = useState('')
  const [active, setActive] = useState('')

  const q = useAdminCourses({ search, program, yearLevel: '', semester: '', active })
  const courses: AdminCourse[] = q.data?.courses ?? []

  if (q.isPending) return (
    <div className="space-y-4">
      <PageHeader title="Course Management" description="Manage course offerings." />
      <LoadingState label="Loading courses..." />
    </div>
  )

  if (q.isError) return (
    <div className="space-y-4">
      <PageHeader title="Course Management" />
      <Card className="mt-6">
        <CardContent className="p-2">
          <ErrorState title="Unable to load courses" onRetry={() => void q.refetch()} />
        </CardContent>
      </Card>
    </div>
  )

  return (
    <div className="space-y-6">
      <PageHeader
        title="Course Management"
        description="View, create, and update course records."
        actions={
          <Button
            onClick={() => navigate(routePaths.admin.students)}
            variant="outline"
          >
            <BookOpen className="h-4 w-4 mr-2" aria-hidden="true" />
            Manage Students
          </Button>
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
                placeholder="Search by course code or title..."
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
                value={active}
                onValueChange={(v) => setActive(v ?? '')}
                onChange={(): void => void q.refetch()}
              >
                <option value="">All Courses</option>
                <option value="true">Active</option>
                <option value="false">Archived</option>
              </Select>
            </div>
          </div>

          {courses.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <p className="text-sm text-muted-foreground">No course records found.</p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Course Code</TableHead>
                  <TableHead className="text-left">Title</TableHead>
                  <TableHead className="text-left">Description</TableHead>
                  <TableHead className="text-left">Program</TableHead>
                  <TableHead className="text-left">Year Level</TableHead>
                  <TableHead className="text-left">Semester</TableHead>
                  <TableHead className="text-center">Active</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {courses.map((c) => (
                  <TableRow key={c.id}>
                    <TableCell className="font-medium text-foreground">{c.courseCode}</TableCell>
                    <TableCell>{c.title}</TableCell>
                    <TableCell className="text-sm text-muted-foreground">{c.description}</TableCell>
                    <TableCell>{c.program}</TableCell>
                    <TableCell>{c.yearLevel}</TableCell>
                    <TableCell>{c.semester}</TableCell>
                    <TableCell className="text-center">
                      {c.active ? (
                        <span className="text-xs font-medium text-success">Yes</span>
                      ) : (
                        <span className="text-xs font-medium text-muted-foreground">No</span>
                      )}
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