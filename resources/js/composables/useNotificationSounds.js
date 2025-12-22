import { ref, watch } from 'vue'

// User preferences (stored in localStorage) - default to false to prevent annoying sounds
const soundEnabled = ref(JSON.parse(localStorage.getItem('studyseco_notification_sounds') ?? 'false'))

// Audio context for notification sounds
let audioContext = null
let notificationAudio = null

// Initialize audio system
const initializeAudio = async () => {
  try {
    // Create audio context
    audioContext = new (window.AudioContext || window.webkitAudioContext)()
    
    // Create notification sound using Web Audio API for better control
    const oscillator = audioContext.createOscillator()
    const gainNode = audioContext.createGain()
    
    oscillator.connect(gainNode)
    gainNode.connect(audioContext.destination)
    
    return { oscillator, gainNode }
  } catch (error) {
    console.warn('Web Audio API not supported, falling back to HTML audio')
    // Fallback to HTML audio
    notificationAudio = new Audio()
    return null
  }
}

// Play notification sound
const playNotificationSound = async (type = 'default') => {
  if (!soundEnabled.value) return
  
  try {
    // Resume audio context if suspended (required by browsers)
    if (audioContext && audioContext.state === 'suspended') {
      await audioContext.resume()
    }
    
    // Define different sound types
    const soundConfig = {
      default: { frequency: 800, duration: 0.2 },
      success: { frequency: 1000, duration: 0.3 },
      warning: { frequency: 600, duration: 0.4 },
      error: { frequency: 400, duration: 0.5 },
      message: { frequency: 900, duration: 0.15 },
      achievement: { frequency: [800, 1000, 1200], duration: 0.6 }
    }
    
    const config = soundConfig[type] || soundConfig.default
    
    if (audioContext) {
      // Use Web Audio API for precise control
      if (Array.isArray(config.frequency)) {
        // Multi-tone sound (for achievements)
        for (let i = 0; i < config.frequency.length; i++) {
          setTimeout(() => {
            playTone(config.frequency[i], config.duration / config.frequency.length)
          }, i * (config.duration * 1000 / config.frequency.length))
        }
      } else {
        playTone(config.frequency, config.duration)
      }
    } else if (notificationAudio) {
      // Fallback: Generate data URL for audio
      const audioData = generateAudioData(config.frequency, config.duration)
      notificationAudio.src = audioData
      notificationAudio.play().catch(console.warn)
    }
  } catch (error) {
    console.warn('Could not play notification sound:', error)
  }
}

// Play a single tone using Web Audio API
const playTone = (frequency, duration) => {
  if (!audioContext) return
  
  const oscillator = audioContext.createOscillator()
  const gainNode = audioContext.createGain()
  
  oscillator.connect(gainNode)
  gainNode.connect(audioContext.destination)
  
  oscillator.frequency.setValueAtTime(frequency, audioContext.currentTime)
  oscillator.type = 'sine'
  
  // Fade in and out to prevent clicks
  gainNode.gain.setValueAtTime(0, audioContext.currentTime)
  gainNode.gain.linearRampToValueAtTime(0.3, audioContext.currentTime + 0.01)
  gainNode.gain.linearRampToValueAtTime(0, audioContext.currentTime + duration)
  
  oscillator.start(audioContext.currentTime)
  oscillator.stop(audioContext.currentTime + duration)
}

// Generate audio data URL for fallback
const generateAudioData = (frequency, duration) => {
  const sampleRate = 44100
  const samples = Math.floor(sampleRate * duration)
  const buffer = new ArrayBuffer(samples * 2)
  const view = new DataView(buffer)
  
  for (let i = 0; i < samples; i++) {
    const sample = Math.sin(2 * Math.PI * frequency * i / sampleRate) * 0.3
    const value = Math.max(-1, Math.min(1, sample)) * 0x7FFF
    view.setInt16(i * 2, value, true)
  }
  
  // Create WAV file
  const wavHeader = new ArrayBuffer(44)
  const wavView = new DataView(wavHeader)
  
  // WAV header
  const writeString = (offset, string) => {
    for (let i = 0; i < string.length; i++) {
      wavView.setUint8(offset + i, string.charCodeAt(i))
    }
  }
  
  writeString(0, 'RIFF')
  wavView.setUint32(4, buffer.byteLength + 36, true)
  writeString(8, 'WAVE')
  writeString(12, 'fmt ')
  wavView.setUint32(16, 16, true)
  wavView.setUint16(20, 1, true)
  wavView.setUint16(22, 1, true)
  wavView.setUint32(24, sampleRate, true)
  wavView.setUint32(28, sampleRate * 2, true)
  wavView.setUint16(32, 2, true)
  wavView.setUint16(34, 16, true)
  writeString(36, 'data')
  wavView.setUint32(40, buffer.byteLength, true)
  
  const wavBuffer = new Uint8Array(wavHeader.byteLength + buffer.byteLength)
  wavBuffer.set(new Uint8Array(wavHeader), 0)
  wavBuffer.set(new Uint8Array(buffer), wavHeader.byteLength)
  
  const blob = new Blob([wavBuffer], { type: 'audio/wav' })
  return URL.createObjectURL(blob)
}

// Toggle sound setting
const toggleNotificationSounds = () => {
  soundEnabled.value = !soundEnabled.value
  localStorage.setItem('studyseco_notification_sounds', JSON.stringify(soundEnabled.value))
  
  // Play a test sound when enabling
  if (soundEnabled.value) {
    playNotificationSound('success')
  }
}

// Set sound preference
const setSoundEnabled = (enabled) => {
  soundEnabled.value = enabled
  localStorage.setItem('studyseco_notification_sounds', JSON.stringify(enabled))
}

// Watch for changes in sound preference
watch(soundEnabled, (newValue) => {
  localStorage.setItem('studyseco_notification_sounds', JSON.stringify(newValue))
}, { immediate: true })

// Initialize audio when first imported
initializeAudio()

export {
  soundEnabled,
  playNotificationSound,
  toggleNotificationSounds,
  setSoundEnabled
}