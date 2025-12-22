<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onUnmounted } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    auth: Object,
    resources: Object,
    subjects: Array,
    years: Array,
    grades: [Array, Object],
    stats: Object,
    filters: Object,
});

const user = props.auth?.user || { name: 'Guest', role: 'guest' };

// Filter states
const searchQuery = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || 'all');
const selectedSubject = ref(props.filters.subject || '');
const selectedGrade = ref(props.filters.grade || '');
const selectedYear = ref(props.filters.year || '');

// Document viewer states
const showViewer = ref(false);
const selectedResource = ref(null);

// Pagination and search states
const currentPage = ref(1);
const totalPages = ref(50); // Default total pages, will be dynamic based on resource
const modalSearchQuery = ref('');
const searchResults = ref([]);
const showSearchResults = ref(false);

// Sample page content for demonstration
const samplePages = ref({
    book: {
        1: {
            title: "Table of Contents",
            content: `
                <h2>Table of Contents</h2>
                <div class="space-y-2">
                    <div class="flex justify-between border-b border-dotted pb-1">
                        <span>Chapter 1: Introduction to Mathematics</span>
                        <span>Page 3</span>
                    </div>
                    <div class="flex justify-between border-b border-dotted pb-1">
                        <span>Chapter 2: Basic Concepts and Principles</span>
                        <span>Page 15</span>
                    </div>
                    <div class="flex justify-between border-b border-dotted pb-1">
                        <span>Chapter 3: Advanced Topics</span>
                        <span>Page 45</span>
                    </div>
                    <div class="flex justify-between border-b border-dotted pb-1">
                        <span>Chapter 4: Practice Exercises</span>
                        <span>Page 78</span>
                    </div>
                </div>
            `
        },
        3: {
            title: "Chapter 1: Introduction",
            content: `
                <h2>Chapter 1: Introduction</h2>
                <p>Welcome to this comprehensive study guide. This resource has been carefully prepared to help students master key concepts and develop problem-solving skills.</p>
                
                <h3>Learning Objectives</h3>
                <ul class="list-disc list-inside space-y-2">
                    <li>Understand fundamental principles and concepts</li>
                    <li>Apply theoretical knowledge to practical problems</li>
                    <li>Develop analytical and critical thinking skills</li>
                    <li>Prepare for examinations with confidence</li>
                </ul>

                <h3>Key Terms</h3>
                <div class="bg-blue-50 p-4 rounded-lg mt-4">
                    <p><strong>Algorithm:</strong> A step-by-step procedure for solving a problem</p>
                    <p><strong>Variable:</strong> A symbol representing a quantity that can change</p>
                    <p><strong>Function:</strong> A relation between input and output values</p>
                </div>
            `
        },
        15: {
            title: "Chapter 2: Basic Concepts",
            content: `
                <h2>Chapter 2: Basic Concepts and Principles</h2>
                
                <h3>2.1 Fundamental Operations</h3>
                <p>In this section, we will explore the basic operations that form the foundation of mathematical problem-solving.</p>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-4">
                    <h4 class="font-semibold">Important Note</h4>
                    <p>Always remember to follow the order of operations (PEMDAS/BODMAS) when solving complex expressions.</p>
                </div>

                <h3>2.2 Practice Examples</h3>
                <div class="space-y-4">
                    <div class="border p-4 rounded">
                        <p><strong>Example 1:</strong> Solve: 3x + 7 = 22</p>
                        <p class="text-sm text-gray-600 mt-2">Solution: x = 5</p>
                    </div>
                    <div class="border p-4 rounded">
                        <p><strong>Example 2:</strong> Find the area of a triangle with base 8cm and height 6cm</p>
                        <p class="text-sm text-gray-600 mt-2">Solution: Area = ½ × 8 × 6 = 24 cm²</p>
                    </div>
                </div>
            `
        },
        45: {
            title: "Chapter 3: Advanced Topics",
            content: `
                <h2>Chapter 3: Advanced Topics</h2>
                
                <h3>3.1 Complex Problem Solving</h3>
                <p>Building upon the fundamental concepts from previous chapters, we now explore more advanced mathematical concepts and problem-solving strategies.</p>

                <h3>3.2 Quadratic Equations</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p>The general form of a quadratic equation is: <strong>ax² + bx + c = 0</strong></p>
                    <p class="mt-2">Where a ≠ 0, and a, b, c are constants.</p>
                </div>

                <h3>3.3 The Quadratic Formula</h3>
                <div class="text-center my-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-lg font-mono">x = (-b ± √(b² - 4ac)) / 2a</p>
                </div>

                <h3>3.4 Practice Problems</h3>
                <ol class="list-decimal list-inside space-y-3">
                    <li>Solve: x² - 5x + 6 = 0</li>
                    <li>Find the roots of: 2x² + 3x - 2 = 0</li>
                    <li>Determine the discriminant of: x² - 4x + 4 = 0</li>
                </ol>
            `
        },
        78: {
            title: "Chapter 4: Practice Exercises",
            content: `
                <h2>Chapter 4: Practice Exercises</h2>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-green-800">Exercise Instructions</h3>
                    <ul class="text-sm text-green-700 mt-2 space-y-1">
                        <li>• Attempt all questions</li>
                        <li>• Show all working clearly</li>
                        <li>• Check your answers using the solutions section</li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold mb-2">Exercise 4.1: Linear Equations</h4>
                        <ol class="list-decimal list-inside space-y-2">
                            <li>Solve: 3x + 8 = 29</li>
                            <li>Find x if: 2(x - 3) = 14</li>
                            <li>Simplify: 5x - 2x + 7 = 25</li>
                        </ol>
                    </div>

                    <div class="border rounded-lg p-4">
                        <h4 class="font-semibold mb-2">Exercise 4.2: Geometry</h4>
                        <ol class="list-decimal list-inside space-y-2">
                            <li>Calculate the perimeter of a rectangle with length 12cm and width 8cm</li>
                            <li>Find the area of a circle with radius 7cm (use π = 3.14)</li>
                            <li>Determine the volume of a cube with side length 5cm</li>
                        </ol>
                    </div>
                </div>
            `
        }
    },
    past_paper: {
        1: {
            title: "Cover Page",
            content: `
                <div class="text-center space-y-4">
                    <h1 class="text-2xl font-bold">MATHEMATICS EXAMINATION</h1>
                    <p class="text-lg">Form 4 - 2024</p>
                    <div class="border-2 border-gray-300 rounded-lg p-6 mt-8">
                        <p><strong>Duration:</strong> 3 hours</p>
                        <p><strong>Total Marks:</strong> 100</p>
                        <div class="mt-4 space-y-2">
                            <p>Name: ________________________</p>
                            <p>Registration No: _______________</p>
                            <p>School: ______________________</p>
                        </div>
                    </div>
                </div>
            `
        },
        2: {
            title: "Instructions",
            content: `
                <h2>INSTRUCTIONS TO CANDIDATES</h2>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Answer ALL questions in the spaces provided in this booklet</li>
                        <li>All working must be clearly shown</li>
                        <li>Non-programmable calculators are allowed</li>
                        <li>Mathematical tables and graph paper are provided</li>
                        <li>Write your name and registration number in the spaces provided</li>
                        <li>Do not write in the margins</li>
                    </ol>
                </div>
                
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="font-semibold">For Examiner's Use Only:</p>
                    <div class="grid grid-cols-4 gap-4 mt-3">
                        <div class="border p-2 text-center">Q1: ___/20</div>
                        <div class="border p-2 text-center">Q2: ___/25</div>
                        <div class="border p-2 text-center">Q3: ___/30</div>
                        <div class="border p-2 text-center">Q4: ___/25</div>
                    </div>
                    <div class="border p-2 text-center mt-2 font-bold">Total: ___/100</div>
                </div>
            `
        },
        3: {
            title: "Question 1",
            content: `
                <div class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-2">
                        <h2 class="text-xl font-bold">Question 1</h2>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded">[20 marks]</span>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="border rounded-lg p-4">
                            <p class="mb-3"><strong>a)</strong> Solve the following equations:</p>
                            <div class="ml-4 space-y-2">
                                <p><strong>i)</strong> 3x - 7 = 2x + 5 <span class="float-right">[3 marks]</span></p>
                                <div class="space-y-1 ml-4">
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                </div>
                                
                                <p class="mt-4"><strong>ii)</strong> 2(x + 3) = 4x - 1 <span class="float-right">[4 marks]</span></p>
                                <div class="space-y-1 ml-4">
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <p class="mb-3"><strong>b)</strong> A rectangle has length (2x + 3) cm and width (x - 1) cm.</p>
                            <p class="mb-2">If the perimeter is 28 cm, find:</p>
                            <div class="ml-4 space-y-3">
                                <p><strong>i)</strong> The value of x <span class="float-right">[5 marks]</span></p>
                                <div class="space-y-1 ml-4">
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                </div>
                                
                                <p class="mt-4"><strong>ii)</strong> The actual dimensions of the rectangle <span class="float-right">[4 marks]</span></p>
                                <div class="space-y-1 ml-4">
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                </div>
                                
                                <p class="mt-4"><strong>iii)</strong> The area of the rectangle <span class="float-right">[4 marks]</span></p>
                                <div class="space-y-1 ml-4">
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `
        },
        4: {
            title: "Question 2",
            content: `
                <div class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-2">
                        <h2 class="text-xl font-bold">Question 2</h2>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded">[25 marks]</span>
                    </div>
                    
                    <div class="border rounded-lg p-4">
                        <p class="mb-4"><strong>a)</strong> The table below shows the marks obtained by 50 students in a mathematics test:</p>
                        
                        <table class="w-full border-collapse border border-gray-300 mb-4">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 p-2">Marks</th>
                                    <th class="border border-gray-300 p-2">0-10</th>
                                    <th class="border border-gray-300 p-2">11-20</th>
                                    <th class="border border-gray-300 p-2">21-30</th>
                                    <th class="border border-gray-300 p-2">31-40</th>
                                    <th class="border border-gray-300 p-2">41-50</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-300 p-2 font-semibold">Frequency</td>
                                    <td class="border border-gray-300 p-2 text-center">3</td>
                                    <td class="border border-gray-300 p-2 text-center">7</td>
                                    <td class="border border-gray-300 p-2 text-center">15</td>
                                    <td class="border border-gray-300 p-2 text-center">18</td>
                                    <td class="border border-gray-300 p-2 text-center">7</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="space-y-4">
                            <div>
                                <p><strong>i)</strong> Calculate the mean mark <span class="float-right">[6 marks]</span></p>
                                <div class="space-y-1 ml-4 mt-2">
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                </div>
                            </div>
                            
                            <div>
                                <p><strong>ii)</strong> Find the modal class <span class="float-right">[2 marks]</span></p>
                                <div class="space-y-1 ml-4 mt-2">
                                    <div class="border-b border-dotted h-4"></div>
                                </div>
                            </div>
                            
                            <div>
                                <p><strong>iii)</strong> Calculate the median mark <span class="float-right">[5 marks]</span></p>
                                <div class="space-y-1 ml-4 mt-2">
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                    <div class="border-b border-dotted h-4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `
        }
    }
});

