<template>
  <div class="bg-root text-dark text-center q-pa-md flex flex-center" style="height: 100vh">
    <div>
      <div style="font-size: 30vh">
        401
      </div>

      <div class="text-h2" style="opacity:.4">
        Unauthorized
      </div>

      <q-btn unelevated class="q-mt-xl" color="dark"
        to="/" label="Go Home" no-caps icon="reply"
      />
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'UnauthorizePage'
})
</script>
