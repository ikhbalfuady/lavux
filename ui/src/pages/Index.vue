<template>
  <div class="row q-pa-md">
    <div class="col-12 q-pt-lg q-mt-lg text-center">
      <img :src="$Config.appLogo()" style="width:40%;"/>
      <br>
      <br>
      <br>
      <div class="text-dark text-h3 text-bold">Welcome to Lavux!</div>
      <p>
        Let's make some module for your apps 
      </p>
      <lv-btn color="orange-9" icon="apps" label="View Lavux Components" to="/example" class="q-ma-sm"/>
      <lv-btn color="primary" icon="auto_fix_normal" label="Make Module Now" to="/generator" class="q-ma-sm"/>
      <lv-btn color="teal" icon="description" label="Documentation" target="_blank" href="https://lavux-doc.sopeus.com" class="q-ma-sm"/>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, defineComponent } from 'vue'
import useServices from './../composables/Services'

export default defineComponent({
  name: "PageIndex",
  setup () {
    const { Config, Handler, Helper, SetMetaPage, Api} = useServices()
    

    onMounted(() => {
      SetMetaPage('Home')
    })

    function onRefresh () {
      //
    }

    return {
      onRefresh,
    }

  }
});
</script>
