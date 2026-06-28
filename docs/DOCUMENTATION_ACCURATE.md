# Group4_Wedding.docx — ACCURATE / NO CHANGES NEEDED

These sections are correct and verified against the actual codebase.

---

## COVER PAGE
- Group-4, Golden Promise — correct
- Group Members table — correct

---

## ACKNOWLEDGEMENT
- All names and descriptions — correct

---

## ABSTRACT
- Full description of Golden Promise as a web-based wedding planning and booking marketplace — correct
- "connects customers with wedding service suppliers through a centralized platform" — correct
- "enables customers to search, compare, and book wedding services" — correct
- "allows suppliers to promote and manage their services effectively" — correct

---

## CHAPTER 1 — INTRODUCTION

### Background (Section 1.1)
- Wedding planning description — correct
- List of service types: photographers, decorators, caterers, makeup artists, venue providers — correct
- Problem description about scattered services across social media — correct
- Need for a centralized marketplace — correct

### Problem Statement (Section 1.2)
- "customers must visit multiple websites and social media pages" — correct
- "time-consuming and may lead to confusion when comparing services" — correct
- "suppliers have limited opportunities to reach potential customers through a single platform" — correct

### Aim and Objectives (Section 1.3)
- "To develop a web-based wedding planning and booking marketplace" — correct
- All five bullet points are correct:
  - centralized platform for wedding services
  - help customers search and compare suppliers
  - allow customers to book wedding services online
  - enable suppliers to promote and manage their services
  - improve communication between customers and suppliers

### Scope of Work (Section 1.4)
- Overview paragraph — correct
- All scope items are correct:
  - Wedding service marketplace
  - Supplier verification and approval
  - Service listing management
  - Booking request management
  - Payment proof submission and verification
  - Customer review and rating system
  - Category management
  - Admin monitoring and management

### Limitations (Section 1.4)
- All four limitations are accurate:
  - No mobile application in the current version
  - No direct real-time chat between customers and suppliers
  - Automated coin withdrawal is not included (partially implemented via 2C2P but not fully)
  - The system focuses only on wedding-related services

### System Features — Intro Paragraph (Section 1.5)
- "centralized marketplace that simplifies wedding planning by connecting customers with verified suppliers" — correct

---

## CHAPTER 2 — SYSTEM ANALYSIS AND DESIGN

### System Users (Section 2.1)

**Admin description:**
- "primary authority, quality gatekeeper, and financial mediator" — correct
- "maintaining the integrity, security, and reliability of the marketplace" — correct
- "overseeing supplier verification, user management, payment validation, and transaction monitoring" — correct

**Supplier description:**
- "verified wedding service providers who have successfully completed the Administrator's approval process" — correct
- Service types listed: photography, videography, catering, venue rental, decoration, makeup, entertainment — correct

**Customer description:**
- "individuals or couples planning their wedding" — correct
- "discover, compare, and book trusted wedding services from verified suppliers" — correct

### Functional Requirements — User Management
- User registration — correct
- User login and logout — correct
- Profile management — correct
- Password update — correct

### Functional Requirements — Service Management
- Add service listings — correct
- Edit service details — correct
- Delete service listings — correct
- View service information — correct

### Functional Requirements — Search and Booking
- Search wedding services — correct
- Filter services by category — correct
- Make bookings — correct
- View booking history — correct

### Functional Requirements — Review and Rating
- Submit reviews — correct
- Rate suppliers — correct
- View customer feedback — correct

### Functional Requirements — Administration
- Manage customers and suppliers — correct
- Monitor bookings — correct
- Manage categories — correct
- Generate reports — partially correct (CSV export exists)
- Payment Proof — correct
- Package Management — correct

### Non-Functional Requirements — Security
- "User authentication and role-based authorization" — correct
- "Passwords shall be securely stored using strong hashing algorithms" — correct
- "Prepared Statements (PDO/MySQLi) shall be used to prevent SQL Injection" — correct
- "Output encoding and input validation against XSS" — correct
- "CSRF protection mechanisms" — correct
- "Secure cookies with HttpOnly and Secure attributes" — correct
- "User sessions shall be managed securely" — correct

### Non-Functional Requirements — Performance
- "Web pages should load within a reasonable response time" — correct
- "Database queries shall be optimized" — correct
- "support multiple concurrent users" — correct

### Non-Functional Requirements — Reliability
- "maintain data integrity throughout booking, payment verification, and coin transfer processes" — correct
- "System failures shall be minimized through proper error handling" — correct
- "stable operation and high availability with minimal downtime" — correct