// Normalize grades to always be an array
const normalizedGrades = computed(() => {
    if (Array.isArray(props.grades)) {
        return props.grades;
    } else if (props.grades && typeof props.grades === 'object') {
        return Object.values(props.grades);
    } else {
        return [];
    }
});

// Search and filter functions
const applyFilters = () => {
    const params = {};
    
    if (searchQuery.value) params.search = searchQuery.value;
    if (selectedType.value !== 'all') params.type = selectedType.value;
    if (selectedSubject.value) params.subject = selectedSubject.value;
    if (selectedGrade.value) params.grade = selectedGrade.value;
    if (selectedYear.value) params.year = selectedYear.value;
    
    router.get(route('library.index'), params, {
        preserveState: true,
        preserveScroll: true
    });
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedType.value = 'all';
    selectedSubject.value = '';
    selectedGrade.value = '';
    selectedYear.value = '';
    
    router.get(route('library.index'));
};

// Document viewer functions
const openViewer = (resource) => {
    selectedResource.value = resource;
    showViewer.value = true;
    currentPage.value = 1;
    modalSearchQuery.value = '';
    searchResults.value = [];
    showSearchResults.value = false;
    
    // Set total pages based on resource type
    if (resource.type === 'book') {
        totalPages.value = 78; // Sample book has 78 pages
    } else if (resource.type === 'past_paper') {
        totalPages.value = 8; // Sample past paper has 8 pages
    } else {
        totalPages.value = 10; // Default for other documents
    }
    
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
    
    // Enable content security measures
    enableContentProtection();
};

