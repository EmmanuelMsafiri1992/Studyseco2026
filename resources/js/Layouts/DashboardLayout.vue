<template>
  <div class="flex h-screen bg-gradient-to-br from-slate-50 to-blue-50 font-sans text-slate-800">
    <!-- Mobile overlay -->
    <div 
      v-if="sidebarOpen" 
      @click="sidebarOpen = false"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden"
    ></div>
    
    <!-- Sidebar -->
    <div 
      :class="[
        'bg-white/80 backdrop-blur-xl border-r border-slate-200/50 shadow-xl transition-transform duration-300 ease-in-out z-50 flex flex-col',
        'fixed lg:static inset-y-0 left-0 w-72 lg:w-72',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <div class="p-8 border-b border-slate-200/50 flex-shrink-0">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197m.547-4.197h-.547"></path>
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-bold text-slate-800">StudySeco</h1>
            <p class="text-sm text-slate-500">School Management</p>
          </div>
        </div>
      </div>

      <nav class="px-4 py-6 space-y-2 overflow-y-auto flex-1 max-h-[calc(100vh-120px)]">
        <!-- Dashboard - Available to all roles -->
        <Link :href="dashboardLink" :class="['flex items-center px-4 py-3 rounded-xl border transition-all duration-200 hover:bg-slate-50 hover:text-slate-800', 
                                         isDashboardActive ? 'text-slate-700 bg-indigo-50 border-indigo-100' : 'text-slate-600 border-transparent']">
          <svg class="h-5 w-5 mr-4" :class="isDashboardActive ? 'text-indigo-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span class="font-medium">Dashboard</span>
        </Link>

        <!-- Academics - Enhanced with Admin Dropdown -->
        <div class="group">
          <button
            v-if="$page.props.auth?.user?.role === 'admin'"
            @click="toggleDropdown('academics')"
            :class="[
              'flex items-center justify-between w-full px-4 py-3 rounded-xl transition-all duration-300 hover:bg-slate-50 hover:text-slate-800',
              isActiveSection(['Subject', 'Department', 'Teacher', 'AITraining']) ? 'text-slate-700 bg-indigo-50' : 'text-slate-600'
            ]"
          >
            <div class="flex items-center">
              <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
              </svg>
              <span class="font-medium">Academics</span>
            </div>
            <svg 
              :class="['h-4 w-4 transition-all duration-300 transform', dropdowns.academics ? 'rotate-180' : '']" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          
          <!-- Regular link for non-admin users -->
          <Link v-else href="/subjects" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800', 
                                              $page.component?.includes('Subject') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
            <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
            </svg>
            <span class="font-medium">Academics</span>
          </Link>

          <!-- Admin Dropdown Content -->
          <div 
            v-if="$page.props.auth?.user?.role === 'admin'"
            :class="[
              'overflow-hidden transition-all duration-500 ease-out ml-6 mt-1',
              dropdowns.academics ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'
            ]"
          >
            <div class="space-y-1 pl-4 border-l-2 border-indigo-100">
              <Link href="/subjects" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                             $page.component?.includes('Subject') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>📚 Subjects</span>
              </Link>
              <Link :href="route('admin.departments.index')" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                                                      $page.component?.includes('Department') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>🏛️ Departments</span>
              </Link>
              <Link href="/teachers" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                             $page.component?.includes('Teacher') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>👨‍🏫 Teachers</span>
                <span v-if="stats?.total_teachers" class="ml-auto text-xs bg-indigo-500 text-white px-2 py-1 rounded-full">{{ stats.total_teachers }}</span>
              </Link>
              <Link :href="route('admin.ai-training.index')" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                                                      $page.component?.includes('AITraining') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>🤖 AI Training</span>
              </Link>
            </div>
          </div>
        </div>

        <!-- Library - Available to all roles -->
        <Link href="/library" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800', 
                                       $page.component?.includes('Library') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 00-2 2v2m0 0V9a2 2 0 012-2m0 2a2 2 0 012-2h4a2 2 0 012 2m0 2v10"></path>
          </svg>
          <span class="font-medium">Library</span>
        </Link>

        <!-- Chat - Available to all roles -->
        <Link href="/chat" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800', 
                                    $page.component?.includes('Chat') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.645C5.525 14.88 7.42 16 9 16c2.31 0 4.792-.88 6-2.5l-.5-1.5"></path>
          </svg>
          <span class="font-medium">Chat</span>
        </Link>


        <!-- Students - Enhanced with Admin Dropdown -->
        <div v-if="$page.props.auth?.user?.role === 'admin'" class="group">
          <button
            @click="toggleDropdown('students')"
            :class="[
              'flex items-center justify-between w-full px-4 py-3 rounded-xl transition-all duration-300 hover:bg-slate-50 hover:text-slate-800',
              isActiveSection(['Student', 'Enrollment', 'Role', 'SchoolSelection']) ? 'text-slate-700 bg-indigo-50' : 'text-slate-600'
            ]"
          >
            <div class="flex items-center">
              <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
              <span class="font-medium">Students</span>
            </div>
            <div class="flex items-center space-x-2">
              <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">{{ stats?.total_students || 0 }}</span>
              <svg 
                :class="['h-4 w-4 transition-all duration-300 transform', dropdowns.students ? 'rotate-180' : '']" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </button>

          <!-- Admin Student Management Dropdown -->
          <div 
            :class="[
              'overflow-hidden transition-all duration-500 ease-out ml-6 mt-1',
              dropdowns.students ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'
            ]"
          >
            <div class="space-y-1 pl-4 border-l-2 border-indigo-100">
              <Link href="/students" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                             $page.component?.includes('Student') && !$page.component?.includes('SchoolSelection') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>👥 All Students</span>
                <span class="ml-auto text-xs bg-indigo-500 text-white px-2 py-1 rounded-full">{{ stats?.total_students || 0 }}</span>
              </Link>
              <Link href="/admin/enrollments" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                                       $page.component?.includes('Enrollment') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>📝 Enrollments</span>
                <span v-if="stats?.pending_enrollments" class="ml-auto text-xs bg-amber-500 text-white px-2 py-1 rounded-full">{{ stats.pending_enrollments }}</span>
              </Link>
              <Link href="/admin/school-selections" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                                             $page.component?.includes('SchoolSelection') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>🏫 School Applications</span>
              </Link>
            </div>
          </div>
        </div>

        
        <!-- Assignments - Students only -->
        <Link v-if="$page.props.auth?.user?.role === 'student'" :href="route('student.assignments.index')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                    $page.component?.includes('Student/Assignments') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <span class="font-medium">Assignments</span>
        </Link>

        <!-- Quizzes - Students only -->
        <Link v-if="$page.props.auth?.user?.role === 'student'" :href="route('student.quizzes.index')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                    $page.component?.includes('Student/Quizzes') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
          </svg>
          <span class="font-medium">Quizzes</span>
        </Link>

        <!-- Mock Exams - Students only -->
        <Link v-if="$page.props.auth?.user?.role === 'student'" :href="route('student.mock-exams.index')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                    $page.component?.includes('Student/MockExams') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          <span class="font-medium">Mock Exams</span>
        </Link>

        <!-- Certificates - Students only -->
        <Link v-if="$page.props.auth?.user?.role === 'student'" :href="route('student.certificates.index')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                    $page.component?.includes('Student/Certificates') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
          </svg>
          <span class="font-medium">Certificates</span>
        </Link>

        <!-- Games - Students only -->
        <Link v-if="$page.props.auth?.user?.role === 'student'" :href="route('student.games.index')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                    $page.component?.includes('Student/Games') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="font-medium">Games</span>
        </Link>

        <!-- School Selection - Students only -->
        <Link v-if="$page.props.auth?.user?.role === 'student'" href="/school-selections" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                    $page.component?.includes('SchoolSelection') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M9 7l3-3 3 3M4 10h16v11H4V10z"></path>
          </svg>
          <span class="font-medium">Exam Schools</span>
        </Link>

        <!-- Course Extension - Students only -->
        <Link v-if="$page.props.auth?.user?.role === 'student'" href="/student/extension" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                    $page.component?.includes('Extension') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="font-medium">Extend Access</span>
        </Link>

        <!-- Payments for Students - Limited access to own payments -->
        <Link v-if="$page.props.auth?.user?.role === 'student'" href="/payments/create" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                    $page.component?.includes('Payment') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
          </svg>
          <span class="font-medium">Payments</span>
        </Link>

        <!-- Teacher Section -->
        <div v-if="$page.props.auth?.user?.role === 'teacher'" class="pt-4 mt-4 border-t border-slate-200">
          <!-- My Students - Teachers only -->
          <Link :href="route('teacher.students.index')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800', 
                                          $page.component?.includes('TeacherStudent') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
            <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <span class="font-medium">My Students</span>
            <span class="ml-auto text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">{{ stats?.assigned_students || 0 }}</span>
          </Link>
          
          <!-- Teaching Calendar - Teachers only -->
          <Link :href="route('teacher.calendar')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800', 
                                          $page.component?.includes('TeacherCalendar') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
            <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="font-medium">Calendar</span>
          </Link>
          
          <!-- Teaching Materials - Teachers only -->
          <Link :href="route('teacher.materials')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                          $page.component?.includes('TeacherMaterial') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
            <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="font-medium">Materials</span>
          </Link>

          <!-- Assignments - Teachers only -->
          <Link :href="route('teacher.assignments.index')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                          $page.component?.includes('Teacher/Assignments') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
            <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="font-medium">Assignments</span>
          </Link>

          <!-- Quizzes - Teachers only -->
          <Link :href="route('teacher.quizzes.index')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                          $page.component?.includes('Teacher/Quizzes') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
            <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
            <span class="font-medium">Quizzes</span>
          </Link>

          <!-- Certificates - Teachers only -->
          <Link :href="route('teacher.certificates.index')" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                          $page.component?.includes('Teacher/Certificates') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
            <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
            </svg>
            <span class="font-medium">Certificates</span>
          </Link>
        </div>

        <!-- Achievements - Available to all roles -->
        <Link href="/achievements" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800',
                                           $page.component?.includes('Achievement') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
          <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
          </svg>
          <span class="font-medium">Achievements</span>
        </Link>

        <!-- Support & System Management -->
        <div class="group">
          <button
            v-if="$page.props.auth?.user?.role === 'admin'"
            @click="toggleDropdown('support')"
            :class="[
              'flex items-center justify-between w-full px-4 py-3 rounded-xl transition-all duration-300 hover:bg-slate-50 hover:text-slate-800',
              isActiveSection(['Complaint', 'SystemSettings', 'Audit', 'Report', 'PaymentMethod', 'Payment']) ? 'text-slate-700 bg-indigo-50' : 'text-slate-600'
            ]"
          >
            <div class="flex items-center">
              <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              <span class="font-medium">System & Support</span>
            </div>
            <div class="flex items-center space-x-2">
              <span v-if="stats?.pending_enrollments" class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full">{{ stats.pending_enrollments }}</span>
              <svg 
                :class="['h-4 w-4 transition-all duration-300 transform', dropdowns.support ? 'rotate-180' : '']" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>
          </button>
          
          <!-- Regular Support link for non-admin users -->
          <Link v-else href="/complaints" :class="['flex items-center px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-50 hover:text-slate-800', 
                                                  $page.component?.includes('Complaint') ? 'text-slate-700 bg-indigo-50' : 'text-slate-600']">
            <svg class="h-5 w-5 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span class="font-medium">Support</span>
          </Link>

          <!-- Admin System Management Dropdown -->
          <div 
            v-if="$page.props.auth?.user?.role === 'admin'"
            :class="[
              'overflow-hidden transition-all duration-500 ease-out ml-6 mt-1',
              dropdowns.support ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'
            ]"
          >
            <div class="space-y-1 pl-4 border-l-2 border-indigo-100">
              <Link href="/complaints" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                               $page.component?.includes('Complaint') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>🎧 Support Tickets</span>
                <span v-if="stats?.pending_enrollments" class="ml-auto text-xs bg-red-500 text-white px-2 py-1 rounded-full">{{ stats.pending_enrollments }}</span>
              </Link>
              <Link href="/admin/roles" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                               $page.component?.includes('Role') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>🛡️ Role Management</span>
              </Link>
              <Link :href="route('admin.system-settings.index')" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                                                           $page.component?.includes('SystemSettings') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>⚙️ System Settings</span>
              </Link>
              <Link :href="route('admin.audit.index')" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                                               $page.component?.includes('Audit') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>🔍 Audit Trail</span>
              </Link>
              <Link href="/reports" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                            $page.component?.includes('Report') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>📊 Analytics</span>
              </Link>
              <Link href="/admin/payment-methods" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50', 
                                                           $page.component?.includes('PaymentMethod') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>💳 Payment Methods</span>
              </Link>
              <Link href="/payments" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50',
                                             $page.component?.includes('Payment') && !$page.component?.includes('PaymentMethod') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>💰 Payments Overview</span>
              </Link>
              <Link :href="route('admin.mock-exams.index')" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50',
                                                                     $page.component?.includes('MockExam') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>📝 Mock Exams</span>
              </Link>
              <Link :href="route('admin.games.index')" :class="['flex items-center px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-indigo-50',
                                                                     $page.component?.includes('Admin/Games') ? 'text-indigo-700 bg-indigo-50' : 'text-slate-600 hover:text-indigo-700']">
                <span>🎮 Games</span>
              </Link>
            </div>
          </div>
        </div>

      </nav>
    </div>

    <div class="flex-1 flex flex-col">
      <!-- Mobile Header -->
      <div class="lg:hidden bg-white/90 backdrop-blur-xl border-b border-slate-200/50 p-4 flex items-center justify-between">
        <button 
          @click="sidebarOpen = !sidebarOpen"
          class="p-2 rounded-lg hover:bg-slate-100 transition-colors"
        >
          <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197m.547-4.197h-.547"></path>
            </svg>
          </div>
          <span class="text-lg font-bold text-slate-800">StudySeco</span>
        </div>
        <!-- Country Display for Students (Mobile) -->
        <div v-if="$page.props.auth?.user?.role === 'student' && $page.props.auth?.user?.enrollment?.country" 
             class="flex items-center space-x-1 px-2 py-1 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200/50">
          <div class="relative w-5 h-3 rounded-sm shadow-sm overflow-hidden">
            <div :class="'fi fi-' + getCountryFlag($page.props.auth.user.enrollment.country) + ' w-full h-full'"></div>
            <div :class="'flag-fallback fi-' + getCountryFlag($page.props.auth.user.enrollment.country) + ' w-full h-full absolute inset-0'" 
                 style="background-image: none !important; font-size: 8px;">
              {{ getCountryCode($page.props.auth.user.enrollment.country) }}
            </div>
          </div>
          <span class="text-xs font-medium text-blue-800 hidden sm:block">{{ $page.props.auth.user.enrollment.country }}</span>
        </div>
        <div v-else class="w-10"></div> <!-- Spacer for balance -->
      </div>
      
      <!-- Desktop Header -->
      <header class="hidden lg:flex h-20 bg-white/70 backdrop-blur-xl border-b border-slate-200/50 px-4 lg:px-8 items-center justify-between relative z-50">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">
            <slot name="header-title">{{ title }}</slot>
          </h1>
          <p class="text-slate-500 text-sm">
            <slot name="header-subtitle">{{ subtitle }}</slot>
          </p>
        </div>

        <div class="flex items-center space-x-4">
          <!-- Country Display for Students -->
          <div v-if="$page.props.auth?.user?.role === 'student' && $page.props.auth?.user?.enrollment?.country" 
               class="flex items-center space-x-2 px-3 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200/50">
            <div class="relative w-6 h-4 rounded-sm shadow-sm overflow-hidden">
              <div :class="'fi fi-' + getCountryFlag($page.props.auth.user.enrollment.country) + ' w-full h-full'"></div>
              <div :class="'flag-fallback fi-' + getCountryFlag($page.props.auth.user.enrollment.country) + ' w-full h-full absolute inset-0'" 
                   style="background-image: none !important;">
                {{ getCountryCode($page.props.auth.user.enrollment.country) }}
              </div>
            </div>
            <span class="text-sm font-medium text-blue-800">{{ $page.props.auth.user.enrollment.country }}</span>
          </div>
          
          <div class="relative">
            <input type="text" placeholder="Search anything..." class="bg-slate-100/70 backdrop-blur-sm px-4 py-3 pl-10 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white w-80 transition-all duration-200">
            <svg class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>

          <Link href="/notifications" class="relative p-3 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-2xl transition-all duration-200">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0a3 3 0 00-6 0"></path>
            </svg>
            <span v-if="notificationCount > 0" class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ notificationCount }}</span>
          </Link>

          <div class="relative group">
            <div class="flex items-center space-x-3 pl-4 border-l border-slate-200 cursor-pointer">
              <img :src="$page.props.auth?.user?.profile_photo_url || 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face&facepad=2&bg=white'"
                   :alt="$page.props.auth?.user?.name" class="h-12 w-12 rounded-2xl ring-2 ring-white shadow-md">
              <div class="text-sm">
                <p class="font-semibold text-slate-800">{{ $page.props.auth?.user?.name }}</p>
                <p class="text-slate-500">{{ $page.props.auth?.user?.role }}</p>
              </div>
              <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </div>

            <div class="absolute right-0 top-full mt-2 w-56 bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-slate-200/50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-[9999]">
              <div class="p-2">
                <Link href="/profile" class="flex items-center px-4 py-3 text-slate-700 hover:bg-slate-100/70 rounded-xl transition-colors duration-150">
                  <svg class="w-4 h-4 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  View Profile
                </Link>
                <Link href="/account-settings" class="flex items-center px-4 py-3 text-slate-700 hover:bg-slate-100/70 rounded-xl transition-colors duration-150">
                  <svg class="w-4 h-4 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.82 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.82 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.82-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.82-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  </svg>
                  Account Settings
                </Link>
                <Link href="/notification-settings" class="flex items-center px-4 py-3 text-slate-700 hover:bg-slate-100/70 rounded-xl transition-colors duration-150">
                  <svg class="w-4 h-4 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5M9 17H4l3.5-3.5M17 9a5 5 0 11-10 0 5 5 0 0110 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.73 21a2 2 0 01-3.46 0"></path>
                  </svg>
                  Notification Settings
                </Link>
                <hr class="my-2 border-slate-200">
                <Link href="/logout" method="post" as="button" class="flex items-center w-full px-4 py-3 text-rose-600 hover:bg-rose-50 rounded-xl transition-colors duration-150">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                  </svg>
                  Sign Out
                </Link>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-4 lg:p-8 relative">
        <slot />
      </main>
    </div>
    
    <!-- Notification Sound Component - Disabled to prevent conflicts -->
    <!-- <NotificationSound 
      ref="notificationSoundRef"
      :enabled="notificationSettings.sound_enabled"
      :unread-count="notificationCount"
    /> -->
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import NotificationSound from '@/Components/NotificationSound.vue'
import { playNotificationSound, soundEnabled } from '@/composables/useNotificationSounds'