### Non-Functional Requirements — Usability
- "simple, clear, and easy to navigate" — correct
- "learn and operate the system with minimal training" — correct
- "Responsive design shall ensure compatibility across desktop, tablet, and mobile devices" — correct
- "Status indicators and notifications shall be clearly displayed" — correct

### Non-Functional Requirements — Maintainability
- "modular and structured architecture" — correct
- "System components shall be documented" — correct
- "New features, security updates, and database modifications with minimal impact" — correct
- "Regular maintenance and security updates" — correct

---

## CHAPTER 3 — DEVELOPMENT METHODOLOGY AND SYSTEM DESIGN

### Development Methodology (Section 3.1)
- SDLC methodology description — correct
- Phases: requirement analysis, system design, implementation, testing, deployment, maintenance — correct

### System Architecture (Section 3.2)
- "web-based client-server architecture" — correct
- "Users interact through a web browser, while the server processes requests and manages data" — correct
- "centralized data management and secure access control" — correct
- (Only remove "staff" from the user roles list)

### Security Implementation (Section 3.3)
- "passwords are securely stored using encryption techniques" — correct
- "prepared statements and data validation methods to prevent SQL Injection and XSS" — correct
- "role-based access control ensures that users can only access functions relevant to their responsibilities" — correct

### User Workflow and Booking Process (Section 3.4)
- Full workflow description — correct:
  - Customers browse services and select suppliers
  - Submit booking requests and provide payment information
  - Suppliers review and respond to requests
  - Administrators verify payment submissions and monitor transactions
  - System updates booking status after service completion
- "ensures transparency, accountability, and efficient coordination" — correct

---

## CHAPTER 4 — DATABASE DESIGN

### Database Schema Overview (Section 4.1)
- "MySQL relational database" — correct
- "manage users, suppliers, services, bookings, payments, wallets, reviews, and system administration activities" — correct
- "relational model to ensure data consistency, integrity, and efficient data retrieval" — correct

### Entity Relationship Diagram (Section 4.2)
- Description of ERD and core entities — correct
- "connected through primary and foreign keys" — correct

### Table Relationships (Section 4.4)
- "A user can create multiple bookings" — correct
- "suppliers can provide multiple services" — correct
- "Each booking may contain several booking items and related payments" — correct
- "Suppliers maintain wallet balances through transaction records" — correct
- "reviews are associated with completed bookings" — correct

### Database Constraints and Keys (Section 4.5)
- "primary keys, foreign keys, unique constraints, and validation rules" — correct
- "Primary keys uniquely identify each record" — correct
- "foreign keys enforce relationships between tables" — correct

### Database Implementation (Section 4.6)
- "implemented using MySQL and managed through phpMyAdmin within the XAMPP environment" — correct
- "SQL statements with appropriate data types, indexes, and constraints" — correct
- "secure data storage, efficient query processing, and reliable management" — correct

---

## CHAPTER 5 — SYSTEM FEATURES AND IMPLEMENTATION

### 5.1 Admin Module — Intro Paragraph
- "core management component" — correct
- "quality controller and financial mediator" — correct
- "managing users, verifying suppliers, monitoring transactions, and maintaining system activities" — correct

### Supplier Verification and Management (Section 5.1)
- "reviews supplier registration requests and checks business information" — correct
- "Only verified and qualified suppliers are allowed to appear on the marketplace" — correct

### User Management (Section 5.1)
- "manage customer and supplier accounts" — correct
- "viewing user information, suspending accounts, or removing users who violate platform rules" — correct

### Service and Category Management (Section 5.1)
- "manages wedding service categories such as photography, catering, venues" — correct

### Payment Verification and Escrow Management (Section 5.1)
- "verifies customer payment proofs uploaded through the system" — correct
- "payment amount is secured in the escrow system" — correct

### System Monitoring and Audit Logs (Section 5.1)
- "monitor system activities, booking status changes, and important actions" — correct

### 5.2 Supplier Module — Intro Paragraph
- "designed for wedding service providers" — correct
- "create professional profiles, manage services, receive booking requests, and track their earnings" — correct

### Supplier Registration and Profile Management (Section 5.2)
- "apply to become partners by providing business information" — correct
- "After Admin approval, suppliers can manage their shop profiles, descriptions, service categories, and portfolio images" — correct

### Service and Package Management (Section 5.2)
- "create and update their wedding service packages" — correct
- "photography services, catering packages, venue details, and pricing information" — correct

