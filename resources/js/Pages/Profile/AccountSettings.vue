<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted, nextTick, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    enrollment: Object,
    enrollmentStatus: Object,
});
const user = props.auth?.user || { name: 'Guest', role: 'guest', profile_photo_url: null };

const form = useForm({
    name: user.name || '',
    email: user.email || '',
    password: '',
    profile_photo: null,
    _method: 'patch',
});

// Clear any existing validation errors if fields are already populated
if (form.name && form.email) {
    form.clearErrors(['name', 'email']);
}

// Watch for changes in form fields and clear respective errors
watch(() => form.name, (newValue) => {
    if (newValue && form.errors.name) {
        form.clearErrors('name');
    }
});

watch(() => form.email, (newValue) => {
    if (newValue && form.errors.email) {
        form.clearErrors('email');
    }
});

// Clear errors on component mount if fields are populated
onMounted(async () => {
    // Wait for component to fully mount
    await nextTick();
    
    // Clear all validation errors when component loads
    form.clearErrors();
    
    // Ensure form fields are populated from user data
    if (user.name && !form.name) {
        form.name = user.name;
    }
    if (user.email && !form.email) {
        form.email = user.email;
    }
    
    // Clear errors again after a short delay to handle any async error setting
    setTimeout(() => {
        if (form.name && form.email) {
            form.clearErrors(['name', 'email']);
        }
    }, 100);
});

// Computed properties to determine if errors should be shown
const shouldShowNameError = computed(() => {
    return form.errors.name && (!form.name || form.name.trim() === '');
});

const shouldShowEmailError = computed(() => {
    return form.errors.email && (!form.email || form.email.trim() === '');
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    console.log('File selected:', file ? {
        name: file.name,
        size: file.size,
        type: file.type,
        lastModified: file.lastModified
    } : 'No file');
    
    if (file) {
        // Basic validation
        const maxSize = 2 * 1024 * 1024; // 2MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        
        if (file.size > maxSize) {
            alert('File size must be less than 2MB');
            return;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, GIF)');
            return;
        }
        
        form.profile_photo = file;
        console.log('File set to form.profile_photo:', form.profile_photo);
    } else {
        form.profile_photo = null;
    }
};

const updateProfile = () => {
    // Always ensure name and email are populated from current user data
    const name = user.name || form.name || '';
    const email = user.email || form.email || '';
    
    // Debug: Log the form data being sent
    console.log('Form data before submission:', {
        name: name,
        email: email,
        password: form.password ? '***' : '',
        profile_photo: form.profile_photo ? {
            name: form.profile_photo.name,
            size: form.profile_photo.size,
            type: form.profile_photo.type,
            lastModified: form.profile_photo.lastModified
        } : null,
        hasFile: !!form.profile_photo
    });
    
    // Clear any existing errors before submission
    form.clearErrors();
    
    // Create a new form with all data explicitly set
    const submissionData = {
        name: name,
        email: email,
        password: form.password || '',
        profile_photo: form.profile_photo
    };
    
    // Update the form fields to ensure they have the correct values
    form.name = name;
    form.email = email;
    form._method = 'patch';  // Add method spoofing to form data
    
    form.post(route('profile.update'), {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => {
            // Clear errors when starting the request
            form.clearErrors();
            console.log('Starting profile update request with form data:', {
                name: form.name,
                email: form.email,
                password: form.password ? '***' : '',
                profile_photo: form.profile_photo ? 'File selected' : null
            });
        },
        onSuccess: (response) => {
            console.log('Profile update successful', response);
            // Clear password field after successful update
            form.reset('password');
            // Clear all errors after success
            form.clearErrors();
            // Force page reload to show updated profile photo
            window.location.reload();
        },
        onError: (errors) => {
            console.error('Profile update errors:', errors);
            // Only show errors if they are actually relevant
            // If name and email are populated, clear those specific errors
            if (name && form.errors.name) {
                form.clearErrors('name');
            }
            if (email && form.errors.email) {
                form.clearErrors('email');
            }
        },
        onFinish: () => {
            console.log('Profile update request finished');
            // Additional cleanup after request finishes
            if (name && email) {
                form.clearErrors(['name', 'email']);
            }
        }
    });
};

