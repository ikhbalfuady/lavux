<template>
  <q-btn flat dense color="primary" icon="launch" class="ml" 
    :to="link" 
    target="_blank"
  />
</template>

<script>
import { computed, defineComponent } from 'vue'
export default defineComponent({
  name: 'LvOpenLink',
  props: {
    to: {
      type: [String, Object],
      default: null
    },
    meta: {
      type: [Object],
      default: null
    },
    data: {
      type: [Object],
      default: null
    },
    page: {
      type: [String],
      default: null
    },
    queryParams: {
      type: [String, Object],
      default: null
    }
  },
  setup (props) {
    
    const link = computed(() => {
      let res = null
      if (props.meta) {
        var page = props.page ? props.page : ''
        var data = props.data ? props.data : null
        res = {name: `${props.meta.module}-${page}`, params: data, query: props.queryParams}
      }
      if (props.to) res = props.to
      return res
    })
 
    return {
      link,
    }
  }
})
</script>