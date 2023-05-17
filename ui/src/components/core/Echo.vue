<template>
  <div>
    <div v-if="label" class="text-h6 bold">{{label}}</div>
    <div :class="(className) ? className : 'bg-dark text-orange'" :style="(styleEl) ? styleEl : 'min-height:200px; overflow-x:scroll'" >
        <pre v-if="data" style="font-size:11px; font-family:'Consolas'">{{data}}</pre>
    </div>
  </div>
</template>

<style>
.text-footer {
  font-size: 10px;
  opacity: 0.9;
  padding-top: 5px;
  padding-left: 10px;
}

.txtbold {
  font-weight: 600;
}
</style>

<script>
export default {
  name: 'Echo',
  props: ['className', 'styleEl', 'data', 'label'],
  data () {
    return {
      dataModel: null
    }
  },

  created () {
    //
  },

  mounted () {
    //
  },

  methods: {
    onRefresh () {
      console.log('activate')
    }
  }

}

</script>
