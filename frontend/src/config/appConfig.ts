/**
 * Non-sensitive application settings. No secrets belong in frontend config -
 * everything here is visible to the browser.
 */
export const appConfig = {
  name: 'SSIS',
  fullName: 'Student Services Information System',
  tagline: 'Your University Services, All in One Place.',
  introDescription:
    'Access your student services, manage your requests, and stay connected with your university through one convenient platform.',
  version: '1.0.0',
  /** Demo contact address - replace with the real Student Services address when available. */
  supportEmail: 'student.services@demo.edu',
  supportLabel: 'Contact Student Services',
} as const

export function currentYear(): number {
  return new Date().getFullYear()
}
