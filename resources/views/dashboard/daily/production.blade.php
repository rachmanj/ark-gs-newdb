<!-- Daily Production Data - Current Month -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 animate__animated animate__fadeIn ribbon-container">
            <div class="ribbon chart-ribbon">Trial</div>
            <div class="card-header bg-gradient-info text-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        Daily Production Overview
                    </h3>
                    <div class="d-flex">
                        <select id="productionMonth" class="form-control form-control-sm mr-2" style="width: auto;">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                        <select id="productionYear" class="form-control form-control-sm mr-2" style="width: auto;">
                            <option value="{{ now()->year - 1 }}">{{ now()->year - 1 }}</option>
                            <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                        </select>
                        <button id="updateProductionChart" class="btn btn-light btn-sm">
                            <i class="fas fa-sync-alt mr-1"></i>Update
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="dailyChartContainer" style="position: relative; height: 300px;">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2">Loading chart data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Yearly Production Data -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 animate__animated animate__fadeIn ribbon-container">
            <div class="ribbon chart-ribbon">Trial</div>
            <div class="card-header bg-gradient-success text-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Monthly Production Overview - <span id="yearlyChartYearDisplay">{{ now()->year }}</span>
                    </h3>
                </div>
            </div>
            <div class="card-body">
                <div id="yearlyChartContainer" style="position: relative; height: 300px;">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2">Loading chart data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Production Table -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 animate__animated animate__fadeIn ribbon-container">
            <div class="ribbon">Trial</div>
            <div class="card-header bg-gradient-info text-white border-0">
                <h3 class="card-title">
                    <i class="fas fa-table mr-1"></i>
                    Production Summary by Project
                </h3>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="productionTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="current-month-tab" data-toggle="tab" href="#current-month"
                            role="tab" aria-controls="current-month" aria-selected="true">This Month</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="yearly-tab" data-toggle="tab" href="#yearly-data" role="tab"
                            aria-controls="yearly-data" aria-selected="false"><span
                                id="yearlyTabYearDisplay">{{ now()->year }}</span> Summary</a>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="productionTabsContent">
                    <div class="tab-pane fade show active" id="current-month" role="tabpanel"
                        aria-labelledby="current-month-tab">
                        <div class="table-responsive-xl horizontal-scroll">
                            <table class="table table-bordered table-striped table-hover table-compact table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center align-middle sticky-col first-col">Project</th>
                                        <th class="text-center align-middle sticky-col second-col">Total</th>
                                        <th class="text-center day-column" id="day-columns">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="dailyProductionTableBody">
                                    <tr>
                                        <td colspan="33" class="text-center">
                                            <div class="spinner-border" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <p class="mt-2">Loading production data...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="yearly-data" role="tabpanel" aria-labelledby="yearly-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-compact">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center">Project</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Monthly Average</th>
                                    </tr>
                                </thead>
                                <tbody id="yearlyProductionTableBody">
                                    <tr>
                                        <td colspan="3" class="text-center">Loading data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
