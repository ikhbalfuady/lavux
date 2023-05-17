/* eslint-disable no-unused-vars */
import axios from 'axios'
import { Dialog } from 'quasar'
import { Helper } from './Helper'
import { Handler } from './Handler'

export default class Api {
  constructor () {
    const cache = false
    const http = axios
    http.interceptors.response.use(this.handleSuccess, this.handleError)
    this.cache = cache
    this.http = http
    this.useToken = true
  }

  init (path, isMultipart = false) {

    const apiRoot = Helper.getLdb('apiroot')
    this.http.create({
      baseURL: apiRoot
    }) 

    this.http.defaults.headers['Access-Control-Allow-Origin'] = '*'

    const contentType = 'application/json'
    if (isMultipart) contentType = 'application/x-www-form-urlencoded'
    else this.http.defaults.headers['Content-Type'] = contentType

    const token = Helper.getLdb('token')
    if (path !== 'login') this.http.defaults.headers.Authorization = `Bearer ${token}`

    return {
      apiRoot,
      header: this.http.defaults.headers
    }
  }

  initConfig (config) {
    this.http = {
      ...this.http,
      ...config
    }
  }

  get (path, callback, rawEndpoint = false) {
    if (!rawEndpoint) {
      path = this.init(path).apiRoot + path
      // handler trash url
      if (Helper.getUrlParams('trash')) {
        if (path.includes("?")) path = path + '&trash'
        else path = path + '?trash'
      }
    }

    return this.http.get(path).then(response => {
      const resApi = this.compileResponse(response, path)
      callback(resApi.status, resApi.data, resApi.message, resApi.full)
    }).catch(response => {
      const resApi = this.compileResponse(response, path, 'error')
      callback(resApi.status, resApi.data, resApi.message, resApi.full)
    })
  }

  post (path, payload, callback, formatFormData = true, rawEndpoint = false) {
    if (!rawEndpoint) path = this.init(path).apiRoot + path

    // always compile form data when use post
    let data = payload
    if (formatFormData) {
      data = new FormData()
      if (payload) {
        for (const key in payload) {
          if (payload[key]) {
            var valueFormated = payload[key]
            if (valueFormated === true) valueFormated = 1
            else if (valueFormated === false) valueFormated = 0
            else if (valueFormated instanceof File) valueFormated = valueFormated
            else if (typeof valueFormated === 'object') valueFormated = JSON.stringify(valueFormated)
            data.append(key, valueFormated)
          }
        }
      }
    }

    return this.http.post(path, data).then(response => {
      var resApi = this.compileResponse(response, path)
      callback(resApi.status, resApi.data, resApi.message, resApi.full)
    }).catch(response => {
      var resApi = this.compileResponse(response, path, 'error')
      callback(resApi.status, resApi.data, resApi.message, resApi.full)
    })
  }

  put (path, payload, callback) {
    var init = this.init(path)
    path = init.apiRoot + path
    return this.http.put(path, payload).then(response => {
      var resApi = this.compileResponse(response, path)
      callback(resApi.status, resApi.data, resApi.message, resApi.full)
    }).catch(response => {
      var resApi = this.compileResponse(response, path, 'error')
      callback(resApi.status, resApi.data, resApi.message, resApi.full)
    })
  }

  delete (path, callback) {
    var init = this.init(path)
    path = init.apiRoot + path
    return this.http.delete(path).then(response => {
      var resApi = this.compileResponse(response, path)
      callback(resApi.status, resApi.data, resApi.message, resApi.full)
    }).catch(response => {
      var resApi = this.compileResponse(response, path, 'error')
      callback(resApi.status, resApi.data, resApi.message, resApi.full)
    })
  }

  /**
   * 
   * @param {object} config request configuration
   * @param {callback} callback getting callback function, usage : (status, data, message, response) => { // your code }
   * @returns void
   */
  request (config, callback) {
    const baseConfig = {
      method: config && config.method ? config.method.toUpperCase() : null, // Method HTTP (GET, POST, PUT, DELETE, etc.)
      url: config && config.url ? config.url : null, // URL target
      params: config && config.params ? config.params : null, // URL query
      data: config && config.data ? config.data : null, // Body data
      headers: config && config.headers ? config.headers : null, // Body data
      timeout: config && config.timeout ? config.timeout : 100000, // timeout limit
      responseType: config && config.responseType ? config.responseType : null, // response type
      // if "raw" set true, the initialize will be skip, it can used if you want make external request
      // you must see the "init()"" method so that you know what is set in the method if you want to use "raw" requests
      raw: config && config.raw ? config.raw : false, 
    }

    if (!baseConfig.raw) {
      const init = this.init(baseConfig.url)
      baseConfig.url = init.apiRoot + baseConfig.url
      baseConfig.headers = init.header

    }

    if (this.handleRequestConfig(baseConfig)) {
      return this.http(baseConfig).then(response => {
        var resApi = this.compileResponse(response, baseConfig.url)
        callback(resApi.status, resApi.data, resApi.message, resApi.full)
      }).catch(response => {
        var resApi = this.compileResponse(response, baseConfig.url, 'error')
        callback(resApi.status, resApi.data, resApi.message, resApi.full)
      }) 
    }
    else return false
  }

