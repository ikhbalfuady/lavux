<template>
  <q-list :class="`deepth${deepthMenu ? '-' + deepthMenu : ''}`" :style="paddingSide">
    <template v-for="(menu, index) in formatedMenus" :key="index">

      <template v-if="!menu.children.length ">
        <router-link custom :to="{ name: menu.route }" v-slot="{ navigate, href }"  >
          <div class="side-menu-wrapper">
            <q-item dense clickable tag="a" :href="href" @click="navigate"
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
        </router-link>
      </template>

      <div v-else class="side-menu-wrapper">
        <q-item dense clickable @click="formatedMenus[index].collapse = !formatedMenus[index].collapse"
          :class="[
            'side-menu-item',
            menu.children.find(r => r.collapse) ? 'menu-active' : ''
          ]"
        >
          <q-item-section avatar>
            <q-icon :name="menu.icon" size="16px"/>
          </q-item-section>
          <q-item-section> {{ menu.title }} </q-item-section>
          <q-item-section side>
            <q-item-label caption><q-icon :name="menu.collapse ? 'expand_less' : 'expand_more'" /></q-item-label>
          </q-item-section>
          <q-tooltip v-if="miniMode" anchor="center right" self="center left" :offset="[10, 10]" >{{ menu.title }}</q-tooltip>
        </q-item>
      </div>

      <q-slide-transition>
        <side-nav-list v-if="menu.collapse && menu.children.length" :menus="menu.children" :miniMode="miniMode" :deepth="deepthMenu" class="sub-menu" :hideSkeleton="true"/>
      </q-slide-transition>
    </template>

    <template v-if="!formatedMenus.length && !hideSkeleton">
      <div class="q-pa-md animated fadeIn">
        <q-skeleton v-for="n in 10" :key="n" height="30px" class="q-mb-md"/>
      </div>
    </template>
  </q-list>
</template>

<script>
import { ref, onMounted, computed, watchEffect, defineComponent } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useServices from './../../composables/Services'

export default defineComponent({
  name: 'SideNavList',
  props: {
    menus: {
      type: Array,
      default: () => { return [] }
    },
    miniMode: {
      type: Boolean,
      default: false
    },
    deepth: {
      type: Number,
      default: 1
    },
    hideSkeleton: {
      type: Boolean,
      default: false
    },
  },
  setup (props) {
    const { Config, Helper, Handler } = useServices()
    const route = useRoute()
    const router = useRouter()

    const formatedMenus = ref(props.menus)

    watchEffect(() => { // handle reactive for declared element
      formatedMenus.value = props.menus
    })

    onMounted(() => {
      //
    })

    const deepthMenu = computed(() => {
      return props.deepth + 1
    })
    
    const paddingSide = computed(() => {
      let res = null
      const padding = (props.deepth * 3) + 5
      if (deepthMenu.value >= 4) res = `padding-left:${padding}px;`
      return res
    })

    return {
      formatedMenus,
      deepthMenu,
      paddingSide,
    }
  }
})
</script>