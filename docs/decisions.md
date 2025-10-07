**Purpose**: Record technical decisions and rationale for future reference
**Last Updated**: [Auto-updated by AI]

# Technical Decision Records

## Decision Template

Decision: [Title] - [YYYY-MM-DD]

**Context**: [What situation led to this decision?]

**Options Considered**:

1. **Option A**: [Description]
    - ✅ Pros: [Benefits]
    - ❌ Cons: [Drawbacks]
2. **Option B**: [Description]
    - ✅ Pros: [Benefits]
    - ❌ Cons: [Drawbacks]

**Decision**: [What we chose]

**Rationale**: [Why we chose this option]

**Implementation**: [How this affects the codebase]

**Review Date**: [When to revisit this decision]

---

## Recent Decisions

### Decision: Laravel 8.75+ with AdminLTE Integration - 2022-02-14

**Context**: Need for a robust ERP system with modern UI and comprehensive data management capabilities for industrial operations.

**Options Considered**:

1. **Custom Frontend (React/Vue)**:

    - ✅ Pros: Modern UI, better user experience, component reusability
    - ❌ Cons: Additional complexity, longer development time, maintenance overhead

2. **AdminLTE with Blade Templates**:
    - ✅ Pros: Rapid development, proven UI components, Laravel integration, cost-effective
    - ❌ Cons: Less flexibility, potential UI limitations

**Decision**: Laravel 8.75+ with AdminLTE 3.x and Blade templates

**Rationale**: Chosen for rapid development of enterprise features with proven UI components. AdminLTE provides comprehensive dashboard, tables, forms, and charts out of the box, significantly reducing development time while maintaining professional appearance.

**Implementation**:

-   AdminLTE 3.x integrated in `public/adminlte/`
-   Blade templates in `resources/views/` using AdminLTE components
-   DataTables integration for dynamic data presentation
-   Chart.js for dashboard visualizations

**Review Date**: 2025-08-01

---

### Decision: Spatie Permission Package for RBAC - 2022-03-04

**Context**: Need for role-based access control across multiple business modules with different permission levels.

**Options Considered**:

1. **Custom Permission System**:

    - ✅ Pros: Full control, lightweight, tailored to needs
    - ❌ Cons: Development time, security considerations, maintenance burden

2. **Spatie Laravel Permission**:
    - ✅ Pros: Battle-tested, comprehensive features, Laravel integration, active maintenance
    - ❌ Cons: Additional package dependency

**Decision**: Spatie Laravel Permission package

**Rationale**: Spatie package provides robust, secure, and well-maintained RBAC functionality. It handles complex permission scenarios, caching, and integrates seamlessly with Laravel's authentication system.

**Implementation**:

-   Migration: `2022_03_04_014507_create_permission_tables.php`
-   User model extends `HasRoles` trait
-   Route protection with middleware
-   Role and permission management interfaces

**Review Date**: 2025-06-01

---

### Decision: Laravel Excel for Data Import/Export - 2022-02-14

**Context**: Need to process large Excel files from SAP systems (POWITHETA, GRPO, MIGI, INCOMING) and generate reports.

**Options Considered**:

1. **PHPExcel/PhpSpreadsheet**:

    - ✅ Pros: Direct control, no framework dependency
    - ❌ Cons: Manual integration, more complex implementation

2. **Laravel Excel (Maatwebsite)**:
    - ✅ Pros: Laravel integration, queue support, export classes, import validation
    - ❌ Cons: Package dependency

**Decision**: Laravel Excel (Maatwebsite) package

**Rationale**: Laravel Excel provides excellent Laravel integration with import/export classes, validation, and queue support for large files. It simplifies Excel processing while maintaining Laravel conventions.

**Implementation**:

-   Import classes: `PowithetaImport`, `GrpoImport`, `MigiImport`, `IncomingImport`, `DailyProductionsImport`
-   Export classes: Various export classes for different data types and time periods
-   Queue support for large file processing
-   Validation and error handling

**Review Date**: 2025-05-01

---

### Decision: Batch Processing for Data Import - 2022-05-20

**Context**: Need to handle multiple data imports without conflicts and provide data versioning capabilities.

**Options Considered**:

1. **Direct Table Replacement**:

    - ✅ Pros: Simple implementation, no data conflicts
    - ❌ Cons: Data loss, no versioning, no rollback capability

2. **Batch-based Processing**:
    - ✅ Pros: Data versioning, rollback capability, conflict resolution, audit trail
    - ❌ Cons: Additional complexity, storage overhead

**Decision**: Batch-based processing with batch field

**Rationale**: Batch processing allows for data versioning, rollback capabilities, and prevents data conflicts during imports. It provides better data management and audit capabilities.

**Implementation**:

-   Added `batch` field to powithetas, grpos, migis tables
-   Batch-based queries for data retrieval
-   Truncate functionality with batch consideration
-   Progress tracking for import operations

