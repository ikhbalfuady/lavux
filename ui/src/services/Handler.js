import {
  LocalStorage
} from 'quasar'

import { Config } from './../Config'
import { Helper } from './Helper'
import { useGlobalStore } from '../store/GlobalStore';
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'

export const Handler = {

  // Side Menus
  drawer (setValue = null) {
    const store = useGlobalStore()
    const { drawer } = storeToRefs(useGlobalStore())
    if (setValue !== null) store.setDrawer(setValue)
    return drawer.value // to get realtime data, use as computed
  },

  drawerMini (val = '') {
    if (LocalStorage.has('miniModeMenu') === false) {
      LocalStorage.set('miniModeMenu', false)
    }
    if (val !== '') LocalStorage.set('miniModeMenu', val)
    return LocalStorage.getItem('miniModeMenu')
  },

  // Top Menu
  topMenu (setValue = null) {
    const store = useGlobalStore()
    const { topMenu } = storeToRefs(useGlobalStore())
    if (setValue !== null) store.setTopMenu(setValue)
    return topMenu.value // to get realtime data, use as computed
  },

  topMenuHasSub (params) {
    if (params.children !== undefined) {
      if (params.children.length !== 0) return true
      else return false
    } else return false
  },

  topMenuCallEvent (params, index) {
    if (!params) return
    if (params[index].event !== null && params[index].event !== undefined) return params[index].event()
  },

  // Table
  table (columns = [], cacheNameVisColumn = null, limit = 16) {
    let table = {
      search: '',
      endpoint: '',
      sort: null,
      data: [],
      dataTmp: [],
      searchBy: [],
      periode: null, // untuk default filterDate by
      filterDate: null, // nilai dari filterdate
      filterDateList: [], // list periode
      loading: false,
      isTrashed: false, // flag to get trash data
      hasLoaded: true, // flag to get notice data has loaded
      action: true, // visibility action col
      limit, // for rowsPerPage & perpage pagination default
      tmp: null, // untuk simpan full data table terakhir sebelum melakukan search
      searchColumn: true, // search column control
      sortColumn: true, // sort column control
      columns: [
        // ...columns
        // { name: 'id', label: 'id', field: 'id', align: 'left', style: 'width: 20px' }
      ],
      pagination: {
        isLast: false,
        lastPage: 0,
        from: 0,
        to: 0,
        page: 1,
        rowsPerPage: 0,
        perPage: 0,
        rowsNumber: 0
      },
      paginationTmp: {
        isLast: false,
        page: 1,
        rowsPerPage: 0,
        perPage: 0,
        rowsNumber: 0
      },
      selected: [],
      cacheVisibilityColumn: cacheNameVisColumn, // caching name of visibility column saved
      visibleColumns: []
    }

    table.pagination.rowsPerPage = table.limit
    table.paginationTmp.rowsPerPage = table.limit

    // handle cache
    let cache = []
    let cacheVC = Helper.getLdb(table.cacheVisibilityColumn)
    if (table.cacheVisibilityColumn && cacheVC) cache = cacheVC
    if (cache.length) table.visibleColumns = cache

    for (const col of columns) {
      // define search by
      let push = (col.searchable !== undefined) ? col.searchable : true
      col.searchable = push
      if (col.name !== 'action' && col.field) { // default only set 2 column to global search
        if (push && table.searchBy.length < 2) table.searchBy.push(col.name)
      }

      // set default, disimpan disini karena menghindari proses assign ke searchBy
      // col.field = (col.field) ? col.field : col.name

      // handle visibility column on cache
      if (!cache.length) table.visibleColumns.push(col.name)

      col.searching = {
        show: false,
        value: null,
        value2: null,
        operator: (col.searching && col.searching.operator) ? col.searching.operator : 'like',
        operator2: (col.searching && col.searching.operator2) ? col.searching.operator2 : null,
      }

      col.align = col.align ? col.align : 'center' // force default align

      table.columns.push(col)
    }

    return table
  },

  tableReset (table) {
    table.columns.map(r => {
      if (r.sort) r.sort = 'asc'
      if (r.searching) {
        r.show = false
        r.value = null
      }

    })

    let _table = this.table([])
    table.pagination.rowsNumber = _table.pagination.rowsNumber
    table.pagination.page = _table.pagination.page
    table.pagination.rowsPerPage = _table.pagination.rowsPerPage
    table.pagination.isLast = _table.pagination.isLast
    return table
  },

  // Preparation
  modalConfig (additional) {
    return {
      target: 'form',
      show: false,
      data: null,
      ...additional
    }
  },

  makeSlug (val) {
    return Helper.makeSlug(val)
  },

  makeModuleName (val) {
    return Helper.replace(val, ' ', '')
  },

  columnToLabel (val) {
    val = val.replace(/_/g, ' ') // replace all space to strip
    // val = val.toLowerCase()
    return val
  },

  toObjectSelect (arr) {
    let temp = []
    for (let i = 0; i < arr.length; i++) {
      let name =  this.columnToLabel(arr[i])
      temp.push({ id: arr[i], name: name })
    }
    return temp
  },

  viewList (data, addExclude = []) {
    let exlude = [
      ...addExclude,
      'id',
      'created_at',
      'updated_at',
      'deleted_at',
      'created_ip',
      'updated_ip',
      'deleted_ip',
      'created_by',
      'updated_by',
      'deleted_by',
      '_log_data',
    ]
    let list = {}
    for (const key in data) {
      if (exlude.indexOf(key) < 0) list[key] = data[key]
    }
    return list
  },

  labelFilterDate (val) {
    if (val) {
      if (val && val.from && val.to) {
        return `${Helper.beautyDate(val.from)} - ${Helper.beautyDate(val.to)}`
      } else if (val) {
        return Helper.beautyDate(val)
      }
    } else return null
  },

  async filterSelect (val, update, target, selectSource, searchField = null) {
    let targetNameTmp = target + 'Tmp'
    await update(() => {
      selectSource[target] = selectSource[targetNameTmp]
    })

    await update(() => {
      const needle = val.toLowerCase()
      let tmp = selectSource[targetNameTmp]
      selectSource[target] = tmp.filter(v => {
        let selector = 'name'
        if (v[selector] === undefined) selector = 'id'
        if (searchField) selector = searchField
        return v[selector].toLowerCase().indexOf(needle) > -1
      })
    })
    return selectSource
  },

  today () {
    return Helper.today()
  },

  // Credentials & Permissions
  credentials (saving = null) {
    return Config.credentials(saving)
  },
  
  permissionList (storing = null) {
    return Config.permissions(storing)
  },

  // permissions = like  structure you get from login  
  makePermissions (permissions, store = false) {
    let perms = []
    for (let i = 0; i < permissions.length; i++) {
      if(permissions[i]) {
        let slug = `${permissions[i].module} ${permissions[i].name}`
        slug = this.makeSlug(slug)
        perms.push(slug)
      }
    }
    if (store) this.permissionList(perms)
    return perms
  },

  permissionPage (permission, autoredirect = false) {
    permission = this.makeSlug(permission) // covert to slug format
    let check = this.checkPermission(permission, this.permissionList())
    if (!check && autoredirect) this._403()
    return check
  },

  checkPermission (permission, permissionList) {
    if (!permissionList) permissionList = [] // set default
    if (permission) {
      for (let i = 0; i < permissionList.length; i++) {
        if (permissionList.includes(permission))  return true
      }
      return false
    } else return true
  },

  checkPermissionMenu (permissions, permissionList) {
    if (permissions) {
      if (permissions.length === 0) return true // handle allowed jika permission tidak ada
      for (let i = 0; i < permissions.length; i++) {
        if (permissionList.includes(permissions[i])) {
          return true
        }
      }
      return false
    } else return true
  },

  makeAllPermissionAllowed(permissionsMeta) {
    Object.keys(permissionsMeta).forEach(key => {
      permissionsMeta[key] = true;
    });
    return permissionsMeta;
  },

  rules (val, _type = 'required', _msg = null, _raw = false) {
    /* Rules avail
      min-char-{lenght}
      max-char-{lenght}
      min-numb-{lenght}
      max-numb-{lenght}
      min-max-char-{lenghtMin}-{lengthMax}
      min-max-numb-{lenghtMin}-{lengthMax}
      required
    */
    let mxVal = null
    let minVal = 0
    let maxVal = 0
    let msg = ''
    let length = 0
    let res = []
    let minChar = _type.split('min-char-')
    let maxChar = _type.split('max-char-')
    let minNumb = _type.split('min-numb-')
    let maxNumb = _type.split('max-numb-')
    let minMaxChar = _type.split('min-max-char-')
    let minMaxNumb = _type.split('min-max-numb-')

    // minumum character
    if (minChar.length === 2 && minChar[0] === '') {
      length = parseInt(minChar[1])
      msg = _msg ? _msg : `Minimal ${length} characters`
      res = [ val => !!val && val.length >= length || msg ]
    }
    // maximum character
    else if (maxChar.length === 2 && maxChar[0] === '') {
      length = parseInt(maxChar[1])
      msg = _msg ? _msg : `Maximum ${length} characters`
      res = [ val => !!val && val.length <= length || msg ]
    }
    // minumum number value
    else if (minNumb.length === 2 && minNumb[0] === '') {
      length = parseInt(minNumb[1])
      msg = _msg ? _msg : `Minimal value is ${length}`
      res = [ val => !!val && val >= length || msg ]
    }
    // maximum number value
    else if (maxNumb.length === 2 && maxNumb[0] === '') {
      length = parseInt(maxNumb[1])
      msg = _msg ? _msg : `Maximum value is ${length}`
      res = [ val => val <= length || msg ]
    }
    // minumum & maximum character
    else if (minMaxChar.length === 2 && minMaxChar[0] === '') {
      mxVal = minMaxChar[1].split('-')
      if (mxVal.length === 2) {
        minVal = parseInt(mxVal[0])
        maxVal = parseInt(mxVal[1])
        res = [
          val => !!val && val.length >= minVal || _msg ? _msg : `Min ${minVal} characters`,
          val => !!val && val.length <= maxVal || _msg ? _msg : `Max ${maxVal} characters`
        ]
      }
    }
    // minumum & maximum number
    else if (minMaxNumb.length === 2 && minMaxNumb[0] === '') {
      mxVal = minMaxNumb[1].split('-')
      if (mxVal.length === 2) {
        minVal = parseInt(mxVal[0])
        maxVal = parseInt(mxVal[1])
        res = [
          val => !!val && val >= minVal || _msg ? _msg : `Min value is ${minVal}`,
          val => !!val && val <= maxVal || _msg ? _msg : `Max value is ${maxVal}`
        ]
      }
    }
    // required
    else {
      msg = _msg ? _msg : `Field is required`
      res = [ val => !!val && val !== '' && val !== ' ' && val !== '  ' && val !== '   '|| msg ]
    }

    if (_raw) return res[0]
    return res
  },

  // Page utils
  actionLinkPage (Meta, target, params = null, state = null, queryParams = null, customPath = null) {
    let mode = 'add'

    // converting to real object (remove Proxy) to prevent warning vue-router
    params = Helper.unReactive(params)
    state = Helper.unReactive(state)

    if (params && params.id) mode = target === 'form' ? 'edit' : 'detail'
    const res = {
      name: customPath ? customPath : `${Meta.module}-${mode}`,
      params: {...params},
      state: {...state},
      query: {...queryParams},
    }
    return res
  },

  _403 () {
    let router = useRouter()
    router.push({name: '403'})
  },

  _404 () {
    let router = useRouter()
    router.push({name: '404'})
  },

  backBtn () {
    return '<i class="q-icon notranslate material-icons" style="font-size: 20px;" aria-hidden="true" role="presentation">arrow_back</i>'
  },

  // Accessor
  urlParams (_route, attr, strict = false) {
    let route = _route.params
    let res = null
    if (route[attr] !== undefined) {
      if (route[attr]) res = route[attr]
      else res = strict ? null : true
    }
    return res
  },

  queryParams (_route, attr) {
    let route = _route.query
    let res = null
    if (route[attr] !== undefined) {
      if (route[attr]) res = route[attr]
      else res = true
    }
    return res
  },

  keyLabelDisplay (data, key) {
    let res = null
    if (data[key]) res = data[key]
    return res
  }
}