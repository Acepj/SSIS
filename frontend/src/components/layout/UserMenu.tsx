import { LogOut, UserRound } from 'lucide-react'
import { Link, useNavigate } from 'react-router-dom'

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { useAuth } from '@/features/auth/AuthProvider'
import { routePaths } from '@/routes/routePaths'

function initials(name: string): string {
  const parts = name.trim().split(/\s+/).filter(Boolean)
  if (parts.length === 0) return '?'
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase()
}

/** Avatar + name button with Profile and Logout actions. */
export function UserMenu() {
  const { user, signOut } = useAuth()
  const navigate = useNavigate()

  if (!user) return null

  const handleSignOut = async () => {
    await signOut()
    navigate(routePaths.login, { replace: true })
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <button
          type="button"
          aria-label="Account menu"
          className="flex items-center gap-2.5 rounded-xl px-2 py-1.5 transition-colors hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          <span
            aria-hidden="true"
            className="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-[13px] font-semibold text-white"
          >
            {initials(user.displayName)}
          </span>
          <span className="hidden text-left leading-tight md:block">
            <span className="block max-w-[160px] truncate text-[13px] font-semibold text-foreground">
              {user.displayName}
            </span>
            <span className="block text-xs capitalize text-muted-foreground">{user.role}</span>
          </span>
        </button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-56">
        <DropdownMenuLabel>
          <span className="block truncate">{user.displayName}</span>
          <span className="block truncate text-xs font-normal text-muted-foreground">{user.identifier}</span>
        </DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem asChild>
          <Link to={routePaths.student.profile}>
            <UserRound aria-hidden="true" />
            Profile
          </Link>
        </DropdownMenuItem>
        <DropdownMenuItem onSelect={() => void handleSignOut()}>
          <LogOut aria-hidden="true" />
          Logout
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
