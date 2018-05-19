<template>
  <card :title="$t('about')">
    <div class="text-center">
      <h1 style="font-size:7rem;"> {{ appName }} </h1>
      <div class="mb-4" style="color:grey">{{ $t('tagline') }}</div>
    </div><hr>
    <p><b>{{ appName }}</b> is a web-based application to identify SNP and construct phylogenetic tree. In order to identify SNP and construct phylogenetic tree, {{ appName }} was build as a pipeline that include certain SNP-related tools (e.g. Bowtie2, SAMtools, GATK, SnpEff, and VCF-kit).</p>

    <p><b>{{ appName }}</b> can not be done without help from
      <ul>
        <li>Tropica Biopharmaca Research Center <b>(Trop BRC)</b>, LPPM IPB</li>
        <li>Indonesian Medical Education and Research Institue <b>(IMERI)</b> Fakultas Kedoteran Universitas Indonesia</li>
        <li><b>Department of Medical Chemistry</b> Fakultas Kedoteran Universitas Indonesia</li>
      </ul>
    </p>

  </card>
</template>

<script>
export default {

  metaInfo () {
    return { title: this.$t('about') }
  },

  data: () => ({
    appName: window.config.appName,
  }),

}
</script>
