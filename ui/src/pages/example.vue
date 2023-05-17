<template>
  <div class="row">
    <div class="q-pa-md col-12 text-bold text-primary text-h4 q-mb-md">Example Components</div>

    <!-- Container -->
    <lv-container id="notification" class="col-12" height="420px">
      <div class="col-12 q-mb-sm text-dark text-h5 text-bold">
       # <span>Notification</span>  <span class="code">API</span> 
      </div>

      <SendNotification class="col-12 q-pa-md"/>
    </lv-container>


    <!-- Input -->
    <lv-container id="input" class="col-12" height="850px">
      <div class="col-12 q-mb-sm text-dark text-h5 text-bold">
       # <span>Input</span>  <span class="code">&lt;lv-input&gt;</span> 
      </div>

      <div class="col-12 q-mb-sm text-primary text-h6 text-bold">
       Mode : <small class="text-normal text-dark">text (default), number, currency, date, datetime, daterange</small>
      </div>

      <lv-input class="col-sm-4" label="Text" type="email" v-model="inputModel.string" />
      <lv-input class="col-sm-4" mode="number" label="Currency" v-model="inputModel.integer"/>
      <lv-input class="col-sm-4" mode="currency" :decimal="2" label="Currency with Decimal" v-model="inputModel.decimal">
        <template v-slot:append>
          <q-btn label="USD" color="primary" dense unelevated flat class="text-bold" size="sm" />
        </template>
      </lv-input>
      <lv-input class="col-sm-4" mode="date" label="Date" v-model="inputModel.date"/>
      <lv-input class="col-sm-4" mode="datetime" label="Date Time" v-model="inputModel.date_time"/>
      <lv-input class="col-sm-4" mode="daterange" label="Date Range" v-model="inputModel.date_range"/>

      <q-separator class="col-12 q-my-md"/>


      <div  class="col-12 q-mb-sm text-primary text-h6 text-bold">
       Variant
      </div>
      
      <lv-input class="col-sm-6" top-label label="Top Label" v-model="inputModel.string"/>
      <lv-input class="col-sm-6" label="Top Label Slot" v-model="inputModel.string">
        <template v-slot:topLabel>
          <b class="text-primary">Top label using slot</b>
        </template>
      </lv-input>

      <lv-input class="col-sm-3" label="Flat Mode" v-model="inputModel.string" flat placeholder="Placeholder & flat mode"/>
      <lv-input class="col-sm-3" label="type=password" type="password" v-model="inputModel.string"/>
      <lv-input class="col-sm-3" label="type=number" type="number" v-model="inputModel.integer"/>
      <lv-input class="col-sm-3" label="Stack Label" stack-label v-model="inputModel.string" placeholder="With placeholder..."/>
      <lv-input class="col-sm-4" label="With Rules" v-model="inputModel.string" placeholder="error triggered when value not inputed" :rules="$Handler.rules('required')"/>
      <lv-input class="col-sm-4" label="Hint Slot" v-model="inputModel.string" @input="e => onInput('@input', e)" >
        <template v-slot:hint>Include event @input</template>
      </lv-input>
      <lv-input class="col-sm-4" label="Clearable" v-model="inputModel.string" clearable/>
      <lv-input class="col-sm-4" label="Error state" v-model="inputModel.string" error error-message="Set Error manualy"/>
      <lv-input class="col-sm-4" label="Readonly" v-model="inputModel.string" readonly/>
      <lv-input class="col-sm-4" label="Rules with props 'required'" v-model="inputModel.string" required
        hint="when using required, rules required auto set" clearable
      />
      
      <q-separator class="col-12 q-my-md"/>

      <echo class="col-12 col-sm-6" label="#Model" :data="inputModel" />
      <echo class="col-12 col-sm-6" label="#Event : @input" :data="onEvent" />

    </lv-container>

    <!-- Select -->
    <lv-container id="select" class="col-12" height="1050px">
      <div class="col-12 q-mb-sm text-dark text-h5 text-bold">
       # <span>Select</span>  <span class="code">&lt;lv-select&gt;</span> 
      </div>

      <div class="col-12 q-mb-sm text-primary text-h6 text-bold">
       Mode : <small class="text-normal text-dark">options (static / dynamic), url (ajax) </small>
      </div>
 
      <lv-select class="col-sm-12" top-label label="Static with top-label" v-model="selectModel.select" :options="select.countries"/>
      <lv-select class="col-sm-4" label="Static Autocomplete" v-model="selectModel.select" :options="select.countries" searchable clearable/>
      <lv-select class="col-sm-4" label="Event : @input" @input="e => onInput('@input', e)" v-model="selectModel.select" :options="select.countries" searchable clearable/>
      <lv-select class="col-sm-4" label="Event : @selected" v-model="selectModel.select" :options="select.countries" 
        @selected="e => onInput('@selected', e)"  searchable clearable
        hint="Result of selected is raw value of options object"
      />
      <lv-select class="col-sm-4" label="Custom value & label" v-model="selectModel.select" 
        :options="select.countries2" option-value="value" option-label="label"
        @selected="e => onInput('@selected', e)"  searchable clearable
        hint="using rules" :rules="$Handler.rules('required')"
      />
      <lv-select class="col-sm-4" label="Ajax" v-model="selectModel.select_ajax" clearable
        url="users" :search-by="['name', 'email']"
        hint="Result of selected is raw value of options object"
      />

     <lv-select class="col-sm-4" label="Ajax with auto fetch default" v-model="selectModel.select_ajax_auto"
        url="users" :search-by="['name', 'email']"
        hint="Automation fetch needed data for default"
      />

      <lv-select class="col-sm-6" label="Ajax with manual set default" v-model="selectModel.select_ajax_default"
        url="users" :search-by="['name', 'email']" :default-data="defaultData"
        hint="Auto set to option from default data"
      />

      <lv-select class="col-sm-6" label="Custom option label" v-model="selectModel.select_ajax_default"
        url="users" :search-by="['name', 'email']" :default-data="defaultData"
        :option-label="opt => opt ? `${opt.name} || <small>${opt.email}</small>` : '...'"
        hint="use || for separator label to sanitize selected"
      />

      <lv-select class="col-sm-4" label="Ajax with Additional query params " v-model="selectModel.select_ajax_default"
        url="users" :search-by="['name', 'email']" :default-data="defaultData"
        :url-params="[{key: 'score', value: 'gte@1'}]"
        hint="using array object {key, value} in props url-params"
      />

      <lv-select class="col-sm-4" label="Ajax with Additional query params " v-model="selectModel.select_ajax_default"
        url="users" :search-by="['name', 'email']" :default-data="defaultData"
        :url-params="`score=gte@1`"
        hint="using string in props url-params"
      />

      <lv-select class="col-sm-4" readonly label="Readonly" v-model="selectModel.selected" :options="select.countries"/>

      <lv-select class="col-sm-12" label="Slots" v-model="selectModel.select_ajax_default"
        url="users" :search-by="['name', 'email']" :default-data="defaultData"
        :option-label="opt => opt ? `${opt.name} || <small>${opt.email}</small>` : '...'"
        hint="hint props"
      >
        <template v-slot:prepend>
          <q-btn label="Pr" color="primary" round dense unelevated size="xs" >
            <q-tooltip>v-slot:prepend</q-tooltip>
          </q-btn>
        </template>
        <template v-slot:append>
          <q-btn label="Ap" color="purple" round dense unelevated size="xs" >
            <q-tooltip>v-slot:append</q-tooltip>
          </q-btn>
        </template>
        <template v-slot:after>
          <q-btn label="Af" color="teal" round dense unelevated size="xs" >
            <q-tooltip>v-slot:after</q-tooltip>
          </q-btn>
        </template>
        <template v-slot:before>
          <q-btn label="Be" color="orange" round dense unelevated size="xs" >
            <q-tooltip>v-slot:before</q-tooltip>
          </q-btn>
        </template>
      </lv-select> 

      <q-separator class="col-12 q-my-md"/>

      <div  class="col-12 q-mb-sm text-primary text-h6 text-bold">
       Variant <small>Same as input</small>
      </div>

      <q-separator class="col-12 q-my-md"/>

      <echo class="col-12 col-sm-6" label="#Model" :data="selectModel" />
      <echo class="col-12 col-sm-6" label="#Event : @input @selected" :data="onEvent" />

    </lv-container>

     <!-- Toggle -->
     <lv-container id="toggle" class="col-12" height="440px">
      <div class="col-12 q-mb-sm text-dark text-h5 text-bold">
       # <span>Toggle</span>  <span class="code">&lt;lv-toggle&gt;</span> 
      </div>

      <lv-toggle class="col-sm-12" top-label label="Top Label" v-model="toggle" @input="e => onInput('@input', e)" />
      <lv-toggle class="col-sm-3" left-label label="Left Label" v-model="toggle" @input="e => onInput('@input', e)" />
      <lv-toggle class="col-sm-3" label="Basic" v-model="toggle" @input="e => onInput('@input', e)" />
      <lv-toggle class="col-sm-3" flat label="Flat witch icon true / false" 
        checked-icon="check" unchecked-icon="close"  color="purple"
        v-model="toggle" @input="e => onInput('@input', e)"
      />
      <lv-toggle class="col-sm-3" label="Readonly" readonly v-model="toggle"/>
 
      <echo class="col-12 col-sm-6" label="#Model" :data="toggle" />
      <echo class="col-12 col-sm-6" label="#Event : @input" :data="onEvent" />
    </lv-container>

    <!-- Checkbox -->
    <lv-container id="checkbox" class="col-12" height="440px">
      <div class="col-12 q-mb-sm text-dark text-h5 text-bold">
       # <span>Checkbox</span>  <span class="code">&lt;lv-checkbox&gt;</span> 
      </div>

      <lv-checkbox class="col-sm-12" top-label label="Top Label" v-model="checkbox" @input="e => onInput('@input', e)" />
      <lv-checkbox class="col-sm-3" left-label label="Left Label" v-model="checkbox" @input="e => onInput('@input', e)" />
      <lv-checkbox class="col-sm-3" label="Basic with color" color="red" v-model="checkbox" @input="e => onInput('@input', e)" />
      <lv-checkbox class="col-sm-3" flat label="Flat witch icon true / false" 
        checked-icon="check" unchecked-icon="close"  color="purple"
        v-model="checkbox" @input="e => onInput('@input', e)"
      />
      <lv-checkbox class="col-sm-3" label="Readonly" readonly v-model="checkbox"/>
 
      <echo class="col-12 col-sm-6" label="#Model" :data="checkbox" />
      <echo class="col-12 col-sm-6" label="#Event : @input" :data="onEvent" />
    </lv-container>

    <!-- Textarea -->
    <lv-container id="container" class="col-12" height="800px">
      <div class="col-12 q-mb-sm text-dark text-h5 text-bold">
       # <span>Textarea</span>  <span class="code">&lt;lv-texarea&gt;</span> 
      </div>

      <p class="text-red-8 text-bold">* For this component, event <i class="text-dark">@input</i> not available, use <i class="text-dark">@update</i>, that similar like  <i class="text-dark">@input</i></p>

      <lv-textarea class="col-sm-12" label="Top Label" top-label rows="1" v-model="inputModel.textarea2" @update="e => onInput('@update', e)"/>
      <lv-textarea class="col-sm-6" label="Custom Rows" rows="1" v-model="inputModel.textarea2" @update="e => onInput('@update', e)"/>
      <lv-textarea class="col-sm-6" label="Auto Grows" autogrow v-model="inputModel.textarea2" @update="e => onInput('@update', e)"/>
      <lv-textarea class="col-sm-6" label="Basic" v-model="inputModel.textarea" @update="e => onInput('@update', e)"/>
      <lv-textarea class="col-sm-6" label="With Placeholder" placeholder="this is placeholder" v-model="inputModel.textarea2" @update="e => onInput('@update', e)"/>

      <echo class="col-12 col-sm-6" label="#Model" :data="inputModel" />
      <echo class="col-12 col-sm-6" label="#Event : @update" :data="onEvent" />
    </lv-container>

    <!-- Container -->
    <lv-container id="container" class="col-12" height="200px">
      <div class="col-12 q-mb-sm text-dark text-h5 text-bold">
       # <span>Container</span>  <span class="code">&lt;lv-container&gt;</span> 
      </div>

      <textarea class="col-12 codeplace" rows="3"><!--Card for display default content-->
        <lv-container height="320px">
          default has have class : bg-white, shadow & row
          must define height to controll scroll
        </lv-container>
      </textarea>
    </lv-container>

    <!-- common -->
    <lv-container id="common" class="col-12" height="1500px">
      <div class="col-12 q-mb-sm text-dark text-h5 text-bold">
       # <span>Common</span> 
      </div>

      <div class="col-12 "><span class="code text-bold ">&lt;lv-btn&gt;</span> <br>
      </div>
      <div class="q-gutter-sm q-mb-md">
        <lv-btn label="default" />
        <lv-btn label="color" color="primary" />
        <lv-btn label="icon" color="orange" soft icon="reply"/>
        <lv-btn label="color soft" color="purple" soft/>
        <lv-btn label="icon soft" color="green" soft icon="check"/>
        <lv-btn label="auto hide label on xs / mobile use : labelVisibility" color="teal" icon="check" labelVisibility/>
        <lv-btn label="redirect using :to" color="orange-8" :to="{ name: 'home'}" />
        <lv-btn label="redirect using href" color="indigo-8" href="/example" />
      </div>

      <q-separator class="col-12 q-my-lg" style="height: 1px;"/>

      <div class="col-12 "><span class="code text-bold ">&lt;lv-banner&gt;</span> <br>
      <i class="text-indigo-8">* when using <b>dismiss slot</b> , content : col-11 & dismiss slot : col-1</i>
      </div>

      <lv-banner class="col-12 q-mb-md">
        ⚠️ Banner default 
      </lv-banner>

      <lv-banner class="col-12 q-mb-md" solid>
        ⚠️ Banner solid default 
      </lv-banner>

      <lv-banner class="col-12 q-mb-md" dense>
        ❤ Banner dense
      </lv-banner>

      <lv-banner class="col-12 q-mb-md" color="orange" solid>
        👽 <b>Banner solid custom color</b>
      </lv-banner>

      <lv-banner class="col-12 q-mb-md" color="purple" text-color="dark" dismissable>
       <b>Custom color & text color with dismissable</b>
      </lv-banner>

      <lv-banner class="col-12 q-mb-md" color="red-3" text-color="red-9" solid>
       <b>Solid & custom color & text color with custom dismissable</b>
       <template v-slot:dismiss>
        <q-btn unelevated label="Oke" color="green" size="sm"/>
       </template>
      </lv-banner>

      <q-separator class="col-12 q-my-lg" style="height: 1px;"/>

      <div class="col-12 "><span class="code text-bold ">&lt;lv-displayer&gt;</span> <br>
      <i class="text-indigo-8">* auto formater display</i>
      </div>

      <div class="col-4 border q-pa-sm">
        <span class="text-primary">Decimal</span> <br>
        <lv-displayer :data="4.25"/>
      </div>

      <div class="col-4 border q-pa-sm">
        <span class="text-primary">Boolean</span> <br>
        <lv-displayer class="col-12" :data="false"/>
      </div>

      <div class="col-4 border q-pa-sm">
        <span class="text-primary">Array</span> <br>
        <lv-displayer class="col-12" :data="[1,2,3, 'sangu', false]"/>
      </div>

      <div class="col-4 border q-pa-sm">
        <span class="text-primary">ArrayObject</span> <br>
        <small>Focus here to see detail</small> <br>
        <lv-displayer class="col-12" :data="[{}, {code: 'JC', name: 'John'}, {code: 'SH', name: 'Shisk'}]"/>
      </div>

      <div class="col-4 border q-pa-sm">
        <span class="text-primary">Object</span> <br>
        <lv-displayer class="col-12" :data="{id: 1, name: 'John', birthdate: '2022-01-10'}"/>
      </div>

      <div class="col-4 border q-pa-sm">
        <span class="text-primary">Date Time</span> :
        <lv-displayer class="col-12" data="2002-01-10 10:10:10"/> <br>
        <span class="text-primary">Date</span> :
        <lv-displayer class="col-12" data="2002-01-10"/> <br>
        <span class="text-primary"> Time</span> :
        <lv-displayer class="col-12" data="10:30"/>
      </div>

      <q-separator class="col-12 q-my-lg" style="height: 1px;"/>

      <div class="col-12 "><span class="code text-bold ">&lt;lv-view-item&gt;</span> <br>
      <i class="text-indigo-8">* can auto formater value display using optimize-display, the display using &lt;lv-displayer&gt;</i>
      </div>
      <p># Basic</p>
      <lv-view-item label="label" display="display"/>
      <lv-view-item label="label"><i>Display using slot</i></lv-view-item>

      <p># With "optimize-display"</p>
      <lv-view-item optimize-display label="string" display="text"/>
      <lv-view-item optimize-display label="float" :display="4.25"/>
      <lv-view-item optimize-display label="date" display="2002-01-10"/>
      <lv-view-item optimize-display label="time" display="10:30"/>


    </lv-container>

    <q-page-sticky position="top-right" :offset="[35, 18]">
      <q-fab color="purple" text-color="white" icon="help" direction="down" padding="xs">
        <q-fab-action color="purple" text-color="white" @click="scrollTo('#notification')" dense label="notification" padding="xs" />
        <q-fab-action color="purple" text-color="white" @click="scrollTo('#input')" dense label="input" padding="xs" />
        <q-fab-action color="purple" text-color="white" @click="scrollTo('#select')" dense label="select" padding="xs" />
        <q-fab-action color="purple" text-color="white" @click="scrollTo('#toggle')" dense label="toggle" padding="xs" />
        <q-fab-action color="purple" text-color="white" @click="scrollTo('#checkbox')" dense label="checkbox" padding="xs" />
        <q-fab-action color="purple" text-color="white" @click="scrollTo('#container')" dense label="container" padding="xs" />
        <q-fab-action color="purple" text-color="white" @click="scrollTo('#common')" dense label="common" padding="xs" />
      </q-fab>
    </q-page-sticky>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { defineComponent } from "vue";
