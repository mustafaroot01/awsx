import { createMongoAbility, type MongoAbility } from '@casl/ability'
import { abilitiesPlugin } from '@casl/vue'
import { useCookie } from '@core/composable/useCookie'
import type { App } from 'vue'

export type Actions = 'create' | 'read' | 'update' | 'delete' | 'manage' | 'print'
export type Subjects =
  | 'all'
  | 'Auth'
  | 'Policy'
  | 'ProductionPlan'
  | 'Statistics'
  | 'Branch'
  | 'Employee'
  | 'Evaluation'
  | 'User'
  | 'Role'
  | 'Settings'
  | 'Log'

export type AppAbility = MongoAbility<[Actions, Subjects]>

export interface Rule {
  action: Actions
  subject: Subjects
}

export const ability = createMongoAbility<AppAbility>()

export default function (app: App) {
  const userAbilityRules = useCookie<Rule[]>('userAbilityRules')
  
  console.log('CASL Plugin Init - Rules from cookie:', userAbilityRules.value)

  if (userAbilityRules.value) {
    console.log('CASL Plugin Init - Updating ability with:', userAbilityRules.value)
    ability.update(userAbilityRules.value)
  }

  // @ts-expect-error expose for debugging
  if (typeof window !== 'undefined') window.ability = ability

  app.use(abilitiesPlugin, ability, {
    useGlobalProperties: true,
  })
}
