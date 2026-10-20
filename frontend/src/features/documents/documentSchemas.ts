import { z } from 'zod'

import { DELIVERY_METHODS, DOCUMENT_TYPES } from './documentTypes'

export const documentRequestSchema = z.object({
  documentType: z.enum(DOCUMENT_TYPES, {
    errorMap: () => ({ message: 'Please select a document type.' }),
  }),
  purpose: z
    .string()
    .trim()
    .min(5, 'Please state the purpose of your request (at least 5 characters).')
    .max(500, 'The purpose must be 500 characters or fewer.'),
  copies: z.coerce
    .number({ invalid_type_error: 'Enter the number of copies.' })
    .int('Copies must be a whole number.')
    .min(1, 'At least 1 copy is required.')
    .max(10, 'A maximum of 10 copies is allowed.'),
  deliveryMethod: z.enum(DELIVERY_METHODS),
  additionalInstructions: z
    .string()
    .trim()
    .max(1000, 'Additional instructions must be 1000 characters or fewer.')
    .optional()
    .or(z.literal('')),
})

export type DocumentRequestFormValues = z.infer<typeof documentRequestSchema>
