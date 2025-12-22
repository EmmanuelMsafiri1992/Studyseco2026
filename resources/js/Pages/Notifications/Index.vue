<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    notifications: Array
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

// Use notifications from controller (role-based filtered)
const notificationData = ref(props.notifications || [
    { 
        id: 1,
        type: 'enrollment', 
        title: 'New Student Enrollment', 
        description: 'Alex Kumar has enrolled in Grade 11 Mathematics and Science programs', 
        time: '5 minutes ago',
        timestamp: new Date(Date.now() - 5 * 60 * 1000),
        read: false,
        priority: 'normal',
        avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop&crop=face'
    },
    { 
        id: 2,
        type: 'payment', 
        title: 'Payment Received', 
        description: 'Lisa Park successfully paid MWK 85,000 for 1-year access subscription', 
        time: '12 minutes ago',
        timestamp: new Date(Date.now() - 12 * 60 * 1000),
        read: false,
        priority: 'high',
        amount: 'MWK 85,000'
    },
    { 
        id: 3,
        type: 'alert', 
        title: 'Low Attendance Warning', 
        description: 'Grade 9A Mathematics class attendance has dropped below 75% threshold', 
        time: '1 hour ago',
        timestamp: new Date(Date.now() - 60 * 60 * 1000),
        read: true,
        priority: 'high'
    },
    { 
        id: 4,
        type: 'assignment', 
        title: 'Assignment Submitted', 
        description: 'John Mwangi submitted Physics Lab Report - Wave Properties assignment', 
        time: '2 hours ago',
        timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000),
        read: true,
        priority: 'normal',
        subject: 'Physics'
    },
    { 
        id: 5,
        type: 'system', 
        title: 'System Maintenance', 
        description: 'Scheduled maintenance window completed. All services are now operational', 
        time: '3 hours ago',
        timestamp: new Date(Date.now() - 3 * 60 * 60 * 1000),
        read: true,
        priority: 'low'
    },
    { 
        id: 6,
        type: 'message', 
        title: 'New Support Ticket', 
        description: 'Sarah Phiri created a new support ticket regarding login issues', 
        time: '4 hours ago',
        timestamp: new Date(Date.now() - 4 * 60 * 60 * 1000),
        read: false,
        priority: 'normal'
    }
]);

const filterType = ref('all');
const searchQuery = ref('');

// Computed properties
const filteredNotifications = computed(() => {
    let filtered = notificationData.value;
    
    if (filterType.value !== 'all') {
        if (filterType.value === 'unread') {
            filtered = filtered.filter(n => !n.read);
        } else {
            filtered = filtered.filter(n => n.type === filterType.value);
        }
    }
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(n => 
            n.title.toLowerCase().includes(query) || 
            n.description.toLowerCase().includes(query)
        );
    }
    
    return filtered.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
});

const unreadCount = computed(() => 
    notificationData.value.filter(n => !n.read).length
);

const notificationStats = computed(() => {
    const stats = {
        enrollment: 0,
        payment: 0,
        alert: 0,
        assignment: 0,
        system: 0,
        message: 0
    };
    
    notificationData.value.forEach(n => {
        if (stats.hasOwnProperty(n.type)) {
            stats[n.type]++;
        }
    });
    
    return stats;
});

// Methods
const handleNotificationClick = (notification) => {
    // Mark as read
    notification.read = true;
    
    // Mark as read on server if it's a real notification
    if (notification.id && !notification.id.toString().includes('_')) {
        router.post(route('notifications.read', notification.id));
    }
    
    // Handle different notification types based on user role and notification type
    const userRole = user.role;
    
    try {
        if (notification.type === 'enrollment') {
            if (userRole === 'admin') {
                if (notification.id.startsWith('enrollment_')) {
                    const enrollmentId = notification.id.replace('enrollment_', '');
                    router.visit(route('admin.enrollments.show', enrollmentId));
                } else {
                    router.visit(route('admin.enrollments.index'));
                }
            } else {
                // For students, go to dashboard
                router.visit(route('dashboard'));
            }
        } else if (notification.type === 'payment') {
            if (userRole === 'admin') {
                if (notification.id.startsWith('payment_')) {
                    // Go to payments management page
                    router.visit(route('payments.index'));
                } else {
                    router.visit(route('admin.enrollments.index'));
                }
            } else {
                // For students, go to payments page
                router.visit(route('payments.create'));
            }
        } else if (notification.type === 'alert') {
            if (notification.id === 'pending_enrollments') {
                router.visit(route('admin.enrollments.index'));
            } else if (notification.id === 'trial_expiry') {
                router.visit(route('student.extension'));
            } else {
                router.visit(route('dashboard'));
            }
        } else if (notification.type === 'assignment') {
            // Navigate to subjects or specific assignment page
            router.visit(route('subjects.index'));
        } else if (notification.type === 'system') {
            // Navigate to settings or dashboard
            if (userRole === 'admin') {
                router.visit(route('admin.system-settings.index'));
            } else {
                router.visit(route('dashboard'));
            }
        } else if (notification.type === 'message') {
            // Navigate to chat or support
            router.visit(route('chat.index'));
        } else if (notification.type === 'welcome') {
            // Navigate to dashboard for new users
            router.visit(route('dashboard'));
        } else if (notification.type === 'warning') {
            // Navigate to extension page for trial warnings
            if (notification.id === 'trial_expiry') {
                router.visit(route('student.extension'));
            } else {
                router.visit(route('dashboard'));
            }
        } else if (notification.type === 'account') {
            // Navigate to account settings
            router.visit(route('profile.edit'));
        } else {
            // Default: navigate to dashboard
            router.visit(route('dashboard'));
        }
    } catch (error) {
        console.error('Navigation error:', error);
        // Fallback to dashboard
        router.visit(route('dashboard'));
    }
};

