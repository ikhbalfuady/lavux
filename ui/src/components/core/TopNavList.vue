<template>
  <q-list dense style="min-width: 100px">
    <q-item v-for="(rowSub, indexSub) in menu" v-bind:key="indexSub" clickable>
      <q-item-section>
        
        <router-link v-if="!$Handler.topMenuHasSub(rowSub)" custom :to="{ name: rowSub.route }" v-slot="{ navigate, href }" >
          <a class="link" @click="navigate" :href="href" v-html="rowSub.title"></a>
        </router-link>
         <span v-else v-html="rowSub.title"> </span>
      </q-item-section>
      <q-item-section v-if="$Handler.topMenuHasSub(rowSub)" side>
        <q-icon name="arrow_drop_down" /></q-item-section>

      <q-menu anchor="top right" self="top left" v-if="$Handler.topMenuHasSub(rowSub)" >
        <top-nav-list :menu="rowSub.children" />
      </q-menu>
    </q-item>
  </q-list>
</template>

<script>
import { ref, onMounted, computed, defineComponent } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useServices from './../../composables/Services'

export default defineComponent({
  name: 'TopNavList',
  props: {
    menu: {
      type: Array,
      default: () => { return [] }
    }
  },
  setup (props) {
    const { Config, Helper, Handler } = useServices()
    const route = useRoute()
    const router = useRouter()

    const menuList = ref([])

    onMounted(() => {
      //
      // console.log(drawer.value)
    })

    return {
      menuList,
    }
  }
})
</script>