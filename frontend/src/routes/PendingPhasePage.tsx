import { Construction } from 'lucide-react'

import { EmptyState } from '@/components/common/EmptyState'
import { PageHeader } from '@/components/common/PageHeader'

/**
 * TEMPORARY shell for routes whose pages are implemented in later phases.
 * The route path and its session/role guards are real; the content is
 * replaced page-by-page during Phase 4 (student portal) and Phase 5 (admin).
 */
export function PendingPhasePage({ title, phase }: { title: string; phase: string }) {
  return (
    <main className="min-h-screen bg-background p-6 lg:p-10">
      <PageHeader title={title} description={`Part of ${phase}.`} />
      <div className="mt-6 rounded-xl border border-border bg-card p-6">
        <EmptyState
          icon={Construction}
          title={`${title} arrives in ${phase}`}
          description="This route already enforces authentication and role guards. Its content is built in the phase named above."
        />
      </div>
    </main>
  )
}
