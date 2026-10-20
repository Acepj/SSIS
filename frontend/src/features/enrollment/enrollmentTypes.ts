import type { EnrollmentStatus } from '@/types/common'

export interface AcademicTerm {
  year: string
  semester: string
}

export interface EnrollmentInfo {
  id: string
  academicYear: string
  semester: string
  program: string
  yearLevel: string
  status: EnrollmentStatus | string
  dateEnrolled: string
}

export interface EnrollmentResponse {
  enrollment: EnrollmentInfo | null
  academic: AcademicTerm
}