const showCountryUpdateModal = ref(false)

const countryUpdateForm = ref({
    new_country: '',
    new_phone: '',
    reason: '',
    verification_document: null
})

const upgradeToPremium = () => {
    // Redirect to payment page with upgrade flag
    window.location.href = '/payments/create?upgrade=true';
}

const getPaymentMethodsForCountry = (country) => {
    const methods = {
        // Countries with specific payment methods
        'malawi': 'TNM Mpamba, Airtel Money, Bank Transfer',
        'south africa': 'Mukuru, Hello Paisa',
        
        // All other countries get international methods
        'international': 'WorldRemit, Remitly, Western Union, MoneyGram, PayPal'
    }
    
    const countryKey = country?.toLowerCase().replace(/\s+/g, ' ');
    
    // For Malawi and South Africa, show their specific methods
    if (countryKey === 'malawi' || countryKey === 'south africa') {
        return methods[countryKey];
    }
    
    // For all other countries, show international methods
    return methods['international'];
}

const submitCountryUpdate = () => {
    if (!countryUpdateForm.value.new_country || !countryUpdateForm.value.new_phone || !countryUpdateForm.value.reason) {
        alert('Please fill in all required fields')
        return
    }

    const formData = new FormData()
    Object.keys(countryUpdateForm.value).forEach(key => {
        if (countryUpdateForm.value[key]) {
            formData.append(key, countryUpdateForm.value[key])
        }
    })

    fetch('/profile/country-update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showCountryUpdateModal.value = false
            alert('Country update request submitted! Admin will review within 24-48 hours.')
            // Reset form
            countryUpdateForm.value = {
                new_country: '',
                new_phone: '',
                reason: '',
                verification_document: null
            }
        } else {
            alert(data.message || 'Error submitting request')
        }
    })
    .catch(error => {
        console.error('Error:', error)
        alert('Error submitting request. Please try again.')
    })
}
</script>

