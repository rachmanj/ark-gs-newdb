Purpose: Technical reference for understanding system design and development patterns
Last Updated: [Auto-updated by AI]

## Architecture Documentation Guidelines

### Document Purpose

This document describes the CURRENT WORKING STATE of the application architecture. It serves as:

-   Technical reference for understanding how the system currently works
-   Onboarding guide for new developers
-   Design pattern documentation for consistent development
-   Schema and data flow documentation reflecting actual implementation

### What TO Include

-   **Current Technology Stack**: Technologies actually in use
-   **Working Components**: Components that are implemented and functional
-   **Actual Database Schema**: Tables, fields, and relationships as they exist
-   **Implemented Data Flows**: How data actually moves through the system
-   **Working API Endpoints**: Routes that are active and functional
-   **Deployment Patterns**: How the system is actually deployed
-   **Security Measures**: Security implementations that are active

### What NOT to Include

-   **Issues or Bugs**: These belong in `MEMORY.md` with technical debt entries
-   **Limitations or Problems**: Document what IS working, not what isn't
-   **Future Plans**: Enhancement ideas belong in `backlog.md`
-   **Deprecated Features**: Remove outdated information rather than marking as deprecated
-   **Wishlist Items**: Planned features that aren't implemented yet

### Update Guidelines

-   **Reflect Reality**: Always document the actual current state, not intended state
-   **Schema Notes**: When database schema has unused fields, note them factually
-   **Cross-Reference**: Link to other docs when appropriate, but don't duplicate content

### For AI Coding Agents

-   **Investigate Before Updating**: Use codebase search to verify current implementation
-   **Move Issues to Memory**: If you discover problems, document them in `MEMORY.md`
-   **Factual Documentation**: Describe what exists, not what should exist

---

# System Architecture

## Project Overview

**ARK-GS (ARK Global System)** is a comprehensive enterprise resource planning (ERP) system designed for industrial operations management, specifically focused on procurement, production tracking, and financial management. The system handles multiple business domains including:

-   **Purchase Order Management**: POWITHETA data processing and PO conversion
-   **Goods Receipt Processing**: GRPO (Goods Receipt Purchase Order) management
-   **Production Monitoring**: Daily production tracking with shift-based operations
-   **Budget Management**: CAPEX and regular budget tracking
-   **Supplier Management**: Vendor and supplier relationship management
-   **Project Management**: Multi-project support with project-specific tracking
-   **Financial Reporting**: Comprehensive dashboards and analytics

## Technology Stack

-   **Backend**: Laravel 8.75+ (PHP 7.3+)
-   **Frontend**: Blade templates with AdminLTE 3.x UI framework
-   **Database**: MySQL with Eloquent ORM
-   **Authentication**: Laravel Sanctum with Spatie Permission package
-   **Data Processing**: Laravel Excel (Maatwebsite) for import/export operations
-   **UI Components**: DataTables (Yajra) for dynamic tables
-   **Data Visualization**: 
    -   Chart.js (local asset: public/adminlte/plugins/chart.js/) for basic visualizations
    -   ApexCharts v3.45.1 (local asset: public/adminlte/plugins/apexcharts/) for interactive charts
-   **PDF Generation**: DomPDF for report generation
-   **Asset Compilation**: Laravel Mix with Webpack

## Core Components

### 1. Data Import/Export System

-   **POWITHETA Controller**: Handles purchase order data from SAP systems
-   **GRPO Controller**: Manages goods receipt processing
-   **MIGI Controller**: Processes material issue data
-   **INCOMING Controller**: Handles incoming material tracking
-   **Daily Production Controller**: Manages production data entry and tracking

### 2. Dashboard System

-   **Daily Dashboard**: Real-time operational metrics
-   **Monthly Dashboard**: Monthly performance analytics
-   **Yearly Dashboard**: Annual reporting and trends with comprehensive index page
    -   **Index Page** (`resources/views/dashboard/yearly/index.blade.php`):
        -   Year selection card with modern dropdown interface
        -   Information card explaining dashboard features
        -   Quick links navigation to Daily, Monthly, Summary by Unit, Search PO
        -   Current year preview cards (full-width, stacked vertically):
            -   REGULER card: Budget vs PO Sent with progress bars and status badges
            -   GRPO card: Completion rates with color-coded performance indicators
            -   NPI Index card: Production efficiency with lower-is-better logic
            -   CAPEX card: Capital expenditure tracking
        -   Multi-year performance chart showing 5-year trends (Budget, PO Sent, GRPO)
    -   **Display Page** (`resources/views/dashboard/yearly/new_display.blade.php`):
        -   Interactive ApexCharts visualizations (7 chart types)
        -   Budget Performance Bar Chart (Budget vs PO Sent comparison)
        -   Budget Distribution Donut Chart (project allocation breakdown)
        -   GRPO Completion Rate Bar Chart (color-coded performance indicators)
        -   GRPO Gauge Chart (overall completion percentage)
        -   NPI Production Index Bar Chart (incoming/outgoing comparison)
        -   NPI Scatter Chart (production flow analysis)
        -   Radar Chart (360° multi-metric performance view)
        -   Individual chart export to PNG/SVG
        -   Interactive tooltips, zoom, and pan capabilities
        -   Responsive design for mobile and desktop
-   **Other Dashboards**: Specialized views for different business units

### 3. User Management

-   **Role-based Access Control**: Using Spatie Permission package
-   **Multi-project Support**: Users can be assigned to specific projects
-   **Authentication**: Laravel Sanctum for API and web authentication

### 4. Budget Management

