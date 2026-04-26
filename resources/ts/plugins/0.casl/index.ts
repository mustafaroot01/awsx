import type { App } from 'vue'
import { createMongoAbility } from '@casl/ability'
import { abilitiesPlugin } from '@casl/vue'
import { useCookie } from '@core/composable/useCookie'

export type Actions = 'create' | 'read' | 'update' | 'delete' | 'manage' | 'print'
export type Subjects = 'all' | 'Auth' | 'Employee' | 'Branch' | 'User' | 'Role' | 'Evaluation' | 'ProductionPlan' | 'Policy'

export interface Rule {
  action: Actions
  subject: Subjects
}

export const ability = createMongoAbility<Rule>()

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
