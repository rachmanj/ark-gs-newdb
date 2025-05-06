/**
 * Dashboard Production Charts JS
 * Handles the daily and yearly production charts
 */

// Create color palette for the lines
const colorPalette = [
    "rgba(54, 162, 235, 1)", // Blue
    "rgba(255, 99, 132, 1)", // Red
    "rgba(75, 192, 192, 1)", // Green
    "rgba(255, 159, 64, 1)", // Orange
    "rgba(153, 102, 255, 1)", // Purple
    "rgba(255, 205, 86, 1)", // Yellow
    "rgba(201, 203, 207, 1)", // Grey
];

// Initialize chart instances
let dailyProductionChart = null;
let yearlyProductionChart = null;

/**
 * Initialize the production charts on page load
 */
document.addEventListener("DOMContentLoaded", function () {
    // Set default values to current month/year
    const currentDate = new Date();
    const currentMonth = currentDate.getMonth() + 1; // JS months are 0-indexed
    const currentYear = currentDate.getFullYear();

    const productionMonthSelect = document.getElementById("productionMonth");
    const productionYearSelect = document.getElementById("productionYear");
    const updateProductionChart = document.getElementById(
        "updateProductionChart"
    );

    // Check if elements exist before using them
    if (productionMonthSelect) {
        productionMonthSelect.value = currentMonth;
    }

    if (productionYearSelect) {
        productionYearSelect.value = currentYear;
    }

    // Load initial data
    loadProductionData(currentMonth, currentYear);

    // Add event listener for update button if it exists
    if (updateProductionChart) {
        updateProductionChart.addEventListener("click", function () {
            const selectedMonth = productionMonthSelect
                ? productionMonthSelect.value
                : currentMonth;
            const selectedYear = productionYearSelect
                ? productionYearSelect.value
                : currentYear;
            loadProductionData(selectedMonth, selectedYear);
        });
    }
});

/**
 * Load production data for the selected month and year
 */
