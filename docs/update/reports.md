Below is the **FINAL, COMPREHENSIVE, IMPLEMENTATION-READY BLUEPRINT** you can **directly feed into a coding agent** (frontend + backend + BI).
This is written as a **single source of truth**: scope, structure, data mapping, UI behavior, and guardrails.

No missing pieces. No ambiguity. No design drift.

---

# ✅ FINAL COMPREHENSIVE DASHBOARD BLUEPRINT

**Dynamic Report Selector + Charts + Tables (Survey Snapshot Analytics)**

---

## 0️⃣ PURPOSE & SCOPE (LOCKED)

**Objective**
Build a **single-page dashboard** that dynamically renders **14 compatible analytical reports** from a **cross-sectional survey dataset** using **charts + summary tables**, optimized for **executive and program decision-making**.

**Explicit Constraints**

* Snapshot data only (no trends)
* Aggregated analytics only (no PII)
* One dashboard canvas, multiple report views
* All reports selectable via a **dynamic dropdown**
* Drill-downs via **modal windows only**

---

## 1️⃣ DATA CONTRACT (FOR CODING AGENT)

### Source Fields (Authoritative List)

```
Sex
Age Range
Office Type
Office / Field Office Assignment
Office Field / Unit / Program Assignment
Current Position / Designation
Employment Status
Total Years of Work Experience
Years of Social Work–Related Experience
Current Tasks / Functions
Social Work–Related Experiences
Highest Education
Undergraduate Course / Degree
Diploma Course
Graduate Course / Degree
Availed DSWD Training
DSWD Courses Taken
ETEEAP Awareness
ETEEAP Interest Level
Motivations
Barriers
Will Apply
```

### Hard Exclusions (DO NOT USE)

```
Last Name
First Name
Middle Name
Extension Name
Email
Phone
Consent Given
Created At
Completed At
```

---

## 2️⃣ GLOBAL UI LAYOUT (FIXED)

```
┌──────────────────────────────────────────────┐
│ Header / Title                               │
├──────────────────────────────────────────────┤
│ Report Selector (Dropdown)                   │
├──────────────────────────────────────────────┤
│ Global Filters (Persistent)                  │
│ Sex | Age Range | Office Type | Employment   │
│ Highest Education                            │
├──────────────────────────────────────────────┤
│ KPI Strip (Conditional)                      │
├──────────────────────────────────────────────┤
│ Main Visualization Canvas                    │
│   Charts (2–6 per report)                    │
├──────────────────────────────────────────────┤
│ Summary Table (Aggregated)                   │
├──────────────────────────────────────────────┤
│ Footer (Metadata)                            │
└──────────────────────────────────────────────┘
```

---

## 3️⃣ GLOBAL FILTER BEHAVIOR

* Filters apply to **ALL reports**
* Filters persist when switching reports
* Multi-select enabled
* Default state = ALL

---

## 4️⃣ REPORT REGISTRY (AUTHORITATIVE)

The dashboard **must support exactly these 14 reports**
(no more, no less unless new data fields are added).

---

# 📊 REPORT-LEVEL SPECIFICATIONS (FIELD-MAPPED)

Below is the **exact mapping the coding agent must follow**.

---

## 1. Executive Summary

### KPIs

* `COUNT(*)`
* `% Years of Social Work–Related Experience > 0`
* `% ETEEAP Awareness = Yes`
* `% ETEEAP Interest Level ≠ None`
* `% Will Apply = Yes`

### Charts

1. **Funnel**

   * `ETEEAP Awareness → ETEEAP Interest Level → Will Apply`
2. **Stacked Bar**

   * X: `Office Type`
   * Stack: `Employment Status`
3. **100% Stacked Bar**

   * Indicators:

     * `Years of Social Work–Related Experience`
     * `Availed DSWD Training`
     * `Highest Education`

### Table

| Metric | Value | % |

---

## 2. Workforce Demographics

### Charts

1. Stacked Bar

   * X: `Age Range`
   * Stack: `Sex`
2. Bar

   * `Sex`