  compileResponse (response, url, mode = 'success') {
    var resApi = {
      url: url,
      status: 599,
      data: null,
      message: null,
      full: null,
    }

    if (mode === 'error') {
      var res2 = response.response ? response.response : null

      var resMsg = ''
      var dataApi = null
      var message = ''
      var status = 599
      if (res2) {
        // resMsg = res2.statusText ? res2.statusText : ''
        dataApi = res2.data ? res2.data : null
        message = dataApi.message ? dataApi.message : ''
        // message = `${resMsg} ${message}`
        status = res2.status ? res2.status : 599
      }

      resApi = {
        url: url,
        status: status,
        data: null,
        message: message,
        full: res2,
      }

    } else {
      var res = response.data ? response.data : null
      resApi = {
        url: url,
        status: response.status ? response.status : 599,
        data: res.data ? res.data : null,
        message: res.message ? res.message : null,
        full: response,
      }
    }

    this.validateResponseData(resApi) // validate data to get notif if have error
    return resApi
  }

  validateResponseData (reCompile) {
    var msg = ''
    if (reCompile) {
      msg = reCompile.message
    }

    if (reCompile === null) {
      return null
    } else if (reCompile.status === 200) {
      return reCompile.response
    } else if (reCompile.status === 400) {
      // console.log('ERR:400', reCompile, msg)
      Helper.showAlert('😅 Opps!', msg)
      return false
    } else if (reCompile.status === 401) {
      Helper.showAlert('Session ended', 'your session has ended, please login to continue')
      // console.log('ERR:401', reCompile)
      let resume = `resume=${window.location.href}`
      if (resume.includes("403")) resume = ''

      window.location = `/login?${resume}`
      return false
    } else if (reCompile.status === 404) {
      // console.log('ERR:404', reCompile)
      Helper.showAlert('😅 Not found!', msg ? msg : 'Page or host target (404) Not found')
      return false
    } else if (reCompile.status === 403) {
      Helper.showAlert('😂 Forbidden [403]', msg)
      // console.log('ERR:403', reCompile)
      this.reloadPermission()
      return false
    } else if (reCompile.status === 405) {
      Helper.showAlert('Opps! [405]', msg)
      // console.log('ERR:405', reCompile)
      return false
    } else if (reCompile.status === 500) {
      Helper.showAlert('😔 Error [500]', msg)
      // console.log('ERR:500', reCompile)
      return false
    } else if (reCompile.status === 599) {
      this.failedConnect(reCompile)
      return false
    } else return reCompile.data
  }

  failedConnect (reCompile) {
    Dialog.create({
      transitionShow: 'jump-up',
      transitionHide: 'jump-down',
      title: '🔌 <b class="text-red-8">Connection Failed!</b>',
      message: `Can't access url : <br> <small class="text-bold">${reCompile ? reCompile.url : ''}</small> <br> please try again!`,
      class: 'vl-dialog',
      ok: 'Close',
      html: true,
      position: 'top'
    }).onOk(() => {
    }).onCancel(() => {
    }).onDismiss(() => {
    })
  }

  setToken (token) {
    this.http.defaults.headers.common.Authorization = `Bearer ${token}`
    this.http.interceptors.request.use(config => {
      config.headers.post.Authorization = `Bearer ${token}`
      return config
    })
  }

  setFormMultipart () {
    this.http.defaults.headers['Content-Type'] = 'application/x-www-form-urlencoded'
  }

  handleRequestConfig (config) {
    if (!config.method) {
      console.error("Api.request(config.method)", "\n value not defined, at least fill it with http method like : GET, POST etc...")
      return false
    }
    else if (!config.url) {
      console.error("Api.request(config.url)", "\n value not defined, at least fill it with url target like : http://example.com")
      return false
    }
    else return true
  }

  getCache (full, defaultVaule = '') {
    // console.log('getCache :', this.cache)
    var cache = this.cache
    var cacheCOnfig = ModuleConfig.getAppConfig('app_config', 'cache_data')
    if (cacheCOnfig !== null) cache = cacheCOnfig

    if (cache) {
      if (full.config !== undefined) {
        var checkCacheLDB = Helper.checkLdb(full.config.method + ':' + full.config.url)
        var res = Helper.getLdb(full.config.method + ':' + full.config.url)
        if (!checkCacheLDB) res = defaultVaule
      } else res = defaultVaule

      // console.log('getCache:', typeof defaultVaule)
      // console.log('getCache LDB ' + full.config.url + ' :', checkCacheLDB)
      // console.log('getCache FILTER ' + full.config.url + ' :', res)
      return res
    } else {
      console.warn('API.getCache: false')
      return null
    }
  }

  reloadPermission () {
    this.get('me/permissions', (status, data, message, full) => {
      console.log('api.perm', status)
      if (status === 200) {
        Handler.makePermissions(data, true)
      }
    });
  }

  makeImageBlog (url, ldbName = 'img_blob') {
    this.http.get(url, { responseType: 'blob' })
    .then(response => {
      const blob = new Blob([response.data], { type: 'image/jpeg' })
      Helper.saveLdb(ldbName, URL.createObjectURL(blob))
    })
    .catch(error => {
      console.error('makeImageBlog', error)
    })
   
  }

}