function loadProductionData(month, year) {
    // Update year displays with null checks
    const yearlyChartYearDisplay = document.getElementById(
        "yearlyChartYearDisplay"
    );
    if (yearlyChartYearDisplay) {
        yearlyChartYearDisplay.textContent = year;
    }

    // Only update the yearlyTabYearDisplay if it exists
    const yearlyTabElement = document.getElementById("yearlyTabYearDisplay");
    if (yearlyTabElement) {
        yearlyTabElement.textContent = year;
    }

    // Update the heading of the monthly production table
    const monthlyProductionHeaders = document.querySelectorAll(
        ".card-header h3.card-title"
    );
    monthlyProductionHeaders.forEach((header) => {
        if (header && header.querySelector("i.fas.fa-table")) {
            header.innerHTML = `<i class="fas fa-table mr-1"></i> Production Summary by Project - ${year}`;
        }
    });

    // Show loading state for charts with null checks
    const dailyChartContainer = document.getElementById("dailyChartContainer");
    const yearlyChartContainer = document.getElementById(
        "yearlyChartContainer"
    );

    if (dailyChartContainer) {
        showLoading("dailyChartContainer");
    }

    if (yearlyChartContainer) {
        showLoading("yearlyChartContainer");
    }

    // Build URL with parameters and add timestamp to prevent caching
    const timestamp = new Date().getTime();

    // Ensure dashboardDataUrl is defined
    if (typeof dashboardDataUrl === "undefined") {
        console.error("dashboardDataUrl is not defined");

        // Show error messages in the containers if they exist
        if (dailyChartContainer) {
            showError(
                "dailyChartContainer",
                "Configuration error: API URL not defined"
            );
        }

        if (yearlyChartContainer) {
            showError(
                "yearlyChartContainer",
                "Configuration error: API URL not defined"
            );
        }

        return; // Exit the function
    }

    const url = `${dashboardDataUrl}?month=${month}&year=${year}&_=${timestamp}`;

    // Fetch data with proper headers to prevent caching
    fetch(url, {
        method: "GET",
        headers: {
            "Cache-Control": "no-cache, no-store, must-revalidate",
            Pragma: "no-cache",
            Expires: "0",
        },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(
                    "Network response was not ok: " + response.status
                );
            }
            return response.json();
        })
        .then((data) => {
            try {
                // Process the daily chart data
                if (
                    data.current_month &&
                    data.current_month.chart_data &&
                    data.current_month.chart_data.length > 0 &&
                    dailyChartContainer
                ) {
                    createDailyChart(data.current_month, month, year);
                    populateCurrentMonthTable(data.current_month);
                } else if (dailyChartContainer) {
                    showNoData(
                        "dailyChartContainer",
                        "No production data available for this month"
                    );

                    // Check if table body exists
                    const dailyProductionTableBody = document.getElementById(
                        "dailyProductionTableBody"
                    );
                    if (dailyProductionTableBody) {
                        showNoDataTable("dailyProductionTableBody");
                    }
                }

                // Process the yearly chart data
                if (
                    data.yearly &&
                    data.yearly.chart_data &&
                    data.yearly.chart_data.length > 0 &&
                    yearlyChartContainer
                ) {
                    createYearlyChart(data.yearly);
                    populateYearlyTable(data.yearly);
                } else if (yearlyChartContainer) {
                    showNoData(
                        "yearlyChartContainer",
                        "No yearly production data available"
                    );

                    // Check if table body exists
                    const yearlyProductionTableBody = document.getElementById(
                        "yearlyProductionTableBody"
                    );
                    if (yearlyProductionTableBody) {
                        showNoDataTable("yearlyProductionTableBody");
                    }
                }
            } catch (error) {
                console.error("Error processing production data:", error);
                if (dailyChartContainer) {
                    showError(
                        "dailyChartContainer",
                        "Error processing chart data"
                    );
                }
                if (yearlyChartContainer) {
                    showError(
                        "yearlyChartContainer",
                        "Error processing chart data"
                    );
                }
            }
        })
        .catch((error) => {
            console.error("Error fetching production data:", error);
            if (dailyChartContainer) {
                showError("dailyChartContainer", "Error loading chart data");
            }
            if (yearlyChartContainer) {
                showError("yearlyChartContainer", "Error loading chart data");
            }

            // Check if table bodies exist before showing errors
            const dailyProductionTableBody = document.getElementById(
                "dailyProductionTableBody"
            );
            if (dailyProductionTableBody) {
                showErrorTable("dailyProductionTableBody");
            }

            const yearlyProductionTableBody = document.getElementById(
                "yearlyProductionTableBody"
            );
            if (yearlyProductionTableBody) {
                showErrorTable("yearlyProductionTableBody");
            }
        });
}

/**
 * Show loading spinner in a container
 */
function showLoading(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading chart data...</p>
                </div>
            </div>
        `;
    }
}

/**
 * Show no data message in a container
 */
function showNoData(containerId, message) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="alert alert-info m-0">
                    <i class="fas fa-info-circle mr-2"></i> ${message}
                </div>
            </div>
        `;
    }
}

/**
 * Show error message in a container
 */
