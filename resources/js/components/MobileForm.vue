<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  title: String,
  subtitle: String,
  submitText: {
    type: String,
    default: 'Submit'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  loading: {
    type: Boolean,
    default: false
  },
  fullScreen: {
    type: Boolean,
    default: false
  },
  stickyActions: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['submit', 'cancel'])

const isMobile = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
}

const handleSubmit = (event) => {
  event.preventDefault()
  emit('submit', event)
}

const handleCancel = () => {
  emit('cancel')
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})
</script>

<template>
  <div :class="[
    'mobile-form',
    {
      'min-h-screen bg-slate-50': fullScreen && isMobile,
      'bg-white rounded-lg shadow': !fullScreen || !isMobile
    }
  ]">
    <!-- Header -->
    <div v-if="title || subtitle" :class="[
      'form-header p-4 border-b border-slate-200',
      { 'sticky top-0 bg-white z-10': isMobile }
    ]">
      <h2 v-if="title" class="text-xl font-bold text-slate-900 mb-1">{{ title }}</h2>
      <p v-if="subtitle" class="text-sm text-slate-600">{{ subtitle }}</p>
    </div>

    <!-- Form Content -->
    <form @submit="handleSubmit" class="form-content">
      <div :class="[
        'form-body',
        { 'p-4 space-y-6': isMobile, 'p-6 space-y-4': !isMobile }
      ]">
        <slot :isMobile="isMobile" />
      </div>

      <!-- Actions -->
      <div :class="[
        'form-actions p-4 border-t border-slate-200 bg-white',
        {
          'sticky bottom-0': stickyActions && isMobile,
          'flex flex-col space-y-3': isMobile,
          'flex flex-row space-x-3 justify-end': !isMobile
        }
      ]">
        <slot name="actions" :isMobile="isMobile" :loading="loading">
          <button
            type="submit"
            :disabled="loading"
            :class="[
              'btn-primary px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium transition-colors',
              'hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500',
              'disabled:opacity-50 disabled:cursor-not-allowed',
              { 'w-full': isMobile, 'order-2': !isMobile }
            ]"
          >
            <span v-if="loading" class="inline-flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Processing...
            </span>
            <span v-else>{{ submitText }}</span>
          </button>
          
          <button
            type="button"
            @click="handleCancel"
            :class="[
              'btn-secondary px-6 py-3 bg-slate-200 text-slate-700 rounded-lg font-medium transition-colors',
              'hover:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500',
              { 'w-full': isMobile, 'order-1': !isMobile }
            ]"
          >
            {{ cancelText }}
          </button>
        </slot>
      </div>
    </form>
  </div>
</template>

<style scoped>
.mobile-form {
  @apply w-full max-w-2xl mx-auto;
}

/* Mobile-specific styling */
@media (max-width: 768px) {
  .mobile-form :deep(input),
  .mobile-form :deep(textarea),
  .mobile-form :deep(select) {
    @apply text-base; /* Prevents zoom on iOS */
    min-height: 44px;
  }
  
  .mobile-form :deep(label) {
    @apply text-base font-medium mb-2 block;
  }
  
  .mobile-form :deep(.form-group) {
    @apply mb-6;
  }
  
  .mobile-form :deep(.form-row) {
    @apply flex flex-col space-y-4;
  }
  
  .mobile-form :deep(.checkbox-group),
  .mobile-form :deep(.radio-group) {
    @apply space-y-3;
  }
  
  .mobile-form :deep(.checkbox-item),
  .mobile-form :deep(.radio-item) {
    @apply flex items-center p-3 bg-slate-50 rounded-lg;
  }
  
  .mobile-form :deep(.error-message) {
    @apply text-red-600 text-sm mt-1;
  }
}

/* Desktop-specific styling */
@media (min-width: 769px) {
  .mobile-form :deep(.form-row) {
    @apply grid grid-cols-2 gap-4;
  }
  
  .mobile-form :deep(.form-group) {
    @apply mb-4;
  }
}
</style>