### Table

| Age Range | Sex | Count |

---

## 3. Employment Profile

### Charts

1. Grouped Bar

   * X: `Office Type`
   * Group: `Employment Status`
2. Bar

   * `Office / Field Office Assignment`

### Table

| Office Type | Employment Status | Count |

---

## 4. Position & Role Distribution

### Charts

1. Horizontal Bar

   * `Current Position / Designation`
2. Horizontal Bar

   * `Current Tasks / Functions`

### Table

| Current Position / Designation | Current Tasks / Functions | Count |

---

## 5. Work Experience Profile

### Charts

1. Box Plot

   * `Total Years of Work Experience`
2. Box Plot

   * `Years of Social Work–Related Experience`

### Table

| Metric | Value |

---

## 6. Educational Attainment

### Charts

1. Donut

   * `Highest Education`
2. Bar

   * `Undergraduate Course / Degree`
   * `Diploma Course`
   * `Graduate Course / Degree`

### Table

| Highest Education | Count | % |

---

## 7. Social Work Exposure

### Charts

1. Horizontal Bar

   * `Social Work–Related Experiences`

### Table

| Social Work–Related Experiences | Count |

---

## 8. Training & Capacity Building

### Charts

1. Stacked Bar

   * `Availed DSWD Training`
2. Bar

   * `DSWD Courses Taken`

### Table

| DSWD Courses Taken | Availed DSWD Training | Count |

---

## 9. ETEEAP Awareness & Interest

### Charts

1. Bar

   * `ETEEAP Awareness`
2. Bar

   * `ETEEAP Interest Level`

### Table

| Awareness | Interest Level | Count |

---

## 10. Application Readiness

### Charts

1. Bar

   * `Will Apply`

### Table

| Will Apply | Count | % |

---

## 11. Motivation Analysis

### Charts

1. Horizontal Bar

   * `Motivations`

### Table

| Motivations | Frequency |

---

## 12. Barrier Analysis

### Charts

1. Horizontal Bar

   * `Barriers`

### Table

| Barriers | Frequency |

---

## 13. Program Readiness Snapshot

### Charts

1. 100% Stacked Bar

   * `Years of Social Work–Related Experience`
   * `Highest Education`
   * `Availed DSWD Training`
2. Bar

   * `Will Apply`

### Table

| Indicator | % Meeting |

---

## 14. Organizational Readiness Overview

### Charts

1. Grouped Bar

   * X: `Office / Field Office Assignment`
   * Measures:

     * `% with Social Work Experience`
     * `% Will Apply`
2. Bar

   * `Office / Field Office Assignment × Will Apply`

### Table

| Office | % With Experience | % Will Apply |

---

## 5️⃣ MODAL DRILL-DOWN RULES

### Trigger

* Clicking any chart element or “View details”

### Modal Content

* Expanded **aggregated table** OR
* Single focused breakdown chart

### Prohibitions

* No full reports
* No navigation
* No multiple charts

---

## 6️⃣ NON-FUNCTIONAL REQUIREMENTS

### Performance

* Lazy load reports
* Cache aggregations
* No raw row rendering

### Accessibility

* Keyboard navigable
* Tooltips + labels
* Color-safe palettes

---

## 7️⃣ GOVERNANCE & TRUST

Always display:

* Data source
* Filter state
* “Data as of” date

---

## 8️⃣ HARD STOP RULES (DO NOT VIOLATE)

❌ No time-series
❌ No financial KPIs
❌ No geographic maps
❌ No predictive analytics
❌ No PII tables

---

## ✅ FINAL VERDICT (FOR SIGN-OFF)

* ✔ Covers **ALL compatible templates**
* ✔ Uses **only existing fields**
* ✔ No speculative metrics
* ✔ Coding-agent ready
* ✔ Scalable if new fields are added later

---

If you want, next I can:

* Convert this into **JSON config schemas**
* Generate **SQL views per report**
* Produce **Power BI / Tableau datasets**
* Break this into **engineering tasks**

Just tell me the next handoff format.
