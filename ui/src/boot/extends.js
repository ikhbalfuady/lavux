import { boot } from 'quasar/wrappers'
import { createPinia } from 'pinia'

// Services
import { Handler } from './../services/Handler'
import { Helper } from './../services/Helper'
import { Config } from './../Config'
import Api from './../services/Api'

// Core Components
import Echo from '../components/core/Echo'
import SideNav from '../components/core/SideNav'
import SideNavList from '../components/core/SideNavList'
import TopNav from '../components/core/TopNav'
import TopNavList from '../components/core/TopNavList'
import TopBar from '../components/core/TopBar'
import TopMenus from '../components/core/TopMenus'
import ProfilePopup from '../components/core/ProfilePopup'
import Notif from '../components/core/Notif'
import NotificationList from '../components/core/NotificationList'
const pinia = createPinia()

export default boot(async ({ app }) => {
  // Core
  app.component('echo', Echo)
  app.component('side-nav', SideNav)
  app.component('side-nav-list', SideNavList)
  app.component('top-nav', TopNav)
  app.component('top-nav-list', TopNavList)
  app.component('top-bar', TopBar)
  app.component('top-menus', TopMenus)
  app.component('profile-popup', ProfilePopup)
  app.component('notif', Notif)
  app.component('notification-list', NotificationList)

  // Services
  app.config.globalProperties.$Api = new Api()
  app.config.globalProperties.$Handler = Handler
  app.config.globalProperties.$Helper = Helper
  app.config.globalProperties.$Config = Config
  app.use(pinia)
   
  // Auto register globals components
  const globalComponents = require.context(
    '../components/globals', // The relative path of the components folder
    false, // Whether or not to look in subfolders
    /[A-Z]\w+\.(vue|js)$/ // The regular expression used to match base component filenames
  )

  const registerGlobalComponents = app => globalComponents.keys().forEach(fileName => {
    const uppercaseToStrip = (text) => {
      return text.replace(/(.)([A-Z][a-z]+)/, '$1-$2').replace(/([a-z0-9])([A-Z])/, '$1-$2').toLowerCase()
    }
    const componentConfig = globalComponents(fileName)
    const componentName = fileName.split('/').pop().replace(/\.\w+$/, '')  // Get PascalCase name of component
    const tagName = uppercaseToStrip(componentName)
    // console.log('AutoRegComponents: ', componentName , tagName)
    app.component(tagName, componentConfig.default || componentConfig)
  })

  registerGlobalComponents(app)

  // Auto register globals components
  const requireLavuxComponents = require.context(
    '../components/lavux', // The relative path of the components folder
    false, // Whether or not to look in subfolders
    /[A-Z]\w+\.(vue|js)$/ // The regular expression used to match base component filenames
  )

  const registerLavuxComponents = app => requireLavuxComponents.keys().forEach(fileName => {
    const uppercaseToStrip = (text) => {
      return text.replace(/(.)([A-Z][a-z]+)/, '$1-$2').replace(/([a-z0-9])([A-Z])/, '$1-$2').toLowerCase()
    }
    const componentConfig = requireLavuxComponents(fileName)
    const componentName = fileName.split('/').pop().replace(/\.\w+$/, '')  // Get PascalCase name of component
    const tagName = uppercaseToStrip(componentName)
    // console.log('AutoRegComponents: ', componentName , tagName)
    app.component(tagName, componentConfig.default || componentConfig)
  })

  registerLavuxComponents(app)

})

