<template>
  <q-drawer v-if="!isTopNav" show-if-above v-model="drawer" :class="`side-menu ${primaryDrawer ? 'primary' : ''}`"
    :width="260"
    :breakpoint="500"
    :mini="isMiniMode"
    :behavior="menuBehavior"
  >
    <q-img class="absolute-top head-menu" :style="`height: ${headSize}`">
      <!-- Full Mode -->
      <div class="logo bg-transparent full-width " v-if="!isMiniMode">
        <img :src="primaryDrawer ? $Config.appLogoLight() : $Config.appLogo()" width="82" class="text-dark text-bold text-h4" alt="Lavux"/> <br>
        <small class="app-name">{{ $Config.appName() }}</small>

        <q-btn flat dense size="sm" class="apiroot-label text-normal" :label="$Config.getApiRoot()" icon="las la-info-circle">
          <q-tooltip>Api Root</q-tooltip>
        </q-btn>
      </div>
      <!-- Mini Mode -->
      <div class="logo bg-transparent " v-if="isMiniMode">
        <img class="mini" :src="$Config.appLogo(true)" style="width:32px !important"/> 
        <br>
        <q-btn @click="disableMiniMode" style="position:relative; top: 12px;" flat dense size="sm" class="disable-mini text-normal" icon="keyboard_double_arrow_right">
          <q-tooltip>Expand</q-tooltip>
        </q-btn>
      </div>
    </q-img>
    
    <q-scroll-area :style="`height: calc(94% - ${headSize}); margin-top: ${headSize};`"
      :thumb-style="$Config.scrollThumbStyle()" :bar-style="$Config.scrollBarStyle()"
    >
      <side-nav-list :menus="formatedMenus" :miniMode="isMiniMode" />
    </q-scroll-area>
    
  </q-drawer>
  <q-resize-observer @resize="onResize" />
</template>

<script>
import { useGlobalStore } from '../../store/GlobalStore';
import { ref, onMounted, computed, watch, watchEffect, defineComponent } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useServices from './../../composables/Services'
import { useQuasar } from 'quasar'

export default defineComponent({
  name: 'SideNav',
  setup () {
    const $q = useQuasar()
    const { Config, Helper, Handler, Api, GlobalStore } = useServices()
    const store = useGlobalStore()

    const route = useRoute()
    const router = useRouter()
 
    // properties
    const formatedMenus = ref([])
    // automated menus
    const currentBreakPoint = ref('')
    const menuBehavior = ref('desktop')
    const pauseOnResize = ref(false)
    const drawer = ref(false)
    const headSize = ref('99px')

    const isTopNav = computed(() => { return GlobalStore.getTopMenuMode })
    const isMiniMode = computed(() => { return GlobalStore.getMiniMenuMode })
    const primaryDrawer = computed(() => { return GlobalStore.getPrimaryDrawer})

    onMounted(() => {
      if (!isTopNav.value)  getMenu()
      drawer.value = drawerState.value

      const screenSize = $q.screen.name
      if (screenSize === 'sm' || screenSize === 'xs') Handler.drawer(false)
    })

    const drawerState = computed(() => {
      return Handler.drawer()
    })

    const routeName = computed(() => {
      return route.name
    })

    watch(drawerState, async (newVal, val) => {
      drawer.value = newVal
     
    })

    watch(isTopNav, async (newVal, val) => {
      if (!isTopNav.value && drawer.value) getMenu()
    })

    watch(routeName, async (newVal, val) => {
      formatedMenus.value = filterPermissionMenu(formatDrawer(formatedMenus.value))
    })

    // Methods
    function onResize () {
      currentBreakPoint.value = $q.screen.name

      if (!pauseOnResize.value) {
        if (currentBreakPoint.value === 'sm' || currentBreakPoint.value === 'xs') {
          menuBehavior.value = 'mobile'
          Handler.drawer(drawer.value)
        } else {
          menuBehavior.value = 'desktop'
          drawer.value = true
        }
      }
    }

    function getMenu () {
      Api.get('app/menus', (status, data, message, full) => {
        if (status === 200 && data) {
          formatedMenus.value = filterPermissionMenu(formatDrawer(data))
        }
      })
    }
    
    function _formatDrawer (menus) {
      let data = [];
      menus.map(r => {
        data = [...data, {
          ...r,
          children: r.children ? formatDrawer(r.children) : [],
          collapse: r.children ? !!r.children.find(child => child.route === routeName.value) : false,
        }];
      });
      return data;
    }

    function isRouteNameMatch(currentRoute, route) {
      return route.route === currentRoute || currentRoute.includes(`${route.route}-`);
    }

    function formatDrawer(menus) {
      const currentRoute = routeName.value
      let data = [];
      menus.map((r) => {
        const hasChildren = r.children && r.children.length > 0;
        const isCurrentRoute = isRouteNameMatch(currentRoute, r);
        const children = hasChildren ? formatDrawer(r.children, currentRoute) : [];

        data = [
          ...data,
          {
            ...r,
            collapse: isCurrentRoute || children.some((child) => child.collapse),
            children,
          },
        ];
      });
      return data;
    }

    function filterPermissionMenu (arr) {
      var permissionList = Handler.permissionList()
      return arr.map(item => {
        if (Handler.checkPermissionMenu(item.permissions, permissionList)) {
          return {
            ...item,
            children: filterPermissionMenu(item.children)
          };
        }
        return {
          ...item,
          children: filterPermissionMenu(item.children).filter(child => Handler.checkPermissionMenu(child.permissions, permissionList))
        };
      }).filter(item => Handler.checkPermissionMenu(item.permissions, permissionList));
    }

    function disableMiniMode () {
      store.setMiniMenuMode(false)
    }

    return {
      drawer,
      primaryDrawer,
      formatedMenus,
      menuBehavior,
      headSize,
      // computed
      routeName,
      isTopNav,
      isMiniMode,
      // methods
      onResize,
      disableMiniMode,
      isRouteNameMatch,
    }
  }
})

</script>

