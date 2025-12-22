<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  headers: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  mobileBreakpoint: {
    type: Number,
    default: 768
  },
  showSearch: {
    type: Boolean,
    default: true
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  emptyMessage: {
    type: String,
    default: 'No data found'
  },
  cardMode: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['rowClick', 'actionClick'])

const isMobile = ref(false)
const searchQuery = ref('')

// Check if mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < props.mobileBreakpoint
}

// Filtered data based on search
const filteredData = computed(() => {
  if (!searchQuery.value) return props.data
  
  const query = searchQuery.value.toLowerCase()
  return props.data.filter(row => {
    return Object.values(row).some(value => 
      String(value).toLowerCase().includes(query)
    )
  })
})

// Handle row click
const handleRowClick = (row, index) => {
  emit('rowClick', row, index)
}

// Handle action click
const handleActionClick = (action, row, index) => {
  emit('actionClick', action, row, index)
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})
</script>

<template>
  <div class="responsive-table-container">
    <!-- Search -->
    <div v-if="showSearch" class="mb-4">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        >
      </div>
    </div>

    <!-- Desktop Table View -->
    <div v-if="!isMobile" class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full">
        <thead class="bg-slate-50">
          <tr>
            <th 
              v-for="header in headers" 
              :key="header.key"
              class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
            >
              {{ header.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <tr 
            v-for="(row, index) in filteredData" 
            :key="index"
            @click="handleRowClick(row, index)"
            class="hover:bg-slate-50 cursor-pointer transition-colors"
          >
            <td 
              v-for="header in headers" 
              :key="header.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"
            >
              <slot :name="header.key" :row="row" :index="index">
                {{ row[header.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="filteredData.length === 0" class="text-center py-12">
        <div class="text-slate-400 text-4xl mb-4">📄</div>
        <p class="text-slate-500">{{ emptyMessage }}</p>
      </div>
    </div>

    <!-- Mobile Card View -->
    <div v-else-if="cardMode" class="space-y-4">
      <div 
        v-for="(row, index) in filteredData" 
        :key="index"
        @click="handleRowClick(row, index)"
        class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 cursor-pointer hover:shadow-md transition-shadow"
      >
        <slot name="mobile-card" :row="row" :index="index">
          <div class="space-y-2">
            <div v-for="header in headers" :key="header.key" class="flex justify-between items-start">
              <span class="text-sm font-medium text-slate-500">{{ header.label }}:</span>
              <span class="text-sm text-slate-900 text-right ml-2">
                <slot :name="header.key" :row="row" :index="index">
                  {{ row[header.key] }}
                </slot>
              </span>
            </div>
          </div>
        </slot>
      </div>
      
      <div v-if="filteredData.length === 0" class="text-center py-12">
        <div class="text-slate-400 text-4xl mb-4">📄</div>
        <p class="text-slate-500">{{ emptyMessage }}</p>
      </div>
    </div>

    <!-- Mobile List View (Alternative) -->
    <div v-else class="bg-white rounded-lg shadow divide-y divide-slate-200">
      <div 
        v-for="(row, index) in filteredData" 
        :key="index"
        @click="handleRowClick(row, index)"
        class="p-4 cursor-pointer hover:bg-slate-50 transition-colors"
      >
        <slot name="mobile-row" :row="row" :index="index">
          <div class="space-y-1">
            <div v-for="header in headers.slice(0, 2)" :key="header.key">
              <span class="text-sm font-medium text-slate-900">
                <slot :name="header.key" :row="row" :index="index">
                  {{ row[header.key] }}
                </slot>
              </span>
            </div>
            <div class="text-xs text-slate-500" v-if="headers.length > 2">
              {{ headers.slice(2).map(h => row[h.key]).join(' • ') }}
            </div>
          </div>
        </slot>
      </div>
      
      <div v-if="filteredData.length === 0" class="text-center py-12">
        <div class="text-slate-400 text-4xl mb-4">📄</div>
        <p class="text-slate-500">{{ emptyMessage }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.responsive-table-container {
  @apply w-full;
}

@media (max-width: 768px) {
  .responsive-table-container :deep(button),
  .responsive-table-container :deep(a) {
    @apply min-h-11 min-w-11 px-3 py-2;
  }
}</style>