<template>
  <div class="row">
    
    <!-- Header -->
    <lv-header-page class="bg-white" split>
      <template v-slot:left>
        <span class="page-title animated zoomIn">Module Generator</span>
      </template>
      <template v-slot:right>
        <lv-btn soft icon="add" label="Add New Scope" labelVisibility  @click="addScope()"   />
      </template>
    </lv-header-page>

    <!-- Tutorial -->
    <div class="col-12 q-pa-md">
      <lv-banner>
        <span class="text-h5 text-bold">Welcome to <span class="text-dark">Lavux</span> module Generator.</span>
        <p class="text-dark">The data you see here is taken directly from local data which is located at : 
          <code class="code-block">\generator\scopes</code>
        </p>
        <p>
          To generate your module or scope module you can run with this command : <br>
          Generate All Scope :<code class="code-block text-dark">$ php artisan lavux-generate</code> <br>
          Generate Specific Scope : <code class="code-block text-dark">$ php artisan lavux-generate --s=scope_name </code> <br>
          <br>
          <span class="text-bold text-red-9">* The Controller only for API, if you need for web you must create manual.</span>
        </p>
        <p>
          <b>For Deploy the Apps : </b><br><br>
          1. Run <code class="code-block text-dark">$ quasar build</code> inside folder /ui, wait till finish.<br> 
          2. Click <lv-btn flat size="sm" label="Generate Route UI" @click="downloadRoute"/> , copy & paste the file inside "/routes" , make sure the name is "ui_route.json"<br> 
          3. Run <code class="code-block text-dark">$ php artisan lavux-deploy</code> in root <br> 
        </p>
      </lv-banner>
    </div>

    <div class="row col-12 q-pa-sm">
      <div class="col-12 col-sm-4 q-pa-sm" v-for="(scope, i) in list" :key="`md-${i}`" >
        <q-card class="animated zoomIn bg-primary text-center link" @click="editScope(scope)">
          <q-avatar rounded size="100px" font-size="82px" color="primary" text-color="white" >
            <b>{{ $Helper.getFirstChar(scope.name) }}</b>
          </q-avatar>

          <q-card-section class="q-pa-sm bg-white">
            <div class="text-h6 text-bold ">{{ scope.name }}</div>
            <div class="text-subtitle2 text-grey-7">{{ scope.modules ? scope.modules.length : 0 }} Modules</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-dialog v-model="modal.show" @hide="modal.show = false" persistent transition-show="jump-up" transition-hide="jump-down">
      <q-card v-if="modal.data" style="min-width: 90vw">
        <q-card-section class="row items-center q-py-xs bg-grey-2">
          <div class="">
            <lv-btn v-if="modal.data.id" icon="delete" color="red" @click="removeScope(modal.data.id)"/>
            <span class="text-h6 q-pl-sm">Scope : </span>
            <input class="input-base" style="font-size: 16px;" v-model="modal.data.name"/>
          </div>
          <q-space />
          <lv-btn v-if="tab === 'module'" @click="addModule()" color="indigo" icon="apps" label="Add Module"/>
          <lv-btn v-if="tab === 'structure'" @click="addColumn()" color="purple" icon="table_chart" label="Add Column"/>
          <lv-btn v-if="!errorColumn.message" @click="commit()" color="green" icon="check" label="Commit" class="q-ml-sm"/>
          <q-btn icon="close" flat round dense v-close-popup class="q-ml-sm"/>
        </q-card-section>

        <q-card-section class="col-12 q-pa-none" v-if="tableModule.data.length">
          <q-tabs v-model="tab" dense
            class="text-grey"
            active-color="primary"
            indicator-color="primary"
            align="justify"
            narrow-indicator
          >
            <q-tab name="module" label="Module" />
            <q-tab v-if="table.data.length" name="structure" label="Structure" />
          </q-tabs>

          <q-tab-panels v-model="tab" animated>
            <q-tab-panel name="module" class="row q-pa-none">
              <lv-table class="col-12" :config="tableModule" :setting="false" hide-top height="70vh" >

                <template v-slot:col-act="props">
                  <lv-btn soft icon="delete" color="red" @click="removeModule(props.row, props.index)"/>
                  <lv-btn icon="table_chart" color="indigo" @click="toStructure(props.row)" class="q-ml-sm ">
                    <q-tooltip>View Table</q-tooltip>
                  </lv-btn>
                </template>

                <template v-slot:col-name="props">
                  <lv-input v-model="props.row.name" placeholder="use PascalCase"/>
                </template>

                <template v-slot:col-table="props">
                  {{ $Helper.makeTableName(props.row.name, '_') }}
                </template>

                <template v-slot:col-info="props">
                  {{ props.row.column.length }} columns
                </template>

              </lv-table>
            </q-tab-panel>

            <q-tab-panel name="structure" class="row q-pa-none">
              <lv-table class="col-12" :config="table" :setting="false" hide-top height="70vh" >

                <template v-slot:col-act="props">
                  <lv-btn v-if="props.row.type !== 'bigIncrements'" soft icon="delete" color="red" @click="removeColumn(props.row, props.index)"/>
                </template>

                <template v-slot:col-name="props">
                  <lv-input v-if="props.row.type !== 'bigIncrements'" v-model="props.row.name" 
                  @input="val => onInputColumn('name', props.index, val)"
                  :error="(errorColumn.index === props.index && errorColumn.message) ? true : false" 
                  :error-message="`${(errorColumn.index === props.index && errorColumn.message) ? errorColumn.message : '' }`" />
                  <b v-else ><q-icon name="key" style="font-size: 18px" class="text-orange-9 rotate-45"/> ID</b>
                </template>

                <template v-slot:col-type="props">
                  <div class="row" style="width: 150px;">
                    <lv-select v-if="props.row.type !== 'bigIncrements'" v-model="props.row.type" :options="select.type" @input="val => onInputColumn('type', props.index, val)"/>
                    <b v-else class="text-italic text-indigo" >
                      Primary, bigIncrements
                    </b>
                  </div>
                </template>

                <template v-slot:col-attributes="props">
                  <template v-if="props.row.type !== 'morph'">
                    <lv-select v-if="props.row.type !== 'bigIncrements'" v-model="props.row.attributes" :options="select.attributes" multiple />
                    <b v-else class="text-italic text-indigo" >INDEX</b>
                  </template>
                </template>

                <template v-slot:col-specs="props" >
                  <div class="row" v-if="props.row.type !== 'morph'">
                    <template v-if="props.row.type !== 'bigIncrements'">
                      <q-select v-if="props.row.type === 'enum'" label="Enum List" class="col-12" v-model="props.row.enum_list" 
                        filled dense use-input use-chips multiple hide-dropdown-icon input-debounce="0"
                        new-value-mode="add-unique" style="width: 250px"
                      />

                      <lv-input col="6" v-if="props.row.type === 'integer' || props.row.type === 'double'" 
                        mode="number" label="Length" v-model="props.row.length"
                      />
                      <lv-input col="6" v-if="props.row.type === 'double'" 
                        mode="number" label="Length 2" v-model="props.row.length2"
                      />

                    </template>
                  </div>
                  <span v-else class="text-indigo-9">
                    will be automatically created column <br> {{ props.row.name }}_type & {{ props.row.name }}_id
                  </span>
                </template>

                <template v-slot:col-default="props" >
                  <template v-if="props.row.type !== 'bigIncrements' && props.row.type !== 'morph'">
                    <lv-toggle v-if="props.row.type === 'boolean'" v-model="props.row.default" />
                    <lv-input v-else :mode="getInputType(props.row.type)" v-model="props.row.default" :decimal="props.row.length2 ? props.row.length2 : 0"/>
                  </template>
                </template>

                <template v-slot:col-belongsTo="props">
                  <template v-if="props.row.type !== 'morph'">
                    <lv-displayer dense v-if="props.row.belongsTo" :data="[props.row.belongsTo]"
                      @click="openModalRelation('belongsTo', props.index, props.row.name)" 
                    />
                    <lv-btn v-if="!props.row.belongsTo && props.row.type !== 'bigIncrements'" 
                      icon="add" color="primary" @click="openModalRelation('belongsTo', props.index, props.row.name)"
                    />
                  </template>
                </template>

                <template v-slot:col-hasMany="props">
                  <template v-if="props.row.type !== 'morph'">
                    <lv-displayer dense v-if="props.row.hasMany" :data="[props.row.hasMany]" @click="openModalRelation('hasMany', props.index, props.row.name)" />
                    <lv-btn v-if="!props.row.hasMany" 
                      icon="add" color="primary" @click="openModalRelation('hasMany', props.index, props.row.name)"
                    />
                  </template>
                </template>

              </lv-table>
            </q-tab-panel>
          </q-tab-panels>
          
        </q-card-section>

        <q-card-section class="col-12 q-pa-lg text-center" v-else>
          <span class="text-h5 text-bold">Click "Add Module" to define new module of this scope </span>
          <p class="q-pt-md">You can change this scope name in top left field, <br>
            To prevent miss data, please using "snake_case" for scope name.</p>
        </q-card-section>

      </q-card>
    </q-dialog>

    <q-dialog v-model="modalRelation.show" @hide="modalRelation.show = false" transition-show="jump-up" transition-hide="jump-down">
      <q-card v-if="modalRelation.data">
        <q-card-section class="row items-center q-py-xs bg-grey-2">
          <div class="text-h6">{{ modalRelation.type }} : {{ modalRelation.data.name }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup class="q-ml-sm"/>
        </q-card-section>
      
        <q-card-section class="col-12 q-gutten-md">
          <q-form @submit="saveRelation()" class="col-12 row animated fadeIn" >
            <lv-input class="q-mb-sm" label="Name*" v-model="modalRelation.data.name" placeholder="Use PascalCase" required/>

            <lv-input class="q-mb-sm" v-if="modalRelation.type === 'belongsTo' || modalRelation.type === 'hasMany'" 
              label="Model*" v-model="modalRelation.data.model" placeholder="Use PascalCase" required
              />

            <lv-input class="q-mb-sm" v-if="modalRelation.type === 'belongsTo' || modalRelation.type === 'hasMany'" readonly
              label="Foreign Key*" v-model="modalRelation.data.foreign_key" hint="Auto fill & follow this column name"
            />

            <lv-input class="q-mb-sm" v-if="modalRelation.type === 'belongsTo'" label="Owner Key" v-model="modalRelation.data.owner_key"/>
            <lv-input class="q-mb-sm" v-if="modalRelation.type === 'hasMany'" label="Local Key" v-model="modalRelation.data.local_key"/>

            <q-card-actions align="right" class="col-12 text-primary">
              <lv-btn label="Cancel" v-close-popup/>
              <lv-btn label="Save" color="green" type="submit"/>
            </q-card-actions>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    
  </div>
</template>

<script>
import { ref, reactive, onBeforeMount, computed, watch, defineComponent } from 'vue'
import { Dialog } from 'quasar';
import useServices from './../composables/Services'
import { useRouter, useRoute } from 'vue-router'
import { BuildRoute } from './../services/BuildRoute'

export default defineComponent({
  name: "Generator",
  setup () {
    const { Config, Handler, Helper, SetMetaPage, Api} = useServices()
    const router = useRouter()

    const baseColumn = {
      name: 'your_column',
      type: 'string',
      length: null,
      length2: null,
      default: null,
      belongsTo: null,
      hasMany: null,
      enum_list: [],
      attributes: [],
    }

    const errorColumn = reactive({
      index: null,
      message: null,
    })
    const loading = ref(false)
    const list = ref([]);
    const tab = ref('module')
    const currentModule = ref(null) // di set ketika toStructure di trigger
    const modal = reactive(Handler.modalConfig())
    const modalRelation = reactive(Handler.modalConfig())

    let tableModule = reactive({
      ...Handler.table([
        { name: 'act', label: '_', style: 'width: 40px', searchable: false },
        { name: 'name', label: 'name', style: 'width: 190px', searchable: false },
        { name: 'table', label: 'table', style: 'width: 190px', searchable: false },
        { name: 'info', label: 'info', style: 'width: 190px', searchable: false },
      ]), // init default columns
      action: false
    })

    let table = reactive({
      ...Handler.table([
        { name: 'act', label: '_', style: 'width: 40px', searchable: false },
        { name: 'name', label: 'name', style: 'width: 290px', searchable: false },
        { name: 'type', label: 'type', style: 'width: 190px', searchable: false },
        { name: 'attributes', label: 'attributes', style: 'width: 190px', searchable: false },
        { name: 'specs', label: 'specs', style: 'width: 120px', searchable: false }, // length, enum_list
        { name: 'default', label: 'default', style: 'width: 190px', searchable: false },
        { name: 'belongsTo', label: 'belongsTo', style: 'width: 190px', searchable: false },
        { name: 'hasMany', label: 'hasMany', style: 'width: 190px', searchable: false },
      ]), // init default columns
      action: false
    })

    const select = reactive({
      type: Handler.toObjectSelect(['bigIncrements', 'string', 'unsignedBigInteger', 'integer', 'double', 'enum', 'boolean', 'date', 'dateTime', 'text', 'longtext', 'json', 'morph' ]),
      attributes: Handler.toObjectSelect(['index', 'nullable', 'fulltext'])
    })

    onBeforeMount(() => {
      Handler.topMenu([{name: 'Refresh', event: onRefresh }])
      SetMetaPage(`Module Generator`)
      onRefresh()
    })

    watch(tableModule, (nv, ov) => {
      updateModuleScope()
    })

    watch(table, (nv, ov) => {
      updateModuleScope()
    })

    function onInputColumn (column, i, val) {
      let error  = 0
      table.data.some((col, idx) => {
        
        // column name validation
        if (column === 'name' ) {
          if (col.name === val && idx !== i) {
            error += 1
            errorColumn.index = i
            errorColumn.message = 'Name already exist on this table!'
            return true
          }
        }

        // column type validation
        if (column === 'type' ) {
          table.data[i].default = null // reset default when change type
          if (col.type === 'bigIncrements' && col.name !== 'id') {
            error += 1
            errorColumn.index = i
            errorColumn.message = 'Cannot set multiple bigIncrement!'
            table.data[i].type = 'string'
            return true
          }
        }

      })

      if (error === 0) {
        errorColumn.index = null
        errorColumn.message = null
      }
    }

    function onRefresh () {
      getData()
    }

    function getData() {
      loading.value = true
      Api.get('app/scopes', (status, data, message, full) => {
        loading.value = false
        if (status === 200 && data) {
          list.value = data
        }
      })
    }

    function deleteScope(id) {
      Helper.loadingOverlay(true, 'Deleting..')
      Api.delete(`app/scopes/${id}`, (status, data, message, full) => {
        Helper.loadingOverlay(false)
        if (status === 200 && data) {
          modal.show = false
          Helper.showSuccess('Succesfully', message)
          onRefresh()
        }
      })
    }
    
    function commit () {
      Helper.loadingOverlay(true, 'Comitting..')
      let endpoint = `app/scopes`
      if (modal.data.id) endpoint = `app/scopes/${modal.data.id}`

      Api.put(endpoint, modal.data, (status, data, message) => {
        Helper.loadingOverlay(false)
        if (status === 200) {
          modal.show = false
          Helper.showSuccess('Succesfully', message)
          onRefresh()
        }
      })
    }

    function addScope () {
      modal.show = true
      tab.value = 'module'
      modal.data = {
        id: null,
        name: 'using_snake_case',
        modules: [],
      }

      tableModule.data = []
    }

    function editScope (data) {
      tab.value = 'module'
      modal.show = true
      modal.data = data
      tableModule.data = data.modules
      table.data = []
    }

    function removeScope (id) {
      let message = `Are you sure want to delete scope "${id}"?`
      message += `<br> <span class="text-red">be careful, data can't be recovered once deleted</span>`
      Dialog.create({
        title: 'Delete Scope',
        message,
        ok: 'Delete Now',
        cancel: 'Cancel',
        html: true,
      }).onOk(() => {
        deleteScope(id)
      })
    }

    function addModule () {
      modal.show = true
      let colId = {}

      Object.assign(colId, baseColumn)

      colId.name = 'id'
      colId.type = 'bigIncrements'
      colId.attributes = ['index']

      let column = []
      column.push(colId)

      tableModule.data.push({ name: 'YourModule', column })
    }

    function removeModule (data, i) {
      const message = `Are you sure want to delete module "${data ? data.name : ''}"?`
      Dialog.create({
        title: 'Delete Module',
        message,
        ok: 'Delete Now',
        cancel: 'Cancel',
      }).onOk(() => {
        tableModule.data.splice(i, 1)
      })
    }
    
    function addColumn () {
      modal.show = true
      let column = {}
      Object.assign(column, baseColumn)
      table.data.push(column)
    }

    function removeColumn (data, i) {
      const message = `Are you sure want to delete column "${data ? data.name : ''}"?`
      Dialog.create({
        title: 'Delete Column',
        message,
        ok: 'Delete Now',
        cancel: 'Cancel',
      }).onOk(() => {
        table.data.splice(i, 1)
      })
    }

    function toStructure (mdl) {
      tab.value = 'structure'
      table.data = mdl.column
      currentModule.value = mdl
    }

    function updateModuleScope () {

      if (!modal.data) return false

      modal.data.modules = tableModule.data // update latest data 
      const mdl = currentModule.value
      if (!mdl) return false

      // updating latest modules after edit structure
      if (modal.data && modal.data.modules.length) {
        let fix = []
        modal.data.modules.map((mod, i) => {
          if (mod.name === mdl.name) fix.push(mdl)
          else fix.push(mod)
        })
        modal.data.modules = fix
      }
    }

    function getInputType (type) {
      let res = 'text'
      // if (type === 'integer' || type === 'unsignedBigInteger') res = 'number'
      if (type === 'integer') res = 'number'
      if (type === 'double') res = 'currency'
      return res
    }

    function openModalRelation (type, index, fk = null) {
      let data = table.data.length ? table.data[index] : null
      
      const model = initModelRelation(type, data)
      model.foreign_key = fk

      modalRelation.data = model
      modalRelation.index = index
      modalRelation.type = type
      modalRelation.show = true
    }

    function initModelRelation (type, data = null) {
      let res = {
        type: 'belongsTo',
        name: null, // *
        model: null, // *
        foreign_key: null, // *
        owner_key: 'id', // if null set id
      }

      if (type === 'hasMany') {
        res = {
          type: 'hasMany',
          name: null, // *
          model: null, // *
          foreign_key: null, // *
          local_key: 'id', // if null set id
        }
      }

      return data[type] ? data[type] : res
    }

    function saveRelation () {
      if (modalRelation.type && modalRelation.index !== null && modalRelation.data) {
        const data = Object.assign({}, modalRelation.data) 
        const type = modalRelation.type 
        const index = modalRelation.index 
        
        // remove unused
        delete data.type
        if (type === 'belongsTo') {
          if (!data.owner_key) data.owner_key = 'id'
        }
        if (type === 'hasMany') {
          if (!data.local_key) data.owner_key = 'id'
        }

        table.data[index][type] = data
        modalRelation.show = false
      }
    }

    function downloadRoute () {
      BuildRoute.export(router.getRoutes())
    }

    return {
      tab,
      loading,
      list,
      modal,
      modalRelation,
      tableModule,
      table,
      select,
      errorColumn,
      // computed
      // methods
      onRefresh,
      onInputColumn,
      addScope,
      editScope,
      removeScope,
      addModule,
      removeModule,
      addColumn,
      removeColumn,
      getInputType,
      toStructure,
      openModalRelation,
      saveRelation,
      commit,
      downloadRoute,
    }

  }
});
</script>
