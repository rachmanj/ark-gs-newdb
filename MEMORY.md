**Purpose**: AI's persistent knowledge base for project context and learnings
**Last Updated**: [Auto-updated by AI]

## Memory Maintenance Guidelines

### Structure Standards

-   Entry Format: ### [ID] [Title (YYYY-MM-DD)] ✅ STATUS
-   Required Fields: Date, Challenge/Decision, Solution, Key Learning
-   Length Limit: 3-6 lines per entry (excluding sub-bullets)
-   Status Indicators: ✅ COMPLETE, ⚠️ PARTIAL, ❌ BLOCKED

### Content Guidelines

-   Focus: Architecture decisions, critical bugs, security fixes, major technical challenges
-   Exclude: Routine features, minor bug fixes, documentation updates
-   Learning: Each entry must include actionable learning or decision rationale
-   Redundancy: Remove duplicate information, consolidate similar issues

### File Management

-   Archive Trigger: When file exceeds 500 lines or 6 months old
-   Archive Format: `memory-YYYY-MM.md` (e.g., `memory-2025-01.md`)
-   New File: Start fresh with current date and carry forward only active decisions

---

## Project Memory Entries

### [001] ARK-GS Comprehensive System Analysis (2025-01-16) ✅ COMPLETE

**Challenge**: Analyze and document the complete ARK-GS (ARK Global System) ERP architecture and business logic for industrial operations management.

**Solution**: Conducted comprehensive codebase analysis covering 33 controllers, 14 models, 23 database migrations, and complete business workflow understanding. Documented system architecture, technology stack, data flows, and architectural decisions.

**Key Learning**: ARK-GS is a sophisticated ERP system handling procurement (POWITHETA/SAP integration), production tracking (daily production with shift management), goods receipt processing (GRPO), material management (MIGI/INCOMING), and financial management (budget tracking). System uses Laravel 8.75+ with AdminLTE, Spatie permissions, and Laravel Excel for data processing. Architecture follows MVC pattern with batch-based data imports and normalized purchase order structures.

---

### [002] Multi-Modal Data Processing Architecture (2025-01-16) ✅ COMPLETE

**Challenge**: Understanding complex data import/export workflows for SAP system integration and production tracking.

**Solution**: Identified four main data processing modules: POWITHETA (PO data from SAP), GRPO (goods receipt), MIGI (material issues), INCOMING (incoming materials). Each module supports Excel import/export, batch processing, and conversion to normalized structures.

**Key Learning**: System handles both legacy SAP data (flattened structure) and modern normalized data structures. Batch processing enables data versioning and rollback capabilities. Production tracking includes shift-based operations with MTD (Month-to-Date) calculations for limestone and shalestone processing.

---

### [003] Role-Based Access Control Implementation (2025-01-16) ✅ COMPLETE

**Challenge**: Understanding security and access control mechanisms across multiple business modules.

**Solution**: System uses Spatie Laravel Permission package with role-based access control. Users can be assigned to specific projects via project_code field. Authentication handled by Laravel Sanctum with CSRF protection and middleware-based route protection.

**Key Learning**: RBAC system supports multi-project isolation where users can only access their assigned project data. Admin functions are restricted with proper middleware protection. System maintains audit trails through user tracking in production and budget management.

---

### [004] Dashboard and Reporting Architecture (2025-01-16) ✅ COMPLETE

**Challenge**: Understanding complex dashboard system with multiple views and real-time data aggregation.

**Solution**: Identified four main dashboard types: Daily (real-time operational metrics), Monthly (performance analytics), Yearly (annual trends), and Other (specialized business unit views). Dashboard system integrates Chart.js for visualizations and DataTables for dynamic data presentation.

**Key Learning**: Dashboard system provides comprehensive business intelligence with CAPEX vs Regular budget tracking, production performance metrics, and financial reporting. System supports both real-time data aggregation and historical trend analysis with export capabilities to Excel and PDF formats.

---

### [005] Yearly Dashboard Phase 1 Critical Fixes (2025-01-16) ✅ COMPLETE

