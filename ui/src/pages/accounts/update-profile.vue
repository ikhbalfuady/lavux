
<template >
  <div class="root row q-pa-md ">

      <!-- Header Title -->
      <div class="col-12 ">
        <div class="text-bold text-h4">Profile</div>
        <span class="text-grey-8">Update Profile</span>
      </div>

      <div class="col-12 offset-sm-3 col-sm-5 animated zoomIn">
        <div class="col-12 text-center">
          <lv-uploader size="180px" avatar rouded label="Picture"
            :placeholder="dataModel.picture"
            url="me/upload-picture"
            :extension="allowExtension"
            accept="image/*"
            :body="{ user_id: dataModel.id, name: 'user-pp-'}"
            @uploaded="(res) => handleUpload(res, 'picture')"
            auto-upload
          />
        </div>

        <q-form @submit="submit" class="col-12 bg-white shadow q-mt-lg q-pa-md radius">
          <lv-input label="Name *" v-model="dataModel.name" :rules="$Handler.rules(dataModel.name, 'required')" />
          <lv-input label="Username *" v-model="dataModel.username" :rules="$Handler.rules(dataModel.username, 'required')" />
          <lv-input label="Email *" v-model="dataModel.email" :rules="$Handler.rules(dataModel.email, 'required')" />
          <div class="text-right" >
            <lv-btn class="capital bold q-mr-sm" label="Change Password" icon="lock" :to="{ name: 'change-password' }"/>
            <lv-btn class="capital bold" color="green" label="Update" :disable="disableSubmit" type="submit" icon="check_circle"/>
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
  name: 'Profile',
  props: {
 //
  },
  setup () {
    const { Config, Handler, Helper, Api, Store, SetMetaPage} = useServices()

    const dataModel = ref({...Meta.model})
    const disableSubmit = ref(false)
    const allowExtension = ref(['jpg', 'jpeg', 'png', 'gif'])
 
    onMounted(() => {
      dataModel.value = user.value
      SetMetaPage('My Profile')
    })

    const user = computed(() => {
      return Config.credentials()
    })

    function submit () {
      Api.post('me/update-profile', dataModel.value, (status, data, message, response) => {
        if (status === 200) {
          Helper.showToast(message)
          let cre = user.value
          cre.name = dataModel.value.name
          cre.username = dataModel.value.username
          cre.email = dataModel.value.email
          Config.credentials(cre) // updating picture to ldb
        }
      })
    }

    function handleUpload (response, target) {
      const result = response?.data?.data?.url || null
      dataModel.value[target] = result
      let cre = user.value
      cre.picture = result
      Config.credentials(cre) // updating picture to ldb
      Helper.showToast('Profile picture updated!')
      // console.log('handleUpload', response, result)
    }

    return {
      dataModel,
      disableSubmit,
      allowExtension,
      submit,
      handleUpload,
    }

  }
})
</script>
