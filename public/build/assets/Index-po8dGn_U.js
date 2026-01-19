import{r as u,x as re,E as _e,c as n,o as l,a as z,u as _,h as Ce,w as I,b as e,j as m,t as r,l as N,d as C,v as le,k as ne,g as H,F as y,f as j,i as A,n as w,q as D,e as Q,m as ae}from"./app-PgZLZy6T.js";import{_ as je}from"./DashboardLayout-Cf8-137X.js";import{_ as Me}from"./_plugin-vue_export-helper-DlAUqK2U.js";const Pe={class:"space-y-6"},Se={class:"grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"},Te={class:"bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50"},Le={class:"flex items-center justify-between"},Ee={class:"text-3xl font-bold text-slate-900"},Ae={class:"flex items-center justify-between"},Be={class:"text-3xl font-bold text-slate-900"},Ve={class:"flex items-center justify-between"},ze={class:"text-3xl font-bold text-slate-900"},Ie={class:"bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50"},Ne={class:"flex items-center justify-between"},He={class:"text-3xl font-bold text-slate-900"},De={class:"bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50"},Fe={class:"flex flex-col lg:flex-row gap-4 items-center justify-between"},qe={class:"flex-1 max-w-md"},Oe={class:"relative"},$e={class:"flex flex-wrap gap-3"},Re=["value"],Qe=["value"],Ke=["value"],Ue={class:"bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl border border-slate-200/50 overflow-hidden"},Ge={class:"p-6 border-b border-slate-200/50"},We={class:"text-slate-600 text-sm"},Ye={key:0,class:"p-6"},Xe={class:"grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"},Je=["onClick"],Ze={class:"w-16 h-16 text-white/80",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},et=["d"],tt={class:"absolute top-2 right-2 bg-black/20 backdrop-blur-sm px-2 py-1 rounded-full"},st={class:"text-xs font-medium text-white capitalize"},ot={class:"p-4"},rt={class:"font-semibold text-slate-800 mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors duration-200"},lt={class:"space-y-2 text-sm text-slate-600"},nt={key:0,class:"flex items-center"},at={class:"inline-flex items-center px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full"},it={class:"flex items-center justify-between"},dt={key:0},ct={key:1},ut={class:"flex items-center justify-between"},pt={class:"text-xs text-slate-500"},vt={class:"text-xs text-slate-500"},ht={key:0,class:"mt-3 flex items-center text-xs text-green-600"},mt={key:0,class:"mt-8 flex justify-center"},bt={class:"flex space-x-2"},xt=["innerHTML"],gt={key:1,class:"p-12 text-center"},ft={class:"flex flex-col border-b border-slate-200/50 bg-slate-50/80 backdrop-blur-sm"},yt={class:"flex items-center justify-between p-6"},wt={class:"flex items-center space-x-3"},kt={class:"w-5 h-5 text-white",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},_t=["d"],Ct={class:"text-xl font-bold text-slate-800"},jt={class:"text-sm text-slate-600"},Mt={class:"px-6 pb-4"},Pt={class:"relative"},St={class:"absolute inset-y-0 right-0 pr-2 flex items-center space-x-1"},Tt={key:0,class:"absolute z-10 mt-2 w-full bg-white rounded-2xl shadow-xl border border-slate-200 max-h-64 overflow-y-auto"},Lt={class:"p-3 border-b border-slate-200"},Et={class:"text-sm font-medium text-slate-600"},At={class:"py-2"},Bt=["onClick"],Vt={class:"flex items-center justify-between mb-1"},zt={class:"font-medium text-slate-800"},It={class:"text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded"},Nt={class:"text-sm text-slate-600 truncate"},Ht={class:"text-xs text-slate-500 mt-1"},Dt={key:1,class:"absolute z-10 mt-2 w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-4"},Ft={class:"text-center"},qt={class:"mt-2 text-sm text-slate-600"},Ot={class:"flex-1 bg-slate-100 overflow-hidden relative"},$t={class:"w-full h-full overflow-y-auto"},Rt={class:"max-w-4xl mx-auto p-8"},Qt={class:"bg-white rounded-xl shadow-lg overflow-hidden min-h-[600px]"},Kt={class:"flex items-center justify-between"},Ut={class:"flex items-center space-x-4"},Gt={class:"w-8 h-8",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},Wt=["d"],Yt={class:"text-xl font-bold"},Xt={class:"text-white/80 text-sm"},Jt={class:"flex items-center space-x-2"},Zt=["value","max"],es=["innerHTML"],ts={class:"flex items-center justify-between p-4 border-t border-slate-200/50 bg-slate-50/80"},ss={class:"flex items-center space-x-4 text-sm text-slate-600"},os={class:"flex items-center"},rs={key:0,class:"flex items-center"},ls={key:1,class:"flex items-center text-green-600"},ns={class:"flex items-center space-x-4"},as=["disabled"],is={class:"flex items-center space-x-3 text-sm"},ds=["value","max"],cs={class:"text-slate-600"},us=["disabled"],ps={__name:"Index",props:{auth:Object,resources:Object,subjects:Array,years:Array,grades:[Array,Object],stats:Object,filters:Object},setup(c){const p=c,ie=p.auth?.user||{name:"Guest",role:"guest"},M=u(p.filters.search||""),P=u(p.filters.type||"all"),S=u(p.filters.subject||""),T=u(p.filters.grade||""),L=u(p.filters.year||""),F=u(!1),d=u(null),a=u(1),v=u(50),b=u(""),x=u([]),g=u(!1),K=u({book:{1:{title:"Table of Contents",content:`
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
            `},3:{title:"Chapter 1: Introduction",content:`
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
            `},15:{title:"Chapter 2: Basic Concepts",content:`
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
            `},45:{title:"Chapter 3: Advanced Topics",content:`
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
            `},78:{title:"Chapter 4: Practice Exercises",content:`
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
            `}},past_paper:{1:{title:"Cover Page",content:`
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
            `},2:{title:"Instructions",content:`
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
            `},3:{title:"Question 1",content:`
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
            `},4:{title:"Question 2",content:`
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
            `}}}),de=re(()=>Array.isArray(p.grades)?p.grades:p.grades&&typeof p.grades=="object"?Object.values(p.grades):[]),E=()=>{const s={};M.value&&(s.search=M.value),P.value!=="all"&&(s.type=P.value),S.value&&(s.subject=S.value),T.value&&(s.grade=T.value),L.value&&(s.year=L.value),ae.get(route("library.index"),s,{preserveState:!0,preserveScroll:!0})},U=()=>{M.value="",P.value="all",S.value="",T.value="",L.value="",ae.get(route("library.index"))},ce=s=>{d.value=s,F.value=!0,a.value=1,b.value="",x.value=[],g.value=!1,s.type==="book"?v.value=78:s.type==="past_paper"?v.value=8:v.value=10,document.body.style.overflow="hidden",be()},q=()=>{F.value=!1,d.value=null,a.value=1,b.value="",x.value=[],g.value=!1,document.body.style.overflow="",xe()},ue=()=>{a.value<v.value&&a.value++},pe=()=>{a.value>1&&a.value--},B=s=>{s>=1&&s<=v.value&&(a.value=s)},O=re(()=>{if(!d.value)return null;const s=d.value.type,o=(K.value[s]||{})[a.value];return o||{title:`Page ${a.value}`,content:`
            <div class="text-center py-12">
                <h2 class="text-2xl font-bold text-slate-800 mb-4">Page ${a.value}</h2>
                <p class="text-slate-600 mb-4">Content for this page would be loaded from the actual document.</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 max-w-md mx-auto">
                    <p class="text-sm text-blue-700">
                        <strong>Sample Page:</strong> This demonstrates the page navigation functionality. 
                        Real documents will display their actual page content here.
                    </p>
                </div>
            </div>
        `}}),G=()=>{if(!b.value.trim()){x.value=[],g.value=!1;return}const s=b.value.toLowerCase().trim(),t=[],o=d.value.type,i=K.value[o]||{};Object.entries(i).forEach(([f,h])=>{const k=h.content.toLowerCase(),we=h.title.toLowerCase();if(k.includes(s)||we.includes(s)){const ke=(k.match(new RegExp(s,"g"))||[]).length;t.push({page:parseInt(f),title:h.title,matches:ke,preview:ve(h.content,s)})}}),(s.includes("equation")||s.includes("solve")||s.includes("formula"))&&(t.push({page:15,title:"Chapter 2: Basic Concepts",matches:3,preview:"Examples of solving equations and mathematical formulas..."}),t.push({page:45,title:"Chapter 3: Advanced Topics",matches:2,preview:"Quadratic equations and formula applications..."})),(s.includes("exercise")||s.includes("practice")||s.includes("question"))&&t.push({page:78,title:"Chapter 4: Practice Exercises",matches:5,preview:"Practice questions and exercises for skill development..."}),t.sort((f,h)=>h.matches-f.matches),x.value=t,g.value=!0},ve=(s,t)=>{const o=s.replace(/<[^>]*>/g," ").replace(/\s+/g," ").trim(),i=o.toLowerCase().indexOf(t.toLowerCase());if(i===-1)return o.substring(0,100)+"...";const f=Math.max(0,i-50),h=Math.min(o.length,i+t.length+50),k=o.substring(f,h);return(f>0?"...":"")+k+(h<o.length?"...":"")},he=s=>{a.value=s.page,g.value=!1},me=()=>{b.value="",x.value=[],g.value=!1},be=()=>{document.addEventListener("contextmenu",W),document.addEventListener("keydown",Y),document.addEventListener("selectstart",X),document.addEventListener("dragstart",J),window.addEventListener("beforeprint",Z),ge(),window.addEventListener("blur",ee),window.addEventListener("focus",te),document.addEventListener("visibilitychange",se)},xe=()=>{document.removeEventListener("contextmenu",W),document.removeEventListener("keydown",Y),document.removeEventListener("selectstart",X),document.removeEventListener("dragstart",J),window.removeEventListener("beforeprint",Z),window.removeEventListener("blur",ee),window.removeEventListener("focus",te),document.removeEventListener("visibilitychange",se),ye()},W=s=>(s.preventDefault(),s.stopPropagation(),V("Right-click is disabled for content protection"),!1),Y=s=>{const t=["F12","F3","F5","PrintScreen","Insert"],o=[{ctrl:!0,key:"u"},{ctrl:!0,key:"i"},{ctrl:!0,key:"j"},{ctrl:!0,key:"s"},{ctrl:!0,key:"p"},{ctrl:!0,key:"a"},{ctrl:!0,key:"c"},{ctrl:!0,key:"v"},{ctrl:!0,key:"x"},{ctrl:!0,key:"r"},{ctrl:!0,key:"f"},{ctrl:!0,shift:!0,key:"i"},{ctrl:!0,shift:!0,key:"j"},{ctrl:!0,shift:!0,key:"c"},{alt:!0,key:"PrintScreen"},{cmd:!0,key:"i"},{cmd:!0,key:"u"},{cmd:!0,key:"s"},{cmd:!0,key:"p"},{cmd:!0,key:"c"},{cmd:!0,key:"shift",key:"4"},{cmd:!0,key:"shift",key:"3"}];if(t.includes(s.key)||t.includes(s.code))return s.preventDefault(),s.stopPropagation(),V("This keyboard shortcut is disabled for content protection"),!1;for(const i of o){const f=i.ctrl&&(s.ctrlKey||s.metaKey),h=i.shift?s.shiftKey:!0,k=i.key.toLowerCase()===s.key.toLowerCase();if(f&&h&&k)return s.preventDefault(),s.stopPropagation(),V("This keyboard shortcut is disabled for content protection"),!1}},X=s=>(s.preventDefault(),!1),J=s=>(s.preventDefault(),!1),Z=s=>(s.preventDefault(),V("Printing is disabled for content protection"),!1),V=s=>{const t=document.createElement("div");t.className="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-[9999] text-sm",t.textContent=s,document.body.appendChild(t),setTimeout(()=>{t.parentNode&&t.parentNode.removeChild(t)},3e3)},ge=()=>{const s=document.createElement("div");s.id="content-security-overlay",s.style.cssText=`
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
    `,document.body.appendChild(s),fe()},fe=()=>{const s=p.auth?.user,t=s?`${s.name} - ${s.email} - ${new Date().toLocaleString()}`:"Protected Content";for(let o=0;o<8;o++){const i=document.createElement("div");i.className="security-watermark",i.textContent=t,i.style.cssText=`
            position: fixed;
            top: ${Math.random()*80+10}%;
            left: ${Math.random()*80+10}%;
            color: rgba(0, 0, 0, 0.03);
            font-size: 12px;
            font-weight: bold;
            pointer-events: none;
            user-select: none;
            z-index: 2;
            transform: rotate(${Math.random()*60-30}deg);
            font-family: monospace;
        `,document.body.appendChild(i)}},ye=()=>{const s=document.getElementById("content-security-overlay");s&&s.parentNode.removeChild(s),document.querySelectorAll(".security-watermark").forEach(o=>{o.parentNode&&o.parentNode.removeChild(o)})},ee=()=>{const s=document.querySelector("[data-modal-content]");s&&(s.style.filter="blur(10px)",s.style.transition="filter 0.1s ease")},te=()=>{const s=document.querySelector("[data-modal-content]");s&&(s.style.filter="none")},se=()=>{if(document.hidden){const s=document.querySelector("[data-modal-content]");s&&(s.style.filter="blur(15px)",s.style.transition="filter 0.05s ease")}else{const s=document.querySelector("[data-modal-content]");s&&(s.style.filter="none")}},$=s=>{const t={book:"M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197",past_paper:"M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",document:"M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",video:"M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z",audio:"M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M9 17a1 1 0 01-1-1V8a1 1 0 011-1h1.5a1 1 0 01.707.293L13 9h3a1 1 0 011 1v6a1 1 0 01-1 1h-3l-1.793 1.793A1 1 0 019 17z"};return t[s]||t.document},R=s=>{const t={book:"from-blue-500 to-indigo-600",past_paper:"from-green-500 to-emerald-600",document:"from-purple-500 to-violet-600",video:"from-red-500 to-rose-600",audio:"from-yellow-500 to-orange-600"};return t[s]||t.document},oe=s=>{if(!s)return"N/A";const t=["B","KB","MB","GB"],o=Math.floor(Math.log(s)/Math.log(1024));return Math.round(s/Math.pow(1024,o)*100)/100+" "+t[o]};return _e(()=>{document.body.style.overflow=""}),(s,t)=>(l(),n(y,null,[z(_(Ce),{title:"Digital Library"}),z(je,{title:"Digital Library",subtitle:"Access books, past papers, and educational resources",stats:c.stats},{default:I(()=>[e("div",Pe,[e("div",Se,[e("div",Te,[e("div",Le,[e("div",null,[t[14]||(t[14]=e("p",{class:"text-sm font-medium text-slate-600"},"Total Resources",-1)),e("p",Ee,r(c.stats.total_resources.toLocaleString()),1)]),t[15]||(t[15]=e("div",{class:"w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center"},[e("svg",{class:"w-6 h-6 text-white",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 00-2 2v2m0 0V9a2 2 0 012-2m0 2a2 2 0 012-2h4a2 2 0 012 2m0 2v10"})])],-1))])]),z(_(N),{href:s.route("library.books"),class:"bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 group"},{default:I(()=>[e("div",Ae,[e("div",null,[t[16]||(t[16]=e("p",{class:"text-sm font-medium text-slate-600"},"Books",-1)),e("p",Be,r(c.stats.books.toLocaleString()),1)]),t[17]||(t[17]=e("div",{class:"w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300"},[e("svg",{class:"w-6 h-6 text-white",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M12 6.253v13.447m0-13.447l6.818-4.757M12 6.253l-6.818-4.757m6.818 4.757l-.547 4.197"})])],-1))])]),_:1},8,["href"]),z(_(N),{href:s.route("library.pastPapers"),class:"bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-slate-200/50 hover:shadow-2xl transition-all duration-300 group"},{default:I(()=>[e("div",Ve,[e("div",null,[t[18]||(t[18]=e("p",{class:"text-sm font-medium text-slate-600"},"Past Papers",-1)),e("p",ze,r(c.stats.past_papers.toLocaleString()),1)]),t[19]||(t[19]=e("div",{class:"w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300"},[e("svg",{class:"w-6 h-6 text-white",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"})])],-1))])]),_:1},8,["href"]),e("div",Ie,[e("div",Ne,[e("div",null,[t[20]||(t[20]=e("p",{class:"text-sm font-medium text-slate-600"},"Documents",-1)),e("p",He,r(c.stats.documents.toLocaleString()),1)]),t[21]||(t[21]=e("div",{class:"w-12 h-12 bg-gradient-to-r from-purple-500 to-violet-600 rounded-2xl flex items-center justify-center"},[e("svg",{class:"w-6 h-6 text-white",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"})])],-1))])])]),e("div",De,[e("div",Fe,[e("div",qe,[e("div",Oe,[C(e("input",{"onUpdate:modelValue":t[0]||(t[0]=o=>M.value=o),onInput:E,type:"text",placeholder:"Search resources...",class:"w-full bg-slate-100/70 backdrop-blur-sm px-4 py-3 pl-10 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200"},null,544),[[le,M.value]]),t[22]||(t[22]=e("svg",{class:"absolute left-3 top-3.5 h-4 w-4 text-slate-400",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"})],-1))])]),e("div",$e,[C(e("select",{"onUpdate:modelValue":t[1]||(t[1]=o=>P.value=o),onChange:E,class:"bg-slate-100/70 px-4 py-2 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"},[...t[23]||(t[23]=[e("option",{value:"all"},"All Types",-1),e("option",{value:"book"},"Books",-1),e("option",{value:"past_paper"},"Past Papers",-1),e("option",{value:"document"},"Documents",-1),e("option",{value:"video"},"Videos",-1),e("option",{value:"audio"},"Audio",-1)])],544),[[H,P.value]]),C(e("select",{"onUpdate:modelValue":t[2]||(t[2]=o=>S.value=o),onChange:E,class:"bg-slate-100/70 px-4 py-2 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"},[t[24]||(t[24]=e("option",{value:""},"All Subjects",-1)),(l(!0),n(y,null,j(c.subjects,o=>(l(),n("option",{key:o.id,value:o.id},r(o.name),9,Re))),128))],544),[[H,S.value]]),C(e("select",{"onUpdate:modelValue":t[3]||(t[3]=o=>T.value=o),onChange:E,class:"bg-slate-100/70 px-4 py-2 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"},[t[25]||(t[25]=e("option",{value:""},"All Grades",-1)),(l(!0),n(y,null,j(de.value,o=>(l(),n("option",{key:o,value:o},r(o),9,Qe))),128))],544),[[H,T.value]]),C(e("select",{"onUpdate:modelValue":t[4]||(t[4]=o=>L.value=o),onChange:E,class:"bg-slate-100/70 px-4 py-2 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"},[t[26]||(t[26]=e("option",{value:""},"All Years",-1)),(l(!0),n(y,null,j(c.years,o=>(l(),n("option",{key:o,value:o},r(o),9,Ke))),128))],544),[[H,L.value]]),e("button",{onClick:U,class:"px-4 py-2 text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-2xl transition-colors duration-200"},[...t[27]||(t[27]=[e("svg",{class:"w-4 h-4",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"})],-1)])]),_(ie).role==="admin"?(l(),ne(_(N),{key:0,href:s.route("admin.library.index"),class:"px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-2xl text-sm font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2"},{default:I(()=>[...t[28]||(t[28]=[e("svg",{class:"w-4 h-4",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"}),e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M15 12a3 3 0 11-6 0 3 3 0 016 0z"})],-1),A(" Manage ",-1)])]),_:1},8,["href"])):m("",!0)])])]),e("div",Ue,[e("div",Ge,[t[29]||(t[29]=e("h2",{class:"text-xl font-bold text-slate-800"},"Resources",-1)),e("p",We,r(c.resources?.total||0)+" resources found",1)]),(c.resources?.data?.length||0)>0?(l(),n("div",Ye,[e("div",Xe,[(l(!0),n(y,null,j(c.resources?.data||[],o=>(l(),n("button",{key:o.id,onClick:i=>ce(o),class:"bg-white rounded-2xl shadow-lg border border-slate-200/50 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group text-left w-full"},[e("div",{class:w(["h-32 bg-gradient-to-br",R(o.type),"flex items-center justify-center relative overflow-hidden"])},[(l(),n("svg",Ze,[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"1.5",d:$(o.type)},null,8,et)])),e("div",tt,[e("span",st,r(o.type.replace("_"," ")),1)])],2),e("div",ot,[e("h3",rt,r(o.title),1),e("div",lt,[o.subject?(l(),n("div",nt,[e("span",at,r(o.subject.name),1)])):m("",!0),e("div",it,[o.grade_level?(l(),n("span",dt,"Grade "+r(o.grade_level),1)):m("",!0),o.year?(l(),n("span",ct,r(o.year),1)):m("",!0)]),e("div",ut,[e("span",pt,r(oe(o.file_size)),1),e("span",vt,r(o.view_count)+" views",1)])]),o.is_protected?(l(),n("div",ht,[...t[30]||(t[30]=[e("svg",{class:"w-3 h-3 mr-1",fill:"currentColor",viewBox:"0 0 20 20"},[e("path",{"fill-rule":"evenodd",d:"M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z","clip-rule":"evenodd"})],-1),A(" Protected Content ",-1)])])):m("",!0)])],8,Je))),128))]),c.resources?.links&&c.resources.links.length>3?(l(),n("div",mt,[e("div",bt,[(l(!0),n(y,null,j(c.resources?.links||[],(o,i)=>(l(),n(y,{key:`link-${i}`},[o?.url?(l(),ne(_(N),{key:0,href:o.url,class:w(["px-4 py-2 text-sm rounded-lg transition-colors duration-200",o.active?"bg-indigo-500 text-white":"text-slate-600 hover:bg-slate-100"]),innerHTML:o.label},null,8,["href","class","innerHTML"])):(l(),n("span",{key:1,class:w(["px-4 py-2 text-sm rounded-lg transition-colors duration-200 cursor-not-allowed","text-slate-400 bg-slate-100"]),innerHTML:o?.label||""},null,8,xt))],64))),128))])])):m("",!0)])):(l(),n("div",gt,[t[31]||(t[31]=e("div",{class:"w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4"},[e("svg",{class:"w-8 h-8 text-slate-400",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 00-2 2v2m0 0V9a2 2 0 012-2m0 2a2 2 0 012-2h4a2 2 0 012 2m0 2v10"})])],-1)),t[32]||(t[32]=e("h3",{class:"text-lg font-semibold text-slate-800 mb-2"},"No resources found",-1)),t[33]||(t[33]=e("p",{class:"text-slate-500 mb-6"},"Try adjusting your search criteria or filters.",-1)),e("button",{onClick:U,class:"px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1"}," Reset Filters ")]))])]),F.value&&d.value?(l(),n("div",{key:0,class:"fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4",onClick:q},[e("div",{class:"bg-white rounded-3xl shadow-2xl w-full h-full max-w-7xl max-h-[95vh] flex flex-col overflow-hidden modal-content",onClick:t[13]||(t[13]=D(()=>{},["stop"])),"data-modal-content":"",style:{"user-select":"none","-webkit-user-select":"none","-moz-user-select":"none","-ms-user-select":"none","-webkit-touch-callout":"none","-webkit-tap-highlight-color":"transparent"}},[e("div",ft,[e("div",yt,[e("div",wt,[e("div",{class:w(["w-10 h-10 rounded-xl flex items-center justify-center bg-gradient-to-br",R(d.value.type)])},[(l(),n("svg",kt,[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:$(d.value.type)},null,8,_t)]))],2),e("div",null,[e("h2",Ct,r(d.value.title),1),e("p",jt,r(d.value.subject?.name||"Digital Resource")+" • "+r(O.value?.title||`Page ${a.value}`),1)])]),e("button",{onClick:q,class:"p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-200/50 rounded-xl transition-colors duration-200"},[...t[34]||(t[34]=[e("svg",{class:"w-6 h-6",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M6 18L18 6M6 6l12 12"})],-1)])])]),e("div",Mt,[e("div",Pt,[C(e("input",{"onUpdate:modelValue":t[5]||(t[5]=o=>b.value=o),onKeyup:Q(G,["enter"]),type:"text",placeholder:"Search for topics, keywords, or page numbers...",class:"w-full bg-white/80 backdrop-blur-sm px-4 py-3 pl-12 pr-32 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all duration-200 border border-slate-200"},null,544),[[le,b.value]]),t[36]||(t[36]=e("div",{class:"absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"},[e("svg",{class:"h-5 w-5 text-slate-400",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"})])],-1)),e("div",St,[b.value?(l(),n("button",{key:0,onClick:me,class:"p-1 text-slate-400 hover:text-slate-600 rounded"},[...t[35]||(t[35]=[e("svg",{class:"w-4 h-4",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M6 18L18 6M6 6l12 12"})],-1)])])):m("",!0),e("button",{onClick:G,class:"px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-xs rounded-lg transition-colors duration-200"}," Search ")])]),g.value&&x.value.length>0?(l(),n("div",Tt,[e("div",Lt,[e("p",Et,r(x.value.length)+" result"+r(x.value.length!==1?"s":"")+" found",1)]),e("div",At,[(l(!0),n(y,null,j(x.value,o=>(l(),n("button",{key:`search-${o.page}`,onClick:i=>he(o),class:"w-full px-4 py-3 text-left hover:bg-slate-50 transition-colors duration-150 border-b border-slate-100 last:border-b-0"},[e("div",Vt,[e("span",zt,r(o.title),1),e("span",It,"Page "+r(o.page),1)]),e("p",Nt,r(o.preview),1),e("p",Ht,r(o.matches)+" match"+r(o.matches!==1?"es":""),1)],8,Bt))),128))])])):g.value&&x.value.length===0&&b.value?(l(),n("div",Dt,[e("div",Ft,[t[37]||(t[37]=e("svg",{class:"mx-auto h-8 w-8 text-slate-400",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"})],-1)),e("p",qt,'No results found for "'+r(b.value)+'"',1),t[38]||(t[38]=e("p",{class:"text-xs text-slate-500"},'Try searching for "equation", "exercise", or "formula"',-1))])])):m("",!0)])]),e("div",Ot,[e("div",$t,[e("div",Rt,[e("div",Qt,[e("div",{class:w(["p-6 bg-gradient-to-r text-white",R(d.value.type)])},[e("div",Kt,[e("div",Ut,[(l(),n("svg",Gt,[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:$(d.value.type)},null,8,Wt)])),e("div",null,[e("h3",Yt,r(O.value?.title||`Page ${a.value}`),1),e("p",Xt,r(d.value.title)+" • Page "+r(a.value)+" of "+r(v.value),1)])]),e("div",Jt,[t[39]||(t[39]=e("span",{class:"text-white/80 text-sm"},"Go to:",-1)),e("input",{value:a.value,onKeyup:t[6]||(t[6]=Q(o=>B(parseInt(o.target.value)),["enter"])),onBlur:t[7]||(t[7]=o=>B(parseInt(o.target.value))),type:"number",min:1,max:v.value,class:"w-16 px-2 py-1 text-sm text-slate-800 rounded border-0 focus:ring-2 focus:ring-white/50"},null,40,Zt)])])],2),e("div",{class:"p-8 min-h-[500px] select-none",style:{"user-select":"none","-webkit-user-select":"none","-moz-user-select":"none","-ms-user-select":"none","-webkit-touch-callout":"none","-webkit-tap-highlight-color":"transparent","pointer-events":"auto"},innerHTML:O.value?.content||"",onContextmenu:t[8]||(t[8]=D(()=>{},["prevent"])),onSelectstart:t[9]||(t[9]=D(()=>{},["prevent"])),onDragstart:t[10]||(t[10]=D(()=>{},["prevent"]))},null,40,es)])])])]),e("div",ts,[e("div",ss,[e("span",os,[t[40]||(t[40]=e("svg",{class:"w-4 h-4 mr-1",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"})],-1)),A(" "+r(d.value.file_type?.toUpperCase()),1)]),d.value.file_size?(l(),n("span",rs,[t[41]||(t[41]=e("svg",{class:"w-4 h-4 mr-1",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"})],-1)),A(" "+r(oe(d.value.file_size)),1)])):m("",!0),d.value.is_protected?(l(),n("span",ls,[...t[42]||(t[42]=[e("svg",{class:"w-4 h-4 mr-1",fill:"currentColor",viewBox:"0 0 20 20"},[e("path",{"fill-rule":"evenodd",d:"M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z","clip-rule":"evenodd"})],-1),A(" Protected ",-1)])])):m("",!0)]),e("div",ns,[e("button",{onClick:pe,disabled:a.value<=1,class:w(["p-2 rounded-xl transition-all duration-200 flex items-center space-x-1",a.value<=1?"text-slate-400 cursor-not-allowed":"text-slate-600 hover:text-slate-800 hover:bg-slate-200/50"]),title:"Previous Page"},[...t[43]||(t[43]=[e("svg",{class:"w-5 h-5",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M15 19l-7-7 7-7"})],-1),e("span",{class:"text-sm font-medium"},"Previous",-1)])],10,as),e("div",is,[t[44]||(t[44]=e("span",{class:"text-slate-600"},"Page",-1)),e("input",{value:a.value,onKeyup:t[11]||(t[11]=Q(o=>B(parseInt(o.target.value)),["enter"])),onBlur:t[12]||(t[12]=o=>B(parseInt(o.target.value))),type:"number",min:1,max:v.value,class:"w-16 px-2 py-1 text-center text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400"},null,40,ds),e("span",cs,"of "+r(v.value),1)]),e("button",{onClick:ue,disabled:a.value>=v.value,class:w(["p-2 rounded-xl transition-all duration-200 flex items-center space-x-1",a.value>=v.value?"text-slate-400 cursor-not-allowed":"text-slate-600 hover:text-slate-800 hover:bg-slate-200/50"]),title:"Next Page"},[...t[45]||(t[45]=[e("span",{class:"text-sm font-medium"},"Next",-1),e("svg",{class:"w-5 h-5",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 5l7 7-7 7"})],-1)])],10,us)]),e("div",{class:"flex items-center space-x-2"},[e("button",{onClick:q,class:"px-6 py-2 bg-slate-500 hover:bg-slate-600 text-white rounded-xl transition-colors duration-200"}," Close Viewer ")])])])])):m("",!0)]),_:1},8,["stats"])],64))}},bs=Me(ps,[["__scopeId","data-v-6ff3fafa"]]);export{bs as default};
