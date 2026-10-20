import { Menu } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { NotificationMenu } from './NotificationMenu'
import { UserMenu } from './UserMenu'

export interface AppHeaderProps {
  title: string
  onOpenSidebar: () => void
}

/** Top bar inside the student portal: menu toggle, page title, notifications, user. */
export function AppHeader({ title, onOpenSidebar }: AppHeaderProps) {
  return (
    <header className="sticky top-0 z-30 flex h-16 items-center gap-2 border-b border-border bg-white/95 px-4 backdrop-blur sm:px-6">
      <Button
        type="button"
        variant="ghost"
        size="icon"
        onClick={onOpenSidebar}
        aria-label="Open navigation"
        className="text-muted-foreground lg:hidden"
      >
        <Menu className="h-5 w-5" aria-hidden="true" />
      </Button>
      <h1 className="min-w-0 flex-1 truncate text-[15px] font-semibold text-foreground sm:text-base">{title}</h1>
      <div className="flex items-center gap-1">
        <NotificationMenu />
        <span aria-hidden="true" className="mx-1 hidden h-6 w-px bg-border sm:block" />
        <UserMenu />
      </div>
    </header>
  )
}
