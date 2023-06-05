import {
  LocalStorage,
  is,
  uid,
  date,
  Notify,
  Dialog,
  Loading,
  LoadingBar,
  QSpinnerFacebook,
} from 'quasar'


export const Helper = {

  /* LocalStorage */
  saveLdb (key, ret) {
    return LocalStorage.set(key, ret)
  },

  checkLdb (key) {
    var data = LocalStorage.has(key)
    if (data !== true) return false
    else return true
  },

  getLdb (key) {
    var data = LocalStorage.getItem(key)
    // if (!this.checkLdb(key)) console.info('LDB [' + key + '] Not found!')
    return data
  },

  deleteLdb (key) {
    return LocalStorage.remove(key)
  },

  getFromLdb (ldbName, defaultData = null) {
    if (this.checkLdb(ldbName)) {
      if (this.getLdb(ldbName) !== null) defaultData = this.getLdb(ldbName)
    }
    return defaultData
  },

  /* Notif, Alert & Loading */
  showAlert (title, msg = null, persistent = false, ok = 'OK', evtOk = null, evtCancel = null, evtDismiss = null) {
    var judul = title
    var pesan = msg
    if (title === 'try') {
      judul = 'Opps!'
      pesan = 'Terjadi kesalahan saat menghubungkan ke server, harap coba lagi atau tekan Refresh!'
    }

    Dialog.create({
      transitionShow: 'jump-up',
      transitionHide: 'jump-down',
      title: judul,
      message: pesan,
      class: 'vl-dialog',
      html: true,
      ok,
      cancel: 'Cancel',
      persistent: persistent
    }).onOk(() => {
      if (evtOk) evtOk()
    }).onCancel(() => {
      if (evtCancel) evtCancel()
    }).onDismiss(() => {
      if (evtDismiss) evtDismiss()
    })
  },

  loadingOverlay (show = true, msg = 'Loading...') {
    /* This is for Codepen (using UMD) to work */
    const spinner = typeof QSpinnerFacebook !== 'undefined'
      ? QSpinnerFacebook // Non-UMD, imported above
      : Quasar.components.QSpinnerFacebook // eslint-disable-line
    /* End of Codepen workaround */

    Loading.show({
      spinner,
      spinnerColor: 'white',
      spinnerSize: 140,
      backgroundColor: 'primary',
      message: msg,
      messageColor: 'white'
    })
    // console.info(show, 'loading: ' + msg)

    if (show === false) {
      setTimeout(() => {
        Loading.hide()
      }, 300)
    }
  },

  loading (show = true) {
    LoadingBar.setDefaults({
      color: 'orange',
      size: '5px',
      position: 'top'
    })

    if (show === false) {
      setTimeout(() => {
        LoadingBar.stop()
      }, 300)
    } else LoadingBar.start()
  },

  successAlertBody (title, msg = null, className = 'text-center') {
    var body = '<div class="success-checkmark"> <div class="check-icon "> <span class="icon-line line-tip "></span> <span class="icon-line line-long"></span> <div class="icon-circle"></div> <div class="icon-fix"></div> </div> </div>'
    var judul = '<div class="text-center q-dialog__title text-primary capital text-bold">' + title + '</div>'
    var pesan = '<div class="' + className + ' animated fadeIn">' + msg + '</div>'
    return body + judul + pesan
  },

  showSuccess (title, msg = null, className = 'text-center') {
    Dialog.create({
      transitionShow: 'jump-up',
      transitionHide: 'jump-down',
      title: '',
      message: this.successAlertBody(title, msg, className),
      html: true
    }).onOk(() => {
      // console.log('OK')
    }).onCancel(() => {
      // console.log('Cancel')
    }).onDismiss(() => {
      // console.log('I am triggered on both OK and Cancel')
    })
  },

  showToast (msg, timeout = 5000, position = 'top-right', caption = '', color = 'dark') {
    Notify.create({
      progress: true,
      message: msg,
      multiLine: false,
      position: position,
      icon: 'info',
      color: color,
      timeout: timeout,
      caption: caption,
      actions: [{ icon: 'close', color: 'white', dense: true, round: true, size: 'sm', handler: () => { /* console.log('wooow') */ } }]
    })
  },

  loadingIndicator (msg, timeout = 5000, position = 'top-right', caption = '', color = 'dark') {
    Notify.create({
      spinner: QSpinnerPuff,
      progress: true,
      message: msg,
      multiLine: false,
      position: position,
      icon: 'info',
      color: color,
      timeout: timeout,
      caption: caption,
      actions: [{ icon: 'close', color: 'white', dense: true, round: true, size: 'sm', handler: () => { /* console.log('wooow') */ } }]
    })
  },

  /* Utils */
  _is (value, typeToCheck = null) {
      const type = typeof value;

      if (value === true) return 'boolean'
      else if (value === false) return 'boolean'
      else if (type === 'number') {
        if (Number.isInteger(value))  return 'integer';
        else return 'float';
      } 
      else if (type === 'object') {
        if (value === null) return 'null'
        else if (Array.isArray(value)) {
          if (value.length === 0) return 'object'
          else if (value.every(item => typeof item === 'object' && item !== null)) return 'arrayObject'
          else return 'array'
        } 
        else if (value instanceof Date) {
          const isDateTime = value.getHours() > 0 || value.getMinutes() > 0 || value.getSeconds() > 0
          return isDateTime ? 'dateTime' : 'date'
        }
      } 
      else if (type === 'string') {
        if (/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}:\d{2}(\.\d{1,3})?Z?)?$/.test(value)) return 'date'
        if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/.test(value)) return 'dateTime'
        else if (/^\d{2}:\d{2}(:\d{2})?$/.test(value)) return 'time'
      }
      return type
    
      // if (typeToCheck === undefined) {
      //   return type
      // } else if (typeToCheck === 'array' || typeToCheck === 'arrayObject') {
      //   const isArray = Array.isArray(value)
    
      //   if (isArray && typeToCheck === 'arrayObject') {
      //     return value.length > 0 && isType(value[0], 'object')
      //   }
    
      //   return isArray && typeToCheck === 'array'
      // } else if (typeToCheck === 'date') {
      //   return value instanceof Date
      // } else if (typeToCheck === 'dateString') {
      //   const regex = /^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}:\d{2}(\.\d{1,3})?Z?)?$/
      //   return regex.test(value)
      // } else if (typeToCheck === 'dateTimeString') {
      //   const regex = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{1,3})?Z?$/
      //   return regex.test(value)
      // } else if (typeToCheck === 'timeString') {
      //   const regex = /^\d{2}:\d{2}(:\d{2}(\.\d{1,3})?)?$/
      //   return regex.test(value)
      // }
    
      // return type === typeToCheck
  },

  base64 (str, type) {
    if (type === 'enc') return btoa(str)
    else return atob(str)
  },

  isBrightColor (color) {
    let r, g, b;
    if (color.startsWith("#")) {
      r = parseInt(color.substring(1, 3), 16);
      g = parseInt(color.substring(3, 5), 16);
      b = parseInt(color.substring(5, 7), 16);
    } else if (color.startsWith("rgb(")) {
      const rgbValues = color.substring(4, color.length - 1).split(",");
      r = parseInt(rgbValues[0]);
      g = parseInt(rgbValues[1]);
      b = parseInt(rgbValues[2]);
    }
    const brightness = (0.299 * r) + (0.587 * g) + (0.114 * b);
    return brightness > 128 ? true : false;
  },
   
  getUrlParams (attr, strict = false) {
    const params = new URLSearchParams(window.location.search)
    var res = null
    var check = params.has(attr)
    var get = params.get(attr)
    if(check) {
      res = get ? get : true
      if (strict) res = get ? get : null
    }
    // console.log('urlParams', check, get, res)
    return res
  },

  findObjectByKey (array, key, value, getIndex = false) {
    if (array !== undefined) {
      for (var i = 0; i < array.length; i++) {
        if (array[i][key] === value) {
          if (getIndex) return i
          else return array[i]
        }
      }
      return null
    } else return null
  },

  createUID (heavy = true) {
    var serial = uid()
    var d = new Date()
    if (heavy) serial = serial + btoa(d)
    return serial
  },

  createYMD (_date, time = false) {
    // create ymd system
    var tanggal = new Date(_date),
      tgl = tanggal.getDate(),
      bln = tanggal.getMonth() + 1,
      thn = tanggal.getFullYear(),
      jam = tanggal.getHours(),
      menit = tanggal.getMinutes()

    if (bln < 10) bln = '0' + bln
    if (tgl < 10) tgl = '0' + tgl
    if (jam < 10) jam = '0' + jam
    if (menit < 10) menit = '0' + menit

    var date = ''
    if (time === false || time === '') date = thn + '-' + bln + '-' + tgl + ''
    else date = thn + '-' + bln + '-' + tgl + ' ' + jam + ':' + menit + ':00'
    return date
  },

  getDateNow (days = 0) {
    var tzoffset = new Date().getTimezoneOffset() * 60000 // offset in milliseconds
    var localISOTime = new Date(Date.now() - tzoffset)
      .toISOString()
      .slice(0, -1)
    var res = localISOTime.split('T')
    var time = res[1]
    time = time.split(':')
    var hasil = res[0] + ' ' + time[0] + ':' + time[1]

    if (days !== 0) {
      var addDate = new Date(hasil)
      addDate.setDate(addDate.getDate() + days)
      hasil = this.createYMD(addDate, true)
    }
    return hasil
  },

  getTimeNow () {
    var tanggal = new Date(this.getDateNow()),
      jam = tanggal.getHours(),
      menit = tanggal.getMinutes()
    return jam + ':' + menit
  },

  today (time = true) {
    return Helper.createYMD(this.getDateNow(), time)
  },

  getStartEndDayOfMonth (date = null) {
    date = date ? date : this.today(false)
    const inputDate = new Date(date);
    const month = inputDate.getMonth();
    const year = inputDate.getFullYear();
  
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
  
    return {
      from: this.createYMD(firstDay),
      to: this.createYMD(lastDay)
    }
  },

  getKeyObject (obj, index = null) {
    let res = null
    const keys = Object.keys(obj)
    if (keys.length) res = keys[0]
    return res
  },

  checkParams (params) {
    if (Object.keys(params).length === 0) return false
    else return true
  },

  /* Formater */
  currency (number, rounding = false, lang = 'id-ID') {
    var res = 0
    if (rounding) {
      // console.log('raw', number)
      res = Number(number).toLocaleString(lang) + (b ? ',' + b : '')
    } else {
      number = '' + number
      // console.log('raw', number)
      var array = number.split('.')
      var a = array[0] ? array[0] : '0'
      var b = array[1] ?? null
      if (b) b = b.substring(0, 4)
      res = Number(a).toLocaleString(lang) + (b ? ',' + b : '')
    }

    return res
  },

  replace(val, needle, placement = ' ') {
    const regExp = new RegExp(needle, 'g');
    return val.replace(regExp, placement)
  },

  ucwords(str, splitter = ' ', separator = ' ') {
    const words = str.split(splitter);
    const capitalizedWords = words.map(word => {
      return word.charAt(0).toUpperCase() + word.slice(1);
    })
    return capitalizedWords.join(separator);
  },

  unReactive (arr) {
    arr = JSON.stringify(arr)
    return JSON.parse(arr)
  },

  makeRef(res) {
    return res.replace(/ /g, '_').replace(/-/g, '_');
  },

  makeSlug (val, separator = '-') {
    val = val.replace(/\s+/g, separator) // replace all space to strip
    val = val.toLowerCase()
    return val
  },

  makeTableName(str) {
    const hasil = str.replace(/([a-z])([A-Z])/g, '$1_$2').toLowerCase();
    return hasil.replace(/\s/g, '_');
  },

  getFirstChar (str, toUpperCase = true) {
    if (!str) return null
    var res = str.charAt(0)
    if (toUpperCase) res = res.toUpperCase()
    return res
  },

  capitalFirst (string) {
    var res = null
    if (string) res = string.charAt(0).toUpperCase() + string.slice(1)
    return res
  },

  beautyDate (date, csep = null, time = null) {
    var sep = ' '
    if (csep) sep = csep
    var format = `D${sep}MMM${sep}YYYY`
    if (time) format = format + ' - HH:mm'
    return this.toDate(date, format)
  },
  
  toFormData (payload) {
    const data = new FormData()
    if (payload) {
      for (const key in payload) {
        if (payload[key]) {
          var valueFormated = payload[key]
          if (valueFormated === true) valueFormated = 1
          if (valueFormated === false) valueFormated = 0
          if (typeof valueFormated === 'object') valueFormated = JSON.stringify(valueFormated)
          data.append(key, valueFormated)
        }
      }
    }
    return data
  },

  toDate (tanggal, format = 'YYYY-MM-DD') { // full date format sql : YYYY-MM-DD HH:MM / beauty DD MMM YYYY
    var _date = new Date(tanggal)
    return date.formatDate(_date, format)
  },

  toNumber (val) {
    val = val + '' // convert to string first
    val = val.replace(/[^0-9]/g, '')
    return +val
  },

  toLabel (text, separator = '_') {
    let res = ''
    if (text) {
      if (separator === '-') res = text.replace(/(?:-| |\b)(\w)/g, function($1){return $1.toUpperCase().replace(separator,' ')})
      else res = text.replace(/(?:_| |\b)(\w)/g, function($1){return $1.toUpperCase().replace(separator,' ')})
    }
    return res
  },



}