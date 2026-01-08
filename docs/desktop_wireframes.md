# Desktop Layout Wireframes

This document outlines how the mobile-first "Stitch" designs will adapt to larger desktop screens.

## 1. Survey Form Layout

### Mobile Design (Current Stitch)
- **Width:** 100% (max-w-md)
- **Navigation:** Sticky Footer
- **Layout:** Single Column vertical stack

```
+--------------------------------+
|  [Header: Logo & Progress]     |
|  [Sticky Top]                  |
+--------------------------------+
|                                |
|  [Title: Education History]    |
|                                |
|  [Card: School Name Input]     |
|                                |
|  [Card: Degree Radio List]     |
|                                |
|  [Card: Year Input]            |
|                                |
+--------------------------------+
|  [Sticky Footer: Back | Next]  |
+--------------------------------+
```

### Desktop Design (Adapted)
- **Width:** Centered, `max-w-4xl`
- **Navigation:** Inline buttons or Floating Sidebar
- **Layout:** 2-Column Grid for efficient use of space
- **Padding:** Increased whitespace for readability

```
+-----------------------------------------------------------------------+
|  [Header: Logo]                                   [Progress: Step 3/8]|
+-----------------------------------------------------------------------+
|                                                                       |
|   +-------------------+    +---------------------------------------+  |
|   | SIDEBAR / CONTEXT |    | MAIN CONTENT FORM                     |  |
|   |                   |    |                                       |  |
|   |  1. Consent  (✓)  |    |  [Title: Education History]           |  |
|   |  2. Basic    (✓)  |    |                                       |  |
|   |  3. Education (•) |    |  +---------------------------------+  |  |
|   |  4. Work          |    |  | Grid Row 1                      |  |  |
|   |                   |    |  | [School Name Input ______ ]     |  |  |
|   |                   |    |  | [Year Graduated Input ____]     |  |  |
|   |                   |    |  +---------------------------------+  |  |
|   |                   |    |                                       |  |
|   |                   |    |  +---------------------------------+  |  |
|   |                   |    |  | Grid Row 2 (Full Width)         |  |  |
|   |                   |    |  | [Degree Radio List]             |  |  |
|   |                   |    |  | (o) Bachelors  ( ) Masters      |  |  |
|   |                   |    |  | ( ) PhD        ( ) Vocational   |  |  |
|   |                   |    |  +---------------------------------+  |  |
|   |                   |    |                                       |  |
|   |                   |    |  [ Buttons:  < Back      Next >  ]    |  |  |
|   +-------------------+    +---------------------------------------+  |
|                                                                       |
+-----------------------------------------------------------------------+
```

**Key CSS Changes:**
- Container: `max-w-md` -> `lg:max-w-4xl`
- Grid: Flex-col -> `lg:grid lg:grid-cols-12 lg:gap-8`
- Sidebar: Hidden on mobile -> `lg:col-span-3 lg:block`
- Form Area: Full width -> `lg:col-span-9`
- Input Groups: Stacked -> `lg:grid lg:grid-cols-2 lg:gap-6` for short fields (e.g. Name + Year)

---

## 2. Admin Dashboard Layout

### Mobile Dashboard
- Hamburger menu for sidebar
- Stacked summary cards
- Scrollable tables

### Desktop Dashboard
- Persistent Sidebar
- Grid layout for Charts (2x2 or 3x1)
- Full width data tables

```
+-----------------------------------------------------------------------+
|  [SIDEBAR NAV]  |  [TOP BAR: Search & User Profile]                   |
|                 +-----------------------------------------------------+
|  - Dashboard    |  Page Title: Dashboard Overview                     |
|  - Responses    |                                                     |
|  - Reports      |  +-------+  +-------+  +-------+  +-------+         |
|  - Settings     |  | Total |  | Today |  | Avg % |  | Alerts|         |
|                 |  | 1,240 |  |  45   |  |  85%  |  |   2   |         |
|                 |  +-------+  +-------+  +-------+  +-------+         |
|                 |                                                     |
|                 |  +-----------------------+  +--------------------+  |
|                 |  | Chart: Responses/Day  |  | Chart: Demographics|  |
|                 |  | [Line Chart ........] |  | [Pie Chart ......] |  |
|                 |  +-----------------------+  +--------------------+  |
|                 |                                                     |
|                 |  +-----------------------------------------------+  |
|                 |  | Frequent Responses Table                      |  |
|                 |  | ID | Name | Office | Status | [Action]        |  |
|                 |  | ...| ...  | ...    | ...    | ...             |  |
|                 |  +-----------------------------------------------+  |
+-----------------+-----------------------------------------------------+
```

**Key CSS Changes:**
- Wrappers: `<aside>` is `hidden md:block w-64 fixed`
- Content: `ml-0 md:ml-64`
- Chart Grid: `grid-cols-1` -> `md:grid-cols-2 lg:grid-cols-3`
