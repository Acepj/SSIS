import { ChevronLeft, ChevronRight } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

export interface PaginationProps {
  page: number
  totalPages: number
  onPageChange: (page: number) => void
  className?: string
}

/** Compact numbered pagination (used on longer tables when needed). */
export function Pagination({ page, totalPages, onPageChange, className }: PaginationProps) {
  if (totalPages <= 1) return null

  const pages: number[] = []
  const start = Math.max(1, Math.min(page - 2, totalPages - 4))
  for (let p = start; p <= Math.min(totalPages, start + 4); p += 1) {
    pages.push(p)
  }

  return (
    <nav aria-label="Pagination" className={cn('flex items-center justify-center gap-1', className)}>
      <Button
        type="button"
        variant="outline"
        size="sm"
        disabled={page <= 1}
        onClick={() => onPageChange(page - 1)}
        aria-label="Go to previous page"
      >
        <ChevronLeft className="h-4 w-4" aria-hidden="true" />
      </Button>
      {pages.map((p) => (
        <Button
          key={p}
          type="button"
          variant={p === page ? 'default' : 'outline'}
          size="sm"
          aria-current={p === page ? 'page' : undefined}
          onClick={() => onPageChange(p)}
          className="min-w-9"
        >
          {p}
        </Button>
      ))}
      <Button
        type="button"
        variant="outline"
        size="sm"
        disabled={page >= totalPages}
        onClick={() => onPageChange(page + 1)}
        aria-label="Go to next page"
      >
        <ChevronRight className="h-4 w-4" aria-hidden="true" />
      </Button>
    </nav>
  )
}