function showError(containerId, message) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="alert alert-danger m-0">
                    <i class="fas fa-exclamation-circle mr-2"></i> ${message}
                </div>
            </div>
        `;
    }
}

/**
 * Show no data message in a table
 */
function showNoDataTable(tableId) {
    const tableBody = document.getElementById(tableId);
    if (tableBody) {
        // Determine the number of columns based on which table we're updating
        const colSpan = tableId === "yearlyProductionTableBody" ? 14 : 33;

        tableBody.innerHTML = `
            <tr>
                <td colspan="${colSpan}" class="text-center">
                    <div class="alert alert-info m-0 border-0">
                        <i class="fas fa-info-circle mr-2"></i> No data available
                    </div>
                </td>
            </tr>
        `;
    }
}

/**
 * Show error message in a table
 */
function showErrorTable(tableId) {
    const tableBody = document.getElementById(tableId);
    if (tableBody) {
        // Determine the number of columns based on which table we're updating
        const colSpan = tableId === "yearlyProductionTableBody" ? 14 : 33;

        tableBody.innerHTML = `
            <tr>
                <td colspan="${colSpan}" class="text-center">
                    <div class="alert alert-danger m-0 border-0">
                        <i class="fas fa-exclamation-circle mr-2"></i> Error loading data
                    </div>
                </td>
            </tr>
        `;
    }
}

/**
 * Create the daily production chart
 */
function createDailyChart(monthData, requestedMonth, requestedYear) {
    try {
        // Get the actual month/year from the response if available, otherwise use requested values
        const selectedMonth = monthData.selected_month || requestedMonth;
        const selectedYear = monthData.selected_year || requestedYear;

        // Clean up previous chart if exists
        if (dailyProductionChart) {
            dailyProductionChart.destroy();
        }

        // Get the container and create a fresh canvas
        const container = document.getElementById("dailyChartContainer");
        container.innerHTML = '<canvas id="dailyProductionChart"></canvas>';

        // Get month name and days in month
        const monthNames = [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        ];
        const monthName = monthNames[selectedMonth - 1];
        const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();

        // Prepare days array (1 to last day of month)
        const days = Array.from({ length: daysInMonth }, (_, i) => i + 1);

        // Prepare datasets
        const datasets = [];

        // Process each project's data
        monthData.chart_data.forEach((series, index) => {
            // Create an array of zeros for all days
            const seriesData = Array(daysInMonth).fill(0);

            // Fill in actual data
            if (series.data && Array.isArray(series.data)) {
                series.data.forEach((point) => {
                    try {
                        // Parse date taking into account the timezone offset
                        const dateObj = new Date(point.x);
                        const day = dateObj.getDate();

                        if (day >= 1 && day <= daysInMonth) {
                            // Convert value to number and assign to the correct day index
                            seriesData[day - 1] = Number(point.y);
                        }
                    } catch (e) {
                        console.error("Error parsing date:", e);
                    }
                });
            }

            // Check if this is a plan or actual dataset
            const isPlan = series.name.includes("(Plan)");

            // Add dataset with different styling for plan vs actual
            datasets.push({
                label: series.name,
                data: seriesData,
                borderColor: isPlan
                    ? colorPalette[index % colorPalette.length].replace(
                          "1)",
                          "0.7)"
                      )
                    : colorPalette[index % colorPalette.length],
                backgroundColor: "transparent",
                borderWidth: isPlan ? 1 : 2,
                pointRadius: isPlan ? 2 : 3,
                pointHoverRadius: isPlan ? 4 : 5,
                borderDash: isPlan ? [5, 5] : [],
                tension: 0.3,
            });
        });

        // Get canvas context
        const ctx = document
            .getElementById("dailyProductionChart")
            .getContext("2d");

        // Create chart
        dailyProductionChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: days,
                datasets: datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `${monthName} ${selectedYear} Daily Production`,
                        font: { size: 16 },
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false,
                        callbacks: {
                            title: function (context) {
                                return `Day ${context[0].label}`;
                            },
                        },
                    },
                    legend: {
                        position: "top",
                        labels: { usePointStyle: true },
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: "Day of Month",
                        },
                        ticks: {
                            stepSize: 1,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Production",
                        },
                    },
                },
            },
        });
    } catch (error) {
        console.error("Error creating daily chart:", error);
        showError("dailyChartContainer", "Error creating chart");
    }
}

/**
 * Create the yearly production chart
 */
function createYearlyChart(yearlyData) {
    try {
        // Clean up previous chart if exists
        if (yearlyProductionChart) {
            yearlyProductionChart.destroy();
        }

        // Get the container and create a fresh canvas
        const container = document.getElementById("yearlyChartContainer");
        container.innerHTML = '<canvas id="yearlyProductionChart"></canvas>';

        // Prepare datasets
        const datasets = [];

        // Process each project's data
        yearlyData.chart_data.forEach((series, index) => {
            // Ensure data is numeric
            const numericData = series.data.map((val) => Number(val) || 0);

            // Check if this is a plan dataset
            const isPlan = series.name.includes("(Plan)");

            // Add dataset
            datasets.push({
                label: series.name,
                data: numericData,
                backgroundColor: isPlan
                    ? colorPalette[index % colorPalette.length].replace(
                          "1)",
                          "0.3)"
                      )
                    : colorPalette[index % colorPalette.length].replace(
                          "1)",
                          "0.7)"
                      ),
                borderColor: colorPalette[index % colorPalette.length],
                borderWidth: isPlan ? 1 : 1,
                // Add pattern for plan bars
                ...(isPlan && {
                    borderDash: [5, 5],
                }),
            });
        });

        // Get canvas context
        const ctx = document
            .getElementById("yearlyProductionChart")
            .getContext("2d");

        // Create chart
        yearlyProductionChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: yearlyData.months,
                datasets: datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `${yearlyData.year} Monthly Production vs Plan`,
                        font: { size: 16 },
                    },
                    tooltip: {
                        mode: "index",
                        intersect: false,
                    },
                    legend: {
                        position: "top",
                        labels: { usePointStyle: true },
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: "Month",
                        },
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: "Production",
                        },
                    },
                },
            },
        });
    } catch (error) {
        console.error("Error creating yearly chart:", error);
        showError("yearlyChartContainer", "Error creating chart");
    }
}

/**
 * Populate the current month table with data
 */
function populateCurrentMonthTable(monthData) {
    const tableBody = document.getElementById("dailyProductionTableBody");
    const dayColumns = document.getElementById("day-columns");

    // Exit if the table body doesn't exist
    if (!tableBody) {
        console.warn("dailyProductionTableBody element not found");
        return;
    }

    // Clear loading content
    tableBody.innerHTML = "";

    if (
        !monthData.table_data ||
        monthData.table_data.length === 0 ||
        !monthData.dates ||
        monthData.dates.length === 0
    ) {
        tableBody.innerHTML =
            '<tr><td colspan="33" class="text-center">No production data available for this month</td></tr>';

        // Also reset the day columns header if it exists
        if (dayColumns) {
            dayColumns.innerHTML = "";
        }
        return;
    }

    // Sort dates chronologically
    const sortedDates = [...monthData.dates].sort();

    // Create day column headers
    if (dayColumns && dayColumns.parentElement) {
        const dayColumnsParent = dayColumns.parentElement;

        // Safely recreate header
        // First, restore original column headers setup by clearing all children after the first two columns (project & total)
        // Keep only the first two columns and remove the rest
        while (dayColumnsParent.children.length > 2) {
            dayColumnsParent.removeChild(dayColumnsParent.lastChild);
        }

        // Now add a new th for the days
        const newDayTh = document.createElement("th");
        newDayTh.id = "day-columns";
        newDayTh.className = "text-center day-column";
        dayColumnsParent.appendChild(newDayTh);

        // Add a th for each date
        sortedDates.forEach((date) => {
            // Get just the day number
            const dayNumber = new Date(date).getDate();

            const newTh = document.createElement("th");
            newTh.className = "text-center";
            newTh.textContent = dayNumber;
            dayColumnsParent.appendChild(newTh);
        });
    } else {
        console.error("Could not find day-columns element or its parent");
    }

    // Populate the table rows
    monthData.table_data.forEach((project, index) => {
        // Create row
        const row = document.createElement("tr");

        // Check if this is a plan row
        const isPlan = project.name.includes("(Plan)");

        // Add classes for plan rows
        if (isPlan) {
            row.classList.add("plan-row", "bg-light", "font-italic");
        }

        // Project name and summary columns with proper sticky classes
        let rowHtml = `
            <td class="sticky-col first-col">
                <span class="badge-dot" style="background-color: ${
                    colorPalette[index % colorPalette.length]
                }${isPlan ? "80" : ""}" ${
            isPlan ? 'style="border: 1px dashed #666"' : ""
        }></span>
                <span class="project-name">${project.name}</span>
            </td>
            <td class="text-right sticky-col second-col">${project.total.toLocaleString(
                "en-US",
                { maximumFractionDigits: 1, minimumFractionDigits: 1 }
            )}</td>
        `;

        // Add values for each day
        sortedDates.forEach((date) => {
            const value = project.days[date] || 0;
            // Format with 1 decimal place
            const formattedValue =
                value > 0
                    ? value.toLocaleString("en-US", {
                          maximumFractionDigits: 1,
                          minimumFractionDigits: 1,
                      })
                    : "-";
            rowHtml += `<td class="text-right">${formattedValue}</td>`;
        });

        row.innerHTML = rowHtml;
        tableBody.appendChild(row);
    });

    // Add a total row
    const totalRow = document.createElement("tr");
    totalRow.classList.add("font-weight-bold", "bg-light");

    // Project name and summary columns for total row
    const grandTotal = monthData.table_data.reduce((sum, project) => {
        // Only include actual production in total, not plan values
        if (!project.name.includes("(Plan)")) {
            return sum + project.total;
        }
        return sum;
    }, 0);
    let totalRowHtml = `
        <td class="sticky-col first-col">TOTAL</td>
        <td class="text-right sticky-col second-col">${grandTotal.toLocaleString(
            "en-US",
            { maximumFractionDigits: 1, minimumFractionDigits: 1 }
        )}</td>
    `;

    // Add total for each day
    sortedDates.forEach((date) => {
        const dailyTotal = monthData.table_data.reduce((sum, project) => {
            // Only include actual production in total, not plan values
            if (!project.name.includes("(Plan)")) {
                return sum + (project.days[date] || 0);
            }
            return sum;
        }, 0);
        const formattedDailyTotal =
            dailyTotal > 0
                ? dailyTotal.toLocaleString("en-US", {
                      maximumFractionDigits: 1,
                      minimumFractionDigits: 1,
                  })
                : "-";
        totalRowHtml += `<td class="text-right">${formattedDailyTotal}</td>`;
    });

    totalRow.innerHTML = totalRowHtml;
    tableBody.appendChild(totalRow);

    // Add horizontal scroll hints if needed
    const tableContainer = document.querySelector(".horizontal-scroll");
    if (
        tableContainer &&
        tableContainer.scrollWidth > tableContainer.clientWidth
    ) {
        // Remove existing hint if any
        const existingHint =
            tableContainer.parentNode.querySelector(".scroll-hint");
        if (existingHint) {
            existingHint.remove();
        }

        const scrollHint = document.createElement("div");
        scrollHint.className = "text-muted small text-right mt-1 scroll-hint";
        scrollHint.innerHTML =
            '<i class="fas fa-arrows-alt-h mr-1"></i>Scroll horizontally to view all days';
        tableContainer.parentNode.appendChild(scrollHint);
    }
}

/**
 * Populate the yearly table with data
 */
function populateYearlyTable(yearlyData) {
    const tableBody = document.getElementById("yearlyProductionTableBody");

    // Exit if the table body doesn't exist
    if (!tableBody) {
        console.warn("yearlyProductionTableBody element not found");
        return;
    }

    tableBody.innerHTML = ""; // Clear loading message

    if (yearlyData.table_data && yearlyData.table_data.length > 0) {
        // Group data by project (actual and plan pairs)
        const projectGroups = {};

        yearlyData.table_data.forEach((project) => {
            const projectName = project.name.replace(" (Plan)", ""); // Remove plan suffix if it exists
            if (!projectGroups[projectName]) {
                projectGroups[projectName] = { actual: null, plan: null };
            }

            if (project.name.includes("(Plan)")) {
                projectGroups[projectName].plan = project;
            } else {
                projectGroups[projectName].actual = project;
            }
        });

        // Add project rows (actual and plan)
        Object.keys(projectGroups).forEach((projectName, index) => {
            const projectData = projectGroups[projectName];

            // Add plan data row first if available (so it appears above the actual data for comparison)
            if (projectData.plan) {
                addProductionRow(tableBody, projectData.plan, index, true);
            }

            // Add actual data row if available (with plan data for comparison)
            if (projectData.actual) {
                addProductionRow(
                    tableBody,
                    projectData.actual,
                    index,
                    false,
                    projectData.plan
                );
            }
        });

        // Add monthly totals row
        addMonthlyTotalsRow(tableBody, yearlyData.table_data);
    } else {
        tableBody.innerHTML =
            '<tr><td colspan="14" class="text-center">No yearly production data available</td></tr>';
    }
}

/**
 * Add a production row to the yearly table
 *
 * @param {HTMLElement} tableBody - The table body element
 * @param {Object} project - The project data
 * @param {Number} index - The index for color selection
 * @param {Boolean} isPlan - Whether this is a plan row
 * @param {Object} planData - Optional plan data for comparison with actual
 */
function addProductionRow(tableBody, project, index, isPlan, planData = null) {
    try {
        // Create row
        const row = document.createElement("tr");

        // Add classes for plan rows
        if (isPlan) {
            row.classList.add("plan-row", "bg-light", "font-italic");
        }

        // Make sure months is an array with 12 values
        const monthsArray = Array.isArray(project.months)
            ? project.months
            : Object.values(project.months || {});

        // Ensure we have 12 months of data
        const monthData = Array(12).fill(0);
        monthsArray.forEach((value, idx) => {
            if (idx < 12) {
                monthData[idx] = Number(value) || 0;
            }
        });

        // Format the project name row
        let rowHtml = `
            <td>
                <span class="badge-dot" style="background-color: ${
                    colorPalette[index % colorPalette.length]
                }${isPlan ? "80" : ""}" ${
            isPlan ? 'style="border: 1px dashed #666"' : ""
        }></span>
                ${project.name}
            </td>
        `;

        // Add monthly data
        monthData.forEach((value, monthIndex) => {
            // Always use 1 decimal place formatting
            const formattedValue =
                value > 0
                    ? value.toLocaleString("en-US", {
                          minimumFractionDigits: 1,
                          maximumFractionDigits: 1,
                      })
                    : "-";

            // If this is actual data (not plan) and we have plan data, check if it's below target
            let isBelowTarget = false;
            if (!isPlan && planData && planData.months) {
                const planValue = Array.isArray(planData.months)
                    ? planData.months[monthIndex] || 0
                    : Object.values(planData.months || {})[monthIndex] || 0;

                isBelowTarget = value > 0 && value < planValue;
            }

            const cellClass = isBelowTarget ? "below-target" : "";
            rowHtml += `<td class="text-right ${cellClass}">${formattedValue}</td>`;
        });

        // Add total - always with 1 decimal place
        const formattedTotal = project.total.toLocaleString("en-US", {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1,
        });

        // Check if total is below plan if this is actual data
        let isTotalBelowTarget = false;
        if (!isPlan && planData) {
            isTotalBelowTarget =
                project.total > 0 && project.total < planData.total;
        }

        const totalCellClass = isTotalBelowTarget ? "below-target" : "";
        rowHtml += `<td class="text-right font-weight-bold ${totalCellClass}">${formattedTotal}</td>`;

        row.innerHTML = rowHtml;
        tableBody.appendChild(row);
    } catch (e) {
        console.error("Error adding production row to table:", e);
    }
}

/**
 * Add monthly totals row to the yearly table
 */
function addMonthlyTotalsRow(tableBody, tableData) {
    try {
        // Create totals row
        const totalRow = document.createElement("tr");
        totalRow.classList.add("font-weight-bold", "bg-light");

        // Start with the "TOTAL" cell
        let totalRowHtml = `<td>TOTAL</td>`;

        // Calculate monthly totals (only for actual production, not plans)
        const monthlyTotals = Array(12).fill(0);
        let grandTotal = 0;

        // Filter only actual production rows (not plan rows)
        const actualProductionData = tableData.filter(
            (project) => !project.name.includes("(Plan)")
        );

        // Calculate totals for each month
        actualProductionData.forEach((project) => {
            const monthsArray = Array.isArray(project.months)
                ? project.months
                : Object.values(project.months || {});

            monthsArray.forEach((value, idx) => {
                if (idx < 12) {
                    const numValue = Number(value) || 0;
                    monthlyTotals[idx] += numValue;
                    grandTotal += numValue;
                }
            });
        });

        // Add monthly totals to the row - always with 1 decimal place
        monthlyTotals.forEach((total) => {
            const formattedTotal =
                total > 0
                    ? total.toLocaleString("en-US", {
                          minimumFractionDigits: 1,
                          maximumFractionDigits: 1,
                      })
                    : "-";
            totalRowHtml += `<td class="text-right">${formattedTotal}</td>`;
        });

        // Add grand total - always with 1 decimal place
        const formattedGrandTotal = grandTotal.toLocaleString("en-US", {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1,
        });
        totalRowHtml += `<td class="text-right">${formattedGrandTotal}</td>`;

        totalRow.innerHTML = totalRowHtml;
        tableBody.appendChild(totalRow);
    } catch (e) {
        console.error("Error adding totals row to table:", e);
    }
}
