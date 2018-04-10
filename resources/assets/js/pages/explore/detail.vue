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
    <div class="card-title"><b>{{ $t('snp_annotation') }}</b></div>
    <table class="table table-striped table-hover" v-if="snp_info.annotation">
      <thead>
        <tr>
          <th>{{ $t('eff_annotation') }}</th>
          <th>{{ $t('eff_impact') }}</th>
          <th>{{ $t('eff_gene_name') }}</th>
          <th>{{ $t('eff_gene_id') }}</th>
          <th>{{ $t('eff_feature_type') }}</th>
          <th>{{ $t('eff_feature_id') }}</th>
          <th>{{ $t('eff_transcript_biotype') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(ann, index) in snp_info.annotation">
          <td>{{ ann }}</td>
          <td>{{ snp_info.impact[index] }}</td>
          <td>{{ snp_info.gene_name[index] }}</td>
          <td>{{ snp_info.gene_id[index] }}</td>
          <td>{{ snp_info.feature_type[index] }}</td>
          <td>{{ snp_info.feature_id[index] }}</td>
          <td>{{ snp_info.transcript_biotype[index] }}</td>
        </tr>
      </tbody>
    </table>
    <div class="mb-3" v-else><mark><i>Annotation not found</i></mark></div>
    <div class="card-title"><b>{{ $t('snp_information') }}</b></div>
    <div id="snp-info" class="m-4">
      <div class="row mb-3">
        <div class="col-2 font-weight-bold">
          {{ $t('rs_id') }}
        </div>
        <div class="col">
          {{ snp_info.rs_id }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-2 font-weight-bold">
          {{ $t('chrom') }}
        </div>
        <div class="col">
          {{ snp_info.chrom }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-2 font-weight-bold">
          {{ $t('pos') }}
        </div>
        <div class="col">
          {{ snp_info.pos }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-2 font-weight-bold">
          {{ $t('ref') }}
        </div>
        <div class="col">
          {{ snp_info.ref }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-2 font-weight-bold">
          {{ $t('alt') }}
        </div>
        <div class="col">
          {{ snp_info.alt }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-2 font-weight-bold">
          {{ $t('qual') }}
        </div>
        <div class="col">
          {{ snp_info.qual }}
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-2 font-weight-bold">
          {{ $t('format') }}
        </div>
        <div class="col">
          <table class="table table-small table-responsive mb-0">
            <tr v-for="(value, index) in snp_info.format">
              <td><i>{{ index }}</i></td>
              <td>: {{ value }}</td>
            </tr>
          </table>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-2 font-weight-bold">
          {{ $t('info') }}
        </div>
        <div class="col">
          <table class="table table-small table-responsive mb-0">
            <tr v-for="(value, index) in snp_info.info">
              <td><i>{{ index }}</i></td>
              <td>: {{ value }}</td>
            </tr>
          </table>
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
    snp_info: {},
  }),

  metaInfo () {
    return { title: this.$t('detail_snp') }
  },

  methods: {
    async getSnpDetail () {
      try{
        const { data } = await axios.get('/api/db-snp/detail/'+this.snp_id)
        this.snp_info = data.result
        /* parse SNP Effect */
        if(this.snp_info.annotation){
          this.snp_info.annotation = this.snp_info.annotation.split("|")
          this.snp_info.impact = this.snp_info.impact.split("|")
          this.snp_info.gene_name = this.snp_info.gene_name.split("|")
          this.snp_info.gene_id = this.snp_info.gene_id.split("|")
          this.snp_info.feature_type = this.snp_info.feature_type.split("|")
          this.snp_info.feature_id = this.snp_info.feature_id.split("|")
          this.snp_info.transcript_biotype = this.snp_info.transcript_biotype.split("|")
        }
      }catch(e){
        // console.error(e)
        if(e.response.status === 404 || e.response.status === 500){
          this.$router.go(-1)
        }
      }
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
    font-family: 'Monospace', 'Consolas', serif;
  }
  .borderless td, .borderless th {
    border: none;
  }
  .table-small td, .table-small th {
    padding: 0 0.5rem 0 0;
  }
</style>