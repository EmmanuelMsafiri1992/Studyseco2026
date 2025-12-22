# StudySeco Platform - System Guide

## Table of Contents
1. [Getting Started](#getting-started)
2. [User Roles & Permissions](#user-roles--permissions)
3. [Student Features](#student-features)
4. [Teacher Features](#teacher-features)
5. [Admin Features](#admin-features)
6. [Payment System](#payment-system)
7. [Troubleshooting](#troubleshooting)
8. [FAQs](#faqs)

---

## Getting Started

### Installation & Setup

1. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

2. **Storage Link**
   ```bash
   php artisan storage:link
   ```

3. **Run Application**
   ```bash
   php artisan serve
   npm run dev
   ```

### Default Login Credentials

- **Admin**: admin@example.com / password
- **Teacher**: teacher@example.com / password
- **Student**: student@example.com / password

---

## User Roles & Permissions

### Student Role
- Enroll in subjects
- View enrolled subjects and classes
- Submit assignments
- Take quizzes and exams
- View grades and certificates
- Extend enrollment period
- Make payments

### Teacher Role
- Manage teaching materials
- Create and grade assignments
- Create and manage quizzes
- Issue certificates
- View student submissions
- Track class performance

### Admin Role
- Manage users (students, teachers)
- System settings (logo, fees)
- Create mock MANEB exams
- View audit trails
- Payment management
- Subject and enrollment management

---

## Student Features

### 1. Enrollment & Subjects

**Enrolling in a Subject**:
- Navigate to "Enroll" from dashboard
- Select your school
- Choose subjects and teacher
- Complete payment
- Access granted upon payment confirmation

**Viewing Enrolled Subjects**:
- Dashboard shows all active enrollments
- Click any subject to view details, materials, assignments, and quizzes

### 2. Assignments

**Accessing Assignments**:
- Navigate to "Assignments" from sidebar
- Filter by: All, Available, Submitted, Graded

**Submitting an Assignment**:
1. Click on assignment title
2. Read instructions carefully
3. Upload your work (PDF, DOC, DOCX, etc.)
4. Click "Submit Assignment"
5. Confirmation message appears

**Viewing Grades**:
- Graded assignments show score and feedback
- Download original assignment and your submission
- View teacher comments

### 3. Quizzes

**Taking a Quiz**:
1. Navigate to "Quizzes"
2. Click "Start Quiz" on available quiz
3. Read instructions and click "Start Quiz"
4. Timer starts immediately
5. Answer all questions
6. Submit before time expires

**Quiz Features**:
- **Countdown Timer**: Auto-submit when time expires
- **Multiple Question Types**: Multiple choice, True/False, Short answer
- **Immediate Results**: See score after submission
- **Review Answers**: View correct answers (if enabled by teacher)
- **Multiple Attempts**: Retake quizzes based on teacher settings

### 4. MANEB Mock Exams

**Exam Preparation**:
- Navigate to "Mock Exams" in sidebar
- View available MANEB practice exams by subject
- See duration, passing marks, and total questions

**Taking Mock Exam**:
1. Click exam title
2. Review exam details
3. Click "Start Exam" (cannot pause once started)
4. Answer questions within time limit
5. Submit when complete

**Results & Feedback**:
- View score and percentage
- Identify weak areas by topic
- Personalized feedback for improvement
- Pass/fail status based on MANEB standards

### 5. Certificates

**Viewing Certificates**:
- Navigate to "Certificates"
- Filter by type: Completion, Achievement, Excellence
- Download PDF certificates

**Certificate Types**:
- **Completion**: Awarded for finishing a course
- **Achievement**: Awarded for outstanding performance
- **Excellence**: Awarded for exceptional achievement

### 6. Extension & Payments

**Extending Enrollment**:
1. Navigate to "Extension" when enrollment nears expiration
2. Select subjects to extend
3. Choose duration (1, 3, or 6 months)
4. Complete payment via bank deposit
5. Upload proof of payment
6. Wait for admin approval

---

## Teacher Features

### 1. Dashboard

**Overview Statistics**:
- Total students
- Active subjects
- Pending submissions
- Recent activity

### 2. Assignments

**Creating an Assignment**:
1. Navigate to "Assignments" > "Create Assignment"
2. Fill in details:
   - Title and description
   - Subject selection
   - Instructions
   - Points possible
   - Due date
   - Allow late submissions (optional)
3. Attach reference materials (optional)
4. Publish or save as draft

**Grading Assignments**:
1. Navigate to assignment > "Submissions"
2. Click on student submission
3. Download and review work
4. Enter score (out of max points)
5. Provide feedback
6. Submit grade

### 3. Quizzes

**Creating a Quiz**:
1. Navigate to "Quizzes" > "Create Quiz"
2. Set quiz details:
   - Title, description, subject
   - Duration in minutes
   - Total points and passing score
   - Number of attempts allowed
   - Show correct answers option
3. Save quiz (redirects to edit page)

**Adding Questions**:
1. On edit page, click "Add Question"
2. Select question type
3. Enter question text
4. Add options (for multiple choice)
5. Mark correct answer
6. Add explanation (optional)
7. Assign points
8. Save question

**Viewing Results**:
- Click "View Results" on quiz
- See all student attempts
- Filter and sort by score
- Track pass/fail rates

### 4. Certificates

**Issuing a Certificate**:
1. Navigate to "Certificates" > "Issue Certificate"
2. Select certificate type
3. Choose subject (optional)
4. Select student from dropdown
5. Enter title or auto-generate
6. Add description (optional)
7. Click "Issue Certificate"
8. PDF generated automatically

**Managing Certificates**:
- View all issued certificates
- Filter by subject, type, or student
- Download PDFs
- Delete certificates if needed

### 5. Teaching Materials

**Uploading Materials**:
1. Navigate to subject details
2. Click "Materials" tab
3. Upload files (PDF, DOCX, PPTX, etc.)
4. Add title and description
5. Save material

---

## Admin Features

### 1. System Settings

**Logo Upload**:
- Navigate to "Settings"
- Upload logo (PNG, JPG, SVG)
- Preview before saving
- Logo appears across platform

**Fee Configuration**:
- Set enrollment fees per subject
- Set extension fees
- Configure duration-based pricing

### 2. User Management

**Managing Students**:
- View all students
- Edit student details
- Deactivate accounts
- Reset passwords

**Managing Teachers**:
- Add new teachers
- Assign subjects
- View teaching history
- Manage permissions

### 3. Mock MANEB Exams

**Creating Mock Exam**:
1. Navigate to "Mock Exams" > "Create"
2. Set exam details:
   - Title, description, subject
   - Duration and total marks
   - Passing marks
3. Add questions with topics
4. Publish exam

**Question Management**:
- Add multiple choice questions
- Tag questions by topic (for weak area detection)
- Set marks per question
- Edit or delete questions

**Viewing Results**:
- Track all student attempts
- View average scores
- Identify common weak areas
- Export results

### 4. Audit Trail

**Viewing Logs**:
- Navigate to "Audit Logs"
- Filter by user, action, or date
- See detailed activity history
- Track system changes

### 5. Payment Management

**Reviewing Payments**:
- View all payment submissions
- See proof of payment uploads
- Approve or reject payments
- Track payment history

---

## Payment System

### Payment Process

1. **Student Initiates Payment**:
   - Selects subjects/services
   - Views total amount
   - Chooses payment duration

2. **Bank Deposit**:
   - Student deposits to school account
   - Upload proof of payment (screenshot/photo)
   - Submit for review

3. **Admin Approval**:
   - Admin reviews payment proof
   - Approves or rejects with reason
   - System auto-activates access upon approval

### Payment Types

- **Enrollment Fee**: One-time per subject enrollment
- **Extension Fee**: Renewal of existing enrollment
- **Duration Options**: 1 month, 3 months, 6 months

---

## Troubleshooting

### Common Issues

**Cannot Login**:
- Check email and password
- Use password reset if forgotten
- Contact admin if account deactivated

**Enrollment Access Expired**:
- Check enrollment status on dashboard
- Navigate to "Extension" to renew
- Complete payment to reactivate

**Quiz Timer Not Working**:
- Ensure JavaScript is enabled
- Refresh page before starting
- Use updated browser version

**Assignment Upload Fails**:
- Check file size (max 10MB)
- Use supported formats (PDF, DOC, DOCX)
- Check internet connection

**Payment Not Approved**:
- Verify proof of payment is clear
- Check amount matches fee
- Contact admin if delayed

### Browser Compatibility

Supported Browsers:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### System Requirements

**Minimum**:
- Internet connection: 2Mbps
- RAM: 4GB
- Screen resolution: 1280x720

**Recommended**:
- Internet connection: 5Mbps+
- RAM: 8GB+
- Screen resolution: 1920x1080

---

## FAQs

### For Students

**Q: How do I enroll in a subject?**
A: Navigate to "Enroll" > Select school > Choose subjects > Complete payment > Upload proof.

**Q: Can I retake a quiz?**
A: Yes, if the teacher allows multiple attempts. Check quiz details for attempt limit.

**Q: When will I receive my certificate?**
A: Certificates are issued by teachers upon course completion or achievement. Check "Certificates" section.

**Q: How do I extend my enrollment?**
A: Navigate to "Extension" > Select subjects > Choose duration > Pay > Upload proof.

**Q: What happens if I miss an assignment deadline?**
A: If late submissions are allowed, you may submit with potential penalty. Otherwise, assignment closes.

### For Teachers

**Q: How do I create a quiz?**
A: Navigate to "Quizzes" > "Create Quiz" > Fill details > Add questions > Publish.

**Q: Can I edit a quiz after students have taken it?**
A: You can edit unpublished quizzes freely. Published quizzes should not be edited to maintain fairness.

**Q: How do I grade assignments?**
A: Go to assignment > "Submissions" > Click student name > Enter score > Add feedback > Submit.

**Q: Can I issue certificates to multiple students?**
A: Currently one at a time. Navigate to "Certificates" > "Issue" for each student.

### For Admins

**Q: How do I add a new teacher?**
A: Navigate to "Users" > "Add User" > Select "Teacher" role > Fill details > Save.

**Q: How do I approve payments?**
A: Navigate to "Payments" > View pending > Check proof > Approve or Reject.

**Q: Can I backup the system?**
A: Yes, use `php artisan backup:run` command or database export tools.

**Q: How do I view system logs?**
A: Navigate to "Audit Logs" to see all system activity and changes.

---

## Support

For additional help:
- **Technical Issues**: Contact system administrator
- **Payment Issues**: Contact school finance office
- **Account Issues**: Contact admin via platform

**Platform Version**: 1.0.0
**Last Updated**: October 2025

---

## Quick Reference

### Student Routes
- `/student/dashboard` - Dashboard
- `/student/subjects` - Enrolled subjects
- `/student/assignments` - Assignments
- `/student/quizzes` - Quizzes
- `/student/mock-exams` - MANEB mock exams
- `/student/certificates` - Certificates
- `/student/extension` - Extension requests

### Teacher Routes
- `/teacher/dashboard` - Dashboard
- `/teacher/assignments` - Assignments management
- `/teacher/quizzes` - Quizzes management
- `/teacher/certificates` - Certificate issuance

### Admin Routes
- `/admin/dashboard` - Dashboard
- `/admin/users` - User management
- `/admin/mock-exams` - Mock exams
- `/admin/payments` - Payment approvals
- `/admin/audit-logs` - System logs
- `/admin/settings` - System settings