const markAsRead = (notification) => {
    notification.read = true;
};

const markAllAsRead = () => {
    notificationData.value.forEach(n => n.read = true);
};

const getNotificationIcon = (type) => {
    const icons = {
        enrollment: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        payment: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        alert: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
        assignment: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        system: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.82 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.82 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.82-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.82-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        message: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C4.925 15.355 6 13.668 6 12c0-4.97 4.03-9 9-9s9 4.03 9 9z'
    };
    
    return icons[type] || icons.system;
};

const getPriorityClass = (priority) => {
    const classes = {
        high: 'border-l-4 border-l-error-400 bg-gradient-to-r from-error-50 to-white',
        normal: 'border-l-4 border-l-primary-400 bg-gradient-to-r from-primary-50 to-white',
        low: 'border-l-4 border-l-secondary-300 bg-gradient-to-r from-secondary-50 to-white'
    };
    
    return classes[priority] || classes.normal;
};

const getTypeColor = (type) => {
    const colors = {
        enrollment: { bg: 'from-primary-500 to-primary-600', text: 'text-primary-600', badge: 'bg-primary-100 text-primary-800' },
        payment: { bg: 'from-success-500 to-success-600', text: 'text-success-600', badge: 'bg-success-100 text-success-800' },
        alert: { bg: 'from-warning-500 to-warning-600', text: 'text-warning-600', badge: 'bg-warning-100 text-warning-800' },
        assignment: { bg: 'from-accent-500 to-accent-600', text: 'text-accent-600', badge: 'bg-accent-100 text-accent-800' },
        system: { bg: 'from-secondary-500 to-secondary-600', text: 'text-secondary-600', badge: 'bg-secondary-100 text-secondary-800' },
        message: { bg: 'from-primary-500 to-primary-600', text: 'text-primary-600', badge: 'bg-primary-100 text-primary-800' }
    };
    
    return colors[type] || colors.system;
};
</script>

