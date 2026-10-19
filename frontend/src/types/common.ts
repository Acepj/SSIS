export type UserRole = 'student' | 'admin'

export type RequestStatus = 'Pending' | 'Processing' | 'Ready for Pickup' | 'Completed' | 'Rejected' | 'Cancelled'

export type AccountStatus = 'Active' | 'Inactive'

export type EnrollmentStatus = 'Enrolled' | 'Not Enrolled' | 'On Leave' | 'Graduated'

export interface PaginationMeta {
  page: number
  perPage: number
  total: number
  totalPages: number
}

export interface Paginated<TItem> {
  items: TItem[]
  meta: PaginationMeta
}

export interface SelectOption<TValue extends string = string> {
  value: TValue
  label: string
}
