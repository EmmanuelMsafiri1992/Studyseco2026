<script setup>
import { ref, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    enabled: {
        type: Boolean,
        default: true
    },
    unreadCount: {
        type: Number,
        default: 0
    }
})

const previousCount = ref(0)
const audioContext = ref(null)
const isPlaying = ref(false)

// Initialize Web Audio API
onMounted(() => {
    if (window.AudioContext || window.webkitAudioContext) {
        audioContext.value = new (window.AudioContext || window.webkitAudioContext)()
    }
    previousCount.value = props.unreadCount
})

// Watch for changes in unread count
watch(() => props.unreadCount, (newCount, oldCount) => {
    if (props.enabled && newCount > previousCount.value && !isPlaying.value) {
        playNotificationSound()
    }
    previousCount.value = newCount
})

const playNotificationSound = async () => {
    if (!audioContext.value || isPlaying.value) return
    
    try {
        isPlaying.value = true
        
        // Create a pleasant notification sound
        const oscillator = audioContext.value.createOscillator()
        const gainNode = audioContext.value.createGain()
        
        oscillator.connect(gainNode)
        gainNode.connect(audioContext.value.destination)
        
        // Create a bell-like sound
        oscillator.type = 'sine'
        oscillator.frequency.setValueAtTime(800, audioContext.value.currentTime)
        oscillator.frequency.exponentialRampToValueAtTime(600, audioContext.value.currentTime + 0.1)
        
        gainNode.gain.setValueAtTime(0.1, audioContext.value.currentTime)
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.value.currentTime + 0.5)
        
        oscillator.start()
        oscillator.stop(audioContext.value.currentTime + 0.5)
        
        // Add a second tone for richness
        setTimeout(() => {
            if (!audioContext.value) return
            
            const oscillator2 = audioContext.value.createOscillator()
            const gainNode2 = audioContext.value.createGain()
            
            oscillator2.connect(gainNode2)
            gainNode2.connect(audioContext.value.destination)
            
            oscillator2.type = 'sine'
            oscillator2.frequency.setValueAtTime(1000, audioContext.value.currentTime)
            oscillator2.frequency.exponentialRampToValueAtTime(800, audioContext.value.currentTime + 0.1)
            
            gainNode2.gain.setValueAtTime(0.05, audioContext.value.currentTime)
            gainNode2.gain.exponentialRampToValueAtTime(0.01, audioContext.value.currentTime + 0.3)
            
            oscillator2.start()
            oscillator2.stop(audioContext.value.currentTime + 0.3)
        }, 100)
        
        setTimeout(() => {
            isPlaying.value = false
        }, 800)
        
    } catch (error) {
        console.warn('Could not play notification sound:', error)
        isPlaying.value = false
    }
}

// Test sound function (can be called from parent)
const testSound = () => {
    playNotificationSound()
}

defineExpose({
    testSound
})
</script>

<template>
    <!-- This component handles notification sounds in the background -->
    <div style="display: none;"></div>
</template>