<template>
<div v-if="menu" class="side-menu-wrapper">
  <q-item dense clickable tag="a" :href="href"
    :class="[
      'animated fadeIn',
      'side-menu-item',
      menu.collapse ? 'menu-active' : ''
    ]"
  >
    <q-item-section avatar>
      <q-icon :name="menu.icon" size="16px"/>
    </q-item-section>
    <q-item-section> {{ menu.title }} </q-item-section>
    <q-tooltip v-if="miniMode" anchor="center right" self="center left" :offset="[10, 10]" >{{ menu.title }}</q-tooltip>
  </q-item>
</div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'SideNavListItem',
  props: {
    href: {
      type: String,
      default: null
    },
    menu: {
      type: Object,
      default: null
    },
    miniMode: {
      type: Boolean,
      default: false
    },
  },
  setup (props) {
    //
    return {
      //
    }
  }
})
</script>
