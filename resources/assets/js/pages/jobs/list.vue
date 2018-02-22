<template>
	<card>
		<h1>Nothing</h1>
		{{ message }}
	</card>
</template>

<script>
	import Vue from 'vue'
	import VueSocketio from 'vue-socket.io'
	
	Vue.use(VueSocketio, 'http://localhost:4200')
	
	export default{
		scrollToTop: false,

		sockets:{
	    connect: function(){
	      console.log('socket connected')
	    },
	    // join: function(){
	    // 	this.$socket.emit(1)
	    // },
	    stream: function(val){
	      // console.log('this method was fired by the socket server. eg: io.emit("customEmit", data)')
	      console.log(val)
	    	// this.$socket.emit('stream', 1)
	    	this.message = val.message;
	    }
	  },

		data: () => ({
			message: ''
		}),

		// watch: {
		// 	message: function (val) {
		// 		this.message = val;
		// 		console.log(val);
		// 	},
		// },
		mounted () {
			this.$socket.emit('join', 1)
		},

		metaInfo () {
	    return { title: this.$t('jobs') }
	  },
	}
</script>