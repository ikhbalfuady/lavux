
<template >
  <div :class="`${(fromModal) ? '' : 'q-pb-xs'} row root`" style="min-width:75vw">


  <q-form @submit="submit" class="col-12 row animated fadeIn" >

    <!-- Header -->
    <lv-header-page class="bg-white" split showBack :backTo="{name: Meta.module}" :preventBackTo="fromModal ? true : false" >
      <template v-slot:left>
        <lv-breadcumb v-if="!fromModal" :title="Meta.name" :subtitle="type"/>
      </template>
      <template v-slot:right>
        <lv-btn labelVisibility icon="reply" label="Cancel" ref="closeForm" @click="backToRoot()" v-close-popup/>
        <lv-btn v-if="!loading && !id" labelVisibility icon="check" label="Save & Lanjut Upload" @click="submit(null, true)" color="primary" :disable="disableSubmit"/>
        <lv-btn v-if="!loading" labelVisibility icon="check" label="Save" color="primary" :disable="disableSubmit" type="submit"/>
      </template>
    </lv-header-page>

    <!-- Content -->
    <lv-loading v-if="loading" />
    <lv-container v-if="!loading" class="col-12" :height="fromModal ? '60vh' : null">

      <lv-input col="7" label="Judul*" v-model="dataModel.judul" required />

      <lv-select col="3" label="Tingkat / Jenjang*" v-model="dataModel.tingkat_sekolah_id" required searchable
        :url="`tingkat-sekolah?_columns=id,kode,nama,list_kelas`" :searchBy="['kode', 'nama']" merge-url
        :option-label="opt => `(<b class='text-uppercase'>${opt.kode}</b>) ${opt.nama}`"
        @selected="onPickedTingkat" :defaultData="dataModel.tingkat_sekolah"
      />

      <lv-select v-if="dataModel.tingkat_sekolah_id" col="2" label="Kelas*" v-model="dataModel.kelas"
        :options="$Handler.toObjectSelect(select?.kelas || [], true, 'name')" required
      />

      <lv-select col="4" label="Kurikulum*" v-model="dataModel.kurikulum_id" searchable
        :url="`kurikulum?_raw&_columns=id,nama,tahun`" :searchBy="['tahun', 'nama']" merge-url
        :option-label="opt => `(<b class='text-uppercase'>${opt.tahun}</b>) ${opt.nama}`"
        :defaultData="dataModel.kurikulum"
      />

      <lv-select col="4" label="Kategori Berkas" v-model="dataModel.kategori_berkas_id" searchable
        :url="`kategori-berkas?_raw&_columns=id,nama,type&type=not:video`" :searchBy="['nama']" merge-url
        option-label="nama" :defaultData="dataModel.kategori_berkas"
      />

      <lv-select v-if="dataModel.tingkat_sekolah_id && dataModel.kelas"
        col="4" label="Mata Pelajaran" v-model="dataModel.mata_pelajaran_id" searchable
        :url="`mata-pelajaran?_raw&_columns=id,nama,tingkat_sekolah_id,kelas&tingkat_sekolah_id=${dataModel.tingkat_sekolah_id}&kelas=${dataModel.kelas}`"
        :searchBy="['keterangan', 'nama']" merge-url
        option-label="nama" :defaultData="dataModel.mata_pelajaran"
      />

      <lv-input col="4" label="Pengarang" v-model="dataModel.pengarang" />
      <lv-input col="4" label="Penerbit" v-model="dataModel.penerbit" />
      <lv-input col="2" label="Tahun Terbit" v-model="dataModel.tahun_terbit" />


      <lv-select col="2" label="Version*" v-model="dataModel.version_id" url="versions" searchable required
        :option-label="opt => `(<b>${opt.kode}</b>) ${$Helper.beautyDate(opt.tanggal)} || ${opt.versi_saat_in ? 'Versi saat ini' : ''} <br> <small>${opt.keterangan}</small>`"
        :defaultData="dataModel.version"
      />

      <lv-textarea col="12" label="Katerangan" v-model="dataModel.katerangan" />

      <div v-if="!id" class="q-px-md col-12">
          <lv-banner dense color="blue" class="text-blue-9">
            <b>PERHATIAN!</b><br>
            Cover & File buku baru bisa di upload setelah Anda menyimpan data ini.
          </lv-banner>
        </div>
      <template v-else>
        <div class="q-px-md col-12">
          <lv-banner dense color="orange" class="text-orange-9">
            <b>PERHATIAN!</b><br>
            Setelah memilih file yang akan di upload, pastikan Anda menekan tombol Upload terlebih dahulu di sebelah kanan kolom, <br>
            tombol akan muncul setelah Anda memilih file, pastikan di kolom sudah ada tulisan <b class="text-primary text-bold">(Uploaded)</b> untuk memastikan
            bahwa file sudah terupload, jika tidak file tidak akan disimpan pada data ini!
          </lv-banner>
        </div>

        <lv-uploader col="6" label="Cover" v-model="dataModel.cover"
          :extension="['jpg', 'jpeg', 'png', 'gif']" accept="image/*"
          map-value="name" :body="$Config.upload('buku_cover', dataModel.uid)"
          :view-file="dataModel.cover_url"
        />

        <lv-uploader col="6" label="File PDF" v-model="dataModel.file_path"
          :extension="['pdf']" accept="application/pdf"
          map-value="name" :body="$Config.upload('buku', dataModel.uid)"
        />
      </template>

    </lv-container>

  </q-form>

  </div>
