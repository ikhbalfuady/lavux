import { Handler } from './../../services/Handler'

const Meta = {
  name: 'Users',
  module: 'users',
  moduleName: null,
  keyLabel: 'name', // properties for getter value of display subtitle in detail
  searchBy: ['name', 'email'], // define the specific column for global search
  withRelation: ['Role'], // including specific relation data when fetching 
  topBarMenu: [],
  permission: null,
  model: {
    id: null,
    name: null,
    username: null,
    password: null,
    email: null,
    email_verified_at: null,
    remember_token: null,
    picture: null,
    is_ban: false

  },
  columns: [
    { name: 'action', label: '#', align: 'left', width: '20px', searchable: false },
    { name: 'name', label: 'Name', field: 'name', search: 'name', align: 'left', sort: 'asc'},
    { name: 'email', label: 'Email', field: 'email', search: 'email', align: 'left', sort: 'asc' },
    { name: 'username', label: 'username', field: 'username', search: 'username', align: 'left', sort: 'asc' },
    { name: 'email_verified_at', label: 'Email Verified At', field: 'email_verified_at', search: 'email_verified_at', align: 'left'},
    { name: 'role_id', label: 'Role', field: 'role', search: 'Role.name', align: 'left', sort: 'asc', format: val => val ? val.name : '' },
    { name: 'log_data', label: 'Log', field: 'log_data', align: 'left', searchable: false },

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
