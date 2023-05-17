<template>
  <q-header bordered v-if="isTopNav">
    <q-bar style="height:48px">
      <img :src="$Config.appLogoLight()" width="62"/>
      <q-btn dense flat @click="showSubMenu = !showSubMenu">
        <q-icon :name="showSubMenu ? 'keyboard_arrow_up' : 'keyboard_arrow_down'" style="font-size:21px;"/>
      </q-btn>

      <template v-for="(menu, index) in formatedMenus" :key="index">
        
        <template v-if="!menu.children.length">
          <div class="main-menu-list animated fadeIn ">
            <router-link custom :to="{ name: menu.route }" v-slot="{ navigate, href }" >
              <a @click="navigate" :href="href" v-html="menu.title"></a>
            </router-link>
          </div>
        </template>

        <template v-else>
          <div class="main-menu-list animated fadeIn">
            <span v-html="menu.title"></span>
            <q-icon name="arrow_drop_down" style="font-size:16px;"/>
            <q-menu transition-show="jump-down" transition-hide="jump-up">
              <top-nav-list :menu="menu.children" />
            </q-menu>
          </div>
        </template>

      </template>

      <q-space />

      <notif />
      <profile-popup topMenuDesign/>
    </q-bar>

    <div v-if="topMenu.length && showSubMenu" class="submenu-container row items-center">
      <div v-for="(row, index) in topMenu" v-bind:key="index" class="submenu-list animated fadeIn">
        <span v-html="row.name" @click="$Handler.topMenuCallEvent(topMenu, index)" :class="`${(row.class) ? row.class : ''} submenu-root`"></span>
        <q-menu v-if="$Handler.topMenuHasSub(row)" transition-show="jump-down" transition-hide="jump-up">
          <top-menus :menu="row" />
        </q-menu>
      </div>
    </div>
  </q-header>
  
</template>

<script>
import { ref, onMounted, computed, watch, watchEffect, defineComponent } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useServices from './../../composables/Services'
import { useQuasar } from 'quasar'

export default defineComponent({
  name: 'TopNav',
  setup () {
    const $q = useQuasar()
    const { Config, Helper, Handler, Api, GlobalStore } = useServices()

    const route = useRoute()
    const router = useRouter()
 
    // properties
    const showSubMenu = ref(false)
    const formatedMenus = ref([])
    // automated menus

    const isTopNav = computed(() => { return GlobalStore.getTopMenuMode })

    onMounted(() => {
      if (isTopNav.value) {
        getMenu()
      }

    })

    watch(isTopNav, async (newVal, val) => {
      if (isTopNav.value)  getMenu()
    })


    const routeName = computed(() => {
      return route.name
    })

    const topMenu = computed(() => {
      return Handler.topMenu()
    })


    // Methods
    function getMenu () {
      Api.get('app/menus', (status, data, message, full) => {
        if (status === 200 && data) {
          console.info('Init menu TopNav')
          formatedMenus.value = filterPermissionMenu(formatDrawer(data))
        }
      })
    }
    
    function formatDrawer (menus) {
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

    return {
      formatedMenus,
      showSubMenu,
      // computed
      routeName,
      isTopNav,
      topMenu,
      // methods
    }
  }
})

</script>

<style lang="scss">

  .main-menu-list {
    cursor: pointer;
    font-size: 13px !important;
    color: #fff !important;
    border-radius: 5px;
    transition: background-color 0.3s ease;
  }
  
  .main-menu-list:hover {
    background-color: #dbd6d641;
  }

  .main-menu-list a, .main-menu-list span {
    color: #fff !important;
    font-weight: 700;
    text-decoration: none;
    padding: 1px 5px 5px 5px;
  }

  .submenu-container {
    padding: 5px 4px 6px 5px;
  }

  .submenu-list {
    margin-left: 5px !important;
    cursor: pointer;
    font-size: 12px !important;
  }


  .submenu-root {
    padding: 4px;
    border-radius: 5px;
    transition: background-color 0.2s ease-in-out; /* transition property */
  }

  .submenu-root:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.2);
  }
</style>