**Challenge**: Multiple critical issues in yearly dashboard: incorrect breadcrumb navigation, missing project 025C from all analytics, poor mobile responsiveness, and lack of export functionality.

**Solution**: Fixed breadcrumb bug from "dashboard / monthly" to "dashboard / yearly". Added project 025C to both YearlyIndexController and YearlyHistoryController include_projects arrays. Implemented responsive design with Select2 dropdowns, loading indicators, collapsible cards, and info boxes. Created comprehensive export functionality supporting Excel, PDF, and CSV formats with backend controller methods and frontend modal interface.

**Key Learning**: Project inclusion is controlled by hardcoded arrays in controller properties - must update both current year (YearlyIndexController::$include_projects) and historical year (YearlyHistoryController::$include_projects) controllers to ensure complete data coverage. Export functionality requires separate backend routes and proper CSRF token handling for form submission.

---

### [006] ApexCharts Interactive Visualization Implementation (2025-01-16) ✅ COMPLETE

**Challenge**: Transform text-based yearly dashboard into interactive visual analytics platform with professional charts for better data comprehension and stakeholder presentations.

**Solution**: Integrated ApexCharts v3.45.1 library and created seven comprehensive chart types: (1) Budget Performance Bar Chart comparing Budget vs PO Sent, (2) Budget Distribution Donut Chart showing percentage breakdown, (3) GRPO Completion Rate Bar Chart with color-coded performance indicators, (4) GRPO Gauge Chart displaying overall completion percentage, (5) NPI Production Index Bar Chart comparing incoming/outgoing quantities, (6) NPI Scatter Chart for production flow analysis, (7) Radar Chart providing 360° multi-metric performance view. Implemented individual chart export functionality, interactive tooltips, zoom/pan capabilities, and responsive design.

**Key Learning**: Chart library initialization must occur after jQuery loads - solved by moving scripts to @section('scripts') instead of inline. Data transformation from backend requires proper JSON encoding with {!! json_encode() !!} to prevent HTML entity issues. Chart instances should be stored globally for export functionality. Indonesian number formatting requires custom tooltip formatters using toLocaleString('id-ID'). ApexCharts provides excellent out-of-box interactivity but requires careful color coding for usability (red < 80%, yellow 80-95%, green > 95% for completion rates).

---

### [007] Monthly Dashboard Project 025C Inclusion (2025-01-16) ✅ COMPLETE

**Challenge**: Project 025C was missing from monthly dashboard reports despite having valid data in the database. Same hardcoded array issue discovered in yearly dashboard also affected monthly dashboard.

**Solution**: Updated MonthlyHistoryController::$include_projects array on line 11 to include '025C'. Also updated TestController for consistency. Verified database contains valid September 2025 data for project 025C (PO Sent: IDR 594,135.97K, Budget: IDR 317,035.90K, GRPO: IDR 354,459.17K, NPI: 4,143 in / 2,583 out).

**Key Learning**: Project inclusion issue exists across multiple dashboard controllers (yearly, monthly, daily). After fixing yearly dashboard, must systematically check all dashboard-related controllers. Used grep search to identify all controllers with $include_projects arrays. Found and fixed MonthlyHistoryController and TestController. All dashboard controllers now consistently include project 025C: YearlyIndexController, YearlyHistoryController, MonthlyHistoryController, NpiController, GrpoIndexController, BudgetController, CapexController, TestController.

---

### [008] Monthly Dashboard UI Enhancement with Visual Performance Indicators (2025-01-16) ✅ COMPLETE

**Challenge**: Monthly dashboard tables displayed raw data without visual context, making it difficult to quickly identify performance issues, trends, or critical metrics. Tables lacked visual hierarchy and status indicators.

**Solution**: Implemented comprehensive visual performance indicator system across all monthly dashboard tables. Added color-coded status icons (check-circle, exclamation-triangle, times-circle), progress bars showing performance metrics with dynamic colors (green >95%, info 80-95%, warning 60-80%, danger <60%), status badges with descriptive labels (Excellent, Good, Attention, Critical), and enhanced table headers with gradient backgrounds. Created five-tier color coding system for budget performance and GRPO completion. Added contextual help with popover on NPI explaining the metric calculation. Implemented striped table styling with hover effects for better readability.

