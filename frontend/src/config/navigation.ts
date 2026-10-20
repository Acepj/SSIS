import {
  BookOpen,
  FileText,
  GraduationCap,
  LayoutDashboard,
  LifeBuoy,
  ListChecks,
  UserRound,
} from 'lucide-react'
import type { LucideIcon } from 'lucide-react'

import { routePaths } from '@/routes/routePaths'

export interface NavigationItem {
  label: string
  href: string
  icon: LucideIcon
  /** Prefix match used to highlight the item for nested routes. */
  matchPrefix: string
}

export const studentNavigation: NavigationItem[] = [
  { label: 'Dashboard', href: routePaths.student.dashboard, icon: LayoutDashboard, matchPrefix: '/student/dashboard' },
  { label: 'Enrollment', href: routePaths.student.enrollment, icon: GraduationCap, matchPrefix: '/student/enrollment' },
  {
    label: 'Document Request',
    href: routePaths.student.documentRequest,
    icon: FileText,
    matchPrefix: '/student/document-request',
  },
  {
    label: 'Request Status',
    href: routePaths.student.requestStatus,
    icon: ListChecks,
    matchPrefix: '/student/request',
  },
  { label: 'Courses', href: routePaths.student.courses, icon: BookOpen, matchPrefix: '/student/courses' },
  { label: 'Profile', href: routePaths.student.profile, icon: UserRound, matchPrefix: '/student/profile' },
]

export const studentSecondaryNavigation: NavigationItem[] = [
  { label: 'Help & Support', href: routePaths.student.help, icon: LifeBuoy, matchPrefix: '/student/help' },
]
