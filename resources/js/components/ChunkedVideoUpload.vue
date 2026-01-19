<template>
    <div class="chunked-upload-container">
        <!-- File Input -->
        <div v-if="!isUploading && !uploadComplete" class="upload-area">
            <input
                ref="fileInput"
                type="file"
                accept="video/*"
                @change="handleFileSelect"
                class="hidden"
            />
            <div 
                @click="$refs.fileInput.click()"
                @dragover.prevent
                @drop.prevent="handleFileDrop"
                class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-all duration-200"
            >
                <div class="flex flex-col items-center space-y-4">
                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <div>
                        <p class="text-lg font-semibold text-slate-700">Click to upload or drag and drop</p>
                        <p class="text-sm text-slate-500 mt-1">Supports MP4, MOV, AVI, WMV, MKV up to 1GB</p>
                        <p class="text-xs text-slate-400 mt-2">Fast chunked upload with compression</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Selected Info -->
        <div v-if="selectedFile && !isUploading && !uploadComplete" class="mt-4 p-4 bg-slate-50 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-slate-700">{{ selectedFile.name }}</p>
                        <p class="text-sm text-slate-500">{{ formatFileSize(selectedFile.size) }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        @click="startUpload"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200"
                    >
                        Start Upload
                    </button>
                    <button
                        @click="clearFile"
                        class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors duration-200"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload Progress -->
        <div v-if="isUploading" class="mt-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800">Uploading Video...</p>
                            <p class="text-sm text-slate-600">{{ uploadedChunks }}/{{ totalChunks }} chunks • {{ uploadSpeed }}</p>
                        </div>
                    </div>
                    <button
                        @click="cancelUpload"
                        class="text-red-600 hover:text-red-700 text-sm font-medium"
                    >
                        Cancel
                    </button>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Progress</span>
                        <span class="font-medium">{{ uploadProgress.toFixed(1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-3 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>{{ formatFileSize(uploadedBytes) }} / {{ formatFileSize(totalBytes) }}</span>
                        <span>ETA: {{ estimatedTimeRemaining }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Complete -->
        <div v-if="uploadComplete" class="mt-4">
            <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-green-800">Upload Successful!</p>
                        <p class="text-sm text-green-600">Video uploaded and processed successfully</p>
                    </div>
                </div>
                <button
                    @click="reset"
                    class="mt-3 text-sm text-green-700 hover:text-green-800 font-medium"
                >
                    Upload Another Video
                </button>
            </div>
        </div>

        <!-- Error Display -->
        <div v-if="error" class="mt-4">
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.126 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div>
                        <p class="font-medium text-red-800">Upload Failed</p>
                        <p class="text-sm text-red-600">{{ error }}</p>
                    </div>
                </div>
                <button
                    @click="retry"
                    class="mt-2 text-sm text-red-700 hover:text-red-800 font-medium"
                >
                    Try Again
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'

const props = defineProps({
    maxFileSize: {
        type: Number,
        default: 1024 * 1024 * 1024 // 1GB
    },
    chunkSize: {
        type: Number,
        default: 1024 * 1024 * 5 // 5MB chunks (larger = fewer requests = more reliable)
    }
})

const emit = defineEmits(['upload-success', 'upload-error', 'cancel'])

// Reactive data
const selectedFile = ref(null)
const compressedFile = ref(null)
const isCompressing = ref(false)
const compressionProgress = ref(0)
const isUploading = ref(false)
const uploadProgress = ref(0)
const uploadedChunks = ref(0)
const totalChunks = ref(0)
const uploadComplete = ref(false)
const error = ref('')
const uploadId = ref('')
const enableCompression = ref(false) // Disabled by default - browser compression often crashes

// Upload speed tracking
const uploadStartTime = ref(null)
const uploadedBytes = ref(0)
const totalBytes = ref(0)
const uploadSpeed = ref('Calculating...')
const estimatedTimeRemaining = ref('Calculating...')

// Note: Compression is disabled by default because browser-based video compression
// using Canvas/MediaRecorder is unreliable and often crashes browsers with large files.
// Enable only for small files if needed.

// File handling
const handleFileSelect = (event) => {
    const file = event.target.files[0]
    if (file) {
        validateAndSetFile(file)
    }
}

const handleFileDrop = (event) => {
    const file = event.dataTransfer.files[0]
    if (file) {
        validateAndSetFile(file)
    }
}

const validateAndSetFile = (file) => {
    // Check file type
    if (!file.type.startsWith('video/')) {
        error.value = 'Please select a valid video file'
        return
    }

    // Check file size
    if (file.size > props.maxFileSize) {
        error.value = `File size exceeds ${formatFileSize(props.maxFileSize)} limit`
        return
    }

    selectedFile.value = file
    error.value = ''
    uploadComplete.value = false
}

const clearFile = () => {
    selectedFile.value = null
    compressedFile.value = null
    error.value = ''
    uploadComplete.value = false
}

// Video compression using canvas and MediaRecorder
const compressVideo = async (file) => {
    if (!enableCompression.value) {
        return file
    }

    return new Promise((resolve, reject) => {
        const video = document.createElement('video')
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')
        
        video.preload = 'metadata'
        video.onloadedmetadata = () => {
            // Set canvas size (reduce for compression)
            const scale = Math.min(1, Math.min(1280 / video.videoWidth, 720 / video.videoHeight))
            canvas.width = video.videoWidth * scale
            canvas.height = video.videoHeight * scale
            
            // Setup MediaRecorder
            const stream = canvas.captureStream(30) // 30 FPS
            const mediaRecorder = new MediaRecorder(stream, {
                mimeType: 'video/webm;codecs=vp9',
                videoBitsPerSecond: 1000000 // 1Mbps
            })
            
            const chunks = []
            let currentTime = 0
            const duration = video.duration
            
            mediaRecorder.ondataavailable = (e) => {
                chunks.push(e.data)
            }
            
            mediaRecorder.onstop = () => {
                const compressedBlob = new Blob(chunks, { type: 'video/webm' })
                const compressedFile = new File([compressedBlob], 
                    file.name.replace(/\.[^/.]+$/, '.webm'), 
                    { type: 'video/webm' }
                )
                resolve(compressedFile)
            }
            
            // Start recording
            mediaRecorder.start()
            
            // Draw frames
            const drawFrame = () => {
                if (currentTime < duration) {
                    video.currentTime = currentTime
                    video.onseeked = () => {
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
                        currentTime += 1/30 // 30 FPS
                        compressionProgress.value = (currentTime / duration) * 100
                        
                        if (currentTime < duration) {
                            requestAnimationFrame(drawFrame)
                        } else {
                            mediaRecorder.stop()
                        }
                    }
                } else {
                    mediaRecorder.stop()
                }
            }
            
            video.currentTime = 0
            video.onseeked = () => {
                drawFrame()
            }
        }
        
        video.onerror = () => {
            reject(new Error('Video compression failed'))
        }
        
        video.src = URL.createObjectURL(file)
    })
}

// Upload functions
const startUpload = async () => {
    if (!selectedFile.value) return

    try {
        error.value = ''
        isUploading.value = true
        uploadStartTime.value = Date.now()
        totalBytes.value = selectedFile.value.size
        uploadedBytes.value = 0

        // Initialize upload with original file (no compression - it crashes browsers)
        await initiateChunkedUpload(selectedFile.value)

    } catch (err) {
        console.error('Upload error:', err)
        error.value = err.message || 'Upload failed. Please try again.'
        isUploading.value = false
        emit('upload-error', err.message)
    }
}

const initiateChunkedUpload = async (file) => {
    const chunks = Math.ceil(file.size / props.chunkSize)
    totalChunks.value = chunks

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (!csrfToken) {
        throw new Error('CSRF token not found. Please refresh the page.')
    }

    // Initialize upload session
    const initResponse = await fetch('/api/upload/initiate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            fileName: file.name,
            fileSize: file.size,
            chunkSize: props.chunkSize,
            totalChunks: chunks
        })
    })

    if (!initResponse.ok) {
        if (initResponse.status === 401) {
            throw new Error('Session expired. Please refresh the page and try again.')
        }
        if (initResponse.status === 419) {
            throw new Error('CSRF token mismatch. Please refresh the page.')
        }
        throw new Error(`Server error: ${initResponse.status}`)
    }

    const initData = await initResponse.json()
    if (!initData.success) {
        throw new Error(initData.error || 'Failed to initialize upload')
    }

    uploadId.value = initData.uploadId

    // Upload chunks concurrently (max 3 at a time for better performance)
    await uploadChunksConcurrent(file, chunks)
}

const uploadChunksConcurrent = async (file, totalChunksCount) => {
    // Upload chunks sequentially to avoid race conditions
    for (let i = 0; i < totalChunksCount; i++) {
        await uploadChunk(file, i)
    }

    // Small delay to ensure all chunks are written to disk
    await new Promise(resolve => setTimeout(resolve, 500))

    // Finalize upload
    await finalizeUpload()
}

const uploadChunk = async (file, chunkIndex) => {
    const start = chunkIndex * props.chunkSize
    const end = Math.min(start + props.chunkSize, file.size)
    const chunkBlob = file.slice(start, end)

    // Convert Blob to File so Laravel recognizes it as a file upload
    const chunkFile = new File([chunkBlob], `chunk_${chunkIndex}`, { type: file.type || 'application/octet-stream' })

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    const formData = new FormData()
    formData.append('chunk', chunkFile, `chunk_${chunkIndex}`)
    formData.append('chunkNumber', String(chunkIndex))
    formData.append('totalChunks', String(totalChunks.value))

    const response = await fetch(`/api/upload/${uploadId.value}/chunk`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: formData
    })

    if (!response.ok) {
        if (response.status === 401 || response.status === 419) {
            throw new Error('Session expired. Please refresh the page.')
        }
        throw new Error(`Chunk ${chunkIndex} upload failed: ${response.status}`)
    }

    const data = await response.json()
    if (!data.success) {
        throw new Error(data.error || 'Chunk upload failed')
    }

    // Update progress
    uploadedChunks.value = data.uploadedChunks
    uploadProgress.value = data.progress
    uploadedBytes.value = (uploadedChunks.value / totalChunks.value) * totalBytes.value

    // Update speed calculation
    updateUploadSpeed()
}

const finalizeUpload = async () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    const response = await fetch(`/api/upload/${uploadId.value}/finalize`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
    })

    if (!response.ok) {
        if (response.status === 401 || response.status === 419) {
            throw new Error('Session expired. Please refresh the page.')
        }
        throw new Error(`Finalization failed: ${response.status}`)
    }

    const data = await response.json()
    if (!data.success) {
        throw new Error(data.error || 'Upload finalization failed')
    }

    isUploading.value = false
    uploadComplete.value = true

    // Notify parent component via emit
    emit('upload-success', {
        filePath: data.filePath,
        fileName: selectedFile.value.name,
        fileSize: data.fileSize,
        duration: data.duration,
        storageDisk: data.storage_disk || 'public'
    })
}

