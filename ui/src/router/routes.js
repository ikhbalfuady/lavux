// CUSTOM REGISTER ROUTE
const customRoutes = [
  {
    name: 'login',
    path: '/login',
    component: () => import('layouts/Login.vue')
  },
  {
    path: "/",
    component: () => import("layouts/MainLayout.vue"),
    children: [
      { name: 'home', path: "/", component: () => import("pages/Index.vue") },
      { name: '401', path: "/401", component: () => import("pages/Error401.vue") },
      { name: '403', path: "/403", component: () => import("pages/Error403.vue") },
      { name: '404', path: "/404", component: () => import("pages/Error404.vue") },
      { name: 'example', path: "/example", component: () => import("pages/example.vue") },
      { name: 'generator', path: "/generator", component: () => import("pages/generator.vue") },
      { name: 'settings', path: "/settings", component: () => import("pages/settings.vue") },
      // accounts
      { name: 'update-profile', path: '/me/update-profile', component: () => import('../pages/accounts/update-profile.vue') },
      { name: 'change-password', path: '/me/change-password', component: () => import('../pages/accounts/change-password.vue') },
      { name: 'notifications', path: '/me/notifications', component: () => import('../pages/accounts/notifications.vue') },

    ],
  },
]

// AUTO REGISTER ROUTE
// define the value as in Meta.module on the page you want to create an autoregsiter
const moduleList = [
  'metas',
  'users',
  'permissions',
  'role-permissions',
  'role-groups',
  'roles',
  'metas',
]

var autoRegisterRoute = moduleList.map(name => {
  var obj = {
    path: '/' + name,
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { name: `${name}`, path: `/${name}`, component: () => import(`pages/${name}/index.vue`) },
      { name: `${name}-detail`, path: `/${name}/:id`, component: () => import(`pages/${name}/detail.vue`) },
      { name: `${name}-add`, path: `/${name}/form`, component: () => import(`pages/${name}/form.vue`) },
      { name: `${name}-edit`, path: `/${name}/form/:id`, component: () => import(`pages/${name}/form.vue`) }
    ]
  }

  // console.log('AutoRegRoutes:', name)
  return obj

})

// FIX ROUTES
const routes = [
  ...autoRegisterRoute,
  ...customRoutes,
  // handle 404 automaticaly
  {
    path: '/:catchAll(.*)*',
    redirect: to => {
      const path = window.location.href
      console.log('to', to)
      // console.log('path', path)
      if (to.path.includes('api')) {
        window.location.href = `https://ExampleURL.com/`
        // return '/e/404';
      } else {
        return '/404';
      }
    }
    // component: () => import('pages/Error404.vue')
  }
]

export default routes
