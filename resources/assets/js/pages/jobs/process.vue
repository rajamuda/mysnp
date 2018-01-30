<template>
	<card>
		<h1>Process id {{ id }}</h1>
	</card>
</template>

<script>
	export default{
		scrollToTop: false,

		metaInfo () {
	    return { title: this.$t('jobs') }
	  },

	  computed: {
	  	id () {
	  		return this.$route.params.id
	  	}
	  }
	}
</script>