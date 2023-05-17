<template>
  <div class="lv-field">
    <input type="file" :id="refFile" style="display: none;" @change="onFileSelected" />

    <q-avatar v-if="avatar" :size="size" :round="rouded" class="lv-image-uploader bordered" >
      <q-img :src="inputValue" :ratio="1" >
        <template v-slot:error>
          <div class="absolute-full flex flex-center bg-negative text-white" >
            <small style="font-size: 13px;">Cannot load image</small>
          </div>
        </template>
      </q-img>
      <div class="absolute-bottom text-center" v-if="!display">
        <input type="file" id="fileInput" style="display: none;" @change="onFileSelected" />
        <lv-btn @click="pickFile" color="primary" icon="file_present" label="Change" class="upload-btn"/>
        <lv-btn v-if="showUploadBtn" @click="uploadFile" color="green" icon="upload" label="Upload" class="q-ml-sm upload-btn"/>
      </div>
    </q-avatar>

    <q-field v-else :model-value="fileDisplay" :label-slot="useInnerLabel" outlined bottom-slots dense @click="pickFile"  >
      <template v-slot:label>
        <div class="row items-center all-pointer-events ellipsis">
          {{ label }}
        </div>
      </template>

      <template v-slot:control>
        <small class="self-center full-width no-outline text-grey-7" tabindex="0">{{fileDisplay}}</small>
      </template>

      <template v-slot:hint><span class="text-red-9"> {{ error }} </span></template>

      <template v-slot:append >
        <q-btn round dense flat icon="attachment" @click="pickFile" />
      </template>
      
      <template v-slot:after >
        <q-btn v-if="showUploadBtn" @click="uploadFile" dense icon="upload" color="primary" round unelevated>
          <q-tooltip>Upload</q-tooltip>
        </q-btn>
      </template>
    </q-field> 
  </div>
  

</template>

<script>
import { ref, computed, onBeforeMount, watchEffect, defineComponent } from 'vue'
import useServices from './../../composables/Services'
export default defineComponent({
  name: 'LvUploader',
  props: {
    modelValue: {
      type: [String, Object],
      default: null,
    },
    placeholder: {
      type: String,
      default: null,
    },
    label: {
      type: String,
      default: null,
    },
    avatar: { // for image upload
      type: Boolean,
      default: false,
    },
    rouded: { // for image upload
      type: Boolean,
      default: false,
    },
    display: { // for image display only
      type: Boolean,
      default: false,
    },
    size: {
      type: String,
      default: '200px',
    },
    name: {
      type: String,
      default: 'file',
    },
    url: {
      type: String,
      default: 'upload',
    },
    body: {
      type: Object,
      default: null,
    },
    extension: { // ex : ['jpg', 'jpeg']
      type: Array,
      default: () => { return [] },
    },
    accept: { // mime type
      type: String,
      default: null,
    },
    external: { // flag for upload to external url
      type: Boolean,
      default: false,
    },
    autoUpload: { // flag for trigger upload after file picked
      type: Boolean,
      default: false,
    },
  },
  setup (props, { emit }) {
    const { Config, Handler, Helper, Api, Store, SetMetaPage} = useServices()

    const inputValue = ref(props.placeholder)
    const rawFile = ref(null)
    const error = ref(null)
    const fileDisplay = ref(null)

    onBeforeMount(()=> {
      if (props.placeholder) {
        inputValue.value = props.placeholder
        fileDisplay.value = Helper.unReactive(inputValue.value)
      }

    })

    watchEffect(() => { // handle reactive for declared element
      if (props.placeholder) {
        inputValue.value = props.placeholder
        fileDisplay.value = props.placeholder
      }

      if (rawFile.value) {
        emit('update:modelValue', rawFile.value)
      }
    })

    const useInnerLabel = computed(() => {
      let res = (props.label) ? true : false
      if (props.topLabel) res = false
      return res
    })

    const refFile = computed(() => {
      return Helper.makeRef(`file_${Helper.createUID(false)}`)
    })

    const showUploadBtn = computed(() => {
      let res = rawFile.value ?? false
      if (props.autoUpload) res = false
      return res
    })

    // Methods

    const pickFile = () => {
      // Set accept attribute to limit file selection to specific types
      const fileInput = document.querySelector(`#${refFile.value}`)
      if (props.accept) fileInput.accept = props.accept
      fileInput.click()
    }

    const onFileSelected = (event) => {
      error.value = null
      const file = event.target.files[0]
      const reader = new FileReader()
      if (!file)  {
        error.value = 'Failed pick file'
        return false
      }

      // validation 
      if (props.extension.length && !props.extension.includes(file.type.split('/').pop())) {
        error.value = 'File not accepted!'
        return false
      }

      reader.onload = () => {
        fileDisplay.value = file?.name || 'File selected'
        rawFile.value = file
        inputValue.value = reader.result
        emit('picked', {name: fileDisplay.value, blob: inputValue.value, raw: rawFile.value })
        if (props.autoUpload) uploadFile()
      }
      reader.readAsDataURL(file)
     
    }

    const uploadFile = () => {
      if (!rawFile.value) return

      const paramName = props.name
      let formData = new FormData()
      formData.append(paramName, rawFile.value)

      // custom param data
      if(props.body) {
        for (let [key, value] of Object.entries(props.body)) {
          if (value) {
            if (value === true) value = 1
            if (value === false) value = 0
            if (typeof value === 'object') value = JSON.stringify(value)
            formData.append(key, value)
          }
        }
      }

      const external = props.external

      Api.post(props.url, formData, (status, data, message, response) => {
        emit('uploaded', response)
        if (status !== 200) {
          error.value = 'Error when uploading File'
          console.error(`Failed upload file to : ${props.url}`, response)
        } else {
          // Helper.showToast('File uploaded!')
          rawFile.value = null
        }
      }, false, external)

    }

    return {
      inputValue,
      fileDisplay,
      rawFile,
      error,
      // computed
      useInnerLabel,
      refFile,
      showUploadBtn,
      // methods
      pickFile,
      onFileSelected,
      uploadFile,
    }
  }
})
</script>

