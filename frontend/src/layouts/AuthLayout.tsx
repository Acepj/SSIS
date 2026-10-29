import { Lock } from 'lucide-react'
import type { SyntheticEvent } from 'react'
import { Outlet } from 'react-router-dom'

import { appConfig, currentYear } from '@/config/appConfig'

/** Hides the campus photo if it fails to load; the navy panel remains legible. */
function handleImageError(event: SyntheticEvent<HTMLImageElement>) {
  event.currentTarget.style.display = 'none'
}

/**
 * Shared split-screen authentication shell (login, forgot password).
 * Left: university-themed brand panel. Right: the page content (outlet).
 * On small screens the panels stack vertically.
 */
export default function AuthLayout() {
  return (
    <div className="min-h-screen bg-white lg:grid lg:grid-cols-2">
      <section className="relative overflow-hidden bg-navy px-6 py-10 text-white sm:px-10 lg:flex lg:flex-col lg:justify-between lg:px-14 lg:py-14">
        <img
          src="/images/campus-building.jpg"
          alt=""
          aria-hidden="true"
          onError={handleImageError}
          className="absolute inset-0 h-full w-full object-cover object-center"
        />
        <div
          className="absolute inset-0 bg-gradient-to-br from-navy via-navy/92 to-navy/70"
          aria-hidden="true"
        />

        <div className="relative">
          <div className="flex items-center gap-3">
            <img src="/images/ssis.png" alt={`${appConfig.name} logo`} className="h-10 w-auto" />
            <div>
              <p className="text-lg font-semibold tracking-wide">{appConfig.name}</p>
              <p className="text-xs text-white/70">{appConfig.fullName}</p>
            </div>
          </div>
        </div>

        <div className="relative mt-10 lg:mt-0">
          <h2 className="max-w-md text-2xl font-semibold leading-snug tracking-tight sm:text-3xl lg:text-4xl">
            {appConfig.tagline}
          </h2>
          <p className="mt-4 hidden max-w-md text-sm leading-relaxed text-white/75 lg:block">
            {appConfig.introDescription}
          </p>
        </div>

        <footer className="relative mt-10 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-white/60 lg:mt-0">
          <Lock className="h-3.5 w-3.5" aria-hidden="true" />
          <span>Secure university access</span>
          <span aria-hidden="true">&middot;</span>
          <span>{currentYear()}</span>
        </footer>
      </section>

      <section className="flex items-center justify-center bg-white px-6 py-12 sm:px-10 lg:min-h-screen lg:px-14">
        <div className="w-full max-w-[400px]">
          <Outlet />
        </div>
      </section>
    </div>
  )
}
