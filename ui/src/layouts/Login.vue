<template>
  <transition  appear
    enter-active-class="animated fadeIn "
    leave-active-class="animated fadeOut"
  >
  <q-layout class="bg-white">
    <q-page-container style="padding: 0 !important;" >

      <div class="row colpad full-height">
        <div class="col-12 col-sm-6 gt-xs bg-side">&nbsp;</div>
        <div class="col-12 col-sm-6 row bg-white">
          <div class="q-pt-lg q-mt-lg col-12 offset-sm-2 col-sm-8">
            <div style="text-align: center;padding-top: 13px;" v-touch-hold.mouse="ApiRoot" class="q-mt-lg q-pb-md">

              <q-img :src="appLogo" spinner-color="white" style="width: 92px;" >
                  <template v-slot:error>
                    <div class="absolute-full bg-white flex flex-center text-grey-4 text-caption text-center">
                      <q-icon name="broken_image" style="font-size: 62px;"/>
                    </div>
                  </template>
              </q-img>

              <div>
                <small class="text-grey-7">{{ appName }}</small>
                <br>
                <br>
                <small class="text-red-9">
                  * Hold click logo to open setting to change Apiroot / baseUrl <br>
                  (for production you must takeout this event)
                </small>
              </div>

            </div>

            <q-form class="form-area row" @submit.prevent="login" >

              <div class="col-12 col-md-12 q-px-md q-pb-sm">
                <q-toggle label="Login using username" v-model="useUsername" />
              </div>

              <lv-input class="q-pa-md" v-model="dataModel.email" required
                :type="useUsername ? 'text' : 'email'"
                :placeholder="useUsername ? 'Username' : 'Email'"
              >
                <template v-slot:prepend>
                  <q-icon :name="useUsername ? 'alternate_email' : 'email'" />
                </template>
              </lv-input>

              <lv-input class="q-pa-md" v-model="dataModel.password" required
                type="password" placeholder="Password"
              >
                <template v-slot:prepend> <q-icon name="lock" /> </template>
              </lv-input>

              <div class="col-12 col-md-12 q-px-md q-pb-sm">
                <lv-btn type="submit" size="lg" color="primary" icon="login" label="Login" class="full-width" :loading="loading">
                  <template v-slot:loading>
                    <q-spinner-facebook />
                  </template>
                </lv-btn>
              </div>

              <div class="col-12 col-md-12 pb-2 text-center">
                <small class="bold text-grey-7">
                  <span class="">App v.{{$Config.version()}} </span> <br>
                  <span v-if="version" class="animated fadeIn">Backend v.{{version}}</span>

                </small>
              </div>

            </q-form>
          </div>
        </div>
      </div>

    </q-page-container>
  </q-layout>
  </transition>
</template>

<script>
import useServices from './../composables/Services'
import { computed, defineComponent, ref, onBeforeMount, onMounted } from "vue";
import { useRouter, useRoute } from 'vue-router'
export default defineComponent({
  name: "Login",
  setup() {
    const route = useRoute()
    const router = useRouter()
    const { Config, Handler, Helper, Api, Store, SetMetaPage} = useServices()
    const { GlobalStore } = useServices()
    const dataModel = ref({
      email: '',
      password: ''
    })

    const isPwd = ref(true)
    const loading = ref(false)
    const useUsername = ref(false)
    const disableSubmit = ref(false)
    const version = ref(null)
    const company = ref(null)

    onMounted(() => {
      getVersion()
      getSetting()

      if (route.query.e) {
        if (route.query.e === 'miss-apiroot') {
          Config.setApiRoot(
            'Your Api Root configuration is missing, this is default value by sistem, make sure the value is correct or you can change the value.',
            '/login'
          )
        }
      }
    })

    const appName = computed(() => {
      let res = company.value?.list?.app_name?.value || Config.appName()
      return res
    })

    const appLogo = computed(() => {
      let res = company.value?.list?.url_logo?.value || Config.appLogo()
      return res
    })

    function auth () {
      if (route.query.resume) {
        window.location = route.query.resume
      } else {
        router.push({ name: 'home' })
      }
    }

    function login () {
      loading.value = true
      Helper.loadingOverlay()

      disableSubmit.value = true
      let send = { password: dataModel.value.password }
      if (useUsername.value) send.username = dataModel.value.email
      else send.email = dataModel.value.email

      Api.post('app/login', send,(status, data, message, full) => {
        loading.value = false
        Helper.loadingOverlay(false)
        if (status === 200) {
          data.permissions = Handler.makePermissions(data.permissions)
          disableSubmit.value = false
          Config.credentials(data)
          auth()
        }
      })
      // end
    }

    function getVersion () {
      Api.get('app/version', (status, data, message, response) => {
        if (status === 200) version.value = data
      })
    }

    function getSetting () {
      Api.get('app/settings?module=company', (status, data, message, response) => {
        if (status === 200) company.value = data
      })
    } 

    function ApiRoot () {
      Config.setApiRoot()
    }

    function ApiTmp () {
      Config.setApiTemp()
    }


    return {
      dataModel,
      isPwd,
      useUsername,
      disableSubmit,
      version,
      loading,
      company,
      appName,
      appLogo,
      // methods
      auth,
      login,
      getVersion,
      ApiRoot,
      ApiTmp,
    }
  },
})
 
</script>

<style>
.bg-side {
  background-image: url('../../public/assets/pattern-1.jpg');
  background-color: #fff;
  background-repeat: no-repeat;
  background-position: right;
  background-size: cover;
  height: 100% !important;
  min-height: 100vh;
}
</style>
