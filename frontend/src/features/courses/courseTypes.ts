export interface Course {
  id: string
  courseCode: string
  title: string
  description: string
  program: string
  yearLevel: string
  semester: string
  active: boolean
}

export interface CourseFilters {
  programs: string[]
  yearLevels: string[]
  semesters: string[]
}

export interface CourseCatalogResponse {
  courses: Course[]
  filters: CourseFilters
}

export interface CourseQuery {
  search?: string
  program?: string
  yearLevel?: string
  semester?: string
}