<template>
    <Head title="Notifications" />
    
    <DashboardLayout 
        title="Notifications" 
        subtitle="Stay updated with important activities and alerts">

        <div class="mb-8">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-slate-800">{{ user.role === 'admin' ? 'All Notifications' : 'My Notifications' }}</h2>
                <div class="flex items-center space-x-4">
                    <div v-if="unreadCount > 0" class="flex items-center space-x-2">
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium">{{ unreadCount }} unread</span>
                        <button @click="markAllAsRead" class="bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Mark all as read
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <!-- Stats Cards - Only show for admin -->
            <div v-if="user.role === 'admin'" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 text-center shadow-lg border border-slate-200/50 hover:shadow-xl transition-all">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-slate-900">{{ notificationStats.enrollment }}</div>
                    <div class="text-xs text-slate-600">Enrollments</div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 text-center shadow-lg border border-slate-200/50 hover:shadow-xl transition-all">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-slate-900">{{ notificationStats.payment }}</div>
                    <div class="text-xs text-slate-600">Payments</div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 text-center shadow-lg border border-slate-200/50 hover:shadow-xl transition-all">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-yellow-500 to-orange-600 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-slate-900">{{ notificationStats.alert }}</div>
                    <div class="text-xs text-slate-600">Alerts</div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 text-center shadow-lg border border-slate-200/50 hover:shadow-xl transition-all">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-600 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-slate-900">{{ notificationStats.assignment }}</div>
                    <div class="text-xs text-slate-600">Assignments</div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 text-center shadow-lg border border-slate-200/50 hover:shadow-xl transition-all">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-slate-500 to-slate-600 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-slate-900">{{ notificationStats.system }}</div>
                    <div class="text-xs text-slate-600">System</div>
                </div>

                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 text-center shadow-lg border border-slate-200/50 hover:shadow-xl transition-all">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-slate-900">{{ notificationStats.message }}</div>
                    <div class="text-xs text-slate-600">Messages</div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 mb-8">
                <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between">
                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="filterType = 'all'"
                            :class="filterType === 'all' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-2xl text-sm font-medium transition-all"
                        >
                            All Notifications
                        </button>
                        <button 
                            @click="filterType = 'unread'"
                            :class="filterType === 'unread' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-2xl text-sm font-medium transition-all"
                        >
                            Unread
                            <span v-if="unreadCount > 0" class="ml-1 px-2 py-0.5 bg-white/20 rounded-full text-xs">{{ unreadCount }}</span>
                        </button>
                        <!-- Admin-specific filters -->
                        <template v-if="user.role === 'admin'">
                            <button 
                                @click="filterType = 'enrollment'"
                                :class="filterType === 'enrollment' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-4 py-2 rounded-2xl text-sm font-medium transition-all"
                            >
                                Enrollments
                            </button>
                            <button 
                                @click="filterType = 'payment'"
                                :class="filterType === 'payment' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-4 py-2 rounded-2xl text-sm font-medium transition-all"
                            >
                                Payments
                            </button>
                            <button 
                                @click="filterType = 'alert'"
                                :class="filterType === 'alert' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-4 py-2 rounded-2xl text-sm font-medium transition-all"
                            >
                                Alerts
                            </button>
                        </template>
                        <!-- Student-specific filters -->
                        <template v-else>
                            <button 
                                @click="filterType = 'account'"
                                :class="filterType === 'account' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-4 py-2 rounded-2xl text-sm font-medium transition-all"
                            >
                                Account
                            </button>
                            <button 
                                @click="filterType = 'welcome'"
                                :class="filterType === 'welcome' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-4 py-2 rounded-2xl text-sm font-medium transition-all"
                            >
                                Welcome
                            </button>
                            <button 
                                @click="filterType = 'warning'"
                                :class="filterType === 'warning' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-4 py-2 rounded-2xl text-sm font-medium transition-all"
                            >
                                Warnings
                            </button>
                        </template>
                    </div>
                    
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search notifications..."
                            class="w-80 bg-slate-100/70 backdrop-blur-sm px-4 py-3 pl-10 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                        >
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="space-y-4">
                <div v-if="filteredNotifications.length === 0" class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">No notifications found</h3>
                    <p class="text-slate-600">{{ searchQuery ? 'Try adjusting your search terms or filters' : 'You\'re all caught up! No new notifications at this time.' }}</p>
                </div>

                <div 
                    v-for="notification in filteredNotifications" 
                    :key="notification.id"
                    :class="[
                        'bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 p-6 cursor-pointer transition-all duration-200 hover:shadow-2xl hover:-translate-y-1',
                        notification.priority === 'high' ? 'border-l-4 border-l-red-400' : 'border-l-4 border-l-indigo-400',
                        { 'ring-2 ring-indigo-200': !notification.read }
                    ]"
                    @click="handleNotificationClick(notification)"
                >
                    <div class="flex items-start space-x-4">
                        <!-- Notification Icon/Avatar -->
                        <div class="flex-shrink-0">
                            <div v-if="notification.avatar" class="relative">
                                <img :src="notification.avatar" :alt="notification.title" class="w-12 h-12 rounded-full object-cover">
                                <div :class="[
                                    'absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center',
                                    `bg-gradient-to-r ${getTypeColor(notification.type).bg}`
                                ]">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                        <path :d="getNotificationIcon(notification.type)"/>
                                    </svg>
                                </div>
                            </div>
                            <div v-else :class="[
                                'w-12 h-12 rounded-xl flex items-center justify-center',
                                `bg-gradient-to-r ${getTypeColor(notification.type).bg}`
                            ]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                    <path :d="getNotificationIcon(notification.type)"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Notification Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-2">
                                <h3 :class="[
                                    'font-semibold truncate',
                                    !notification.read ? 'text-slate-900' : 'text-slate-700'
                                ]">
                                    {{ notification.title }}
                                </h3>
                                <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium capitalize bg-slate-100 text-slate-700">
                                        {{ notification.type }}
                                    </span>
                                    <div v-if="!notification.read" class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                                </div>
                            </div>

                            <p :class="[
                                'text-sm mb-3 line-clamp-2',
                                !notification.read ? 'text-slate-600' : 'text-slate-500'
                            ]">
                                {{ notification.description }}
                            </p>

                            <!-- Additional Info -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <span class="text-xs text-slate-500 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                            <circle cx="12" cy="12" r="10"/>
                                            <polyline points="12,6 12,12 16,14"/>
                                        </svg>
                                        {{ notification.time }}
                                    </span>
                                    
                                    <span v-if="notification.amount" class="text-xs font-semibold text-green-600">
                                        {{ notification.amount }}
                                    </span>
                                    
                                    <span v-if="notification.subject" class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                        {{ notification.subject }}
                                    </span>
                                </div>

                                <div v-if="notification.priority === 'high'" class="flex items-center text-xs text-red-600 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                    High Priority
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>