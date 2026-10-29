import * as Dialog from '@radix-ui/react-dialog'
import { X } from 'lucide-react'

import type { NavigationItem } from '@/config/navigation'
import { SidebarShell } from './Sidebar'

export interface MobileSidebarProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  currentPath: string
  navItems?: NavigationItem[]
  secondaryNavItems?: NavigationItem[]
  sectionTitle?: string
}

/** Collapsible drawer navigation for tablet and mobile. */
export function MobileSidebar({
  open,
  onOpenChange,
  currentPath,
  navItems,
  secondaryNavItems,
  sectionTitle,
}: MobileSidebarProps) {
  return (
    <Dialog.Root open={open} onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-40 bg-navy/60 backdrop-blur-[2px]" />
        <Dialog.Content
          aria-label={`${sectionTitle ?? 'Student'} navigation`}
          className="fixed inset-y-0 left-0 z-50 w-[280px] max-w-[85vw] overflow-hidden rounded-r-2xl focus-visible:outline-none"
        >
          <Dialog.Title className="sr-only">{sectionTitle ?? 'Student'} navigation</Dialog.Title>
          <button
            type="button"
            onClick={() => onOpenChange(false)}
            aria-label="Close navigation"
            className="absolute right-3 top-5 z-10 rounded-md p-1.5 text-white/70 transition-colors hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <X className="h-5 w-5" aria-hidden="true" />
          </button>
          <div className="h-full">
            <SidebarShell
              currentPath={currentPath}
              navItems={navItems}
              sectionTitle={sectionTitle}
              onNavigate={() => onOpenChange(false)}
            />
          </div>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  )
}
