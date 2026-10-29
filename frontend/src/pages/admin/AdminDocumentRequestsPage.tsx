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
import { useAdminDocumentRequests, useUpdateAdminRequestStatus } from '@/features/admin/adminHooks'
import type { AdminDocumentRequest } from '@/features/admin/adminTypes'
import { routePaths } from '@/routes/routePaths'
import { Search, FileText } from 'lucide-react'

export default function AdminDocumentRequestsPage() {
  const navigate = useNavigate()
  const [search, setSearch] = useState('')
  const [status, setStatus] = useState('')

  const q = useAdminDocumentRequests({ search, status })
  const updateStatus = useUpdateAdminRequestStatus()
  const requests: AdminDocumentRequest[] = q.data?.requests ?? []

  if (q.isPending) return (
    <div className="space-y-4">
      <PageHeader title="Document Requests" description="Process and manage document requests." />
      <LoadingState label="Loading requests..." />
    </div>
  )

  if (q.isError) return (
    <div className="space-y-4">
      <PageHeader title="Document Requests" />
      <Card className="mt-6">
        <CardContent className="p-2">
          <ErrorState title="Unable to load requests" onRetry={() => void q.refetch()} />
        </CardContent>
      </Card>
    </div>
  )

  const handleStatusChange = (id: string, newStatus: string) => {
    void updateStatus.mutate({ id, status: newStatus as any })
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Document Requests"
        description="Review and process student document requests."
      />

      <Card>
        <CardContent className="p-6">
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
            <div className="relative">
              <SearchInput
                value={search}
                onChange={(v) => {
                  setSearch(v)
                  void q.refetch()
                }}
                placeholder="Search by request number or student name..."
              />
            </div>
            <div className="relative">
              <Select
                value={status}
                onValueChange={(v) => setStatus(v ?? '')}
                onChange={(): void => void q.refetch()}
              >
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Ready for Pickup">Ready for Pickup</option>
                <option value="Completed">Completed</option>
                <option value="Rejected">Rejected</option>
                <option value="Cancelled">Cancelled</option>
              </Select>
            </div>
          </div>

          {requests.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <p className="text-sm text-muted-foreground">No document requests found.</p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Request #</TableHead>
                  <TableHead className="text-left">Student</TableHead>
                  <TableHead className="text-left">Document Type</TableHead>
                  <TableHead className="text-left">Purpose</TableHead>
                  <TableHead className="text-center">Status</TableHead>
                  <TableHead className="text-right">Created</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {requests.map((r) => (
                  <TableRow key={r.id}>
                    <TableCell className="font-medium text-foreground">{r.requestNumber}</TableCell>
                    <TableCell className="text-foreground">{r.studentName}</TableCell>
                    <TableCell>{r.documentType}</TableCell>
                    <TableCell className="text-sm text-muted-foreground">{r.purpose}</TableCell>
                    <TableCell className="text-center">
                      <StatusBadge status={r.status} />
                    </TableCell>
                    <TableCell className="text-right text-sm text-muted-foreground">
                      {new Date(r.createdAt).toLocaleDateString()}
                    </TableCell>
                    <TableCell className="text-right">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => navigate(routePaths.admin.requestDetails(r.id))}
                      >
                        View
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