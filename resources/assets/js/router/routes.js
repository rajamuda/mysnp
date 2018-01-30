const Welcome = () => import('~/pages/welcome')
const Login = () => import('~/pages/auth/login')
const Register = () => import('~/pages/auth/register')
const PasswordEmail = () => import('~/pages/auth/password/email')
const PasswordReset = () => import('~/pages/auth/password/reset')

const Home = () => import('~/pages/home')
const Settings = () => import('~/pages/settings/index')
const SettingsProfile = () => import('~/pages/settings/profile')
const SettingsPassword = () => import('~/pages/settings/password')
const Jobs = () => import('~/pages/jobs/index')
const JobsList = () => import('~/pages/jobs/list')
const JobsCreate = () => import('~/pages/jobs/create')
const JobsProcess= () => import('~/pages/jobs/process')

export default [
  { path: '/', name: 'welcome', component: Welcome },

  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/password/reset', name: 'password.request', component: PasswordEmail },
  { path: '/password/reset/:token', name: 'password.reset', component: PasswordReset },

  { path: '/home', name: 'home', component: Home },
  { path: '/settings', component: Settings, children: [
    { path: '', redirect: { name: 'settings.profile' }},
    { path: 'profile', name: 'settings.profile', component: SettingsProfile },
    { path: 'password', name: 'settings.password', component: SettingsPassword }
  ] },
  { path: '/jobs', component: Jobs, children: [
    { path: '', redirect: { name: 'jobs.list' }},
    { path: 'list', name: 'jobs.list', component: JobsList },
    { path: 'create', name: 'jobs.create', component: JobsCreate },
    { path: 'process/:id', name: 'jobs.process', component: JobsProcess }
  ] },

  { path: '*', component: require('~/pages/errors/404') }
]
