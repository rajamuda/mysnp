<template>
  <card>
    <div class="card-title"><b>{{ $t('flank_area') }}</b></div>
    <div class="jumbotron flank-area">
      <p class="lead">
        {{ snp_info.flank_left }}
      </p>
      <p class="display-4">
        [{{ snp_info.ref }}/{{ snp_info.alt }}]
      </p>
      <p class="lead">
        {{ snp_info.flank_right }}
      </p>
    </div>
    <div id="snp-info" class="m-4">
      <div class="row mb-3">
        <div class="col-3 font-weight-bold">
          {{ $t('rs_id') }}
        </div>
        <div class="col">
          {{ snp_info.rs_id }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-3 font-weight-bold">
          {{ $t('chrom') }}
        </div>
        <div class="col">
          {{ snp_info.chrom }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-3 font-weight-bold">
          {{ $t('pos') }}
        </div>
        <div class="col">
          {{ snp_info.pos }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-3 font-weight-bold">
          {{ $t('ref') }}
        </div>
        <div class="col">
          {{ snp_info.ref }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-3 font-weight-bold">
          {{ $t('alt') }}
        </div>
        <div class="col">
          {{ snp_info.alt }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-3 font-weight-bold">
          {{ $t('qual') }}
        </div>
        <div class="col">
          {{ snp_info.qual }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-3 font-weight-bold">
          {{ $t('format') }}
        </div>
        <div class="col">
          {{ snp_info.format }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-3 font-weight-bold">
          {{ $t('info') }}
        </div>
        <div class="col">
          {{ snp_info.info }}
        </div>
      </div>
    </div>
  </card>
</template>

<script>
import axios from 'axios'

export default {
  middleware: 'auth',

  data: () => ({
    snp_info: {}
  }),

  metaInfo () {
    return { title: this.$t('detail_snp') }
  },

  methods: {
    async getSnpDetail () {
      const { data } = await axios.get('/api/db-snp/detail/'+this.snp_id)

      this.snp_info = data.result
    }
  },

  computed: {
    snp_id: function () {
      return this.$route.params.id
    }
  },

  mounted () {
    this.getSnpDetail()
  },


}
</script>
<style scoped>
  .flank-area {
    font-family: 'Monospace', serif;
  }
</style>