<template>
  <q-header v-if="!isTopNav" style="margin-top: -16px; height: 65px;">
    <!-- <q-toolbar class="shadow-toolbar bg-soft text-dark q-my-md "> -->
    <q-toolbar class="bg-white text-dark q-my-md ">
      <q-btn @click="drawerToggle" flat dense icon="menu" class="q-mr-sm" />
      <q-separator dark vertical inset />

      <!-- Get Top Menu List -->
      <div v-for="(row, index) in menu" v-bind:key="index" class="top-menu animated fadeIn">
        <span v-html="row.name" @click="$Handler.topMenuCallEvent(menu, index)" :class="`${(row.class !== undefined) ? row.class : ''} top-menu-list`"></span>
        <q-menu v-if="$Handler.topMenuHasSub(row)" class="top-menu-content">
          <top-menus :menu="row" />
        </q-menu>
      </div>

      <q-space />

      <q-separator dark vertical /> 

      <notif />
      <profile-popup/>
    </q-toolbar>
  </q-header>
</template>

<script>
import { ref, onMounted, computed, defineComponent } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useServices from './../../composables/Services'

export default defineComponent({
  name: 'TopBar',
  setup () {
    const { Config, Helper, Handler, GlobalStore, Api } = useServices()
    const route = useRoute()
    const router = useRouter()

    onMounted(() => {
    })

    const menu = computed(() => {
      return Handler.topMenu()
    })

    const isTopNav = computed(() => { return GlobalStore.getTopMenuMode })

    function drawerToggle () {
      const currentDrawer = Handler.drawer()
      Handler.drawer(!currentDrawer)
    }

    return {
      drawerToggle,
      menu,
      isTopNav,
    }
  }
})
</script>