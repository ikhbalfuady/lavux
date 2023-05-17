<template>
  <div class="bg-root text-dark text-center text-center" style="height: 80vh">
    <div>
      <div style="font-size: 30vh">
        403
      </div>

      <div class="text-h2 text-grey-7"> Forbidden </div>
      <div class="text-grey-7"> You don't have permission to access this page! </div>

      <q-btn unelevated class="q-mt-xl" color="dark"
        to="/p" label="Go Home" no-caps icon="reply"
      />
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'ForbiddenPage'
})
</script>
