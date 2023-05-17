<template>
  <span v-if="user" @click="getDetail">
    <span class="text-bold link animated zoomIn lv-user">
      @{{  user.username }}
      <q-popup-proxy>
        <div class="text-center bg-white" style="width: 200px;">
          <template v-if="user.name">
            <div class="q-mb-md text-center text-dark q-py-md link" >
              <q-avatar size="92px" class="lv-image-uploader bordered q-mb-md" >
                <q-img v-if="user.picture" :src="user.picture" fit="cover" >
                  <template v-slot:error>
                    {{ $Helper.getFirstChar(user.username) }}
                  </template>
                </q-img>
                <span v-else>{{ $Helper.getFirstChar(user.username) }}</span>
              </q-avatar>
              <br>
              <i class="animated fadeIn" >{{user.name}}</i><br>
              <span class="animated fadeIn" >{{user.email}}</span> <br>
              <small class="text-primary">{{ user.role_name }} ({{ user.role_slug }})</small><br>
            </div>
          </template>
          <lv-loading v-else/>
        </div>
      </q-popup-proxy>
    </span>
  </span>
  <span v-else class="text-bold link-display animated fadeIn">
    ....
  </span>

</template>

<script>
import useServices from './../../composables/Services'
import { ref, computed, onMounted, watchEffect, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvUser',
  props: {
    id: {
      type: String,
      default: null,
    },
    username: {
      type: String,
      default: null,
    },
  },
  setup(props) {
    const { Config, Handler, Helper, Api, GlobalStore, SetMetaPage } = useServices()

    const user = ref(null)

    onMounted(() => {
      if (props.id) getDetail()
    })

    watchEffect(() => { // handle reactive for declared element
      if (!props.id && props.username) {
        user.value = { username: props.username}
      }
    })

    function getDetail () {
      if (!user.value.id) { // only fetch when user id not set
        let param = ''
        if (props.username) param = `username=${props.username}`
        if (props.id) param = `id=${props.id}`

        Api.get(`app/user-profile?${param}`, (status, data, message, full) => {
          if (status === 200 && data) {
            user.value = data
          }
        })
      }
    }
    
    return {
      user,
      getDetail,
    }
  }
})
</script>