**Review Date**: 2025-07-01

---

### Decision: Normalized Purchase Order Structure - 2025-02-07

**Context**: POWITHETA data contains flattened purchase order information that needs to be converted to a normalized structure for better data management.

**Options Considered**:

1. **Keep Flattened Structure**:

    - ✅ Pros: Simple queries, no joins required
    - ❌ Cons: Data redundancy, difficult to maintain, poor referential integrity

2. **Normalized Structure**:
    - ✅ Pros: Data integrity, reduced redundancy, better relationships, easier maintenance
    - ❌ Cons: More complex queries, additional tables

**Decision**: Normalized structure with separate purchase_orders and purchase_order_items tables

**Rationale**: Normalized structure provides better data integrity, reduces redundancy, and enables proper supplier relationships. It follows database normalization principles and makes the system more maintainable.

**Implementation**:

-   `purchase_orders` table for PO header information
-   `purchase_order_items` table for line items
-   `suppliers` table for vendor master data
-   Conversion logic from POWITHETA to normalized structure
-   Foreign key relationships and constraints

**Review Date**: 2025-08-01

---

### Decision: ApexCharts for Interactive Dashboard Visualizations - 2025-01-16

**Context**: Yearly dashboard required modern, interactive visualizations to replace static data tables. Need for professional charts with export capabilities, responsive design, and better data comprehension for stakeholders.

**Options Considered**:

1. **Chart.js (Existing)**:

    - ✅ Pros: Already integrated, lightweight, simple API
    - ❌ Cons: Limited interactivity, basic chart types, no built-in export, less modern UI

2. **Highcharts**:

    - ✅ Pros: Feature-rich, professional appearance, extensive documentation
    - ❌ Cons: Commercial license required, more expensive, larger bundle size

3. **ApexCharts**:
    - ✅ Pros: Free for commercial use, modern design, interactive out-of-box, built-in export, excellent responsive design, comprehensive chart types, smooth animations
    - ❌ Cons: Larger than Chart.js, requires CDN or npm installation

**Decision**: ApexCharts v3.45.1 for yearly dashboard visualizations

**Rationale**: ApexCharts provides the best balance of features, cost (free), and user experience. Built-in interactivity (hover, zoom, pan), export functionality, and modern design significantly improve data comprehension without licensing costs. The library's responsive design and smooth animations create a professional dashboard experience that aligns with stakeholder expectations.

**Implementation**:

-   ApexCharts loaded via CDN in yearly dashboard view
-   Seven chart types implemented:
    1. Budget Performance Bar Chart (Budget vs PO Sent)
    2. Budget Distribution Donut Chart
    3. GRPO Completion Rate Bar Chart (color-coded)
    4. GRPO Gauge Chart (radial progress)
    5. NPI Production Index Bar Chart
    6. NPI Scatter Chart (production flow)
    7. Radar Chart (multi-metric 360° view)
-   Individual chart export to PNG/SVG
-   Custom Indonesian number formatting in tooltips
-   Responsive design for mobile/tablet/desktop
-   Chart instances stored globally for export functionality
-   Scripts loaded via @section('scripts') for proper jQuery dependency order

**Review Date**: 2026-01-01

---

### Decision: Hardcoded Project Inclusion Arrays in Controllers - 2025-01-16

**Context**: Project 025C was missing from yearly dashboard analytics due to hardcoded project arrays in controllers. Need to ensure all active projects are included in dashboard calculations.

**Options Considered**:

1. **Keep Hardcoded Arrays**:

    - ✅ Pros: Explicit control, no database queries, fast performance
    - ❌ Cons: Manual updates required, easy to forget, maintenance burden

2. **Database-Driven Project Lists**:

    - ✅ Pros: Dynamic updates, no code changes needed, single source of truth
    - ❌ Cons: Additional database queries, potential performance impact

3. **Configuration File**:
    - ✅ Pros: Centralized configuration, easy updates, no code changes
    - ❌ Cons: Requires cache clearing, still manual process

**Decision**: Continue with hardcoded arrays but with improved documentation and dual-controller awareness

**Rationale**: Hardcoded arrays provide best performance for dashboard queries that run frequently. Database-driven approach would add unnecessary queries to every dashboard load. Current approach works well when properly documented. Key improvement is ensuring both YearlyIndexController and YearlyHistoryController are updated together.

**Implementation**:

-   Updated both controllers to include project 025C:
    -   `YearlyIndexController::$include_projects = ['017C', '021C', '022C', '023C', '025C', 'APS']`
    -   `YearlyHistoryController::$include_projects = ['017C', '021C', '022C', '023C', '025C', 'APS']`
-   Documented in MEMORY.md that both controllers must be updated together
-   Added to architecture documentation for future reference

**Review Date**: 2025-06-01 (when considering dashboard refactoring)
