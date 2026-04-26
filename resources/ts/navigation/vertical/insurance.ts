const navigation = [
  { heading: 'نظام التأمين والإنتاج' },
  {
    title: 'لوحة التحكم',
    icon: { icon: 'tabler-chart-pie' },
    to: 'dashboards-insurance',
    action: 'manage',
    subject: 'all',
  },
  {
    title: 'الموارد البشرية',
    icon: { icon: 'tabler-users' },
    action: 'manage',
    subject: 'all',
    children: [
      { title: 'الموظفون', to: 'apps-employees-list', action: 'manage', subject: 'all' },
      { title: 'سجل الحوافز', to: 'apps-employees-incentives', action: 'manage', subject: 'all' },
    ],
  },
  {
    title: 'إدارة الفروع',
    icon: { icon: 'tabler-building-community' },
    action: 'manage',
    subject: 'all',
    children: [
      { title: 'قائمة الفروع', to: 'apps-branches-list', action: 'manage', subject: 'all' },
    ],
  },
  {
    title: 'الإنتاج والتخطيط',
    icon: { icon: 'tabler-clipboard-list' },
    action: 'manage',
    subject: 'all',
    children: [
      { title: 'الخطط الإنتاجية', to: 'apps-production-plans-list', action: 'manage', subject: 'all' },
      { title: 'وثائق التأمين', to: 'apps-policies-list', action: 'manage', subject: 'all' },
      { title: 'الوثائق المنتهية قريباً', to: 'apps-policies-expiring', action: 'manage', subject: 'all' },
    ],
  },
  {
    title: 'الإحصائيات والتقارير',
    icon: { icon: 'tabler-chart-bar' },
    action: 'manage',
    subject: 'all',
    children: [
      { title: 'إحصائيات الإنتاج', to: 'apps-statistics-production', action: 'manage', subject: 'all' },
      { title: 'مقارنة الفروع', to: 'apps-branches-comparison', action: 'manage', subject: 'all' },
      { title: 'ترتيب الفروع', to: 'apps-statistics-branches', action: 'manage', subject: 'all' },
      { title: 'تقارير الموظفين', to: 'apps-statistics-employees', action: 'manage', subject: 'all' },
      { title: 'تحليل التكلفة والإنجاز', to: 'apps-statistics-roi', action: 'manage', subject: 'all' },
    ],
  },
  {
    title: 'التقييم الدوري',
    icon: { icon: 'tabler-star' },
    action: 'manage',
    subject: 'all',
    children: [
      { title: 'فترات التقييم', to: 'apps-evaluations-list', action: 'manage', subject: 'all' },
    ],
  },
  { heading: 'إدارة النظام' },
  {
    title: 'إعدادات النظام',
    icon: { icon: 'tabler-settings' },
    action: 'manage',
    subject: 'all',
    children: [
      { title: 'إعدادات الهوية', to: 'apps-settings-branding', action: 'manage', subject: 'all' },
      { title: 'سجل النشاطات', to: 'apps-settings-activity-logs', action: 'manage', subject: 'all' },
    ],
  },
  {
    title: 'إدارة المستخدمين',
    icon: { icon: 'tabler-user-cog' },
    action: 'manage',
    subject: 'all',
    children: [
      { title: 'قائمة المستخدمين', to: 'apps-user-list', action: 'manage', subject: 'all' },
      { title: 'المجموعات والصلاحيات', to: 'apps-roles', action: 'manage', subject: 'all' },
    ],
  },
]

export default navigation
