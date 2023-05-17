<template>
  <lv-link class="q-px-sm q-py-sm col-12 link" :avatarSize="iconSize" :label="label" :icon="icon" :iconBg="iconBg" :iconColor="iconColor"
    :to="to"
  />
</template>

<script>
import { computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvActionItem',
  props: {
    label: {
      type: String,
      default: ""
    },
    icon: {
      type: String,
      default: "fiber_manual_record"
    },
    color: {
      type: String,
      default: "grey"
    },
    iconSize: {
      type: String,
      default: "xs"
    },
    to: {
      type: [String, Object],
      default: null
    }
  },
  setup (props) {

    const iconBg = computed(() => {
      const res = props.color ? props.color : 'grey'
      return res + '-1'
    })

    const iconColor = computed(() => {
      const res = props.color ? props.color : 'grey'
      return res + '-9'
    })

    return {
      iconBg,
      iconColor,
    }
  }
})
</script>