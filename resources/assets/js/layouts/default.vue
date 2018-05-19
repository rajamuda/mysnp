<template>
  <div class="main-layout">
    <navbar/>

    <div class="container mt-4" style="margin-bottom:5rem">
      <child/>
    </div>

    <v-footer>&copy; {{ year }} {{ $t('copyright') }}</v-footer>
  </div>
</template>

<script>
import Navbar from '~/components/Navbar'

export default {
  name: 'MainLayout',

  data: () => ({
    year: new Date().getFullYear()
  }),

  components: {
    Navbar
  }
}
</script>