**Key Learning**: Visual indicators significantly improve data comprehension - users can instantly identify issues without reading numbers. Progress bars work best when showing percentage completion with color coding. Status badges should use descriptive text not just colors for accessibility. Tooltips provide essential context without cluttering the UI. For over-budget items (>100%), display dual progress bars (solid + striped) to show the excess amount visually. AdminLTE gradient backgrounds (bg-gradient-info, bg-gradient-warning) provide professional polish to table headers.

---

### [009] Monthly Dashboard Interactive ApexCharts Implementation (2025-01-16) ✅ COMPLETE

**Challenge**: Monthly dashboard lacked interactive visualizations to complement the enhanced visual indicators. Needed consistent charting approach across yearly and monthly dashboards for unified user experience.

**Solution**: Integrated ApexCharts v3.45.1 into monthly dashboard with four comprehensive chart types: (1) Budget Performance Bar Chart comparing Budget vs PO Sent for all projects, (2) Budget Distribution Donut Chart showing percentage breakdown, (3) GRPO Completion Rate Bar Chart with dynamic color coding based on completion percentage, (4) NPI Production Index Bar Chart comparing incoming/outgoing quantities. Implemented individual chart export to PNG, Indonesian number formatting in tooltips, responsive design, and proper script loading after jQuery initialization.

**Key Learning**: Reusing same charting library (ApexCharts) across different dashboards creates consistency and reduces learning curve. Monthly charts require similar but adapted configurations from yearly charts - smaller datasets mean simpler aggregations. Chart initialization timing is critical - must wait for both jQuery and ApexCharts to load before calling render methods. Export functionality works identically across dashboards when using same library. Color coordination between table indicators and charts (green=good, yellow=warning, red=critical) reinforces visual language consistency.

---

### [010] Project 026C Addition to All Dashboards (2025-10-29) ✅ COMPLETE

**Challenge**: New project 026C needed to be added to all dashboard controllers to ensure complete data visibility across daily, monthly, and yearly dashboards.

**Solution**: Systematically updated $include_projects arrays in nine controllers: CapexController (line 12), GrpoIndexController (line 11), NpiController (line 12), BudgetController (line 12 and methods at lines 17, 58, 89), YearlyIndexController (line 15), YearlyHistoryController (line 11), MonthlyHistoryController (line 11), DashboardMonthlyController (lines 24, 89), and TestController (line 13). Each controller now includes '026C' in the project list array following '025C' and before 'APS'.

**Key Learning**: Project visibility is controlled by hardcoded $include_projects arrays across multiple dashboard controllers. When adding new projects, must systematically update all dashboard-related controllers to ensure consistency. The pattern established: projects are listed in numeric order (017C, 021C, 022C, 025C, 026C) followed by alphabetic codes (APS, 023C). Use grep to find all $include_projects arrays: `grep -r "include_projects" app/Http/Controllers`. Browser-based testing confirmed the changes work correctly before documentation updates.

---

### [011] NPI Index Logic Correction and Monthly Dashboard Layout Optimization (2025-10-29) ✅ COMPLETE

**Challenge**: User feedback revealed NPI (Net Production Index) logic was inverted - lower index values should indicate better efficiency (less material waste), not higher values. Additionally, monthly dashboard cards needed full-width layout for better visibility and readability.

**Solution**: Corrected NPI status thresholds in resources/views/dashboard/monthly/npi.blade.php: changed from >= comparisons to <= comparisons (Excellent: ≤0.85, Good: ≤1.0, Average: ≤1.2, Below Target: ≤1.5, Critical: >1.5). Updated tooltip text to clarify "Lower is better - less material input needed for same output". Modified resources/views/dashboard/monthly/new_display.blade.php to change all four card sections from col-lg-6 (half-width) to col-12 (full-width), stacking REGULER, PO SENT vs GRPO, NPI Index, and CAPEX vertically.

