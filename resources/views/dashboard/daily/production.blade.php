<!-- Daily Production Data - Current Month -->
<style>
    /* Custom styling to make plan data look like lines in the monthly chart */
    canvas#yearlyProductionChart {
        position: relative;
    }

    /* Add this to your page for a clearer visual of plan vs actual data */
    .plan-visualization-note {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 10px 15px;
        margin-top: 10px;
        border-left: 4px solid #28a745;
    }

    .plan-visualization-note .title {
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .plan-visualization-note .description {
        color: #666;
        font-size: 0.9rem;
    }
</style>

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
                <!-- Legend for Actual vs Plan -->
                <div class="mt-2 d-flex justify-content-center">
                    <div class="small text-muted">
                        <span class="badge bg-info text-white mr-2 px-2">Actual</span> Solid line indicates actual
                        production
                        <span class="badge bg-light text-dark mx-2 px-2" style="border: 1px dashed #666;">Plan</span>
                        Dashed line indicates planned target
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
                <!-- Legend for Actual vs Plan -->
                <div class="mt-2 d-flex justify-content-center">
                    <div class="small text-muted">
                        <span class="badge bg-success text-white mr-2 px-2">Actual</span> Solid bars indicate actual
                        production
                        <span class="badge bg-light text-dark mx-2 px-2" style="border: 1px dashed #666;">Plan</span>
                        Dashed lines indicate planned targets
                    </div>
                </div>

                <!-- Visual representation note -->
                <div class="plan-visualization-note mt-3">
                    <div class="title"><i class="fas fa-info-circle mr-1"></i> Visualizing Monthly Plans</div>
                    <div class="description">
                        For better comparison between actual production and plans, the plan data is visualized using
                        line charts overlaid on the bar charts.
                        This makes it easier to see how actual production compares to targets for each month.
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
                        <!-- Table Legend -->
                        <div class="mt-2 small text-muted">
                            <div><i class="fas fa-circle mr-1"></i> Regular rows show actual production data</div>
                            <div><i class="fas fa-circle-notch mr-1"></i> <span class="font-italic">Italic rows</span>
                                show planned production targets</div>
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
                        <!-- Table Legend -->
                        <div class="mt-2 small text-muted">
                            <div><i class="fas fa-circle mr-1"></i> Regular rows show actual production data</div>
                            <div><i class="fas fa-circle-notch mr-1"></i> <span class="font-italic">Italic rows</span>
                                show planned production targets</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom script to modify how Charts.js handles plan datasets -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for the original chart to be created
        const originalCreateYearlyChart = window.createYearlyChart;

        // Override the createYearlyChart function
        window.createYearlyChart = function(yearlyData) {
            try {
                // Clean up previous chart if exists
                if (yearlyProductionChart) {
                    yearlyProductionChart.destroy();
                }

                // Get the container and create a fresh canvas
                const container = document.getElementById("yearlyChartContainer");
                container.innerHTML = '<canvas id="yearlyProductionChart"></canvas>';

                // Split datasets into actual and plan
                const actualDatasets = [];
                const planDatasets = [];

                // Process each project's data
                yearlyData.chart_data.forEach((series, index) => {
                    // Ensure data is numeric
                    const numericData = series.data.map((val) => Number(val) || 0);

                    // Check if this is a plan dataset
                    const isPlan = series.name.includes("(Plan)");

                    if (isPlan) {
                        // Add to plan datasets (lines)
                        planDatasets.push({
                            label: series.name,
                            data: numericData,
                            type: 'line',
                            borderColor: colorPalette[index % colorPalette.length],
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointBackgroundColor: colorPalette[index % colorPalette.length],
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: false,
                            tension: 0.3,
                            order: 0 // Lines in front
                        });
                    } else {
                        // Add to actual datasets (bars)
                        actualDatasets.push({
                            label: series.name,
                            data: numericData,
                            type: 'bar',
                            backgroundColor: colorPalette[index % colorPalette.length]
                                .replace("1)", "0.7)"),
                            borderColor: colorPalette[index % colorPalette.length],
                            borderWidth: 1,
                            order: 1 // Bars behind
                        });
                    }
                });

                // Combine all datasets
                const combinedDatasets = [...actualDatasets, ...planDatasets];

                // Get canvas context
                const ctx = document.getElementById("yearlyProductionChart").getContext("2d");

                // Create mixed chart
                yearlyProductionChart = new Chart(ctx, {
                    type: 'bar', // Default type, will be overridden by dataset types
                    data: {
                        labels: yearlyData.months,
                        datasets: combinedDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: `${yearlyData.year} Monthly Production vs Plan`,
                                font: {
                                    size: 16
                                }
                            },
                            tooltip: {
                                mode: "index",
                                intersect: false
                            },
                            legend: {
                                position: "top",
                                labels: {
                                    usePointStyle: true
                                }
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: "Month"
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Production"
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error("Error creating yearly chart:", error);
                showError("yearlyChartContainer", "Error creating chart");
            }
        };
    });
</script>
