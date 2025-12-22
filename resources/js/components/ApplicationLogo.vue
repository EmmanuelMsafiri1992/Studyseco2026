<template>
    <div class="flex items-center">
        <!-- StudySeco Educational Logo -->
        <div class="flex items-center">
            <svg :class="$attrs.class || 'h-12 w-12'" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <!-- Graduation cap -->
                <defs>
                    <linearGradient id="studysecoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#3b82f6"/>
                        <stop offset="100%" style="stop-color:#1e40af"/>
                    </linearGradient>
                </defs>
                
                <!-- Graduation cap base -->
                <path d="M50 25 L20 35 L50 45 L80 35 Z" fill="url(#studysecoGrad)"/>
                
                <!-- Graduation cap top -->
                <path d="M20 35 L20 42 L50 52 L80 42 L80 35" fill="#1e40af" opacity="0.8"/>
                
                <!-- Tassel -->
                <circle cx="50" cy="30" r="2" fill="#fbbf24"/>
                <line x1="50" y1="32" x2="52" y2="38" stroke="#fbbf24" stroke-width="1"/>
                
                <!-- Books stack -->
                <rect x="35" y="55" width="30" height="4" fill="#ef4444" rx="2"/>
                <rect x="37" y="60" width="26" height="4" fill="#10b981" rx="2"/>
                <rect x="39" y="65" width="22" height="4" fill="#8b5cf6" rx="2"/>
                
                <!-- Globe/World -->
                <circle cx="25" cy="70" r="8" fill="#06b6d4" opacity="0.7"/>
                <path d="M20 65 Q25 68 30 65" stroke="#fff" stroke-width="1" fill="none"/>
                <path d="M20 70 Q25 73 30 70" stroke="#fff" stroke-width="1" fill="none"/>
                <path d="M20 75 Q25 78 30 75" stroke="#fff" stroke-width="1" fill="none"/>
                
                <!-- Digital device -->
                <rect x="67" y="62" width="16" height="12" fill="#374151" rx="2"/>
                <rect x="69" y="64" width="12" height="8" fill="#60a5fa" rx="1"/>
                <rect x="73" y="75" width="4" height="2" fill="#374151" rx="1"/>
            </svg>
            
            <!-- Brand text when not minimal -->
            <div v-if="showText" class="ml-3">
                <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
                    StudySeco
                </div>
                <div class="text-xs text-gray-600 font-medium -mt-1">
                    Education Excellence
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    showText: {
        type: Boolean,
        default: false
    }
})
</script>