const $page = usePage()

defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  },
  subtitle: {
    type: String,
    default: 'Welcome to your dashboard'
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

// Computed properties for Dashboard navigation
const dashboardLink = computed(() => {
  return '/dashboard' // Let all users access dashboard
})

const isDashboardActive = computed(() => {
  return $page.component === 'Dashboard'
})

const notificationCount = ref(0)
const previousNotificationCount = ref(0)
const notificationSettings = ref({
  sound_enabled: true,
  desktop_enabled: true
})
const notificationSoundRef = ref(null)
const pollingInterval = ref(null)
const sidebarOpen = ref(false)

// Fetch notification count
const fetchNotificationCount = async () => {
  try {
    const response = await fetch('/api/notifications/count')
    const data = await response.json()
    
    // Only play sound if notifications actually increased (not on initial load)
    if (data.count > previousNotificationCount.value && previousNotificationCount.value > 0 && soundEnabled.value) {
      // Only play once per actual new notification
      playNotificationSound('message')
    }
    
    // Show desktop notification if there are actual new notifications
    if (data.count > previousNotificationCount.value && previousNotificationCount.value > 0 && notificationSettings.value.desktop_enabled && 'Notification' in window) {
      if (Notification.permission === 'granted') {
        new Notification('StudySeco', {
          body: `You have new notifications`,
          icon: '/favicon.ico',
          tag: 'studyseco-notification'
        })
      }
    }
    
    // Update counts
    previousNotificationCount.value = notificationCount.value
    notificationCount.value = data.count
  } catch (error) {
    console.error('Failed to fetch notification count:', error)
  }
}

// Fetch notification settings
const fetchNotificationSettings = async () => {
  try {
    const response = await fetch('/api/notification-settings')
    const data = await response.json()
    notificationSettings.value = {
      sound_enabled: data.sound_enabled ?? true,
      desktop_enabled: data.desktop_enabled ?? true
    }
  } catch (error) {
    console.warn('Could not fetch notification settings, using defaults:', error)
  }
}

// Start polling for notifications
const startNotificationPolling = () => {
  // Poll every 30 seconds
  pollingInterval.value = setInterval(fetchNotificationCount, 30000)
}

// Stop polling
const stopNotificationPolling = () => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
    pollingInterval.value = null
  }
}

