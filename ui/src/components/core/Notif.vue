<template>
  <q-btn dense round flat icon="las la-bell" class="q-mr-sm" >
    <q-popup-proxy>
      <KeepAlive>
        <notification-list @loaded="onLoadNotif"/>
      </KeepAlive>
    </q-popup-proxy>
    <q-badge v-if="totalNotif" floating color="red" rounded :label="totalNotif" class="animated zoomIn"/>
  </q-btn>
</template>

<script>
import { ref, reactive, onMounted, computed, defineComponent } from 'vue'
import useServices from './../../composables/Services'

export default defineComponent({
  name: 'Notif',
  setup (props, { emit }) {
    const { Config, Helper, Handler, Api } = useServices()

    const totalNotif = ref(0)
 
    onMounted(() => {
      getTotalNotif()
    })

    function getTotalNotif () {
      Api.get(`me/notifications?total_unread`, (status, data, message, full) => {
        if (status === 200 && data) {
          totalNotif.value = data.total
        }
      })
    }

    function onLoadNotif (res) {
      console.log('onLoadNotif', res)
      if (res && res.summary) {
        totalNotif.value = res.summary.total_unread
      }
    }

    return {
      totalNotif,
      onLoadNotif,
    }
  }
})
</script>