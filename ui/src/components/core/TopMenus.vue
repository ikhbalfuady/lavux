<template>
  <q-list dense style="min-width: 100px" v-if="menu">
    <q-item dense v-for="(rowSub, indexSub) in menu.children" v-bind:key="indexSub" clickable>
      <q-item-section>
        <span v-html="rowSub.name" @click="$Handler.topMenuCallEvent(menu.children, indexSub)"></span>
      </q-item-section>
      <q-item-section v-if="$Handler.topMenuHasSub(rowSub)" side><q-icon name="keyboard_arrow_right" /></q-item-section>
      <q-menu class="top-menu-content" anchor="top right" self="top left" v-if="$Handler.topMenuHasSub(rowSub)" >
        <top-menus :menu="rowSub" />
      </q-menu>
    </q-item>
  </q-list>
</template>

<script>
import { ref, onMounted, computed, defineComponent } from 'vue'
import useServices from './../../composables/Services'

export default defineComponent({
  name: 'TopMenus',
  props: {
    menu: {
      type: Object,
      default: null,
    }
  },
  setup (props) {
    const { Config, Helper, Handler } = useServices()

    onMounted(() => {
      // console.log(props.menu)
    })

    return {
    }
  }
})
</script>