const cancelUpload = async () => {
    if (uploadId.value) {
        try {
            await fetch(`/api/upload/${uploadId.value}/cancel`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            })
        } catch (err) {
            console.error('Cancel upload error:', err)
        }
    }

    reset()
    emit('cancel')
}

const retry = () => {
    error.value = ''
    uploadProgress.value = 0
    uploadedChunks.value = 0
    isUploading.value = false
    startUpload()
}

const reset = () => {
    selectedFile.value = null
    compressedFile.value = null
    isCompressing.value = false
    compressionProgress.value = 0
    isUploading.value = false
    uploadProgress.value = 0
    uploadedChunks.value = 0
    totalChunks.value = 0
    uploadComplete.value = false
    error.value = ''
    uploadId.value = ''
    uploadedBytes.value = 0
    totalBytes.value = 0
}

// Utility functions
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const updateUploadSpeed = () => {
    if (!uploadStartTime.value) return
    
    const elapsed = (Date.now() - uploadStartTime.value) / 1000 // seconds
    const speed = uploadedBytes.value / elapsed // bytes per second
    
    uploadSpeed.value = `${formatFileSize(speed)}/s`
    
    const remainingBytes = totalBytes.value - uploadedBytes.value
    const remainingTime = remainingBytes / speed
    
    if (isFinite(remainingTime)) {
        if (remainingTime < 60) {
            estimatedTimeRemaining.value = `${Math.round(remainingTime)}s`
        } else if (remainingTime < 3600) {
            estimatedTimeRemaining.value = `${Math.round(remainingTime / 60)}m ${Math.round(remainingTime % 60)}s`
        } else {
            const hours = Math.floor(remainingTime / 3600)
            const minutes = Math.floor((remainingTime % 3600) / 60)
            estimatedTimeRemaining.value = `${hours}h ${minutes}m`
        }
    }
}

// Cleanup on unmount
onUnmounted(() => {
    if (uploadId.value && isUploading.value) {
        cancelUpload()
    }
})
</script>

<style scoped>
.chunked-upload-container {
    @apply w-full;
}
</style>