-   **Budget Types**: Configurable budget categories
-   **Budget Tracking**: CAPEX vs Regular budget monitoring
-   **History Management**: Budget change tracking and auditing

## Database Schema

### Core Business Tables

#### Purchase & Procurement

-   **powithetas**: Purchase order data from SAP (PO_NO, vendor info, items, amounts)
-   **grpos**: Goods receipt processing orders (GRPO_NO, delivery tracking)
-   **migis**: Material issue transactions
-   **incomings**: Incoming material tracking
-   **purchase_orders**: Normalized PO structure with supplier relationships
-   **purchase_order_items**: Individual line items for POs
-   **suppliers**: Vendor/supplier master data

#### Production Management

-   **daily_productions**: Daily production metrics (shifts, MTD data, limestone/shalestone)
-   **production_plans**: Production planning and forecasting
-   **projects**: Project master data with active/inactive status

#### Financial Management

-   **budgets**: Budget allocation and tracking
-   **budget_types**: Budget category definitions
-   **histories**: Financial transaction history

#### System Tables

-   **users**: User accounts with project assignments
-   **roles/permissions**: Spatie permission system tables
-   **progress_trackers**: System operation tracking

### Key Relationships

-   Users → Projects (via project_code)
-   Purchase Orders → Suppliers → Purchase Order Items
-   Daily Productions → Users (created_by)
-   Budgets → Budget Types

## API Design

### Web Routes (MVC Pattern)

All routes follow RESTful conventions with resource controllers:

```
/auth → Authentication (login, register, logout)
/dashboard/* → Dashboard views (daily, monthly, yearly, other)
/powitheta/* → PO data management (index, import, export, convert)
/grpo/* → Goods receipt management
/migi/* → Material issue tracking
/incoming/* → Incoming material management
/daily-production/* → Production data CRUD operations
/budget/* → Budget management
/users/* → User management with role assignments
/roles/* → Role management
/permissions/* → Permission management
```

### Data Processing Flow

1. **Import**: Excel files → Validation → Database storage
2. **Export**: Database queries → Excel/PDF generation → Download
3. **Conversion**: POWITHETA data → Purchase Orders → Supplier relationships
4. **Dashboard**: Real-time aggregation → Chart.js/ApexCharts visualizations
5. **Yearly Dashboard**: Data aggregation → JSON encoding → ApexCharts rendering → Interactive charts

### Yearly Dashboard Implementation Details

**Controllers:**

-   `DashboardYearlyController`: Main controller handling year selection and data routing
-   `YearlyIndexController`: Processes current year data (include_projects: 017C, 021C, 022C, 023C, 025C, APS)
-   `YearlyHistoryController`: Processes historical year data with same project inclusion

**Data Flow:**

1. User selects year → POST to `/dashboard/yearly`
2. Controller determines current vs historical year
3. Appropriate controller processes data (reguler, capex, grpo, npi metrics)
4. Data passed to view via compact array
5. Blade template JSON-encodes data for JavaScript
6. ApexCharts library initializes 7 interactive charts
7. Charts render with Indonesian number formatting and custom tooltips

**Chart Export:**

-   Individual chart export via ApexCharts dataURI() method
-   Supports PNG and SVG formats
-   Dashboard-wide export to Excel/PDF/CSV via backend routes

**Key Technical Notes:**

-   Scripts must load via @section('scripts') after jQuery
-   Chart initialization delayed 500ms for library loading
-   Select2 is optional with fallback handling
-   Data transformation uses {!! json_encode() !!} to prevent HTML entity encoding

## Data Flow

```mermaid
graph TD
    A[Excel Import] --> B[Data Validation]
    B --> C[Database Storage]
    C --> D[Data Processing]
    D --> E[Dashboard Aggregation]
    E --> F[Chart Visualization]

    G[User Input] --> H[Form Validation]
    H --> I[Business Logic]
    I --> J[Database Update]
    J --> K[Response Generation]

    L[PO Conversion] --> M[Supplier Matching]
    M --> N[Purchase Order Creation]
    N --> O[Item Line Creation]

    P[Production Entry] --> Q[Shift Calculation]
    Q --> R[MTD Aggregation]
    R --> S[Performance Metrics]
```

## Security Implementation

### Authentication & Authorization

-   **Laravel Sanctum**: API token authentication
-   **Spatie Permission**: Role-based access control (RBAC)
-   **Middleware Protection**: Auth middleware on all protected routes
-   **CSRF Protection**: Laravel's built-in CSRF token validation
-   **Password Hashing**: Laravel's bcrypt hashing

### Data Protection

-   **Mass Assignment Protection**: Eloquent model $fillable/$guarded properties
-   **SQL Injection Prevention**: Eloquent ORM with parameterized queries
-   **XSS Protection**: Blade template escaping
-   **File Upload Security**: Excel import validation and sanitization

### Access Control

-   **Role-based Routes**: Different access levels for different user roles
-   **Project Isolation**: Users can only access their assigned project data
-   **Admin Functions**: Restricted administrative operations

## Deployment

### Development Environment

-   **Local Server**: Laravel Artisan serve (php artisan serve)
-   **Database**: MySQL with Laravel migrations
-   **Asset Compilation**: Laravel Mix (npm run dev/prod)
-   **Testing**: PHPUnit for backend testing

### Production Considerations

-   **Web Server**: Apache/Nginx with PHP-FPM
-   **Database**: MySQL with proper indexing for large datasets
-   **File Storage**: Local storage for Excel imports/exports
-   **Caching**: Laravel's file/database caching for dashboard data
-   **Logging**: Laravel's logging system for error tracking
