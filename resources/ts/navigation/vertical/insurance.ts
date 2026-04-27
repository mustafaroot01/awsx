const navigation = [
  { heading: 'نظام التأمين والإنتاج' },
  {
    title: 'لوحة التحكم',
    icon: { icon: 'tabler-chart-pie' },
    to: 'dashboards-insurance',
    action: 'read',
    subject: 'Auth',
  },
  {
    title: 'الموارد البشرية',
    icon: { icon: 'tabler-users' },
    action: 'read',
    subject: 'Employee',
    children: [
      { title: 'الموظفون',    to: 'apps-employees-list',       action: 'read',   subject: 'Employee' },
      { title: 'سجل الحوافز', to: 'apps-employees-incentives', action: 'read',   subject: 'Employee' },
    ],
  },
  {
    title: 'إدارة الفروع',
    icon: { icon: 'tabler-building-community' },
    action: 'read',
    subject: 'Branch',
    children: [
      { title: 'قائمة الفروع', to: 'apps-branches-list', action: 'read', subject: 'Branch' },
    ],
  },
  {
    title: 'الإنتاج والتخطيط',
    icon: { icon: 'tabler-clipboard-list' },
    action: 'read',
    subject: 'Policy',
    children: [
      { title: 'الخطط الإنتاجية',        to: 'apps-production-plans-list', action: 'read', subject: 'ProductionPlan' },
      { title: 'وثائق التأمين',           to: 'apps-policies-list',         action: 'read', subject: 'Policy' },
      { title: 'الوثائق المنتهية قريباً', to: 'apps-policies-expiring',     action: 'read', subject: 'Policy' },
    ],
  },
  {
    title: 'الإحصائيات والتقارير',
    icon: { icon: 'tabler-chart-bar' },
    action: 'read',
    subject: 'Statistics',
    children: [
      { title: 'إحصائيات الإنتاج',      to: 'apps-statistics-production', action: 'read', subject: 'Statistics' },
      { title: 'مقارنة الفروع',          to: 'apps-branches-comparison',   action: 'read', subject: 'Statistics' },
      { title: 'ترتيب الفروع',           to: 'apps-statistics-branches',   action: 'read', subject: 'Statistics' },
      { title: 'تقارير الموظفين',        to: 'apps-statistics-employees',  action: 'read', subject: 'Statistics' },
      { title: 'تحليل التكلفة والإنجاز', to: 'apps-statistics-roi',        action: 'read', subject: 'Statistics' },
    ],
  },
  {
    title: 'التقييم الدوري',
    icon: { icon: 'tabler-star' },
    action: 'read',
    subject: 'Evaluation',
    children: [
      { title: 'فترات التقييم', to: 'apps-evaluations-list', action: 'read', subject: 'Evaluation' },
    ],
  },
  { heading: 'إدارة النظام' },
  {
    title: 'إعدادات النظام',
    icon: { icon: 'tabler-settings' },
    action: 'manage',
    subject: 'Settings',
    children: [
      { title: 'إعدادات الهوية',  to: 'apps-settings-branding',      action: 'manage', subject: 'Settings' },
      { title: 'سجل النشاطات',    to: 'apps-settings-activity-logs', action: 'read',   subject: 'Log' },
    ],
  },
  {
    title: 'إدارة المستخدمين',
    icon: { icon: 'tabler-user-cog' },
    action: 'read',
    subject: 'User',
    children: [
      { title: 'قائمة المستخدمين',       to: 'apps-user-list', action: 'read', subject: 'User' },
      { title: 'المجموعات والصلاحيات',   to: 'apps-roles',     action: 'read', subject: 'Role' },
    ],
  },
]

export default navigation
