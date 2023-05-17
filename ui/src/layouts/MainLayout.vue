<template>
  <q-layout view="lHh LpR fFf" class="theme-1">

    <top-nav />
    <top-bar />
    <side-nav />

    <q-page-container :class="`${isTopNav ? 'container-top-nav' : ''}`" >
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import useServices from './../composables/Services'
import { defineComponent, ref, computed } from "vue";

export default defineComponent({
  name: "MainLayout",

  setup() {
    const { GlobalStore } = useServices()

    const isTopNav = computed(() => { return GlobalStore.getTopMenuMode })

    return {
      isTopNav
    }
  },
})
</script>
