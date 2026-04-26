// Ported from [Nuxt](https://github.com/nuxt/nuxt/blob/main/packages/nuxt/src/app/composables/cookie.ts)

import type { CookieParseOptions, CookieSerializeOptions } from 'cookie-es'
import { parse } from 'cookie-es'
import { destr } from 'destr'

type _CookieOptions = Omit<CookieSerializeOptions & CookieParseOptions, 'decode' | 'encode'>

export interface CookieOptions<T = any> extends _CookieOptions {
  decode?(value: string): T
  encode?(value: T): string
  default?: () => T | Ref<T>
  watch?: boolean | 'shallow'
}

export type CookieRef<T> = Ref<T>

const CookieDefaults: CookieOptions<any> = {
  path: '/',
  watch: true,
  decode: val => destr(decodeURIComponent(val)),
  encode: val => encodeURIComponent(typeof val === 'string' ? val : JSON.stringify(val)),
}

export const useCookie = <T = string | null | undefined>(name: string, _opts?: CookieOptions<T>): CookieRef<T> => {
  const opts = { ...CookieDefaults, ..._opts || {} }
  const cookies = parse(document.cookie, opts)

  const cookie = ref<T | undefined>(cookies[name] as any ?? opts.default?.())

  watch(cookie, () => {
    setCookie(name, cookie.value, opts)
  })

  return cookie as CookieRef<T>
}

function setCookie(name: string, value: any, opts: any = {}) {
  if (!name) return

  let cookieString = `${name}=`
  
  if (value === null || value === undefined) {
    cookieString += `; Max-Age=-1`
  } else {
    const serializedValue = typeof value === 'object' ? JSON.stringify(value) : String(value)
    cookieString += encodeURIComponent(serializedValue)
    cookieString += `; Max-Age=${60 * 60 * 24 * 30}`
  }

  cookieString += `; Path=${opts.path || '/'}`
  
  if (opts.domain) cookieString += `; Domain=${opts.domain}`
  if (opts.secure) cookieString += `; Secure`
  if (opts.sameSite) cookieString += `; SameSite=${opts.sameSite}`

  document.cookie = cookieString
}
