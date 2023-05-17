import { defineStore } from 'pinia'
import { Helper } from '../services/Helper'

export const useGlobalStore = defineStore({
  id: 'globalStore',
  state: () => ({
    drawer: true,
    primaryDrawer: false,
    topMenuMode: false,
    miniMenuMode: false,
    actionModal: false,
    userInfo: {
      name: 'John Doe',
      username: 'johndoe',
      email: 'johndoe@mail.com',
      role: {
        slug: 'SYS',
        name: 'system'
      }
    },
    topMenu: []
  }),
  getters: {
    /*
      usage :
      store.getDrawer(arg1, arg2)
    */
    getDrawer: (state) => {
      return (add) => { // params from parent
        if (state.drawer) return add + ":isOpen"
        else return add + ":isClose"
      }
    },
    getPrimaryDrawer: (state) => {
      const tmpName = 'primaryDrawer'
      if (!Helper.checkLdb(tmpName)) Helper.saveLdb(tmpName, false) // first init
      if (state[tmpName]) Helper.saveLdb(tmpName, state[tmpName])
      return Helper.getLdb(tmpName)
    },
    getTopMenuMode: (state) => {
      const tmpName = 'topMenuMode'
      if (!Helper.checkLdb(tmpName)) Helper.saveLdb(tmpName, false) // first init
      if (state[tmpName]) Helper.saveLdb(tmpName, state[tmpName])
      return Helper.getLdb(tmpName)
    },
    getMiniMenuMode: (state) => {
      const tmpName = 'miniMenuMode'
      if (!Helper.checkLdb(tmpName)) Helper.saveLdb(tmpName, false) // first init
      if (state[tmpName]) Helper.saveLdb(tmpName, state[tmpName])
      return Helper.getLdb(tmpName)
    },
    getActionModal: (state) => {
      const tmpName = 'actionModal'
      if (!Helper.checkLdb(tmpName)) Helper.saveLdb(tmpName, false) // first init
      if (state[tmpName]) Helper.saveLdb(tmpName, state[tmpName])
      return Helper.getLdb(tmpName)
    },
    getUserInfo: (state) => {
      const tmpName = 'userInfo'
      if (!Helper.checkLdb(tmpName)) Helper.saveLdb(tmpName, false) // first init
      if (state[tmpName]) Helper.saveLdb(tmpName, state[tmpName])
      return Helper.getLdb(tmpName)
    },
    getTopMenu: (state) => {
      const tmpName = 'topMenu'
      if (!Helper.checkLdb(tmpName)) Helper.saveLdb(tmpName, []) // first init
      if (state[tmpName]) Helper.saveLdb(tmpName, state[tmpName])
      return Helper.getLdb(tmpName)
    }
  }, 
  actions: {
    setDrawer (value = true) {
      this.drawer = value
    },
    setPrimaryDrawer (val = false) {
      this.primaryDrawer = val
      Helper.saveLdb('primaryDrawer', val)
    },
    setTopMenuMode (val = false) {
      this.topMenuMode = val
      Helper.saveLdb('topMenuMode', val)
    },
    setMiniMenuMode (val = false) {
      this.miniMenuMode = val
      Helper.saveLdb('miniMenuMode', val)
    },
    setActionModal (val = false) {
      this.actionModal = val
      Helper.saveLdb('actionModal', val)
    },
    setUserInfo (val) {
      if (!val) val = this.userInfo
      this.userInfo = val
      Helper.saveLdb('userInfo', val)
    },
    setTopMenu (menus) {
      this.topMenu = menus
    },
    
  }
})