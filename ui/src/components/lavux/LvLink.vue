<template>
  <router-link v-if="to" :to="to" class="action-link">
      <q-avatar v-if="icon" :icon="icon" class="fix-icon-link"
        :size="avatarSize ? avatarSize : 'xs'" 
        :color="iconBg ? iconBg : 'purple-1'" 
        :text-color="iconColor ? iconColor : 'purple-9'"
      />
      <span :class="`text-bold ${icon ? 'q-pl-sm' : ''}`" v-html="label" ></span>
      <slot></slot>
  </router-link>
  <div v-else @v-on:click.exact="$emit('click')" class="action-link">
    <q-avatar v-if="icon" :icon="icon" class="fix-icon-link"
      :size="avatarSize ? avatarSize : 'xs'" 
      :color="iconBg ? iconBg : 'purple-1'" 
      :text-color="iconColor ? iconColor : 'purple-9'"
    />
    <span :class="`text-bold ${icon ? 'q-pl-sm' : ''}`" v-html="label" ></span>
    <slot></slot>
  </div>
</template>

<script>
import { computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvLink',
  props: {
    iconColor: {
      type: String
    },
    iconBg: {
      type: String
    },
    icon: {
      type: String
    },
    label: {
      type: String
    },
    avatarSize: {
      type: String
    },
    to: {
      type: [String, Object],
      default: null
    }
  },
  setup (props) {
    //
  }
})
</script>