<template>
    <Head title="Account Settings" />
    
    <DashboardLayout title="Account Settings" subtitle="Update your profile information">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-8">
                <form @submit.prevent="updateProfile">
                    <div class="space-y-6">
                        <!-- Profile Photo Section -->
                        <div class="flex items-center space-x-6">
                            <div class="shrink-0">
                                <img 
                                    :src="user.profile_photo_url ? `/storage/${user.profile_photo_url}` : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face&facepad=2&bg=white'" 
                                    :alt="user.name" 
                                    class="h-20 w-20 rounded-full object-cover ring-4 ring-white shadow-lg"
                                >
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Profile Photo</h3>
                                <input 
                                    type="file" 
                                    @change="handleFileSelect" 
                                    accept="image/*" 
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                >
                                <p class="mt-1 text-sm text-gray-600">JPG, PNG, GIF up to 2MB</p>
                                <div v-if="form.errors.profile_photo" class="mt-1 text-sm text-red-600">{{ form.errors.profile_photo }}</div>
                            </div>
                        </div>

                        <hr class="border-gray-200">

                        <!-- Personal Information -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter your full name"
                                >
                                <div v-if="shouldShowNameError" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Enter your email"
                                >
                                <div v-if="shouldShowEmailError" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password (optional)</label>
                            <input 
                                v-model="form.password" 
                                type="password" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Leave blank to keep current password"
                            >
                            <p class="mt-1 text-sm text-gray-600">Leave blank to keep your current password</p>
                            <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                        </div>

                        <!-- Country Information and Update -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Country</label>
                                <div class="px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-900 capitalize">{{ enrollmentStatus?.country || user.country || 'Not Set' }}</p>
                                        <p class="text-xs text-gray-500">Registered country for payment methods</p>
                                    </div>
                                    <button 
                                        v-if="enrollmentStatus?.country"
                                        @click="showCountryUpdateModal = true"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                    >
                                        Update
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Available Payment Methods</label>
                                <div class="px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg">
                                    <p class="text-gray-900">{{ getPaymentMethodsForCountry(enrollmentStatus?.country) }}</p>
                                    <p class="text-xs text-gray-500">Based on your registered country</p>
                                </div>
                            </div>
                        </div>

                        <!-- User Role Display -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                            <div class="px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg">
                                <span class="font-medium text-gray-900 capitalize">{{ user.role }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">Your account type cannot be changed</p>
                        </div>

                        <!-- Enrollment Status & Upgrade Section (Students Only) -->
                        <div v-if="user.role === 'student'" class="space-y-6">
                            <hr class="border-gray-200">
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Enrollment Status</h3>
                                
                                <!-- Current Status Display -->
                                <div v-if="enrollmentStatus" class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900">
                                                {{ enrollmentStatus.is_trial ? 'Free Trial Account' : 'Premium Account' }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                {{ enrollmentStatus.is_trial ? '7 Days Free Trial' : 'Full Access Account' }}
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span v-if="enrollmentStatus.is_trial" class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                                🆓 Trial
                                            </span>
                                            <span v-else class="px-3 py-1 bg-purple-100 text-purple-800 text-sm font-medium rounded-full">
                                                ⭐ Premium
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-white rounded-lg p-4">
                                            <div class="text-sm text-gray-600">Subjects Enrolled</div>
                                            <div class="text-xl font-bold text-gray-900">{{ enrollmentStatus.subjects_count }}</div>
                                        </div>
                                        <div class="bg-white rounded-lg p-4">
                                            <div class="text-sm text-gray-600">Access Remaining</div>
                                            <div class="text-xl font-bold text-gray-900">{{ enrollmentStatus.access_days_remaining }} days</div>
                                        </div>
                                        <div class="bg-white rounded-lg p-4">
                                            <div class="text-sm text-gray-600">Status</div>
                                            <div class="text-xl font-bold capitalize" :class="{
                                                'text-green-600': enrollmentStatus.status === 'approved',
                                                'text-yellow-600': enrollmentStatus.status === 'pending',
                                                'text-red-600': enrollmentStatus.status === 'rejected'
                                            }">{{ enrollmentStatus.status }}</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Upgrade Button for Trial Users -->
                                    <div v-if="enrollmentStatus.is_trial" class="mt-6 pt-4 border-t border-blue-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h5 class="font-semibold text-gray-900 mb-1">Ready to upgrade?</h5>
                                                <p class="text-sm text-gray-600">Get unlimited access to all subjects and features</p>
                                            </div>
                                            <button 
                                                @click="upgradeToPremium" 
                                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200"
                                            >
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                                </svg>
                                                Upgrade to Premium
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- No Enrollment Message -->
                                <div v-else class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Active Enrollment</h4>
                                    <p class="text-gray-600 mb-4">You haven't enrolled in any subjects yet.</p>
                                    <a 
                                        href="/student/enrollment" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Start Learning Journey
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 flex justify-end">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Updating...' : 'Update Profile' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Country Update Modal -->
        <div v-if="showCountryUpdateModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" @click="showCountryUpdateModal = false">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Update Country</h2>
                    <button @click="showCountryUpdateModal = false" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800">Country Change Request</p>
                                <p class="text-xs text-amber-700 mt-1">Requires admin approval and phone verification. Process takes 24-48 hours.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Country</label>
                        <select v-model="countryUpdateForm.new_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select your new country</option>
                            
                            <optgroup label="🌍 Southern Africa">
                                <option value="malawi">Malawi</option>
                                <option value="south_africa">South Africa</option>
                                <option value="botswana">Botswana</option>
                                <option value="zimbabwe">Zimbabwe</option>
                                <option value="zambia">Zambia</option>
                                <option value="namibia">Namibia</option>
                                <option value="lesotho">Lesotho</option>
                                <option value="eswatini">Eswatini (Swaziland)</option>
                                <option value="mozambique">Mozambique</option>
                            </optgroup>
                            
                            <optgroup label="🌍 East Africa">
                                <option value="kenya">Kenya</option>
                                <option value="tanzania">Tanzania</option>
                                <option value="uganda">Uganda</option>
                                <option value="rwanda">Rwanda</option>
                                <option value="ethiopia">Ethiopia</option>
                                <option value="burundi">Burundi</option>
                                <option value="south_sudan">South Sudan</option>
                                <option value="djibouti">Djibouti</option>
                                <option value="eritrea">Eritrea</option>
                                <option value="somalia">Somalia</option>
                            </optgroup>
                            
                            <optgroup label="🌍 West Africa">
                                <option value="nigeria">Nigeria</option>
                                <option value="ghana">Ghana</option>
                                <option value="senegal">Senegal</option>
                                <option value="ivory_coast">Ivory Coast (Côte d'Ivoire)</option>
                                <option value="burkina_faso">Burkina Faso</option>
                                <option value="mali">Mali</option>
                                <option value="niger">Niger</option>
                                <option value="guinea">Guinea</option>
                                <option value="benin">Benin</option>
                                <option value="togo">Togo</option>
                                <option value="liberia">Liberia</option>
                                <option value="sierra_leone">Sierra Leone</option>
                                <option value="gambia">The Gambia</option>
                                <option value="guinea_bissau">Guinea-Bissau</option>
                                <option value="cape_verde">Cape Verde</option>
                            </optgroup>
                            
                            <optgroup label="🌍 Central Africa">
                                <option value="cameroon">Cameroon</option>
                                <option value="democratic_republic_congo">Democratic Republic of Congo</option>
                                <option value="republic_congo">Republic of Congo</option>
                                <option value="central_african_republic">Central African Republic</option>
                                <option value="chad">Chad</option>
                                <option value="gabon">Gabon</option>
                                <option value="equatorial_guinea">Equatorial Guinea</option>
                                <option value="sao_tome_principe">São Tomé and Príncipe</option>
                            </optgroup>
                            
                            <optgroup label="🌍 North Africa">
                                <option value="egypt">Egypt</option>
                                <option value="libya">Libya</option>
                                <option value="tunisia">Tunisia</option>
                                <option value="algeria">Algeria</option>
                                <option value="morocco">Morocco</option>
                                <option value="sudan">Sudan</option>
                            </optgroup>
                            
                            <optgroup label="🌍 Island Nations">
                                <option value="madagascar">Madagascar</option>
                                <option value="mauritius">Mauritius</option>
                                <option value="seychelles">Seychelles</option>
                                <option value="comoros">Comoros</option>
                            </optgroup>
                            
                            <optgroup label="🌎 Other Regions">
                                <option value="united_states">United States</option>
                                <option value="united_kingdom">United Kingdom</option>
                                <option value="canada">Canada</option>
                                <option value="australia">Australia</option>
                                <option value="other">Other Country</option>
                            </optgroup>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Phone Number</label>
                        <input 
                            v-model="countryUpdateForm.new_phone" 
                            type="tel" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter phone number in new country"
                        >
                        <p class="text-xs text-gray-500 mt-1">We'll send SMS verification to this number</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Change</label>
                        <textarea 
                            v-model="countryUpdateForm.reason" 
                            rows="3" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Explain why you need to change your country"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Verification Document (Optional)</label>
                        <input 
                            type="file" 
                            accept=".jpg,.jpeg,.png,.pdf"
                            @change="countryUpdateForm.verification_document = $event.target.files[0]"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        >
                        <p class="text-xs text-gray-500 mt-1">Upload proof of residence in new country (optional)</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button @click="showCountryUpdateModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button @click="submitCountryUpdate" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Submit Request
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>