<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  swipeToClose: {
    type: Boolean,
    default: false
  },
  touchFeedback: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['swipeClose', 'touchStart', 'touchEnd'])

const isMobile = ref(false)
const touchStartX = ref(0)
const touchStartY = ref(0)
const isTouch = ref(false)

// Detect if device is mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768 || 'ontouchstart' in window
}

// Touch event handlers
const handleTouchStart = (event) => {
  if (!props.swipeToClose && !props.touchFeedback) return
  
  isTouch.value = true
  touchStartX.value = event.touches[0].clientX
  touchStartY.value = event.touches[0].clientY
  
  emit('touchStart', event)
}

const handleTouchMove = (event) => {
  if (!props.swipeToClose) return
  
  const touchX = event.touches[0].clientX
  const touchY = event.touches[0].clientY
  
  const deltaX = touchX - touchStartX.value
  const deltaY = touchY - touchStartY.value
  
  // Prevent default scroll if horizontal swipe
  if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 10) {
    event.preventDefault()
  }
}

const handleTouchEnd = (event) => {
  if (!isTouch.value) return
  
  isTouch.value = false
  
  if (props.swipeToClose) {
    const touchX = event.changedTouches[0].clientX
    const deltaX = touchX - touchStartX.value
    
    // If swiped right more than 100px, emit close
    if (deltaX > 100) {
      emit('swipeClose')
    }
  }
  
  emit('touchEnd', event)
}

// Handle resize
const handleResize = () => {
  checkMobile()
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

defineExpose({
  isMobile
})
</script>

<template>
  <div 
    :class="[
      'mobile-optimized',
      { 
        'touch-none': !touchFeedback,
        'select-none': isMobile && touchFeedback
      }
    ]"
    @touchstart="handleTouchStart"
    @touchmove="handleTouchMove"
    @touchend="handleTouchEnd"
  >
    <slot :isMobile="isMobile" />
  </div>
</template>

<style scoped>
.mobile-optimized {
  -webkit-tap-highlight-color: transparent;
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  user-select: none;
}

/* Enhanced touch targets for mobile */
@media (max-width: 768px) {
  .mobile-optimized :deep(button),
  .mobile-optimized :deep(a),
  .mobile-optimized :deep([role="button"]) {
    min-height: 44px;
    min-width: 44px;
    padding: 12px;
  }
  
  .mobile-optimized :deep(input),
  .mobile-optimized :deep(textarea),
  .mobile-optimized :deep(select) {
    min-height: 44px;
    font-size: 16px; /* Prevents zoom on iOS */
  }
  
  /* Larger tap targets */
  .mobile-optimized :deep(.tap-target) {
    padding: 16px;
    margin: 4px;
  }
  
  /* Better spacing for mobile */
  .mobile-optimized :deep(.mobile-spacing) {
    padding: 16px;
    margin-bottom: 16px;
  }
}</style>