import useServices from './../composables/Services'
import SendNotification from '../pages/accounts/send-notification.vue'
export default defineComponent({
  name: "Example",
  components: {
    SendNotification,
  },
  setup () {
    const { Config, Handler, Helper, SetMetaPage, Api} = useServices()
    
    const inputModel = ref({
      string: 'asdasd',
      integer: 124124,
      decimal: 1231.20,
      date: Helper.today(),
      date_time: Helper.today(),
      date_range: Helper.today(),
      textarea: 'content is here',
      textarea2: null,
    })

    const selectModel = ref({
      select: null,
      selected: 'id',
      select_ajax: null,
      select_ajax_auto: 10,
      select_ajax_default: 9,
    })

    const toggle = ref(false)
    const checkbox = ref(false)

    const defaultData = { id: 9, name: "Prof. Dayton Johnston", email: "wweissnat@iqbal.org" } 

    const select = reactive({
      countries: [
        { id: 'id', name: 'Indonesia' },
        { id: 'us', name: 'United States' },
        { id: 'uk', name: 'United Kingdom' },
        { id: 'my', name: 'Malaysia' },
        { id: 'sg', name: 'Singapore' },
      ],
      countries2: [
        { value: 'id', label: 'Indonesia' },
        { value: 'us', label: 'United States' },
        { value: 'uk', label: 'United Kingdom' },
        { value: 'my', label: 'Malaysia' },
        { value: 'sg', label: 'Singapore' },
      ]
    })

    const onEvent = ref(null)

    onMounted(() => {
 
    })

    function onRefresh () {

    }

    function scrollTo (selector) {
      const el = document.querySelector(selector)
      console.log(el)
      if (el) {
        const yOffset = -70;
        const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;

        window.scrollTo({ top: y, behavior: 'smooth' });
      }
    }

    function onInput (target, value) {
      onEvent.value = {[target]: value}
      console.log('onEvent', onEvent.value)
    }

    return {
      inputModel,
      selectModel,
      defaultData,
      onEvent,
      select,
      toggle,
      checkbox,
      onInput,
      scrollTo,
    }

  }
});
</script>

<style>
.codeplace {
  font-size: 12px;
  font-family: consolas;
  font-weight: 600;
  color: #333;
  background: #f4f4f4;
  border: 1px solid #ccc;
  border-radius: 4px;
  padding: 5px;
}

.code {
  font-family: monospace;
  color: #929292;
}
</style>
