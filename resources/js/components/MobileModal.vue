<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: String,
  closable: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg, xl, full
    validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  mobileFullScreen: {
    type: Boolean,
    default: true
  },
  swipeToClose: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['close', 'opened', 'closed'])

const isMobile = ref(false)
const isClosing = ref(false)
const modalRef = ref(null)
const touchStartY = ref(0)
const touchCurrentY = ref(0)
const isDragging = ref(false)

// Check if mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
}

// Modal size classes
const sizeClasses = computed(() => {
  if (isMobile.value && props.mobileFullScreen) {
    return 'inset-0 m-0 rounded-none max-h-screen'
  }
  
  const sizes = {
    sm: 'max-w-sm mx-4',
    md: 'max-w-md mx-4', 
    lg: 'max-w-2xl mx-4',
    xl: 'max-w-4xl mx-4',
    full: 'max-w-7xl mx-4'
  }
  
  return sizes[props.size] || sizes.md
})

// Handle close
const handleClose = () => {
  if (!props.closable) return
  
  isClosing.value = true
  emit('close')
  
  setTimeout(() => {
    isClosing.value = false
    emit('closed')
  }, 300)
}

// Handle escape key
const handleEscape = (event) => {
  if (event.key === 'Escape' && props.show && props.closable) {
    handleClose()
  }
}

// Touch handlers for swipe to close
const handleTouchStart = (event) => {
  if (!props.swipeToClose || !isMobile.value) return
  
  touchStartY.value = event.touches[0].clientY
  touchCurrentY.value = touchStartY.value
  isDragging.value = true
}

const handleTouchMove = (event) => {
  if (!isDragging.value || !props.swipeToClose || !isMobile.value) return
  
  touchCurrentY.value = event.touches[0].clientY
  const deltaY = touchCurrentY.value - touchStartY.value
  
  // Only allow downward swipes
  if (deltaY > 0) {
    const opacity = Math.max(0.3, 1 - (deltaY / 300))
    const transform = `translateY(${deltaY}px)`
    
    if (modalRef.value) {
      modalRef.value.style.transform = transform
      modalRef.value.style.opacity = opacity
    }
  }
}

const handleTouchEnd = () => {
  if (!isDragging.value || !props.swipeToClose || !isMobile.value) return
  
  isDragging.value = false
  const deltaY = touchCurrentY.value - touchStartY.value
  
  if (modalRef.value) {
    if (deltaY > 150) {
      // Close modal
      handleClose()
    } else {
      // Snap back
      modalRef.value.style.transform = ''
      modalRef.value.style.opacity = ''
    }
  }
}

// Lifecycle
onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  window.addEventListener('keydown', handleEscape)
  
  if (props.show) {
    nextTick(() => emit('opened'))
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  window.removeEventListener('keydown', handleEscape)
})
</script>

<template>
  <Teleport to="body">
    <Transition
      name="modal"
      @enter="emit('opened')"
      @after-leave="emit('closed')"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="handleClose"
      >
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
        
        <!-- Modal container -->
        <div :class="[
          'relative min-h-screen flex items-center justify-center',
          { 'items-end': isMobile && mobileFullScreen }
        ]">
          <!-- Modal content -->
          <div
            ref="modalRef"
            :class="[
              'bg-white shadow-2xl transition-all duration-300 relative overflow-hidden',
              sizeClasses,
              {
                'rounded-t-2xl': isMobile && mobileFullScreen,
                'rounded-2xl': !isMobile || !mobileFullScreen,
                'max-h-[90vh]': !isMobile || !mobileFullScreen,
                'max-h-screen': isMobile && mobileFullScreen
              }
            ]"
            @touchstart="handleTouchStart"
            @touchmove="handleTouchMove"
            @touchend="handleTouchEnd"
            @click.stop
          >
            <!-- Drag handle for mobile -->
            <div v-if="isMobile && swipeToClose" class="flex justify-center py-3 bg-slate-50">
              <div class="w-12 h-1 bg-slate-300 rounded-full"></div>
            </div>
            
            <!-- Header -->
            <div v-if="title || $slots.header || closable" class="flex items-center justify-between p-4 border-b border-slate-200">
              <div class="flex-1">
                <slot name="header">
                  <h3 v-if="title" class="text-lg font-semibold text-slate-900">{{ title }}</h3>
                </slot>
              </div>
              
              <button
                v-if="closable"
                @click="handleClose"
                class="p-2 hover:bg-slate-100 rounded-lg transition-colors"
              >
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            
            <!-- Body -->
            <div :class="[
              'modal-body overflow-y-auto',
              {
                'p-4 pb-6': isMobile,
                'p-6': !isMobile,
                'max-h-[calc(100vh-200px)]': isMobile && mobileFullScreen,
                'max-h-[70vh]': !isMobile || !mobileFullScreen
              }
            ]">
              <slot :isMobile="isMobile" />
            </div>
            
            <!-- Footer -->
            <div v-if="$slots.footer" class="border-t border-slate-200 p-4">
              <slot name="footer" :isMobile="isMobile" />
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* Transition animations */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-active .bg-black\/50,
.modal-leave-active .bg-black\/50 {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .bg-black\/50,
.modal-leave-to .bg-black\/50 {
  opacity: 0;
}

/* Mobile slide up animation */
@media (max-width: 768px) {
  .modal-enter-from [ref="modalRef"],
  .modal-leave-to [ref="modalRef"] {
    transform: translateY(100%);
  }
}

/* Desktop scale animation */
@media (min-width: 769px) {
  .modal-enter-from [ref="modalRef"],
  .modal-leave-to [ref="modalRef"] {
    transform: scale(0.9);
    opacity: 0;
  }
}

/* Prevent body scroll when modal is open */
.modal-open {
  overflow: hidden;
}
</style>