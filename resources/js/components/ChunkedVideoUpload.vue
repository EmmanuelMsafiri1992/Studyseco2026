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
                <div class="flex space-x-2">
                    <label class="flex items-center space-x-2">
                        <input v-model="enableCompression" type="checkbox" class="rounded">
                        <span class="text-sm text-slate-600">Compress (faster upload)</span>
                    </label>
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

        <!-- Compression Progress -->
        <div v-if="isCompressing" class="mt-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800">Compressing Video...</p>
                        <p class="text-sm text-slate-600">Optimizing for faster upload</p>
                    </div>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" :style="{ width: compressionProgress + '%' }"></div>
                </div>
                <p class="text-xs text-slate-500 mt-2">{{ compressionProgress.toFixed(1) }}% complete</p>
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
    onUploadComplete: Function,
    maxFileSize: {
        type: Number,
        default: 1024 * 1024 * 1024 // 1GB
    },
    chunkSize: {
        type: Number,
        default: 1024 * 1024 * 2 // 2MB chunks
    }
})

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
const enableCompression = ref(true)

// Upload speed tracking
const uploadStartTime = ref(null)
const uploadedBytes = ref(0)
const totalBytes = ref(0)
const uploadSpeed = ref('Calculating...')
const estimatedTimeRemaining = ref('Calculating...')

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
        isCompressing.value = enableCompression.value
        compressionProgress.value = 0
        
        // Compress video if enabled
        let fileToUpload = selectedFile.value
        if (enableCompression.value) {
            try {
                fileToUpload = await compressVideo(selectedFile.value)
                compressedFile.value = fileToUpload
            } catch (err) {
                console.warn('Compression failed, uploading original file:', err)
                fileToUpload = selectedFile.value
            }
        }
        
        isCompressing.value = false
        isUploading.value = true
        uploadStartTime.value = Date.now()
        totalBytes.value = fileToUpload.size
        uploadedBytes.value = 0

        // Initialize upload
        await initiateChunkedUpload(fileToUpload)
        
    } catch (err) {
        error.value = err.message
        isUploading.value = false
        isCompressing.value = false
    }
}

const initiateChunkedUpload = async (file) => {
    const chunks = Math.ceil(file.size / props.chunkSize)
    totalChunks.value = chunks
    
    // Initialize upload session
    const initResponse = await fetch('/api/upload/initiate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            fileName: file.name,
            fileSize: file.size,
            chunkSize: props.chunkSize,
            totalChunks: chunks
        })
    })
    
    const initData = await initResponse.json()
    if (!initData.success) {
        throw new Error(initData.error || 'Failed to initialize upload')
    }
    
    uploadId.value = initData.uploadId
    
    // Upload chunks concurrently (max 3 at a time for better performance)
    await uploadChunksConcurrent(file, chunks)
}

const uploadChunksConcurrent = async (file, totalChunksCount) => {
    const concurrentUploads = 3
    const chunks = []
    
    // Create chunk upload promises
    for (let i = 0; i < totalChunksCount; i++) {
        chunks.push(() => uploadChunk(file, i))
    }
    
    // Execute chunks with concurrency limit
    let index = 0
    const executing = new Set()
    
    while (index < chunks.length || executing.size > 0) {
        while (executing.size < concurrentUploads && index < chunks.length) {
            const promise = chunks[index++]().then(() => {
                executing.delete(promise)
            }).catch((err) => {
                executing.delete(promise)
                throw err
            })
            executing.add(promise)
        }
        
        if (executing.size > 0) {
            await Promise.race(executing)
        }
    }
    
    // Finalize upload
    await finalizeUpload()
}

const uploadChunk = async (file, chunkIndex) => {
    const start = chunkIndex * props.chunkSize
    const end = Math.min(start + props.chunkSize, file.size)
    const chunk = file.slice(start, end)
    
    const formData = new FormData()
    formData.append('chunk', chunk)
    formData.append('chunkNumber', chunkIndex)
    formData.append('totalChunks', totalChunks.value)
    
    const response = await fetch(`/api/upload/${uploadId.value}/chunk`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    
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
    const response = await fetch(`/api/upload/${uploadId.value}/finalize`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    
    const data = await response.json()
    if (!data.success) {
        throw new Error(data.error || 'Upload finalization failed')
    }
    
    isUploading.value = false
    uploadComplete.value = true
    
    // Notify parent component
    if (props.onUploadComplete) {
        props.onUploadComplete({
            filePath: data.filePath,
            fileName: selectedFile.value.name,
            fileSize: data.fileSize,
            duration: data.duration
        })
    }
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