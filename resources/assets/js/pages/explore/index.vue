<template>
  <card :title="$t('explore_snp')">
    <!-- {{ $t('you_are_logged_in') }} -->
    <div v-if="!job_id" style="padding-bottom: 10px">
      <select v-model="job_selection" name="job_selection" class="form-control">
        <option disabled value="">{{ $t('select_one') }}</option>
        <option v-for="opt in jobs" :value="opt.value">
          {{ opt.text }}
        </option>
      </select>
      <hr>
    </div>
    <div id="explore-table" v-if="job_selection !== ''">
      <div class="row">
        <div class="col-10">
          <div class="row align-items-start mb-2">
            <div class="col-1 align-self-start">
              <label>{{ $t('filter') }}</label>
            </div>
            <div class="col">
              <div class="form-inline mb-1" v-for="(value, index) in filter.attr">
                <div class="row">
                  <div class="col">
                    <select v-model="filter.attr[index]" name="attr" class="custom-select">
                      <option v-for="opt in attr_opts" :value="opt">
                        {{ $t(opt) }}
                      </option>
                    </select>
                  </div>
                  <div class="col" v-if="filter.attr[index] == 'pos' || filter.attr[index] == 'qual'">
                    <select v-model="filter.operator[index]" name="operator" class="custom-select">
                      <option v-for="opt in operator_opts" :value="opt">
                        {{ opt }}
                      </option>
                    </select>
                  </div>
                  <div class="col">
                    <select v-if="filter.attr[index] == 'ref' || filter.attr[index] == 'alt'" v-model="filter.value[index]" name="base-value" class="custom-select mr-sm-2">
                      <option v-for="opt in base_opts" :value="opt">
                        {{ opt }}
                      </option>
                    </select>
                    <select v-else-if="filter.attr[index] == 'chrom'" v-model="filter.value[index]" name="chrom-value" class="custom-select mr-sm-2">
                      <option value="" disabled>{{ $t('select_one') }}</option>
                      <option v-for="opt in chrom_opts" :value="opt">
                        {{ opt }}
                      </option>
                    </select>
                    <select v-else-if="filter.attr[index] == 'eff_annotation'" v-model="filter.value[index]" name="annotation-value" class="custom-select mr-sm-2">
                      <option value="" disabled>{{ $t('select_one') }}</option>
                      <option v-for="opt in annotation_opts" :value="opt">
                        {{ opt }}
                      </option>
                    </select>
                    <select v-else-if="filter.attr[index] == 'eff_impact'" v-model="filter.value[index]" name="impact-value" class="custom-select mr-sm-2">
                      <option value="" disabled>{{ $t('select_one') }}</option>
                      <option v-for="opt in impact_opts" :value="opt">
                        {{ opt }}
                      </option>
                    </select>
                    <input v-else-if="filter.attr[index] == 'pos' || filter.attr[index] == 'qual'" v-model="filter.value[index]" type="number" name="number-value" class="form-control">
                    <input v-else v-model="filter.value[index]" type="text" name="text-value" class="form-control">
                  </div>
                  <div v-if="filter.attr.length > 1" class="col-1 mr-sm-1">
                    <button type="button" class="btn btn-danger" @click="removeFilter(index)" >(-)</button>
                  </div>
                  <div v-if="index == filter.attr.length-1" class="col-1 mr-sm-1">
                    <button type="button" class="btn btn-success" @click="addFilter">(+)</button>
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-primary mt-1" @click="submitFilter">{{ $t('apply') }}</button>
              <button v-if="isFilterSet()" type="button" class="btn btn-sm btn-danger mt-1" @click="resetFilter">{{ $t('reset') }}</button>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="d-flex align-items-end flex-column">
            <div class="p-2">
              <label>{{ $t('show') }}</label>
              <select v-model="show_selection" name="show_selection" class="form-control-sm">
                <option v-for="opt in show_selection_opts" :value="opt">
                  {{ opt }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <hr>
      <div v-if="job_selection != '' && selected_job.result">
        SNPs Information from "<router-link :to="{ name: 'jobs.process', params: { id: selected_job.result.value }}">{{ selected_job.result.text }}</router-link>"
      </div>
      <center>
      <table class="table table-hover table-responsive" v-if="table_data.length">
        <thead>
          <tr>
            <th>{{ $t('chrom') }}</th>
            <th>{{ $t('pos') }}</th>
            <th>{{ $t('rs_id') }}</th>
            <th>{{ $t('ref') }}</th>
            <th>{{ $t('alt') }}</th>
            <th>{{ $t('qual') }}</th>
            <th>{{ $t('eff_annotation') }}</th>
            <th>{{ $t('eff_impact') }}</th>
            <th>{{ $t('option') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(value, index) in table_data">
            <td>{{ value.chrom }}</td>
            <td>{{ value.pos }}</td>
            <td>{{ value.rs_id }}</td>
            <td>{{ value.ref }}</td>
            <td>{{ value.alt }}</td>
            <td>{{ value.qual }}</td>
            <td><span v-if="value.annotation">{{ value.annotation.split('|').filter(onlyUnique).join(', ') }}</span><span v-else>-</span></td>
            <td><span v-if="value.impact">{{ value.impact.split('|').filter(onlyUnique).join(', ') }}</span><span v-else>-</span></td>
            <td><router-link :to="{ name: 'explore.detail', params: { id: value.id }}">{{ $t('detail') }}</router-link></td>
          </tr>
        </tbody>  
      </table>
      </center>
      {{ $t('total_row') }}: {{ pagination.total }}
      <div id="pagination-table">
        <pagination :pagination="pagination" :callback="changePage"></pagination>
      </div>
    </div>
  </card>
</template>

<script>
import axios from 'axios'
import pagination from '~/components/Pagination.vue'

export default {
  middleware: 'auth',

  data: () => ({
    job_selection: '',
    jobs: [],
    table_data: [],
    page_selection: 1,
    pagination: {},
    show_selection: 10,
    show_selection_opts: [10,15,20,25,50,100],
    filter: {
      attr: ['chrom'],
      operator: ['='],
      value: [''],
    },
    attr_opts: ['chrom', 'ref', 'alt', 'pos', 'rs_id', 'qual', 'eff_annotation', 'eff_impact'],
    operator_opts: ['=', '!=', '<=', '>='],
    base_opts: ['C', 'A', 'T', 'G'],
    chrom_opts: [],
    annotation_opts: ['missense_variant','synonymous_variant','upstream_gene_variant','downstream_gene_variant','intergenic_region', 'intron_variant', '3_prime_UTR_variant', '5_prime_UTR_variant','stop_lost','start_lost','stop_gained'],
    impact_opts: ['HIGH','MODERATE','LOW','MODIFIER'],
    query: {},
  }),

  metaInfo () {
    return { title: this.$t('explore_snp') }
  },

  watch: {
    job_selection: function (newval) {
      this.getSnpInfo()
    },

    show_selection: async function (newval) {
      await this.getSnpInfo()
      if(!this.table_data.length){
        this.pagination.current_page = this.pagination.last_page
        this.changePage()
      }
    }
  },

  methods: {
    changePage () {
      this.page_selection = this.pagination.current_page
      this.getSnpInfo()
    },

    async getSnpInfo () {
      let job = this.job_selection
      let show = this.show_selection
      let page = this.page_selection
      let query = JSON.stringify(this.query)
      // console.log('/api/db-snp/'+job+'?show='+show+'&page='+page+'&query='+query)
      try{
        const { data } = await axios.get('/api/db-snp/'+job+'?show='+show+'&page='+page+'&query='+query)
        this.table_data = data.result
        this.pagination = data.pagination
        this.chrom_opts = data.chrom
      }catch(e){
        console.log(e)
      }
    },

    async getJobs () {
      const { data } = await axios.get('/api/jobs/all')
      let job_tmp = []
      for(let i = 0; i < data.length; i++){     
        job_tmp[i] = { 'text': data[i].title, 'value': data[i].id }
      }

      this.jobs = job_tmp
    },

    addFilter () {
      this.filter.attr.push('chrom')
      this.filter.operator.push('=')
      this.filter.value.push('')
    },

    removeFilter (index) {
      this.filter.attr.splice(index,1)
      this.filter.operator.splice(index,1)
      this.filter.value.splice(index,1)
    },

    async submitFilter () {
      this.query = this.filter
      await this.getSnpInfo()
      if(!this.table_data.length){
        this.pagination.current_page = this.pagination.last_page
        this.changePage()
      }
    },

    resetFilter () {
      this.query = {}
      this.filter = {
        attr: ['chrom'],
        operator: ['='],
        value: [''],
      }
      this.getSnpInfo()
    },

    isFilterSet () {
      if(JSON.stringify(this.query) !== "{}")
        return true

      return false
    },

    onlyUnique(value, index, self) { 
        return self.indexOf(value) === index;
    }

  },

  computed: {
    job_id: function () {
      return this.$route.query.job
    },

    selected_job: function () {
      return {
        result: this.jobs.find((x) => { return x.value == this.job_selection})
      }
    },
  },

  mounted () {
    if(this.job_id != undefined){
      this.job_selection = this.job_id
    }
    this.getJobs()  
  },

  components: {
    pagination
  }

}
</script>

<style scoped>
  /*input{
    margin-bottom: 5px;
  }*/
</style>