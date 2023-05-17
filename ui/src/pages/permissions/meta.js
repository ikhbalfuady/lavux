import { Handler } from './../../services/Handler'

const Meta = {
  name: 'Permissions',
  module: 'permissions',
  moduleName: null,
  keyLabel: 'name', // properties for getter value of display subtitle in detail
  searchBy: ['module','name'], // define the specific column for global search
  // withRelation will overide relation mapping in API, if you want to default follow API setting, just set [] 
  withRelation: [], // including specific relation data when fetching 
  topBarMenu: [],
  permission: null,
  model: {
    id: null,
    module: null,
    name: null,

  },
  columns: [
    { name: 'action', label: '#', align: 'left', width: '20px', searchable: false },
    { name: 'module', label: 'Module', field: 'module', search: 'module', align: 'left', sort: 'asc' },
    { name: 'name', label: 'Name', field: 'name', search: 'name', align: 'left', sort: 'asc' },
    { name: 'log_data', label: 'Log', field: 'log_data', searchable: false },
  ]
}

Meta.moduleName = Handler.makeModuleName(Meta.module)

Meta.permission = {
  browse: Handler.permissionPage(`${Meta.module} browse`),
  create: Handler.permissionPage(`${Meta.module} create`),
  read: Handler.permissionPage(`${Meta.module} read`),
  update: Handler.permissionPage(`${Meta.module} update`),
  delete: Handler.permissionPage(`${Meta.module} delete`),
  restore: Handler.permissionPage(`${Meta.module} restore`),
}

// uncomment this if you want to allow all permission / disable permission handler feature
// Meta.permission = Handler.makeAllPermissionAllowed(Meta.permission)

export default Meta
