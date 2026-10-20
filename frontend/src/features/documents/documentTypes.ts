import type { RequestStatus } from '@/types/common'

export const DOCUMENT_TYPES = [
  'Certificate of Enrollment',
  'Transcript of Records',
  'Certificate of Grades',
  'Certificate of Good Moral Character',
  'Other',
] as const

export type DocumentType = (typeof DOCUMENT_TYPES)[number]

export const DELIVERY_METHODS = ['Pickup', 'Delivery'] as const
export type DeliveryMethod = (typeof DELIVERY_METHODS)[number]

export interface RequestSummary {
  id: string
  requestNumber: string
  documentType: string
  status: RequestStatus
  createdAt: string
  updatedAt: string
}

export interface StatusHistoryEntry {
  previousStatus: RequestStatus | null
  newStatus: RequestStatus
  changedByName: string
  changedAt: string
  note: string
}

export interface RequestDetails {
  id: string
  requestNumber: string
  documentType: string
  purpose: string
  copies: number
  deliveryMethod: DeliveryMethod
  additionalInstructions: string
  status: RequestStatus
  processingNotes: string
  createdAt: string
  updatedAt: string
  statusHistory: StatusHistoryEntry[]
  canCancel: boolean
}

export interface CreateRequestInput {
  documentType: DocumentType
  purpose: string
  copies: number
  deliveryMethod: DeliveryMethod
  additionalInstructions?: string
}
