import { useState } from 'react'
import { Outlet, useLocation } from 'react-router-dom'

import { useNavigate } from 'react-router-dom'

import { AppHeader } from '@/components/layout/AppHeader'
import { MobileSidebar } from '@/components/layout/MobileSidebar'
import { SidebarShell } from '@/components/layout/Sidebar'
import { adminNavigation, adminSecondaryNavigation } from '@/config/navigation'
import { appConfig } from '@/config/appConfig'
import { useAuth } from '@/features/auth/AuthProvider'
import { routePaths } from '@/routes/routePaths'
import { cn } from '@/lib/utils'

function titleForPath(pathname: string): string {
  const all = [...adminNavigation, ...adminSecondaryNavigation]
  const match = all.find((item) => pathname === item.href || pathname.startsWith(`${item.matchPrefix}/`))
  if (match) return match.label
  if (pathname.startsWith('/admin/students/')) return 'Student Details'
  if (pathname.startsWith('/admin/requests/')) return 'Request Details'
  if (pathname.startsWith('/admin/courses/')) return 'Course Details'
  if (pathname.startsWith('/admin/notifications')) return 'Notifications'
  return 'Admin Portal'
}

/**
 * Admin portal shell: dark navy sidebar, top header, content area.
 * Collapses to a drawer below the `lg` breakpoint.
 */
export default function AdminLayout() {
  const location = useLocation()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const { signOut } = useAuth()
  const navigate = useNavigate()

  const handleSignOut = async () => {
    await signOut()
    navigate(routePaths.login, { replace: true })
  }

  return (
    <div className="min-h-screen bg-background">
      <aside className="fixed inset-y-0 left-0 z-40 hidden w-64 lg:block" aria-label="Admin sidebar">
        <SidebarShell
          currentPath={location.pathname}
          navItems={adminNavigation}
          sectionTitle="Admin"
          header={
            <div className="px-3 pt-4 pb-2">
              <p className="text-[11px] font-semibold uppercase tracking-wider text-white/50">
                Administrator
              </p>
            </div>
          }
        />
        <MobileSidebar
          open={sidebarOpen}
          onOpenChange={setSidebarOpen}
          currentPath={location.pathname}
          navItems={adminNavigation}
          secondaryNavItems={adminSecondaryNavigation}
          sectionTitle="Admin"
        />
      </aside>

      <div className="lg:pl-64">
        <AppHeader
          title={titleForPath(location.pathname)}
          onOpenSidebar={() => setSidebarOpen(true)}
        />
        <main className="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}