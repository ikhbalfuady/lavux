
<template >
  <div class="root row q-pa-md ">

      <!-- Header Title -->
      <div class="col-12 ">
        <div class="text-bold text-h4">Profile</div>
        <span class="text-grey-8">Change Password</span>
      </div>

      <div class="col-12 offset-sm-3 col-sm-5 animated zoomIn">

        <q-form @submit="submit" class="col-12 bg-white shadow q-mt-lg q-pa-md radius">
          <lv-input top-label type="password" label="Current Password*" v-model="dataModel.current_password" :rules="$Handler.rules(dataModel.current_password, 'required')" />
          <lv-input top-label type="password" label="New Password *" v-model="dataModel.password" :rules="$Handler.rules(dataModel.password, 'required')" />
          <lv-input top-label type="password" label="Repeat New Password *" v-model="repeat" :rules="$Handler.rules(dataModel.repeat, 'required')" />
          <div class="text-right" >
            <lv-btn class="capital bold" unelevated color="green" label="Update" :disable="disableSubmit" type="submit" icon="check_circle"/>
          </div>
        </q-form>
      </div>

  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, defineComponent } from 'vue'
import useServices from './../../composables/Services'
import Meta from '../users/meta'

export default defineComponent({
  name: 'ChangePassword',
  props: {
 //
  },
  setup () {
    const { Config, Handler, Helper, Api, Store, SetMetaPage} = useServices()

    const dataModel = ref({
      current_password: null,
      password: null,
    })
    const repeat = ref(null)
    const disableSubmit = ref(false)
 
    onMounted(() => {
      SetMetaPage('Change Password')
    })

    const user = computed(() => {
      return Config.credentials()
    })

    function submit () {

      if (dataModel.value.password !== repeat.value) {
        Helper.showAlert('Opps!', 'New Password & Repeat not match!')
        return false
      }

      Api.post('me/update-password', dataModel.value, (status, data, message, response) => {
        if (status === 200) {
          Helper.showToast(message)
          dataModel.value.current_password = null
          dataModel.value.password = null
          repeat.value = null
        }
      })
    }

    return {
      dataModel,
      repeat,
      disableSubmit,
      submit,
    }

  }
})
</script>
