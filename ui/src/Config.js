import {
    LocalStorage,
    Dialog
} from 'quasar'
import { Helper } from './services/Helper'

import { colors } from 'quasar'

const { getPaletteColor } = colors

export const Config = {

  appName () {
    const cfg = Helper.findObjectByKey(this.settings(), 'name', 'company')
    let res = '...'
    if (cfg) res = cfg?.list?.app_name?.value || res
    return res
  },

  appLogo (mini = false) {
    const cfg = Helper.findObjectByKey(this.settings(), 'name', 'company')

    if (mini) {
      let res = '/assets/icon.png'
      if (cfg) res = cfg?.list?.url_icon?.value || res
      return res
    }
    else {
      let res = '/assets/logo.png'
      if (cfg) res = cfg?.list?.url_logo?.value || res
      return res
    }
  },

  appLogoLight () {
    const cfg = Helper.findObjectByKey(this.settings(), 'name', 'company')
    let res = '/assets/logo-light.png'
    if (cfg) res = cfg?.list?.url_logo_light?.value || res
    return res
  },

  version () { return '1.0.7' },

  getApiRoot () {
    const cfg = Helper.findObjectByKey(this.settings(), 'name', 'company')
    if (LocalStorage.has('apiroot') === false) {
      Helper.showAlert('API Root Not Defined', 'Please reload this page, to re init Api Root!')
      window.location = '/login?e=miss-apiroot'
    }
    return LocalStorage.getItem('apiroot')
  },

  setApiRoot (message = 'ex: http://localhost/api', redirectTo = null) {
    var api = Config.getApiRoot()
    Dialog.create({
      title: 'Set API Root',
      message: message,
      prompt: {
        model: api,
        type: 'text' // optional
      },
      cancel: true,
      persistent: true
    }).onOk(data => {
      var val = data
      const last = val.charAt(val.length - 1)
      let endpoint = val
      if (last !== '/') endpoint = endpoint + '/'

      Config.saveApiRoot(endpoint)
      if (redirectTo) window.location = redirectTo
      else window.location.reload()
    }).onCancel(() => {
      // console.log('>>>> Cancel')
    }).onDismiss(() => {
      // console.log('I am triggered on both OK and Cancel')
    })
  },

  saveApiRoot (val) {
    LocalStorage.set('apiroot', val)
  },

  scrollThumbStyle () {
    return {
      right: '4px',
      borderRadius: '4px',
      backgroundColor: getPaletteColor('primary'),
      width: '6px',
      opacity: 0.75
    }
  },

  scrollBarStyle () {
    return {
      right: '2px',
      borderRadius: '7px',
      backgroundColor: getPaletteColor('primary'),
      width: '10px',
      opacity: 0
    }
  },

  currencyConfig (decimal = 2) {
    var res = {
      decimal: ',',
      thousands: '.',
      prefix: '',
      suffix: '',
      precision: decimal,
      max: 999999999999999,
      // masked: false,
      modelModifiers: {
        number: true,
      }
    }
    return res
  },

  credentials (saving = null) {
    if (saving === 'destroy') {
      const apiroot = this.getApiRoot()

      localStorage.clear()
      this.saveApiRoot(apiroot)
    } else if (saving) {
      if (saving.token) {
        Helper.saveLdb('token', saving.token)
        saving.token = null; // reset, for clean structure
      }
      if (saving.permissions) {
        this.permissions(saving.permissions)
        saving.permissions = null; // reset, for clean structure
      }
      if (saving.settings) {
        this.settings(saving.settings)
        saving.settings = null; // reset, for clean structure
      }
      Helper.saveLdb('credentials', saving)
      return
    }

    // retreive data
    if (Helper.checkLdb('credentials')) {
      var credentials = Helper.getLdb('credentials')
      return credentials
    } else return false
  },

  userId () {
    let user = this.credentials()
    return user ? user.id : null
  },

  permissions (permissions = null) {
    if (permissions) {
      Helper.saveLdb('permissions', permissions)
      console.info('Permission updated!')
    }
    if (Helper.checkLdb('permissions')) {
      return Helper.getLdb('permissions')
    } else return []
  },

  settings (settings = null) {
    if (settings) {
      Helper.saveLdb('settings', settings)
      console.info('Settings updated!')
    }
    if (Helper.checkLdb('settings')) {
      return Helper.getLdb('settings')
    } else return []
  },

}
