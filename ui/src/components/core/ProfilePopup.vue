<template>
  <q-chip color="primary" :class="`chip-profile text-white `">
    <q-avatar size="sm">
      <q-img v-if="user.picture" :src="user.picture" fit="cover" >
        <template v-slot:error>
          {{ $Helper.getFirstChar(user.username) }}
        </template>
      </q-img>
      <span v-else>{{ $Helper.getFirstChar(user.username) }}</span>
    </q-avatar>

    <div class="chip-text"><span style="text-transform: none;">{{user.username}}</span></div>
    <q-menu @before-show="onRefresh" 
      transition-show="jump-left"
      transition-hide="jump-right"
      class="chip-menu"
    >
      <div class="no-wrap ">

        <div :class="`text-center pt-1 bg-primary`">
          <div class="q-mb-md text-center text-white q-py-md link" @click="openLink('profile')">
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
            <small class="text-yellow">{{(user.role) ? user.role.name : 'role-name' }} ({{ (user.role) ? user.role.slug : 'role-slug' }})</small><br>
          </div>
        </div>

        <div class="q-px-md bg-light q-pb-md">
          <q-toggle size="sm" @update:model-value="val => changePrimaryDrawer(val)" v-model="primaryDrawer" left-label label="Primary Drawer" class="mb-1"/>
          <br>
          <q-toggle size="sm" @update:model-value="val => changeMenuMode(val)" v-model="topMenuMode" left-label label="Top Menu Mode" class="mb-1"/>
          <br>
          <q-toggle v-if="!topMenuMode" size="sm" @update:model-value="val => changeMiniMode(val)" v-model="miniMenuMode" left-label label="Mini Mode Menu" class="mb-1"/>
          <br v-if="!topMenuMode">
          <q-toggle size="sm" @update:model-value="val => switchAction(val)" v-model="actionModal" left-label label="Action on Modal" class="mb-1"/>
          <br>
          <q-btn-dropdown flat class="q-mb-sm full-width bg-grey-2 text-capitalize q-mt-sm" color="primary" label="More..." >
            <q-list>
              
              <q-item dense clickable v-close-popup @click="openLink('profile')">
                <q-item-section avatar>
                  <q-avatar icon="person" color="primary" text-color="white" size="xs"/>
                </q-item-section>
                <q-item-section><q-item-label>Profile</q-item-label></q-item-section>
              </q-item>

              <q-item dense clickable v-close-popup @click="openLink('change-password')">
                <q-item-section avatar>
                  <q-avatar icon="gpp_good" color="primary" text-color="white" size="xs"/>
                </q-item-section>
                <q-item-section><q-item-label>Change Password</q-item-label></q-item-section>
              </q-item>

              <q-item dense clickable v-close-popup @click="reloadPermission">
                <q-item-section avatar>
                  <q-avatar icon="rotate_right" color="primary" text-color="white" size="xs"/>
                </q-item-section>
                <q-item-section><q-item-label>Reload Permissions</q-item-label></q-item-section>
              </q-item>

            </q-list>
          </q-btn-dropdown>
          <lv-btn @click="logout" label="Logout" icon="logout" class="full-width mt-1" color="dark"/>
        </div>

      </div>
    </q-menu>
  </q-chip>
</template>

<script>
import { useGlobalStore } from '../../store/GlobalStore';
import { storeToRefs, useStore  } from 'pinia'
import { useRouter } from 'vue-router'
import { ref, onBeforeMount, computed, watch, defineComponent } from 'vue'
import useServices from './../../composables/Services'

export default defineComponent({
  name: 'ProfilePopup',
  props: {
    data: {
      type: Object,
      default: null
    },
  },
  setup (props) {
    const { Config, Helper, Handler, Api } = useServices()
    const router = useRouter()
    const store = useGlobalStore()
    const { primaryDrawer, actionModal, miniMenuMode, topMenuMode, userInfo } = storeToRefs(useGlobalStore())

    onBeforeMount (() => {
      store.setUserInfo(Config.credentials())
    })

    const user = computed(() => {
      return userInfo.value
    })

    function onRefresh () {
      
      primaryDrawer.value = store.getPrimaryDrawer
      actionModal.value = store.getActionModal
      miniMenuMode.value = store.getMiniMenuMode
      topMenuMode.value = store.getTopMenuMode
      userInfo.value = store.getUserInfo
    }

    function logout () {
      Config.credentials('destroy')
      router.push({ name: 'login' })
    }

    function changePrimaryDrawer (val) {
      store.setPrimaryDrawer(val)
    }

    function changeMiniMode (val) {
      store.setMiniMenuMode(val)
    }

    function changeMenuMode (val) {
      store.setTopMenuMode(val)
    }

    function switchAction (val) {
      store.setActionModal(val)
    }

    function openLink (val) {
      var credential = this.user
      var path = 'change-password'
      var data = { id: credential.id }
      if (val === 'profile') path = 'update-profile'
      router.push({ name: path, params: data })
    }

    function reloadPermission () {
      Api.reloadPermission()
    }

    return {
      primaryDrawer,
      topMenuMode,
      miniMenuMode,
      actionModal,
      user,
      //
      onRefresh,
      logout,
      changePrimaryDrawer,
      changeMiniMode,
      changeMenuMode,
      switchAction,
      openLink,
      reloadPermission,
    }
  }

})

</script>
<style lang="scss">

.chip-profile {
  cursor: pointer;
  width: 90px;

  .q-chip__content {
    font-size: 11.5px;

    .chip-text {
      overflow: hidden;
      text-overflow: ellipsis;
      width: 130px;
      
    }

    .q-avatar {
      font-weight: bold;
    }
  }


}

.chip-menu {
  max-height: 100% !important;
}

.chip-top-menu {
  background-color: rgba(255, 255, 255, 0.2);
}
</style>
