import { keepPreviousData, useQuery } from '@tanstack/react-query'

import { getCourse, getCourses } from './courseApi'
import type { CourseQuery } from './courseTypes'

export function useCourses(query: CourseQuery) {
  return useQuery({
    queryKey: ['student', 'courses', query],
    queryFn: ({ signal }) => getCourses(query, signal),
    placeholderData: keepPreviousData,
  })
}

export function useCourse(id: string | undefined) {
  return useQuery({
    queryKey: ['student', 'course', id],
    queryFn: ({ signal }) => getCourse(id ?? '', signal),
    enabled: Boolean(id),
  })
}