const closeViewer = () => {
    showViewer.value = false;
    selectedResource.value = null;
    currentPage.value = 1;
    modalSearchQuery.value = '';
    searchResults.value = [];
    showSearchResults.value = false;
    // Restore body scroll
    document.body.style.overflow = '';
    // Disable content security measures
    disableContentProtection();
};

const handleIframeError = () => {
    console.warn('Failed to load resource in iframe, falling back to sample content');
};

// Page navigation functions
const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Get current page content
const getCurrentPageContent = computed(() => {
    if (!selectedResource.value) return null;
    
    const resourceType = selectedResource.value.type;
    const pages = samplePages.value[resourceType] || {};
    const pageContent = pages[currentPage.value];
    
    if (pageContent) {
        return pageContent;
    }
    
    // Generate default content for pages that don't have specific content
    return {
        title: `Page ${currentPage.value}`,
        content: `
            <div class="text-center py-12">
                <h2 class="text-2xl font-bold text-slate-800 mb-4">Page ${currentPage.value}</h2>
                <p class="text-slate-600 mb-4">Content for this page would be loaded from the actual document.</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 max-w-md mx-auto">
                    <p class="text-sm text-blue-700">
                        <strong>Sample Page:</strong> This demonstrates the page navigation functionality. 
                        Real documents will display their actual page content here.
                    </p>
                </div>
            </div>
        `
    };
});