**Key Learning**: Domain-specific metrics require validation with business users - mathematical logic may seem counterintuitive but reflects actual operational meaning. For NPI (Incoming/Outgoing ratio), lower values indicate better production efficiency as less raw material input is needed. Dashboard layout preferences vary by use case: side-by-side cards work for quick comparison, full-width cards provide better visibility for detailed data tables. Bootstrap grid system makes it easy to switch between layouts by changing col-\* classes. Always test inverted logic thoroughly - reversed thresholds and icon meanings (arrow-up becomes negative when lower is better).

---

### [012] Yearly Dashboard Index Page Enhancement with Preview Cards (2025-10-31) ✅ COMPLETE

**Challenge**: Yearly dashboard index page (resources/views/dashboard/yearly/index.blade.php) had minimal functionality - just a basic year selection dropdown with no preview information, quick navigation, or data visualization. User requested to add REGULER, CAPEX, GRPO, and NPI preview cards similar to monthly dashboard, displayed in full width with specific ordering.

**Solution**: Enhanced DashboardYearlyController with three new methods: getMultiYearChartData() fetches 5-year historical trends, getCurrentYearStats() calculates current year statistics, and getCurrentYearPreviewData() fetches detailed current year data from YearlyIndexController. Created four preview card blade files in resources/views/dashboard/yearly/preview/ (reguler.blade.php, grpo.blade.php, npi.blade.php, capex.blade.php) using same beautiful designs from monthly dashboard with progress bars, color-coded status badges, gradient headers, and tooltips. Completely redesigned yearly/index.blade.php with full-width cards (col-12) stacked vertically in requested order: REGULER, GRPO, NPI, CAPEX. Added year selection card, information card, quick links navigation, and multi-year performance chart using Chart.js.

**Key Learning**: Dashboard index pages should provide comprehensive preview and navigation capabilities. Consistency in UX across similar pages (monthly vs yearly) improves user familiarity. Reusing monthly dashboard card designs for yearly preview ensures visual consistency while adapting data structure (reguler_monthly vs reguler_yearly arrays). Full-width cards (col-12) provide better visibility for detailed tables with multiple columns compared to side-by-side layout (col-lg-6). Progress bars and status badges provide instant visual feedback on performance metrics. Testing with browser automation validates full workflow including data loading and card rendering. Initial loading timeout can occur when calling YearlyIndexController::index() due to heavy database queries across multiple projects, but successfully loads on retry.

---

### [013] Migration from CDN to Local Assets for Dashboard Libraries (2025-10-31) ✅ COMPLETE

**Challenge**: Dashboard pages were using external CDN links for Chart.js and ApexCharts libraries, which creates dependency on external services and potential performance/availability issues.

**Solution**: Migrated all dashboard visualizations to use local assets from public/adminlte/plugins. Updated Chart.js references in 3 files (resources/views/dashboard/monthly/index.blade.php, resources/views/dashboard/monthly/new_display.blade.php, resources/views/dashboard/yearly/index.blade.php) to use {{ asset('adminlte/plugins/chart.js/Chart.min.js') }} instead of CDN. Downloaded ApexCharts v3.45.1 (apexcharts.min.js and apexcharts.css) to public/adminlte/plugins/apexcharts/ directory. Updated ApexCharts references in 2 files (resources/views/dashboard/monthly/new_display.blade.php, resources/views/dashboard/yearly/new_display.blade.php) to use local assets.

**Key Learning**: AdminLTE already includes Chart.js in plugins/chart.js/ folder with multiple versions (Chart.js, Chart.min.js, Chart.bundle.js, Chart.bundle.min.js). When migrating from CDN to local assets, verify compatibility by checking library versions. Use Laravel's asset() helper for proper URL generation that respects public_path configuration. For libraries not included in AdminLTE (like ApexCharts), download specific versions to maintain consistency. PowerShell's Invoke-WebRequest is effective for downloading library files. Organize third-party libraries in public/adminlte/plugins/[library-name]/ for consistency with AdminLTE structure. After migration, test all charts to ensure functionality remains intact.