import {
  BookOpen,
  BarChart3,
  Bell,
  BookOpen as BookOpenIcon,
  Edit,
  FileText,
  File,
  GraduationCap,
  LayoutDashboard,
  LifeBuoy,
  ListChecks,
  Plus,
  User,
  UserPlus,
  UserRound,
  Users,
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
  { label: 'Courses', href: routePaths.student.courses, icon: BookOpenIcon, matchPrefix: '/student/courses' },
  { label: 'Profile', href: routePaths.student.profile, icon: UserRound, matchPrefix: '/student/profile' },
]

export const studentSecondaryNavigation: NavigationItem[] = [
  { label: 'Help & Support', href: routePaths.student.help, icon: LifeBuoy, matchPrefix: '/student/help' },
]

export const adminNavigation: NavigationItem[] = [
  { label: 'Dashboard', href: routePaths.admin.dashboard, icon: LayoutDashboard, matchPrefix: '/admin/dashboard' },
  { label: 'Students', href: routePaths.admin.students, icon: Users, matchPrefix: '/admin/students' },
  { label: 'Enrollment', href: routePaths.admin.enrollment, icon: GraduationCap, matchPrefix: '/admin/enrollment' },
  { label: 'Courses', href: routePaths.admin.courses, icon: BookOpenIcon, matchPrefix: '/admin/courses' },
  { label: 'Document Requests', href: routePaths.admin.requests, icon: FileText, matchPrefix: '/admin/requests' },
  { label: 'Notifications', href: routePaths.admin.notifications, icon: Bell, matchPrefix: '/admin/notifications' },
  { label: 'Reports', href: routePaths.admin.reports, icon: BarChart3, matchPrefix: '/admin/reports' },
  { label: 'Profile', href: routePaths.admin.profile, icon: UserRound, matchPrefix: '/admin/profile' },
]

export const adminSecondaryNavigation: NavigationItem[] = [
  { label: 'Create Student', href: routePaths.admin.createStudent, icon: UserPlus, matchPrefix: '/admin/students/new' },
  { label: 'Create Course', href: routePaths.admin.createCourse, icon: Plus, matchPrefix: '/admin/courses/new' },
]