// Admin dropdown state management
const dropdowns = ref({
  student: false,
  academic: false,
  financial: false,
  system: false
})

const toggleDropdown = (section) => {
  dropdowns.value[section] = !dropdowns.value[section]
}

const page = usePage()

const isActiveSection = (components) => {
  return components.some(component => page.component?.includes(component))
}

// Country flag mapping with CSS flag classes
const getCountryFlag = (countryName) => {
  const countryFlags = {
    'malawi': 'mw',
    'south africa': 'za',
    'zambia': 'zm',
    'botswana': 'bw',
    'zimbabwe': 'zw',
    'namibia': 'na',
    'lesotho': 'ls',
    'eswatini': 'sz',
    'swaziland': 'sz',
    'mozambique': 'mz',
    'kenya': 'ke',
    'tanzania': 'tz',
    'uganda': 'ug',
    'rwanda': 'rw',
    'ethiopia': 'et',
    'nigeria': 'ng',
    'ghana': 'gh',
    'senegal': 'sn',
    'ivory coast': 'ci',
    'cote divoire': 'ci',
    'democratic republic of congo': 'cd',
    'angola': 'ao',
    'cameroon': 'cm',
    'madagascar': 'mg',
    'burkina faso': 'bf',
    'mali': 'ml',
    'niger': 'ne',
    'chad': 'td',
    'sudan': 'sd',
    'south sudan': 'ss',
    'eritrea': 'er',
    'djibouti': 'dj',
    'somalia': 'so',
    'egypt': 'eg',
    'libya': 'ly',
    'tunisia': 'tn',
    'algeria': 'dz',
    'morocco': 'ma',
    'international': 'un'
  }
  
  if (!countryName) return 'un'
  
  const normalizedCountry = countryName.toLowerCase().trim()
  return countryFlags[normalizedCountry] || 'un'
}

