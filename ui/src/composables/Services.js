import { Handler } from './../services/Handler'
import { Helper } from './../services/Helper'
import Api from './../services/Api'
import { Config } from './../Config'
import { useMeta } from 'quasar';
import { createPinia } from 'pinia'
import { useGlobalStore } from './../store/GlobalStore';

const useServices = () => {

  const SetMetaPage = (title, meta = {}) => {
    title = Helper.capitalFirst(title)
    useMeta({
      title,
      titleTemplate: (title) => `${title ? `${title} | ` : ''}${Config.appName()}`,
      ...meta
    })
  }

  return {
    Config: Config,
    Handler: Handler,
    Helper: Helper,
    Api: new Api(),
    Store: createPinia(),
    GlobalStore: useGlobalStore(),
    SetMetaPage
  }

}

export default useServices