import { useState } from 'react'

/** Simple client-side pagination over an already-fetched array. */
export function usePagination<TItem>(items: TItem[], perPage = 10) {
  const [page, setPage] = useState(1)
  const totalPages = Math.max(1, Math.ceil(items.length / perPage))
  const safePage = Math.min(page, totalPages)
  const start = (safePage - 1) * perPage

  return {
    page: safePage,
    totalPages,
    setPage,
    pagedItems: items.slice(start, start + perPage),
    total: items.length,
  }
}
