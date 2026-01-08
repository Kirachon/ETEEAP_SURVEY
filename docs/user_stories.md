# ETEEAP Survey Application - User Stories

## Best Practices Research Summary

Based on industry research, the following golden standards will guide implementation:

### Multi-Step Form Best Practices
| Practice | Implementation |
|----------|----------------|
| **Progress Indicators** | Show "Step X of 8" + visual progress bar |
| **≤5 fields per step** | Group related questions logically |
| **Partial Save** | Store session data after each step (resume later) |
| **Real-time Validation** | Validate before allowing "Next" |
| **Mobile-First Design** | Design for 375px, then scale up |

### Security Best Practices (PHP)
| Practice | Implementation |
|----------|----------------|
| **Server-side validation** | Never trust client-side only |
| **CSRF tokens** | Generate per-session, validate on POST |
| **Prepared statements** | PDO with parameterized queries |
| **Input sanitization** | `htmlspecialchars()`, `trim()`, `filter_var()` |
| **Session security** | `session_regenerate_id()` after consent |

### Mobile-First UX Standards
| Practice | Implementation |
|----------|----------------|
| **Single column layout** | One question focus at a time |
| **48px minimum touch targets** | Large buttons, spaced checkboxes |
| **Labels above inputs** | Not inline (better for mobile) |
| **Avoid matrix/grid questions** | Use simple radio/checkbox lists |
| **Fast load times** | Minimal JS, optimized assets |

---

## Recommended Open Source Stack (Simplified)

| Layer | Technology | Why |
|-------|------------|-----|
| **Backend** | Vanilla PHP 8.1+ | No framework overhead, easy deployment |
| **Database** | MySQL 8.x | Standard, widely supported |
| **Styling** | Tailwind CSS (CDN) | Already used in stitch designs |
| **Charts** | Chart.js (CDN) | Simple, open source, no build step |
| **Export** | Native PHP (CSV) | No composer dependencies |
| **Routing** | Simple `switch` router | No framework needed for 15 routes |

> **TIP:** Avoiding Over-Engineering - No Composer dependencies required for MVP. Chart.js and Tailwind via CDN. Native PHP for CSV export. PhpSpreadsheet only if Excel is mandatory.

---

## User Stories

### Epic 1: Survey Respondent Journey

---

#### US-01: View Consent Page
**As a** DSWD personnel,  
**I want to** see a clear data privacy consent form,  
**So that** I understand how my data will be used before proceeding.

**Acceptance Criteria:**
- [ ] Consent text displays Data Privacy Act (RA 10173) notice
- [ ] "Yes, I Consent" button is prominent (primary color)
- [ ] "No, I Do Not Consent" button is secondary/outlined
- [ ] Responsive layout works on mobile (375px) and desktop (1024px+)
- [ ] Step indicator shows "Step 1 of 8"

---

#### US-02: Decline Consent
**As a** respondent who does not consent,  
**I want to** decline and see a thank-you message,  
**So that** I can exit gracefully without submitting data.

**Acceptance Criteria:**
- [ ] Clicking "No" redirects to thank-you page
- [ ] Thank-you page shows polite decline message
- [ ] No personal data is stored for declined sessions
- [ ] "Return to Homepage" button is available

---

#### US-03: Complete Multi-Step Survey
**As a** consenting DSWD personnel,  
**I want to** complete a multi-step survey form,  
**So that** I can provide my information in manageable sections.

**Acceptance Criteria:**
- [ ] 8 steps total (Consent → Basic Info → Office → Work Exp → Competencies → Education → Training → ETEEAP Interest)
- [ ] Progress bar updates with each step
- [ ] "Back" and "Next" buttons on each step
- [ ] Data persists if I refresh or navigate back
- [ ] Final submission shows success thank-you page

---

#### US-04: Validate Input in Real-Time
**As a** respondent,  
**I want to** see immediate feedback when I enter invalid data,  
**So that** I can correct mistakes before moving forward.

**Acceptance Criteria:**
- [ ] Email field validates format (client + server)
- [ ] Required fields show error if empty on "Next"
- [ ] Error messages appear near the field
- [ ] Valid fields show green checkmark (per stitch design)

---

#### US-05: Resume Survey Later
**As a** busy respondent,  
**I want to** close my browser and resume later,  
**So that** I don't lose my progress.

**Acceptance Criteria:**
- [ ] Session stores partial data for 24 hours
- [ ] Returning to survey URL resumes from last completed step
- [ ] Completed responses are not resumable (already submitted)

---

#### US-06: Mobile-Friendly Experience
**As a** respondent on a mobile device,  
**I want to** easily tap options and navigate,  
**So that** I can complete the survey on my phone.

**Acceptance Criteria:**
- [ ] Single-column layout on mobile
- [ ] Touch targets ≥48px height
- [ ] Sticky footer with navigation buttons
- [ ] No horizontal scrolling required
- [ ] Checkboxes/radio buttons have large tap areas

---

### Epic 2: Admin Dashboard

---

#### US-07: Admin Login
**As an** administrator,  
**I want to** log in with credentials,  
**So that** only authorized users can access survey data.

