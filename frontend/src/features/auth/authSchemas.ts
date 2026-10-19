import { z } from 'zod'

export const loginSchema = z.object({
  identifier: z
    .string()
    .trim()
    .min(3, 'Enter your student ID or administrator username.')
    .max(64, 'The identifier must be 64 characters or fewer.')
    .regex(/^[A-Za-z0-9._@-]+$/, 'Only letters, numbers, and the characters . _ - @ are allowed.'),
  password: z
    .string()
    .min(1, 'Password is required.')
    .max(128, 'The password must be 128 characters or fewer.'),
  remember: z.boolean(),
})

export type LoginFormValues = z.infer<typeof loginSchema>