</template>

<script>
import { ref, reactive, computed, onBeforeMount, defineComponent } from 'vue'
import useServices from './../../composables/Services'
import { useRouter, useRoute } from 'vue-router'
import Meta from './meta'

export default defineComponent({
  name: Meta.moduleName+'Form',
  props: {
    data: {
      type: Object,
      default: null
    },
    onSubmit: {
      type: Function,
      default: null
    },
    disableMeta: { // disable setPageMeta
      type: Boolean,
      default: false,
    }
  },
  setup (props) {
    const { Config, Handler, Helper, Api, Store, SetMetaPage} = useServices()
    const router = useRouter()
    const route = useRoute()

    // Properties
    const closeForm = ref(1) // refs component
    const loading = ref(false)
    const stayAfterSubmit = ref(false)
    const disableSubmit = ref(false)
    const dataModel = ref({...Meta.model})
    const topMenu = [{ name: 'Refresh', event: onRefresh }]
    const select = reactive({ // select sources
      kelas: []
    })

    /* LIFECYCLE : all processes that are executed in a certain lifecycle are defined here */
    onBeforeMount(() => {
      if (!Meta.permission[type.value]) Handler._403()
      else {
        if (!fromModal.value) {
          Handler.topMenu(topMenu)
          if (!props.disableMeta) SetMetaPage(`${type.value} ${Meta.name}`)
        }
        onRefresh()
        resetModel()
      }
    })

    /* COMPUTED : all computed variables are defined here */
    const fromModal = computed(() => { return (props.data)? props.data : null })
    const id = computed(() => {
      return (fromModal.value && fromModal.value.id) ? fromModal.value.id : Handler.urlParams(route, 'id', true)
    })
    const type = computed(() => { return (id.value) ? 'update' : 'create' })

    /* METHODS : all methods are defined here */
    function onRefresh () {
      if (id.value) getData()
    }

    function backToRoot (data) {
      if (!fromModal.value) {
        console.log('br', data)
        if (data) router.push({ name: `${Meta.module}-detail`, params: data })
        else router.push({ name: Meta.module })
      } else if (closeForm.value) closeForm.value.$el.click()

    }

    function resetModel () {
      Object.assign(dataModel.value, Meta.model)
    }

    function validateSubmit () {
      // if (!dataModel.id) { // example validation
      //   Helper.showAlert('Opps!', 'id is rquired!')
      //   return false
      // } else return true // must set true on ending return
      return true
    }

    function submit (e, stay = false) {
      stayAfterSubmit.value = stay
      if (validateSubmit()) {
        disableSubmit.value = true
        if (dataModel.value.id !== null) update()
        else save()
      }
    }

    const callback  = (status, data, message) => {
      Helper.loadingOverlay(false)
      callbackFunc(data)
      if (status === 200) {
        console.log('stayAfterSubmit.value', stayAfterSubmit.value)
        Helper.showSuccess('Succesfully', message)
        if (stayAfterSubmit.value) {
          window.location = `${Meta.module}/form/${data.id}`
        } else {
          backToRoot(data)
        }
      } else disableSubmit.value = false
    }

    function save () {
      Helper.loadingOverlay(true, 'Saving..')
      Api.post(Meta.module, dataModel.value, callback)
    }

    function update () {
      Helper.loadingOverlay(true, 'Updating..')
      Api.post(`${Meta.module}/${dataModel.value.id}`, dataModel.value, callback)
    }

    function getData () {
      loading.value = true
      Api.get(`${Meta.module}/${id.value}`, (status, data, message, full) => {
        loading.value = false
        if (status === 200 && data) {
          dataModel.value = data
          if (data.tingkat_sekolah) onPickedTingkat(data.tingkat_sekolah)
        }
      })
    }

    function onPickedTingkat (tingkat) {
      if (tingkat && tingkat.list_kelas) select.kelas = tingkat.list_kelas
    }

    function callbackFunc (data) {
      if (props.onSubmit) props.onSubmit(data)
    }

    return {
      Meta,
      closeForm,
      loading,
      disableSubmit,
      dataModel,
      select,
      // computed
      type,
      fromModal,
      id,
      // methods
      onRefresh,
      getData,
      submit,
      backToRoot,
      onPickedTingkat,
    }
  }
})
</script>
