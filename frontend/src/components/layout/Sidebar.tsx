import { LifeBuoy, LogOut } from 'lucide-react'
import { NavLink, useNavigate } from 'react-router-dom'
import type { ReactNode } from 'react'

import { appConfig } from '@/config/appConfig'
import { studentNavigation, studentSecondaryNavigation } from '@/config/navigation'
import { useAuth } from '@/features/auth/AuthProvider'
import { routePaths } from '@/routes/routePaths'
import { cn } from '@/lib/utils'

function isActivePath(current: string, prefix: string): boolean {
  return current === prefix || current.startsWith(`${prefix}/`) || current.startsWith(prefix)
}

export interface SidebarNavProps {
  currentPath: string
  onNavigate?: () => void
}

/** Shared navigation list used by both the desktop sidebar and the mobile drawer. */
export function SidebarNav({ currentPath, onNavigate }: SidebarNavProps) {
  return (
    <nav aria-label="Student" className="flex flex-col gap-1">
      {studentNavigation.map((item) => {
        const active = isActivePath(currentPath, item.matchPrefix)
        return (
          <NavLink
            key={item.href}
            to={item.href}
            onClick={onNavigate}
            aria-current={active ? 'page' : undefined}
            className={cn(
              'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary',
              active ? 'bg-primary text-white' : 'text-white/70 hover:bg-white/10 hover:text-white',
            )}
          >
            <item.icon className="h-[18px] w-[18px] shrink-0" aria-hidden="true" />
            {item.label}
          </NavLink>
        )
      })}

      <p className="mt-4 px-3 text-[11px] font-semibold uppercase tracking-wider text-white/40">Support</p>
      {studentSecondaryNavigation.map((item) => {
        const active = isActivePath(currentPath, item.matchPrefix)
        return (
          <NavLink
            key={item.href}
            to={item.href}
            onClick={onNavigate}
            aria-current={active ? 'page' : undefined}
            className={cn(
              'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary',
              active ? 'bg-primary text-white' : 'text-white/70 hover:bg-white/10 hover:text-white',
            )}
          >
            <item.icon className="h-[18px] w-[18px] shrink-0" aria-hidden="true" />
            {item.label}
          </NavLink>
        )
      })}
    </nav>
  )
}

export interface SidebarShellProps {
  currentPath: string
  header?: ReactNode
  onNavigate?: () => void
}

/** Dark navy sidebar body: brand, navigation, assistance card, account actions. */
export function SidebarShell({ currentPath, header, onNavigate }: SidebarShellProps) {
  const { signOut } = useAuth()
  const navigate = useNavigate()

  const handleSignOut = async () => {
    await signOut()
    navigate(routePaths.login, { replace: true })
  }

  return (
    <div className="flex h-full flex-col bg-sidebar text-white">
      <div className="flex items-center gap-2.5 px-5 pb-5 pt-6">
        <img src="/images/logo.svg" alt={`${appConfig.name} logo`} className="h-9 w-auto" />
        <div className="leading-tight">
          <p className="text-[15px] font-semibold tracking-wide">{appConfig.name}</p>
          <p className="text-[11px] text-white/60">Student Portal</p>
        </div>
      </div>

      {header}

      <div className="flex-1 overflow-y-auto px-3 pb-4">
        <SidebarNav currentPath={currentPath} onNavigate={onNavigate} />

        <div className="mt-6 rounded-xl bg-white/10 p-4">
          <div className="flex items-center gap-2">
            <LifeBuoy className="h-4 w-4 text-white" aria-hidden="true" />
            <p className="text-sm font-semibold">Need assistance?</p>
          </div>
          <p className="mt-1.5 text-xs leading-relaxed text-white/70">
            Contact Student Services for help with requests and enrollment.
          </p>
          <a
            href={`mailto:${appConfig.supportEmail}`}
            className="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-white underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            {appConfig.supportLabel}
          </a>
        </div>
      </div>

      <div className="border-t border-white/10 p-3">
        <button
          type="button"
          onClick={() => void handleSignOut()}
          className="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-white/70 transition-colors hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          <LogOut className="h-[18px] w-[18px] shrink-0" aria-hidden="true" />
          Logout
        </button>
        <p className="mt-2 px-3 text-[11px] text-white/40">Version {appConfig.version}</p>
      </div>
    </div>
  )
}

