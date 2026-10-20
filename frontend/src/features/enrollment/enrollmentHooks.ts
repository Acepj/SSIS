import { useQuery } from '@tanstack/react-query'

import { getEnrollment } from './enrollmentApi'

export function useStudentEnrollment() {
  return useQuery({
    queryKey: ['student', 'enrollment'],
    queryFn: ({ signal }) => getEnrollment(signal),
  })
}
