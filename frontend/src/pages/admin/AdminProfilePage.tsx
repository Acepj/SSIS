import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { PageHeader } from '@/components/common/PageHeader'
import { useAuth } from '@/features/auth/AuthProvider'
import { User } from 'lucide-react'

export default function AdminProfilePage() {
  const { user } = useAuth()

  if (!user) {
    return (
      <div className="space-y-4">
        <PageHeader title="Profile" />
        <p className="text-sm text-muted-foreground">No profile data available.</p>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <PageHeader title="Admin Profile" description="Your administrator account details." />
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <User className="h-5 w-5 text-primary" aria-hidden="true" />
            Account Information
          </CardTitle>
        </CardHeader>
        <CardContent className="pt-0">
          <dl className="grid gap-x-8 gap-y-4 sm:grid-cols-2">
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Name</dt>
              <dd className="mt-1 text-sm font-medium text-foreground">
                {user.displayName ?? user.fullName ?? '—'}
              </dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Email</dt>
              <dd className="mt-1 text-sm text-foreground">{user.email ?? '—'}</dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Role</dt>
              <dd className="mt-1 text-sm font-medium text-primary capitalize">
                {user.role}
              </dd>
            </div>
            <div>
              <dt className="text-xs font-medium uppercase tracking-wide text-muted-foreground">User ID</dt>
              <dd className="mt-1 text-sm text-foreground">{user.id ?? '—'}</dd>
            </div>
          </dl>
        </CardContent>
      </Card>
    </div>
  )
}