// Get country code for fallback display
const getCountryCode = (countryName) => {
  const countryCodes = {
    'malawi': 'MW',
    'south africa': 'ZA',
    'zambia': 'ZM',
    'botswana': 'BW',
    'zimbabwe': 'ZW',
    'namibia': 'NA',
    'lesotho': 'LS',
    'eswatini': 'SZ',
    'swaziland': 'SZ',
    'mozambique': 'MZ',
    'kenya': 'KE',
    'tanzania': 'TZ',
    'uganda': 'UG',
    'rwanda': 'RW',
    'ethiopia': 'ET',
    'nigeria': 'NG',
    'ghana': 'GH',
    'senegal': 'SN',
    'ivory coast': 'CI',
    'cote divoire': 'CI',
    'democratic republic of congo': 'CD',
    'angola': 'AO',
    'cameroon': 'CM',
    'madagascar': 'MG',
    'burkina faso': 'BF',
    'mali': 'ML',
    'niger': 'NE',
    'chad': 'TD',
    'sudan': 'SD',
    'south sudan': 'SS',
    'eritrea': 'ER',
    'djibouti': 'DJ',
    'somalia': 'SO',
    'egypt': 'EG',
    'libya': 'LY',
    'tunisia': 'TN',
    'algeria': 'DZ',
    'morocco': 'MA',
    'international': 'UN'
  }
  
  if (!countryName) return 'UN'
  
  const normalizedCountry = countryName.toLowerCase().trim()
  return countryCodes[normalizedCountry] || 'UN'
}

// Initialize notification system
onMounted(async () => {
  await fetchNotificationSettings()
  await fetchNotificationCount()
  startNotificationPolling()
  
  // Request notification permission on first load
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission()
  }
  
  // Auto-expand active dropdown sections
  if (isActiveSection(['Enrollment', 'Role', 'SchoolSelection'])) {
    dropdowns.value.student = true
  }
  if (isActiveSection(['Subject', 'Department', 'Teacher', 'AITraining'])) {
    dropdowns.value.academic = true
  }
  if (isActiveSection(['PaymentMethod', 'Payment'])) {
    dropdowns.value.financial = true
  }
  if (isActiveSection(['SystemSettings', 'Audit', 'Report'])) {
    dropdowns.value.system = true
  }
})

// Cleanup
onUnmounted(() => {
  stopNotificationPolling()
})
</script>