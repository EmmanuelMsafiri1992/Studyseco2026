<template>
  <Head title="Extend Access" />
  
  <DashboardLayout title="Extend Access" subtitle="Extend your subject access period">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Access Status -->
            <div class="mb-8">
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Access Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Days Remaining</p>
                    <p :class="['text-2xl font-bold', daysRemaining > 7 ? 'text-green-600' : daysRemaining > 0 ? 'text-yellow-600' : 'text-red-600']">
                      {{ daysRemaining > 0 ? daysRemaining : 0 }}
                    </p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Access Expires</p>
                    <p class="text-lg font-medium text-gray-900">
                      {{ formatDate(enrollment.access_expires_at) }}
                    </p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Status</p>
                    <span :class="['px-3 py-1 rounded-full text-sm font-medium', accessExpired ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800']">
                      {{ accessExpired ? 'Expired' : 'Active' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Extension Form -->
            <form @submit.prevent="submitExtension" v-if="!processing">
              <div class="space-y-6">
                <!-- Extension Duration Selection -->
                <div>
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Choose Extension Duration</h3>
                  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div v-for="(price, months) in extensionPricing" :key="months"
                         :class="['border-2 rounded-lg p-4 cursor-pointer transition-all hover:shadow-md',
                                  form.extension_months == months ? 'border-blue-500 bg-blue-50 shadow-lg' : 'border-gray-200 hover:border-blue-300']"
                         @click="form.extension_months = parseInt(months)">
                      <div class="text-center">
                        <p class="text-lg font-bold text-gray-900">{{ months }} Month{{ months > 1 ? 's' : '' }}</p>
                        <p :class="['text-sm font-medium', form.extension_months == months ? 'text-blue-600' : 'text-gray-600']">
                          {{ formatPrice(price, enrollment.currency) }}
                        </p>
                        <div v-if="months == 6" class="mt-1">
                          <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">10% OFF</span>
                        </div>
                        <div v-if="months == 12" class="mt-1">
                          <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">2 Months FREE</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <InputError :message="form.errors.extension_months" class="mt-2" />
                </div>

                <!-- Payment Method Selection -->
                <div v-if="form.extension_months">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Select Payment Method</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="method in paymentMethods" :key="method.id"
                         :class="['border-2 rounded-lg p-4 cursor-pointer transition-all hover:shadow-md flex items-center',
                                  form.payment_method_id == method.id ? 'border-blue-500 bg-blue-50 shadow-lg' : 'border-gray-200 hover:border-blue-300']"
                         @click="form.payment_method_id = method.id">
                      <div class="flex-1">
                        <h4 class="font-medium text-gray-900">{{ method.name }}</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ method.description }}</p>
                        <p class="text-sm font-medium text-blue-600 mt-1">{{ method.account_details }}</p>
                      </div>
                    </div>
                  </div>
                  <InputError :message="form.errors.payment_method_id" class="mt-2" />
                </div>

                <!-- Payment Details -->
                <div v-if="form.payment_method_id && form.extension_months" class="space-y-4">
                  <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-medium text-yellow-800 mb-2">Payment Instructions</h4>
                    <p class="text-sm text-yellow-700">
                      Total Amount: <strong>{{ formatPrice(extensionPricing[form.extension_months], enrollment.currency) }}</strong>
                    </p>
                    <p class="text-sm text-yellow-700 mt-1">
                      Make payment using the selected method above, then provide your payment reference and upload proof below.
                    </p>
                  </div>

                  <div>
                    <InputLabel for="payment_reference" value="Payment Reference Number" />
                    <TextInput
                      id="payment_reference"
                      v-model="form.payment_reference"
                      type="text"
                      class="mt-1 block w-full"
                      placeholder="Enter your transaction reference number"
                      required
                    />
                    <InputError :message="form.errors.payment_reference" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="payment_proof" value="Payment Proof" />
                    <input
                      id="payment_proof"
                      ref="fileInput"
                      type="file"
                      @change="handleFileChange"
                      accept=".jpg,.jpeg,.png,.pdf"
                      class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                      required
                    />
                    <p class="mt-1 text-sm text-gray-600">Upload screenshot or receipt (JPG, PNG, PDF max 5MB)</p>
                    <InputError :message="form.errors.payment_proof" class="mt-2" />
                  </div>

                  <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                    <SecondaryButton @click="resetForm">
                      Reset
                    </SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                      <div v-if="form.processing" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                      </div>
                      <span v-else>Submit Extension Request</span>
                    </PrimaryButton>
                  </div>
                </div>
              </div>
            </form>

            <!-- Processing State -->
            <div v-if="processing" class="text-center py-8">
              <div class="inline-flex items-center px-6 py-3 bg-blue-50 border border-blue-200 rounded-lg">
                <svg class="animate-spin -ml-1 mr-3 h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-blue-800 font-medium">Processing your extension request...</span>
              </div>
              <p class="text-gray-600 mt-3">We will verify your payment within 24 hours and notify you via email and SMS.</p>
            </div>
          </div>
        </div>

        <!-- Previous Extension Requests -->
        <div class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-lg" v-if="enrollment.payments && enrollment.payments.length > 0">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Previous Extension Requests</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="payment in extensionPayments" :key="payment.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(payment.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ payment.extension_months }} Month{{ payment.extension_months > 1 ? 's' : '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatPrice(payment.amount, payment.currency) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                     payment.status === 'verified' ? 'bg-green-100 text-green-800' :
                                     payment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800']">
                        {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import TextInput from '@/components/TextInput.vue'
import InputLabel from '@/components/InputLabel.vue'
import InputError from '@/components/InputError.vue'

const props = defineProps({
  enrollment: Object,
  extensionPricing: Object,
  paymentMethods: Array,
  daysRemaining: Number,
  accessExpired: Boolean
})

const processing = ref(false)
const fileInput = ref(null)

const form = useForm({
  extension_months: null,
  payment_method_id: null,
  payment_reference: '',
  payment_proof: null
})

const extensionPayments = computed(() => {
  return props.enrollment.payments ? props.enrollment.payments.filter(p => p.payment_type === 'extension') : []
})

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatPrice = (amount, currency) => {
  const formatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: currency === 'MWK' ? 0 : 2
  })
  return formatter.format(amount)
}

const handleFileChange = (event) => {
  form.payment_proof = event.target.files[0]
}

const resetForm = () => {
  form.reset()
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const submitExtension = () => {
  processing.value = true

  form.transform((data) => ({
    ...data,
    _method: 'POST'
  })).post(route('student.extension.store'), {
    forceFormData: true,
    onSuccess: () => {
      processing.value = true // Keep processing state to show success message
      resetForm()
    },
    onError: () => {
      processing.value = false
    }
  })
}
</script>
