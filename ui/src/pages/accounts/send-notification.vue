
<template >
  <q-form @submit="submit" class="row">
    <lv-input class="col-sm-6 q-pa-sm" label="Title *" v-model="dataModel.title" placeholder="Ex: Recheck my data" required />
    <lv-input class="col-sm-6 q-pa-sm" label="Category" v-model="dataModel.category" placeholder="Ex: Invoice" />
    <lv-input class="col-sm-12 q-pa-sm" label="Link URL" v-model="dataModel.link_url" placeholder="Ex: https://google.com"/>
    <lv-select class="col-sm-12 q-pa-sm" label="To User" v-model="dataModel.user_id"
      url="users" :search-by="['name', 'email']" multiple required
    />
    <lv-textarea class="col-sm-12 q-pa-sm" label="Content" v-model="dataModel.content" required />
    <div class="col-12 text-right" >
      <lv-btn class="capital bold" unelevated color="green" label="Send" type="submit" icon="send" :loading="loading"/>
    </div>
  </q-form>
</template>

<script>
import { ref, reactive, computed, onMounted, defineComponent } from 'vue'
import useServices from './../../composables/Services'

export default defineComponent({
  name: 'SendNotification',
  props: {
 //
  },
  setup () {
    const { Config, Handler, Helper, Api, Store, SetMetaPage} = useServices()

    const model = {
      user_id: [],
      title: null,
      content: null,
      category: null,
      type: 'direct',
      link_source: 'external',
      link_url: null,
      date: Helper.today(),
    }

    const dataModel = ref({
      user_id: [],
      title: null,
      content: null,
      content: null,
      type: 'direct',
      link_source: 'external',
      link_url: null,
      date: Helper.today(),
    })

    const loading = ref(false)

    onMounted(() => {
      dataModel.value = Object.assign(dataModel.value, model)
    })

    function submit () {
      loading.value = true
      Api.put('me/send-notice', dataModel.value, (status, data, message, response) => {
        loading.value = false
        if (status === 200) {
          Helper.showToast(message)
          dataModel.value = Object.assign(dataModel.value, model)
        }
      })
    }

    return {
      dataModel,
      loading,
      submit,
    }

  }
})
</script>