### Booking Request Management (Section 5.2)
- "view customer requests submitted through the structured booking system" — correct
- "accept or decline requests based on event requirements and availability" — correct

### Payment History Tracking (Section 5.2)
- "review payment records, including verified half payments and full payments released by the admin through the escrow process" — correct

### 5.3 Customer Module — Intro Paragraph
- "convenient platform for couples who are planning their weddings" — correct
- "discover verified wedding services, submit booking requests, make payments securely, and track their wedding service progress" — correct

### Marketplace Search and Service Discovery (Section 5.3)
- "browse different wedding service categories and view verified suppliers" — correct
- "without searching multiple external websites or social media platforms" — correct

### Supplier Profile Viewing (Section 5.3)
- "view supplier information, service descriptions, portfolios, pricing details, and available packages" — correct

### Structured Booking Request (Section 5.3)
- "submit detailed wedding requirements through the system" — correct
- "creates a clear record between customers and suppliers" — correct

### Payment Proof Upload (Section 5.3)
- "upload payment screenshots or transaction references for half payment and full payment verification by the Admin" — correct

### Booking Status Tracking (Section 5.3)
- "monitor booking progress through different statuses such as Pending, Accepted, Payment Verified, and Completed" — correct

### Review and Feedback System (Section 5.3)
- "Only customers who complete bookings can submit reviews" — correct
- "ensuring authentic feedback and improving marketplace reliability" — correct

### 5.4 Security Module — Intro Paragraph
- "one of the most important components" — correct
- "platform handles personal information and financial transactions" — correct
- "protects users and maintains data integrity against cyber threats" — correct

### SQL Injection Prevention (Section 5.4)
- "prepared statements with PDO/MySQLi" — correct

### Cross-Site Scripting (XSS) Protection (Section 5.4)
- "filtered and encoded using secure methods such as htmlspecialchars()" — correct

### Password Protection (Section 5.4)
- "stored using strong hashing algorithms such as PHP password_hash()" — correct

### Role-Based Access Control (Section 5.4)
- "separates permissions between Admin, Supplier, and Customer roles" — correct
- "Each user can only access features according to their assigned role" — correct

### Authentication Security (Section 5.4)
- "Secure login mechanisms and OTP support" — correct

### Data Integrity Protection (Section 5.4)
- "wallet and payment system maintain accurate transaction records" — correct
- "prevent loss, duplication, or unauthorized modification of financial data" — correct

### 5.5 User Interface Design — All Subsections
- Responsive Design — correct
- Minimalist Wedding Theme — correct
- User-Friendly Navigation — correct
- Clear Status Indicators — correct
- Dashboard Design ("Separate dashboards for Admin, Supplier, and Customer") — correct

### 5.6 System Testing — All Subsections
- Functional Testing — correct
- Security Testing — correct
- User Acceptance Testing (UAT) — correct
- Database Testing — correct
- Performance Testing — correct

---

## CHAPTER 6 — CONCLUSION AND FUTURE WORK

### Summary of Findings (Section 6.1)
- "secure and centralized web-based wedding planning and booking marketplace" — correct
- "connects couples with professional wedding service providers" — correct
- "addresses the major challenges: difficulty finding reliable suppliers, unorganized communication, lack of transaction security" — correct
- "one-stop marketplace where customers can easily search and discover verified wedding services" — correct
- "supplier verification process ensures only qualified and professional suppliers" — correct
- "structured booking system replaces informal communication methods" — correct
- "escrow-based approach where customer payments are verified by the Admin" — correct
- Security practices paragraph — correct
- "reliable, secure, and user-friendly solution" — correct

### Future Enhancements (Section 6.2) — Items That Are Still Accurate
- Mobile Application Development — correct (not yet built)
- AI-Based Recommendation System — correct (Gemini is used for category suggestions only, not full recommendations)
- Real-Time Notification System — correct (current polling could be enhanced with real-time push)
- Advanced Supplier Analytics — correct (basic dashboard exists but detailed analytics do not)
- Online Chat and Communication Management — correct (not yet built)
- Enhanced Security Features — correct (always room for improvement)

---

## REFERENCES
- All references are correct and valid:
  - Dribbble (https://dribbble.com)
  - Godly (https://godly.website)
  - Pinterest (https://www.pinterest.com)
  - CodePen (https://codepen.io)
  - Awwwards (https://www.awwwards.com)
  - Unsplash (https://unsplash.com)
  - Google Analytics 4 Documentation (https://developers.google.com/analytics)
