import { useState } from 'react'

import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { ErrorState } from '@/components/common/ErrorState'
import { LoadingState } from '@/components/common/LoadingState'
import { PageHeader } from '@/components/common/PageHeader'
import { SearchInput } from '@/components/common/SearchInput'
import { useAdminNotifications, useCreateAdminNotification } from '@/features/admin/adminHooks'
import type { AdminNotification } from '@/features/admin/adminTypes'
import { Search, Bell } from 'lucide-react'

export default function AdminNotificationsPage() {
  const [search, setSearch] = useState('')
  const [recipient, setRecipient] = useState('')
  const [newTitle, setNewTitle] = useState('')
  const [newMessage, setNewMessage] = useState('')
  const [newRecipient, setNewRecipient] = useState('')

  const q = useAdminNotifications({ search, recipient })
  const createNotification = useCreateAdminNotification()
  const notifications: AdminNotification[] = q.data?.notifications ?? []

  if (q.isPending) return (
    <div className="space-y-4">
      <PageHeader title="Notifications" description="Send and manage notifications to students." />
      <LoadingState label="Loading notifications..." />
    </div>
  )

  if (q.isError) return (
    <div className="space-y-4">
      <PageHeader title="Notifications" />
      <Card className="mt-6">
        <CardContent className="p-2">
          <ErrorState title="Unable to load notifications" onRetry={() => void q.refetch()} />
        </CardContent>
      </Card>
    </div>
  )

  return (
    <div className="space-y-6">
      <PageHeader
        title="Notifications"
        description="Send announcements and messages to students."
        actions={
          <Button onClick={() => {}} variant="outline">
            <Bell className="h-4 w-4 mr-2" aria-hidden="true" />
            Compose
          </Button>
        }
      />

      <Card>
        <CardContent className="p-6">
          <div className="grid gap-4 sm:grid-cols-2 mb-6">
            <div className="relative">
              <SearchInput
                value={search}
                onChange={(v) => {
                  setSearch(v)
                  void q.refetch()
                }}
                placeholder="Search by title or recipient..."
              />
            </div>
            <div className="relative">
              <Select
                value={recipient}
                onValueChange={(v) => setRecipient(v ?? '')}
                onChange={(): void => void q.refetch()}
              >
                <option value="">All Recipients</option>
                <option value="All">All Students</option>
              </Select>
            </div>
          </div>

          {notifications.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-center">
              <p className="text-sm text-muted-foreground">No notifications found.</p>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="text-left">Title</TableHead>
                  <TableHead className="text-left">Message</TableHead>
                  <TableHead className="text-left">Recipient</TableHead>
                  <TableHead className="text-right">Sent</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {notifications.map((n) => (
                  <TableRow key={n.id}>
                    <TableCell className="font-medium text-foreground">{n.title}</TableCell>
                    <TableCell className="text-sm text-muted-foreground">{n.message}</TableCell>
                    <TableCell>{n.recipientName}</TableCell>
                    <TableCell className="text-right text-sm text-muted-foreground">
                      {new Date(n.createdAt).toLocaleDateString()}
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