// Search functionality
const performSearch = () => {
    if (!modalSearchQuery.value.trim()) {
        searchResults.value = [];
        showSearchResults.value = false;
        return;
    }
    
    const query = modalSearchQuery.value.toLowerCase().trim();
    const results = [];
    const resourceType = selectedResource.value.type;
    const pages = samplePages.value[resourceType] || {};
    
    // Search through all pages
    Object.entries(pages).forEach(([pageNumber, pageData]) => {
        const content = pageData.content.toLowerCase();
        const title = pageData.title.toLowerCase();
        
        if (content.includes(query) || title.includes(query)) {
            // Count occurrences
            const matches = (content.match(new RegExp(query, 'g')) || []).length;
            results.push({
                page: parseInt(pageNumber),
                title: pageData.title,
                matches: matches,
                preview: extractSearchPreview(pageData.content, query)
            });
        }
    });
    
    // Also search common terms across all pages
    if (query.includes('equation') || query.includes('solve') || query.includes('formula')) {
        results.push({
            page: 15,
            title: 'Chapter 2: Basic Concepts',
            matches: 3,
            preview: 'Examples of solving equations and mathematical formulas...'
        });
        results.push({
            page: 45,
            title: 'Chapter 3: Advanced Topics',
            matches: 2,
            preview: 'Quadratic equations and formula applications...'
        });
    }
    
    if (query.includes('exercise') || query.includes('practice') || query.includes('question')) {
        results.push({
            page: 78,
            title: 'Chapter 4: Practice Exercises',
            matches: 5,
            preview: 'Practice questions and exercises for skill development...'
        });
    }
    
    // Sort by relevance (number of matches)
    results.sort((a, b) => b.matches - a.matches);
    
    searchResults.value = results;
    showSearchResults.value = true;
};

const extractSearchPreview = (content, query) => {
    // Remove HTML tags for preview
    const plainText = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
    const index = plainText.toLowerCase().indexOf(query.toLowerCase());
    
    if (index === -1) return plainText.substring(0, 100) + '...';
    
    const start = Math.max(0, index - 50);
    const end = Math.min(plainText.length, index + query.length + 50);
    const preview = plainText.substring(start, end);
    
    return (start > 0 ? '...' : '') + preview + (end < plainText.length ? '...' : '');
};

const goToSearchResult = (result) => {
    currentPage.value = result.page;
    showSearchResults.value = false;
};

const clearSearch = () => {
    modalSearchQuery.value = '';
    searchResults.value = [];
    showSearchResults.value = false;
};

// Content Protection Functions
const enableContentProtection = () => {
    // Disable right-click context menu
    document.addEventListener('contextmenu', preventRightClick);
    
    // Disable keyboard shortcuts for screenshots and developer tools
    document.addEventListener('keydown', preventKeyboardShortcuts);
    
    // Disable text selection on content area
    document.addEventListener('selectstart', preventTextSelection);
    document.addEventListener('dragstart', preventDragStart);
    
    // Disable print functionality
    window.addEventListener('beforeprint', preventPrint);
    
    // Add content security overlay
    addSecurityOverlay();
    
    // Blur content when window loses focus (screenshot attempt detection)
    window.addEventListener('blur', blurContentOnFocusLoss);
    window.addEventListener('focus', restoreContentOnFocus);
    
    // Monitor for screenshot attempts using Visibility API
    document.addEventListener('visibilitychange', handleVisibilityChange);
};

const disableContentProtection = () => {
    // Remove all event listeners
    document.removeEventListener('contextmenu', preventRightClick);
    document.removeEventListener('keydown', preventKeyboardShortcuts);
    document.removeEventListener('selectstart', preventTextSelection);
    document.removeEventListener('dragstart', preventDragStart);
    window.removeEventListener('beforeprint', preventPrint);
    window.removeEventListener('blur', blurContentOnFocusLoss);
    window.removeEventListener('focus', restoreContentOnFocus);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    
    // Remove security overlay
    removeSecurityOverlay();
};

const preventRightClick = (e) => {
    e.preventDefault();
    e.stopPropagation();
    showSecurityAlert('Right-click is disabled for content protection');
    return false;
};

const preventKeyboardShortcuts = (e) => {
    // Disable common screenshot and developer tool shortcuts
    const forbiddenKeys = [
        'F12', // Developer Tools
        'F3', // Find
        'F5', // Refresh
        'PrintScreen', // Screenshot
        'Insert' // Screenshot on some keyboards
    ];
    
    const forbiddenCombinations = [
        { ctrl: true, key: 'u' }, // View Source
        { ctrl: true, key: 'i' }, // Developer Tools
        { ctrl: true, key: 'j' }, // Console
        { ctrl: true, key: 's' }, // Save
        { ctrl: true, key: 'p' }, // Print
        { ctrl: true, key: 'a' }, // Select All
        { ctrl: true, key: 'c' }, // Copy
        { ctrl: true, key: 'v' }, // Paste
        { ctrl: true, key: 'x' }, // Cut
        { ctrl: true, key: 'r' }, // Refresh
        { ctrl: true, key: 'f' }, // Find
        { ctrl: true, shift: true, key: 'i' }, // Developer Tools
        { ctrl: true, shift: true, key: 'j' }, // Console
        { ctrl: true, shift: true, key: 'c' }, // Inspect Element
        { alt: true, key: 'PrintScreen' }, // Alt + PrintScreen
        { cmd: true, key: 'i' }, // Mac Developer Tools
        { cmd: true, key: 'u' }, // Mac View Source
        { cmd: true, key: 's' }, // Mac Save
        { cmd: true, key: 'p' }, // Mac Print
        { cmd: true, key: 'c' }, // Mac Copy
        { cmd: true, key: 'shift', key: '4' }, // Mac Screenshot
        { cmd: true, key: 'shift', key: '3' } // Mac Screenshot
    ];
    
    if (forbiddenKeys.includes(e.key) || forbiddenKeys.includes(e.code)) {
        e.preventDefault();
        e.stopPropagation();
        showSecurityAlert('This keyboard shortcut is disabled for content protection');
        return false;
    }
    
    for (const combo of forbiddenCombinations) {
        const ctrlPressed = combo.ctrl && (e.ctrlKey || e.metaKey);
        const shiftPressed = combo.shift ? e.shiftKey : true;
        const keyPressed = combo.key.toLowerCase() === e.key.toLowerCase();
        
        if (ctrlPressed && shiftPressed && keyPressed) {
            e.preventDefault();
            e.stopPropagation();
            showSecurityAlert('This keyboard shortcut is disabled for content protection');
            return false;
        }
    }
};

