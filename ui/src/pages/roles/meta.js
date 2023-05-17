import { Handler } from './../../services/Handler'

const Meta = {
  name: 'Roles',
  module: 'roles',
  moduleName: null,
  keyLabel: 'id', // properties for getter value of display subtitle in detail
  searchBy: ['name'], // define the specific column for global search
  withRelation: ['RoleGroup'], // including specific relation data when fetching 
  topBarMenu: [],
  permission: null,
  model: {
    id: null,
    role_group_id: null,
    name: null,
    slug: null,
    dataModel: null,

  },
  columns: [
    { name: 'action', label: '#', align: 'left', width: '20px', searchable: false },
    { name: 'role_group_id', label: 'Role Group', field: 'role_group', search: 'RoleGroup.id', align: 'left', sort: 'asc', format: val => val ? val.name : '-' },
    { name: 'name', label: 'Name', field: 'name', search: 'name', align: 'left', sort: 'asc' },
    { name: 'slug', label: 'Slug', field: 'slug', search: 'slug', align: 'left', sort: 'asc' },
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
