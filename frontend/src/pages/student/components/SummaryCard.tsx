import type { LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';

export interface SummaryCardProps {
  label: string;
  value: string;
  sub: string;
  icon: LucideIcon;
  iconClassName: string;
}

/** Small stat card used on the student dashboard. */
export function SummaryCard({ label, value, sub, icon: Icon, iconClassName }: SummaryCardProps) {
  return (
    <Card>
      <CardContent className="flex items-start justify-between gap-3 p-5">
        <div className="min-w-0">
          <p className="text-[13px] text-muted-foreground">{label}</p>
          <p className="mt-1 truncate text-xl font-semibold">{value}</p>
          <p className="mt-1 truncate text-xs text-muted-foreground">{sub}</p>
        </div>
        <span className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${iconClassName}`}>
          <Icon className="h-5 w-5" aria-hidden="true" />
        </span>
      </CardContent>
    </Card>
  );
}
