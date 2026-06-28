# Group4_Wedding.docx — CHANGES NEEDED

Items below are wrong, misleading, or missing from the documentation and must be fixed.

---

## 1. REMOVE "STAFF" ROLE EVERYWHERE

The documentation mentions "staff" as a user role in multiple places. This role does NOT exist in the system. The database `roles` table only has: `customer`, `supplier`, `admin`.

**Locations to fix:**

- **Chapter 3, Section 3.2 System Architecture (line 430):**
  - Current: *"multiple user roles, including customers, suppliers, staff, and administrators"*
  - Change to: *"multiple user roles, including customers, suppliers, and administrators"*

- **Chapter 3, Section 3.2 System Architecture (line 431):**
  - Current: *"This structure enables efficient communication between users and the system while ensuring centralized data management and secure access control."*
  - Remove any "staff" reference if present in surrounding context.

---

## 2. REMOVE "staff_profiles" FROM DATABASE TABLES

**Section 4.3 Main Tables (line 479):**
- Current list: users, suppliers, services, categories, bookings, payments, wallets, reviews, notifications, **staff profiles**
- **Remove "staff profiles"** — this table does not exist.

---

## 3. REPLACE DATABASE TABLES LIST (Section 4.3 Main Tables)

Current list has only 10 tables. The actual database has **49 tables**. Replace the Main Tables section with the following grouped list:

**User Management:**
- users
- user_roles
- roles
- permissions
- role_permissions
- email_verifications
- password_resets
- login_attempts
- account_lockout_logs
- otps
- customer_status_logs

**Supplier Management:**
- suppliers
- supplier_categories
- supplier_documents
- supplier_bans
- supplier_warnings

**Service Management:**
- services
- service_media
- service_availability
- service_schedules
- service_time_slots
- service_rental_pricing
- packages
- package_items
- categories

**Booking Management:**
- bookings
- booking_items
- booking_suppliers
- booking_status_logs
- booking_slot_reservations
- booking_supplier_replacements
- booking_vouchers
- event_details

**Payment and Wallet:**
- payments
- wallets
- refunds

**Design Selection:**
- cake_designs
- decoration_styles
- attire_items

**Venue Management:**
- venues
- venue_rooms
- venue_room_availability

**Shopping and Wishlist:**
- carts
- cart_items
- favorites
- wishlist_collections

**Review and Feedback:**
- reviews

**Notifications:**
- notifications

**System Administration:**
- system_logs

---

## 4. REPLACE "COIN" / "VIRTUAL COIN" TERMINOLOGY

The documentation repeatedly uses "Coin" and "virtual Coin wallet." The actual system uses **real currency (MMK)** with an **escrow-based payment system** via Stripe and 2C2P. The `wallets` table tracks real money, not virtual coins.

**Locations to fix:**

- **Section 5.1 (line 519):** *"Coin Distribution and Transaction Monitoring"*
  - Change to: **"Wallet Distribution and Transaction Monitoring"**

- **Section 5.1 (line 520):** *"the transfer of verified payments into supplier wallets after booking completion"*
  - Keep "supplier wallets" — this part is correct.

- **Section 5.2 (line 545):** *"Virtual Coin Wallet System"*
  - Change to: **"Wallet and Earnings System"**

- **Section 5.2 (line 547):** *"earned Coins from completed bookings"*
  - Change to: *"earned revenue from completed bookings"*

- **Section 6.1 (line 633):** *"virtual Coin wallet system"*
  - Change to: *"wallet system"*

- **Section 6.1 (line 635):** *"secure transaction handling"*
  - This is fine, keep it.

- **Section 6.2 (line 649):** *"Automated Coin Withdrawal System"*
  - Change to: **"Automated Withdrawal System"**

- **Section 6.2 (line 650):** *"The current manual Coin withdrawal process"*
  - Change to: *"The current manual withdrawal process"*

---


---

## 7. MOVE GOOGLE ANALYTICS (GA4) OUT OF SECURITY MODULE

**Section 5.4 (line 594-595):**
- Current: GA4 description appears inside "5.4 Security Module"
- Google Analytics is NOT a security feature.
- **Move to:** Section 5.5 (User Interface Design) as a new subsection called "Analytics and Tracking" or create a new Section 5.7.

---

## 8. FIX HTTPS ENFORCEMENT CLAIM

**Section 2.3 Non-Functional Requirements, Security (line 386):**
- Current: *"HTTPS communication shall be enforced to protect against network eavesdropping and data interception."*
- The app only **detects** HTTPS for URL generation. No HTTP-to-HTTPS redirect exists.
- **Change to:** *"HTTPS communication is supported and recommended for production deployment to protect against network eavesdropping and data interception."*

---

## 9. FIX SECTION TITLE GRAMMAR

