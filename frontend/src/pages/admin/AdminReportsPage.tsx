import { useState } from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { ErrorState } from '@/components/common/ErrorState'
import { LoadingState } from '@/components/common/LoadingState'
import { PageHeader } from '@/components/common/PageHeader'
import { useAdminReportSummary, useExportAdminRequestsCsv } from '@/features/admin/adminHooks'
import type { ReportSummary } from '@/features/admin/adminTypes'
import { BarChart3, FileText } from 'lucide-react'
import { cn } from '@/lib/utils'

export default function AdminReportsPage() {
  const [dateFrom, setDateFrom] = useState('')
  const [dateTo, setDateTo] = useState('')

  const q = useAdminReportSummary({ dateFrom, dateTo })
  const exportCsv = useExportAdminRequestsCsv()
  const summary: ReportSummary | undefined = q.data?.summary

  if (q.isPending) return (
    <div className="space-y-4">
      <PageHeader title="Reports" description="System statistics and exports." />
      <LoadingState label="Loading report data..." />
    </div>
  )

  if (q.isError) return (
    <div className="space-y-4">
      <PageHeader title="Reports" />
      <Card className="mt-6">
        <CardContent className="p-2">
          <ErrorState title="Unable to load reports" onRetry={() => void q.refetch()} />
        </CardContent>
      </Card>
    </div>
  )

  return (
    <div className="space-y-6">
      <PageHeader
        title="Reports"
        description="View system statistics and export data."
        actions={
          <Button
            onClick={() => void exportCsv.mutate({ dateFrom, dateTo })}
            disabled={exportCsv.isPending}
            variant="outline"
          >
            {exportCsv.isPending ? null : <FileText className="h-4 w-4 mr-2" aria-hidden="true" />}
            Export CSV
          </Button>
        }
      />

      <Card>
        <CardContent className="p-6">
          <div className="grid gap-4 sm:grid-cols-2 mb-6">
            <div className="relative">
              <Input
                type="date"
                value={dateFrom}
                onChange={(e) => setDateFrom(e.target.value)}
                placeholder="From date"
                className="w-full"
              />
            </div>
            <div className="relative">
              <Input
                type="date"
                value={dateTo}
                onChange={(e) => setDateTo(e.target.value)}
                placeholder="To date"
                className="w-full"
              />
            </div>
          </div>

          {!summary ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <p className="text-sm text-muted-foreground">No report data available.</p>
            </div>
          ) : (
            <>
              <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <SummaryCard
                  label="Total Students"
                  value={String(summary.totalStudents)}
                  sub={`${summary.activeStudents} active`}
                  iconClassName="bg-primary-soft text-primary"
                />
                <SummaryCard
                  label="Total Requests"
                  value={String(summary.totalRequests)}
                  sub={`${summary.pendingRequests} pending`}
                  iconClassName="bg-warning-soft text-warning-strong"
                />
                <SummaryCard
                  label="Completed Requests"
                  value={String(summary.completedRequests)}
                  sub="Ready or finished"
                  iconClassName="bg-success-soft text-success-strong"
                />
                <SummaryCard
                  label="Total Enrollments"
                  value={String(summary.totalEnrollments)}
                  sub={`${summary.enrolledStudents} enrolled`}
                  iconClassName="bg-primary-soft text-primary"
                />
              </div>

              <Card className="mt-6">
                <CardContent className="p-6">
                  <div className="flex flex-col space-y-1.5">
                    <h3 className="text-base font-semibold text-foreground">Requests by Status</h3>
                    <p className="text-xs text-muted-foreground">Document request distribution</p>
                  </div>
                  <Table className="mt-4">
                    <TableHeader>
                      <TableRow>
                        <TableHead className="text-left">Status</TableHead>
                        <TableHead className="text-right">Count</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {Object.entries(summary.requestsByStatus).map(([status, count]) => (
                        <TableRow key={status}>
                          <TableCell>{status}</TableCell>
                          <TableCell className="text-right">{count}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </CardContent>
              </Card>
            </>
          )}
        </CardContent>
      </Card>
    </div>
  )
}

function SummaryCard({
  label,
  value,
  sub,
  iconClassName,
}: {
  label: string
  value: string
  sub: string
  iconClassName: string
}) {
  return (
    <Card>
      <CardContent className="p-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
              {label}
            </p>
            <h2 className="mt-1 text-2xl font-bold text-foreground">{value}</h2>
            <p className="mt-1 text-sm text-muted-foreground">{sub}</p>
          </div>
          <div className={cn('flex h-9 w-9 items-center justify-center rounded-lg', iconClassName)}>
            <BarChart3 className="h-5 w-5" aria-hidden="true" />
          </div>
        </div>
      </CardContent>
    </Card>
  )
}