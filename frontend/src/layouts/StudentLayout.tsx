import { useState } from 'react'
import { Outlet, useLocation } from 'react-router-dom'

import { AppHeader } from '@/components/layout/AppHeader'
import { MobileSidebar } from '@/components/layout/MobileSidebar'
import { SidebarShell } from '@/components/layout/Sidebar'
import { studentNavigation, studentSecondaryNavigation } from '@/config/navigation'

function titleForPath(pathname: string): string {
  const all = [...studentNavigation, ...studentSecondaryNavigation]
  const match = all.find((item) => pathname === item.href || pathname.startsWith(`${item.matchPrefix}/`))
  if (match) return match.label
  if (pathname.startsWith('/student/requests/')) return 'Request Details'
  if (pathname.startsWith('/student/courses/')) return 'Course Details'
  if (pathname.startsWith('/student/notifications')) return 'Notifications'
  return 'Student Portal'
}

/**
 * Student portal shell: dark navy sidebar, top header, content area.
 * Collapses to a drawer below the `lg` breakpoint.
 */
export default function StudentLayout() {
  const location = useLocation()
  const [sidebarOpen, setSidebarOpen] = useState(false)

  return (
    <div className="min-h-screen bg-background">
      <aside className="fixed inset-y-0 left-0 z-40 hidden w-64 lg:block" aria-label="Student sidebar">
        <SidebarShell currentPath={location.pathname} />
      </aside>
      <MobileSidebar open={sidebarOpen} onOpenChange={setSidebarOpen} currentPath={location.pathname} />

      <div className="lg:pl-64">
        <AppHeader title={titleForPath(location.pathname)} onOpenSidebar={() => setSidebarOpen(true)} />
        <main className="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