const preventTextSelection = (e) => {
    e.preventDefault();
    return false;
};

const preventDragStart = (e) => {
    e.preventDefault();
    return false;
};

const preventPrint = (e) => {
    e.preventDefault();
    showSecurityAlert('Printing is disabled for content protection');
    return false;
};

const showSecurityAlert = (message) => {
    // Create a subtle notification instead of alert to avoid disrupting UX
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-[9999] text-sm';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
};

const addSecurityOverlay = () => {
    // Add invisible overlay to detect inspect element attempts
    const overlay = document.createElement('div');
    overlay.id = 'content-security-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: transparent;
        z-index: 1;
        pointer-events: none;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    `;
    document.body.appendChild(overlay);
    
    // Add security watermarks
    addSecurityWatermarks();
};

const addSecurityWatermarks = () => {
    const watermarks = [];
    const user = props.auth?.user;
    const watermarkText = user ? `${user.name} - ${user.email} - ${new Date().toLocaleString()}` : 'Protected Content';
    
    // Add multiple subtle watermarks
    for (let i = 0; i < 8; i++) {
        const watermark = document.createElement('div');
        watermark.className = 'security-watermark';
        watermark.textContent = watermarkText;
        watermark.style.cssText = `
            position: fixed;
            top: ${Math.random() * 80 + 10}%;
            left: ${Math.random() * 80 + 10}%;
            color: rgba(0, 0, 0, 0.03);
            font-size: 12px;
            font-weight: bold;
            pointer-events: none;
            user-select: none;
            z-index: 2;
            transform: rotate(${Math.random() * 60 - 30}deg);
            font-family: monospace;
        `;
        document.body.appendChild(watermark);
        watermarks.push(watermark);
    }
};

const removeSecurityOverlay = () => {
    const overlay = document.getElementById('content-security-overlay');
    if (overlay) {
        overlay.parentNode.removeChild(overlay);
    }
    
    // Remove security watermarks
    const watermarks = document.querySelectorAll('.security-watermark');
    watermarks.forEach(watermark => {
        if (watermark.parentNode) {
            watermark.parentNode.removeChild(watermark);
        }
    });
};

const blurContentOnFocusLoss = () => {
    const modal = document.querySelector('[data-modal-content]');
    if (modal) {
        modal.style.filter = 'blur(10px)';
        modal.style.transition = 'filter 0.1s ease';
    }
};

const restoreContentOnFocus = () => {
    const modal = document.querySelector('[data-modal-content]');
    if (modal) {
        modal.style.filter = 'none';
    }
};

const handleVisibilityChange = () => {
    if (document.hidden) {
        // Page is hidden - potential screenshot attempt
        const modal = document.querySelector('[data-modal-content]');
        if (modal) {
            modal.style.filter = 'blur(15px)';
            modal.style.transition = 'filter 0.05s ease';
        }
    } else {
        // Page is visible again
        const modal = document.querySelector('[data-modal-content]');
        if (modal) {
            modal.style.filter = 'none';
        }
    }
};

// Resource type icons and colors
const getTypeIcon = (type) => {
    const icons = {
        book: 'M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197',
        past_paper: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        video: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
        audio: 'M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M9 17a1 1 0 01-1-1V8a1 1 0 011-1h1.5a1 1 0 01.707.293L13 9h3a1 1 0 011 1v6a1 1 0 01-1 1h-3l-1.793 1.793A1 1 0 019 17z'
    };
    return icons[type] || icons.document;
};

const getTypeColor = (type) => {
    const colors = {
        book: 'from-blue-500 to-indigo-600',
        past_paper: 'from-green-500 to-emerald-600',
        document: 'from-purple-500 to-violet-600',
        video: 'from-red-500 to-rose-600',
        audio: 'from-yellow-500 to-orange-600'
    };
    return colors[type] || colors.document;
};

// File size formatting function
const formatFileSize = (bytes) => {
    if (!bytes) return 'N/A';
    
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

// Cleanup on component unmount
onUnmounted(() => {
    // Restore body scroll if modal was open
    document.body.style.overflow = '';
});
</script>

<template>
    <Head title="Digital Library" />
    
    <DashboardLayout title="Digital Library" subtitle="Access books, past papers, and educational resources" :stats="stats">
        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Total Resources</p>
                            <p class="text-3xl font-bold text-slate-900">{{ stats.total_resources.toLocaleString() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 00-2 2v2m0 0V9a2 2 0 012-2m0 2a2 2 0 012-2h4a2 2 0 012 2m0 2v10"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <Link :href="route('library.books')" class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Books</p>
                            <p class="text-3xl font-bold text-slate-900">{{ stats.books.toLocaleString() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"></path>
                            </svg>
                        </div>
                    </div>
                </Link>

                <Link :href="route('library.pastPapers')" class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Past Papers</p>
                            <p class="text-3xl font-bold text-slate-900">{{ stats.past_papers.toLocaleString() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </Link>

                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">Documents</p>
                            <p class="text-3xl font-bold text-slate-900">{{ stats.documents.toLocaleString() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-violet-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50">
                <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
                    <!-- Search -->
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <input 
                                v-model="searchQuery" 
                                @input="applyFilters"
                                type="text" 
                                placeholder="Search resources..." 
                                class="w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 pl-10 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"
                            >
                            <svg class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-wrap gap-3">
                        <select v-model="selectedType" @change="applyFilters" class="bg-slate-100/70 px-4 py-2 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="all">All Types</option>
                            <option value="book">Books</option>
                            <option value="past_paper">Past Papers</option>
                            <option value="document">Documents</option>
                            <option value="video">Videos</option>
                            <option value="audio">Audio</option>
                        </select>

                        <select v-model="selectedSubject" @change="applyFilters" class="bg-slate-100/70 px-4 py-2 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">All Subjects</option>
                            <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                        </select>

                        <select v-model="selectedGrade" @change="applyFilters" class="bg-slate-100/70 px-4 py-2 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">All Grades</option>
                            <option v-for="grade in normalizedGrades" :key="grade" :value="grade">{{ grade }}</option>
                        </select>

                        <select v-model="selectedYear" @change="applyFilters" class="bg-slate-100/70 px-4 py-2 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">All Years</option>
                            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                        </select>

                        <button @click="resetFilters" class="px-4 py-2 text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-2xl transition-colors duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                        
                        <!-- Admin Link -->
                        <Link 
                            v-if="user.role === 'admin'" 
                            :href="route('admin.library.index')"
                            class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-2xl text-sm font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Manage
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Resources Grid -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden">
                <div class="p-6 border-b border-slate-200/50">
                    <h2 class="text-xl font-bold text-slate-800">Resources</h2>
                    <p class="text-slate-600 text-sm">{{ resources?.total || 0 }} resources found</p>
                </div>

                <div v-if="(resources?.data?.length || 0) > 0" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <button 
                            v-for="resource in (resources?.data || [])" 
                            :key="resource.id"
                            @click="openViewer(resource)"
                            class="bg-white rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group text-left w-full"
                        >
                            <!-- Resource Type Icon -->
                            <div :class="['h-32 bg-gradient-to-br', getTypeColor(resource.type), 'flex items-center justify-center relative overflow-hidden']">
                                <svg class="w-16 h-16 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="getTypeIcon(resource.type)"></path>
                                </svg>
                                <div class="absolute top-2 right-2 bg-black/20 backdrop-blur-sm px-2 py-1 rounded-full">
                                    <span class="text-xs font-medium text-white capitalize">{{ resource.type.replace('_', ' ') }}</span>
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="font-semibold text-slate-800 mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors duration-200">
                                    {{ resource.title }}
                                </h3>
                                
                                <div class="space-y-2 text-sm text-slate-600">
                                    <div v-if="resource.subject" class="flex items-center">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">
                                            {{ resource.subject.name }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <span v-if="resource.grade_level">Grade {{ resource.grade_level }}</span>
                                        <span v-if="resource.year">{{ resource.year }}</span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-slate-500">{{ formatFileSize(resource.file_size) }}</span>
                                        <span class="text-xs text-slate-500">{{ resource.view_count }} views</span>
                                    </div>
                                </div>

                                <!-- Protection Indicator -->
                                <div v-if="resource.is_protected" class="mt-3 flex items-center text-xs text-green-600">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Protected Content
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="resources?.links && resources.links.length > 3" class="mt-8 flex justify-center">
                        <div class="flex space-x-2">
                            <template v-for="(link, index) in (resources?.links || [])" :key="`link-${index}`">
                                <Link 
                                    v-if="link?.url"
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 text-sm rounded-lg transition-colors duration-200',
                                        link.active ? 'bg-indigo-500 text-white' : 'text-slate-600 hover:bg-slate-100'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    :class="[
                                        'px-4 py-2 text-sm rounded-lg transition-colors duration-200 cursor-not-allowed',
                                        'text-slate-400 bg-slate-100'
                                    ]"
                                    v-html="link?.label || ''"
                                />
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="p-12 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 00-2 2v2m0 0V9a2 2 0 012-2m0 2a2 2 0 012-2h4a2 2 0 012 2m0 2v10"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">No resources found</h3>
                    <p class="text-slate-500 mb-6">Try adjusting your search criteria or filters.</p>
                    <button @click="resetFilters" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Document Viewer Modal -->
        <div 
            v-if="showViewer && selectedResource" 
            class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            @click="closeViewer"
        >
            <div 
                class="bg-white rounded-3xl shadow-2xl w-full h-full max-w-7xl max-h-[95vh] flex flex-col overflow-hidden modal-content"
                @click.stop
                data-modal-content
                style="user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; -webkit-touch-callout: none; -webkit-tap-highlight-color: transparent;"
            >
                <!-- Modal Header with Search and Navigation -->
                <div class="flex flex-col border-b border-slate-200/50 bg-slate-50/80 backdrop-blur-sm">
                    <!-- Top Header -->
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center space-x-3">
                            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br', getTypeColor(selectedResource.type)]">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(selectedResource.type)"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-800">{{ selectedResource.title }}</h2>
                                <p class="text-sm text-slate-600">{{ selectedResource.subject?.name || 'Digital Resource' }} • {{ getCurrentPageContent?.title || `Page ${currentPage}` }}</p>
                            </div>
                        </div>
                        <button 
                            @click="closeViewer"
                            class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-200/50 rounded-xl transition-colors duration-200"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Search Bar -->
                    <div class="px-6 pb-4">
                        <div class="relative">
                            <input
                                v-model="modalSearchQuery"
                                @keyup.enter="performSearch"
                                type="text"
                                placeholder="Search for topics, keywords, or page numbers..."
                                class="w-full bg-white/80 backdrop-blur-sm px-4 py-3 pl-12 pr-32 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200 border border-slate-200"
                            >
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center space-x-1">
                                <button
                                    v-if="modalSearchQuery"
                                    @click="clearSearch"
                                    class="p-1 text-slate-400 hover:text-slate-600 rounded"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <button
                                    @click="performSearch"
                                    class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-xs rounded-lg transition-colors duration-200"
                                >
                                    Search
                                </button>
                            </div>
                        </div>

                        <!-- Search Results Dropdown -->
                        <div v-if="showSearchResults && searchResults.length > 0" class="absolute z-10 mt-2 w-full bg-white rounded-2xl shadow-xl border border-slate-200 max-h-64 overflow-y-auto">
                            <div class="p-3 border-b border-slate-200">
                                <p class="text-sm font-medium text-slate-600">{{ searchResults.length }} result{{ searchResults.length !== 1 ? 's' : '' }} found</p>
                            </div>
                            <div class="py-2">
                                <button 
                                    v-for="result in searchResults" 
                                    :key="`search-${result.page}`"
                                    @click="goToSearchResult(result)"
                                    class="w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors duration-150 border-b border-slate-100 last:border-b-0"
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-slate-800">{{ result.title }}</span>
                                        <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Page {{ result.page }}</span>
                                    </div>
                                    <p class="text-sm text-slate-600 truncate">{{ result.preview }}</p>
                                    <p class="text-xs text-slate-500 mt-1">{{ result.matches }} match{{ result.matches !== 1 ? 'es' : '' }}</p>
                                </button>
                            </div>
                        </div>

                        <div v-else-if="showSearchResults && searchResults.length === 0 && modalSearchQuery" class="absolute z-10 mt-2 w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-4">
                            <div class="text-center">
                                <svg class="mx-auto h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm text-slate-600">No results found for "{{ modalSearchQuery }}"</p>
                                <p class="text-xs text-slate-500">Try searching for "equation", "exercise", or "formula"</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Viewer with Pagination -->
                <div class="flex-1 bg-slate-100 overflow-hidden relative">
                    <div class="w-full h-full overflow-y-auto">
                        <div class="max-w-4xl mx-auto p-8">
                            <!-- Page Content Display -->
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden min-h-[600px]">
                                <!-- Page Header -->
                                <div :class="['p-6 bg-gradient-to-r text-white', getTypeColor(selectedResource.type)]">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getTypeIcon(selectedResource.type)"></path>
                                            </svg>
                                            <div>
                                                <h3 class="text-xl font-bold">{{ getCurrentPageContent?.title || `Page ${currentPage}` }}</h3>
                                                <p class="text-white/80 text-sm">{{ selectedResource.title }} • Page {{ currentPage }} of {{ totalPages }}</p>
                                            </div>
                                        </div>
                                        <!-- Quick Page Jump -->
                                        <div class="flex items-center space-x-2">
                                            <span class="text-white/80 text-sm">Go to:</span>
                                            <input 
                                                :value="currentPage"
                                                @keyup.enter="goToPage(parseInt($event.target.value))"
                                                @blur="goToPage(parseInt($event.target.value))"
                                                type="number" 
                                                :min="1" 
                                                :max="totalPages"
                                                class="w-16 px-2 py-1 text-sm text-slate-800 rounded border-0 focus:ring-2 focus:ring-white/50"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Page Content -->
                                <div 
                                    class="p-8 min-h-[500px] select-none" 
                                    style="
                                        user-select: none; 
                                        -webkit-user-select: none; 
                                        -moz-user-select: none; 
                                        -ms-user-select: none;
                                        -webkit-touch-callout: none;
                                        -webkit-tap-highlight-color: transparent;
                                        pointer-events: auto;
                                    "
                                    v-html="getCurrentPageContent?.content || ''"
                                    @contextmenu.prevent
                                    @selectstart.prevent
                                    @dragstart.prevent
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer with Navigation Controls -->
                <div class="flex items-center justify-between p-4 border-t border-slate-200/50 bg-slate-50/80">
                    <!-- Left Side: Resource Info -->
                    <div class="flex items-center space-x-4 text-sm text-slate-600">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            {{ selectedResource.file_type?.toUpperCase() }}
                        </span>
                        <span v-if="selectedResource.file_size" class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            {{ formatFileSize(selectedResource.file_size) }}
                        </span>
                        <span v-if="selectedResource.is_protected" class="flex items-center text-green-600">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            Protected
                        </span>
                    </div>

                    <!-- Center: Page Navigation -->
                    <div class="flex items-center space-x-4">
                        <!-- Previous Page Button -->
                        <button 
                            @click="previousPage"
                            :disabled="currentPage <= 1"
                            :class="[
                                'p-2 rounded-xl transition-all duration-200 flex items-center space-x-1',
                                currentPage <= 1 
                                    ? 'text-slate-400 cursor-not-allowed' 
                                    : 'text-slate-600 hover:text-slate-800 hover:bg-slate-200/50'
                            ]"
                            title="Previous Page"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            <span class="text-sm font-medium">Previous</span>
                        </button>

                        <!-- Page Info -->
                        <div class="flex items-center space-x-3 text-sm">
                            <span class="text-slate-600">Page</span>
                            <input 
                                :value="currentPage"
                                @keyup.enter="goToPage(parseInt($event.target.value))"
                                @blur="goToPage(parseInt($event.target.value))"
                                type="number" 
                                :min="1" 
                                :max="totalPages"
                                class="w-16 px-2 py-1 text-center text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"
                            >
                            <span class="text-slate-600">of {{ totalPages }}</span>
                        </div>

                        <!-- Next Page Button -->
                        <button 
                            @click="nextPage"
                            :disabled="currentPage >= totalPages"
                            :class="[
                                'p-2 rounded-xl transition-all duration-200 flex items-center space-x-1',
                                currentPage >= totalPages 
                                    ? 'text-slate-400 cursor-not-allowed' 
                                    : 'text-slate-600 hover:text-slate-800 hover:bg-slate-200/50'
                            ]"
                            title="Next Page"
                        >
                            <span class="text-sm font-medium">Next</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Right Side: Action Buttons -->
                    <div class="flex items-center space-x-2">
                        <button 
                            @click="closeViewer"
                            class="px-6 py-2 bg-slate-500 hover:bg-slate-600 text-white rounded-xl transition-colors duration-200"
                        >
                            Close Viewer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<style scoped>
/* Content Security Styles */
/* Disable text selection globally within modal */
.modal-content * {
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    -webkit-touch-callout: none !important;
    -webkit-tap-highlight-color: transparent !important;
}

/* Prevent dragging of images and content */
.modal-content img,
.modal-content svg,
.modal-content * {
    -webkit-user-drag: none !important;
    -khtml-user-drag: none !important;
    -moz-user-drag: none !important;
    -o-user-drag: none !important;
    user-drag: none !important;
    pointer-events: auto;
}

/* Hide scrollbars that might reveal content length */
.modal-content::-webkit-scrollbar {
    width: 6px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

/* Prevent content caching by browser */
.modal-content {
    -webkit-transform: translateZ(0);
    -moz-transform: translateZ(0);
    -ms-transform: translateZ(0);
    -o-transform: translateZ(0);
    transform: translateZ(0);
}

/* Block inspect element overlay attempts */
.modal-content::selection {
    background: transparent !important;
}

.modal-content::-moz-selection {
    background: transparent !important;
}

/* Disable image context menu */
.modal-content img {
    -webkit-touch-callout: none !important;
    -webkit-user-select: none !important;
    -khtml-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    user-select: none !important;
    pointer-events: none !important;
}
</style>