**Acceptance Criteria:**
- [ ] Login form with username/password
- [ ] Password stored as hash (password_hash)
- [ ] Session-based authentication
- [ ] Redirect to dashboard on success
- [ ] Error message on invalid credentials

---

#### US-08: View Dashboard Summary
**As an** administrator,  
**I want to** see key metrics at a glance,  
**So that** I can understand survey participation quickly.

**Acceptance Criteria:**
- [ ] Total Responses count
- [ ] Completion Rate percentage
- [ ] Responses This Week/Month
- [ ] Consent Rate (Yes vs No)

---

#### US-09: View Demographics Charts
**As an** administrator,  
**I want to** see visual charts of demographics,  
**So that** I can analyze respondent profiles.

**Acceptance Criteria:**
- [ ] Age distribution (pie/doughnut chart)
- [ ] Gender breakdown (pie chart)
- [ ] Office type distribution (bar chart)
- [ ] Charts render with Chart.js

---

#### US-10: View ETEEAP Interest Analysis
**As an** administrator,  
**I want to** see ETEEAP interest breakdown,  
**So that** I can assess program demand.

**Acceptance Criteria:**
- [ ] Interest level distribution (bar chart)
- [ ] Top motivations (horizontal bar)
- [ ] Top barriers (horizontal bar)
- [ ] Will apply breakdown (pie chart)

---

#### US-11: Browse All Responses
**As an** administrator,  
**I want to** browse individual survey responses,  
**So that** I can review specific submissions.

**Acceptance Criteria:**
- [ ] Table with columns: ID, Name, Office, Date, Status
- [ ] Search by name or email
- [ ] Filter by office type, interest level
- [ ] Pagination (20 per page)
- [ ] Click row to view full response

---

#### US-12: Export Data to CSV
**As an** administrator,  
**I want to** export survey data to CSV,  
**So that** I can analyze it in Excel or share with stakeholders.

**Acceptance Criteria:**
- [ ] "Export CSV" button on responses page
- [ ] Downloads file with all columns
- [ ] Multi-value fields (checkboxes) comma-separated
- [ ] Filename includes date (e.g., `responses_2026-01-08.csv`)

---

#### US-13: View Response Detail
**As an** administrator,  
**I want to** view a single response in full,  
**So that** I can see all answers from one respondent.

**Acceptance Criteria:**
- [ ] All 27 questions displayed with answers
- [ ] Multi-value fields shown as list
- [ ] Submission timestamp visible
- [ ] "Back to List" button

---

### Epic 3: System Requirements

---

#### US-14: CSRF Protection
**As the** system,  
**I want to** protect forms against CSRF attacks,  
**So that** malicious sites cannot submit fake surveys.

**Acceptance Criteria:**
- [ ] Token generated on session start
- [ ] Token included as hidden field in all forms
- [ ] POST requests validate token
- [ ] Invalid token shows error, rejects submission

---

#### US-15: Database Persistence
**As the** system,  
**I want to** store all survey responses in MySQL,  
**So that** data is persistent and queryable.

**Acceptance Criteria:**
- [ ] Main `survey_responses` table for single-value fields
- [ ] Junction tables for multi-value fields (checkboxes)
- [ ] Proper foreign keys with ON DELETE CASCADE
- [ ] Indexes on frequently queried columns

---

#### US-16: Responsive Breakpoints
**As the** system,  
**I want to** render appropriately on all devices,  
**So that** users have a good experience on mobile, tablet, and desktop.

**Acceptance Criteria:**
- [ ] Mobile: 320px - 767px (single column, sticky footer)
- [ ] Tablet: 768px - 1023px (centered, max-w-2xl)
- [ ] Desktop: 1024px+ (centered, max-w-4xl, 2-column form cards)

---

## Story Point Estimates

| Story | Points | Priority |
|-------|--------|----------|
| US-01 Consent Page | 3 | MVP |
| US-02 Decline Flow | 1 | MVP |
| US-03 Multi-Step Form | 8 | MVP |
| US-04 Validation | 3 | MVP |
| US-05 Resume Later | 2 | Nice-to-have |
| US-06 Mobile UX | 3 | MVP |
| US-07 Admin Login | 2 | MVP |
| US-08 Dashboard Summary | 3 | MVP |
| US-09 Demographics Charts | 3 | High |
| US-10 ETEEAP Analysis | 3 | High |
| US-11 Browse Responses | 3 | MVP |
| US-12 Export CSV | 2 | High |
| US-13 Response Detail | 2 | High |
| US-14 CSRF Protection | 2 | MVP |
| US-15 Database | 3 | MVP |
| US-16 Responsive | 2 | MVP |
| **Total** | **45** | |

---

## MVP Scope (30 points)

For initial release, focus on:
1. US-01, US-02, US-03, US-04, US-06 (Survey flow)
2. US-07, US-08, US-11, US-12 (Admin basics)
3. US-14, US-15, US-16 (System requirements)

Phase 2:
- US-05 Resume Later
- US-09, US-10 Charts
- US-13 Response Detail
