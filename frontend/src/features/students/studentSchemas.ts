import { z } from 'zod'

/** Students may only edit contact fields; official data is protected server-side. */
export const profileUpdateSchema = z.object({
  email: z
    .string()
    .trim()
    .min(1, 'Email address is required.')
    .max(190, 'The email address must be 190 characters or fewer.')
    .email('Enter a valid email address.'),
  contactNumber: z
    .string()
    .trim()
    .min(7, 'Enter a valid contact number.')
    .max(25, 'The contact number must be 25 characters or fewer.')
    .regex(/^[0-9+\s()-]{7,25}$/, 'Only digits, spaces, +, - and parentheses are allowed.'),
})

export type ProfileUpdateFormValues = z.infer<typeof profileUpdateSchema>
