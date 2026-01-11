# ETEEAP Survey Codebase Audit

Date: 2026-01-11

Scope: Static review of the local codebase only. No runtime execution, dependency scanning, or infrastructure review performed.

## Summary (Highest Risk)
- Admin reports page is vulnerable to stored XSS via unescaped table cells.
- Web multi-step survey flow allows submission without confirmed consent.
- Installer uses unsafe SQL string construction for DB usernames if left enabled.
- Report CSV export does not guard against spreadsheet formula injection.
- CSP blocks inline styles used on the reports page, causing production UI regressions.

## Findings

### Critical
1) Stored XSS in Admin Reports tables
   - Evidence:
     - `src/views/admin/reports.php:716-720` builds table cells with `innerHTML` and inserts `${cell}` without escaping.
     - The report data includes user-controlled fields (e.g., `current_position`, `task`, `motivation`, `barrier`, `course`) from the database. Examples of sources: `src/services/ReportGenerator.php:422-424`, `src/services/ReportGenerator.php:429-432`, `src/services/ReportGenerator.php:586-589`, `src/services/ReportGenerator.php:712-715`, `src/services/ReportGenerator.php:740-743`.
   - Impact: An attacker can submit a survey response containing HTML/JS in any of these fields. When an admin opens the Reports dashboard, the script executes in the admin’s session.
   - Fix: In `renderTables`, build DOM nodes and set `textContent` (not `innerHTML`) for cell values. Alternatively, HTML-escape server-side before returning report data, or add a strict client-side escape function before interpolation.

### High
2) Consent bypass in the multi-step web flow
   - Evidence:
     - Consent is enforced in GET display (`src/controllers/SurveyController.php:36-39`), but `saveStep()` does not verify `consent_given` for steps > 1 (`src/controllers/SurveyController.php:81-132`).
     - `submitSurvey()` hard-sets `consent_given` to `true` on insert without checking session consent (`src/controllers/SurveyController.php:173-194`).
   - Impact: A user can POST steps 2–8 directly (with a valid CSRF token) and submit a response without ever consenting. This is a compliance and data integrity risk.
   - Fix: Enforce consent in `saveStep()` for all steps > 1 and/or in `submitSurvey()` before insert. Only set `consent_given = true` when the session shows explicit consent.

### Medium
3) Installer SQL injection risk via DB username
   - Evidence: The installer builds `CREATE USER/ALTER USER/GRANT` statements using a DB username escaped only via `str_replace("'", "''", $dbUser)` (`src/controllers/InstallController.php:197-205`).
   - Impact: If the installer is reachable, a crafted DB username could break out of the SQL string in MySQL and execute arbitrary SQL as the installer’s DB admin user.
   - Fix: Validate `$dbUser` with a strict regex (e.g., `^[A-Za-z0-9_]+$`) and escape using `$adminConn->real_escape_string()` (or refuse any value that is not strictly alphanumeric/underscore).

4) CSV/Excel formula injection in report exports
   - Evidence: `ReportGenerator::exportCsv()` writes raw report rows with `fputcsv()` and no formula escaping (`src/services/ReportGenerator.php:150-189`), unlike `exportSurveyToCsv()` which protects with `csvEscapeFormula()` (`src/helpers/export.php:22-26`, `src/helpers/export.php:361-362`).
   - Impact: If a respondent submits values beginning with `=`, `+`, `-`, or `@`, opening exported reports in Excel can trigger formula execution.
   - Fix: Apply the same `csvEscapeFormula()` logic before every `fputcsv()` in `ReportGenerator::exportCsv()`.

5) CSRF protections missing on admin report generation/export endpoints
   - Evidence: `generateReport()` and `exportReport()` do not call `csrfProtect()` (`src/controllers/AdminController.php:187-241`). `exportCsv()` also lacks CSRF protection (`src/controllers/AdminController.php:141-145`).
   - Impact: Cross-site requests can trigger report generation or exports while an admin is logged in (data download/exfiltration risk).
   - Fix: Require POST + CSRF for report generation/export actions, or add a CSRF token check on GET endpoints (then disallow GET).

### Low
6) CSP blocks inline styles used by Reports page (production UI regression)
   - Evidence:
     - CSP allows `style-src 'self' https://fonts.googleapis.com` and blocks inline styles (`src/helpers/security.php:70-71`).
     - Reports page uses inline `<style nonce="...">` and inline `style="..."` attributes (`src/views/admin/reports.php:15-18`, plus multiple inline style attributes in the JS-rendered HTML).
   - Impact: In production (CSP enforced), the reports page’s inline styles will be blocked, causing broken visuals and layout regressions.
   - Fix: Move inline styles to compiled CSS files, or explicitly allow `style-src 'nonce-...'` and avoid `style-src-attr 'none'` if inline style attributes are required.

### Performance
7) Full export loads all responses into memory
   - Evidence: `exportCsv()` fetches all completed responses with `dbFetchAll()` and builds arrays before streaming (`src/controllers/AdminController.php:141-165`).
   - Impact: On large datasets, this can exhaust memory or time out, blocking exports.
   - Fix: Stream responses in chunks (pagination with streaming output), and fetch multi-value fields per chunk.

## Notes / Limitations
- This review is static and code-only; no runtime tests or environment inspection were performed.
- Findings are based strictly on the files listed above; other issues may exist outside the reviewed scope.
