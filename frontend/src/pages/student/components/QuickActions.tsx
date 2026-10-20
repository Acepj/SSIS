import { Link } from 'react-router-dom';
import { ArrowRight, BookOpen, FileText, GraduationCap, ListChecks, type LucideIcon } from 'lucide-react';
import { routePaths } from '@/routes/routePaths';

const actions: Array<{ label: string; desc: string; href: string; icon: LucideIcon }> = [
  { label: 'Request Document', desc: 'Submit a new request', href: routePaths.student.documentRequest, icon: FileText },
  { label: 'Enrollment', desc: 'View enrollment info', href: routePaths.student.enrollment, icon: GraduationCap },
  { label: 'Request Status', desc: 'Track your requests', href: routePaths.student.requestStatus, icon: ListChecks },
  { label: 'View Courses', desc: 'Browse catalog', href: routePaths.student.courses, icon: BookOpen },
];

/** Navigation cards linking to the four main student workflows. */
export function QuickActions() {
  return (
    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      {actions.map((a) => (
        <Link key={a.href} to={a.href} className="group flex items-center gap-3 rounded-xl border border-border bg-card p-4 transition-colors hover:border-primary/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary transition-colors group-hover:bg-primary group-hover:text-white">
            <a.icon className="h-5 w-5" aria-hidden="true" />
          </span>
          <span className="min-w-0">
            <span className="block truncate text-sm font-semibold">{a.label}</span>
            <span className="block truncate text-xs text-muted-foreground">{a.desc}</span>
          </span>
          <ArrowRight className="ml-auto h-4 w-4 shrink-0 text-muted-foreground group-hover:text-primary" aria-hidden="true" />
        </Link>
      ))}
    </div>
  );
}