**Section 1.3 (line 248):**
- Current: **"Aim and Objective Scope"**
- Change to: **"Aim and Objectives"**

---

## 10. MOVE "BAN SUPPLIERS" TO CORRECT SECTION

**Section 2.2 Functional Requirements, Review and Rating (line 362):**
- Current: "Ban Suppliers" is listed under "Review and Rating"
- This is an **Admin** function, not a review feature.
- **Move "Ban Suppliers"** from "Review and Rating" to the "Administration" section.

---

## 11. FIX MISPLACED GA4 LINE IN NON-FUNCTIONAL REQUIREMENTS

**Section 2.3, Security (line 388):**
- Current: *"Tracks website traffic, user behavior, and conversions to measure and improve website performance."*
- This describes Google Analytics, not a security requirement.
- **Remove this line** from the Security section. Add it under a new "Analytics" subsection in Non-Functional Requirements or in the UI Design section.

---

## 12. ADD MISSING TECHNOLOGIES TO TOOLS SECTION

The Tools section (lines 161-183) lists: PHP, MySQL, HTML, CSS, JavaScript, Tailwind CSS, XAMPP, VS Code, Git, GitHub, Figma.

**Add these missing tools:**

| Tool | Description |
|---|---|

| **PHPMailer** | Sends transactional emails including OTP codes, verification emails, booking confirmations, and security alerts |
| **Composer** | Manages PHP dependencies and autoloading |
| **Google OAuth** | Enables social login with Google accounts |
| **Facebook OAuth** | Enables social login with Facebook accounts |
| **Google Gemini AI** | Provides AI-powered category suggestions for supplier services |
| **PostCSS** | Processes and transforms CSS during the build pipeline |

---

## 13. ADD MISSING CUSTOMER FEATURES

**Section 1.5 Customer Features (lines 272-279):**

Current list:
- User Registration and Login
- Search and Filter Services
- View Supplier Profiles
- Submit Booking Requests
- Upload Payment Proof
- Track Booking Status
- Rating and Review

**Add these missing features:**
- Google and Facebook Social Login
- OTP Verification on Login
- Email Verification on Registration
- Shopping Cart (add services, manage cart, checkout)
- Favorites and Wishlist Management
- Password Reset
- Booking Cancellation and Refund Requests
- Venue Browsing and Room Exploration

---

## 14. ADD MISSING SUPPLIER FEATURES

**Section 1.5 Supplier Features (lines 281-286):**

Current list:
- Supplier Registration
- Profile Management
- Service Listing Management
- Booking Request Management
- Payment History Tracking

**Add these missing features:**
- Calendar and Availability Management
- Service Media Management (photos and videos)
- Notification System
- Earnings Dashboard and Payout Tracking
- Service Publish Workflow (request admin approval)
- Package Management (create and manage service packages)

---

## 15. ADD MISSING ADMIN FEATURES

**Section 1.5 Admin Features (lines 288-293):**

Current list:
- User and Supplier Management
- Supplier Verification and Approval
- Service and Category Management
- Payment Verification
- Admin Dashboard

**Add these missing features:**
- Refund Management
- Booking Replacement Management
- System Audit Logs
- Supplier Warning and Ban System
- CSV Data Export (customers, payments, logs)
- Platform Fee Configuration

---

## 16. ADD MISSING SECURITY FEATURES

**Section 5.4 Security Module (lines 577-593):**

Current list covers: SQL Injection Prevention, XSS Protection, Password Protection, RBAC, Authentication Security, Data Integrity Protection.

**Add these missing security features:**
- CSRF Token Protection
- Account Lockout After Failed Login Attempts
- Email Verification for New Registrations
- OTP-Based Two-Factor Authentication
- Audit Logging (system_logs table)
- Cookie Consent and GDPR Compliance
- Brute-Force Protection (login_attempts tracking)

---

## 17. ADD MISSING FUNCTIONAL REQUIREMENTS

**Section 2.2 Functional Requirements (lines 343-369):**

**Add to User Management:**
- Social login (Google, Facebook)
- OTP verification
- Password reset

**Add to Service Management:**
- Service media upload and management
- Service availability and calendar management

**Add to Search and Booking:**
- Add services to cart
- Manage favorites and wishlists
- Cancel bookings and request refunds

**Add to Administration:**
- Refund processing
- Booking replacement management
- System audit logging
- CSV data export
- Supplier warning and ban management

---

## 18. REMOVE OR REWORD GA4 LINE IN SECURITY (Section 2.3)

**Line 388:**
- Current: *"Tracks website traffic, user behavior, and conversions to measure and improve website performance."*
- This is a description of Google Analytics, not a security non-functional requirement.
- **Remove from Security section.** If you want to keep it, add it under Usability or create a separate "Analytics" subsection.
