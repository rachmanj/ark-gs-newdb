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

    document.getElementById("productionMonth").value = currentMonth;
    document.getElementById("productionYear").value = currentYear;

    // Load initial data
    loadProductionData(currentMonth, currentYear);

    // Add event listener for update button
    document
        .getElementById("updateProductionChart")
        .addEventListener("click", function () {
            const selectedMonth =
                document.getElementById("productionMonth").value;
            const selectedYear =
                document.getElementById("productionYear").value;
            loadProductionData(selectedMonth, selectedYear);
        });
});

/**
 * Load production data for the selected month and year
 */
function loadProductionData(month, year) {
    // Update year displays
    document.getElementById("yearlyChartYearDisplay").textContent = year;
    document.getElementById("yearlyTabYearDisplay").textContent = year;

    // Show loading state for charts
    showLoading("dailyChartContainer");
    showLoading("yearlyChartContainer");

    // Build URL with parameters and add timestamp to prevent caching
    const timestamp = new Date().getTime();
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
                    data.current_month.chart_data.length > 0
                ) {
                    createDailyChart(data.current_month, month, year);
                    populateCurrentMonthTable(data.current_month);
                } else {
                    showNoData(
                        "dailyChartContainer",
                        "No production data available for this month"
                    );
                    showNoDataTable("dailyProductionTableBody");
                }

                // Process the yearly chart data
                if (
                    data.yearly &&
                    data.yearly.chart_data &&
                    data.yearly.chart_data.length > 0
                ) {
                    createYearlyChart(data.yearly);
                    populateYearlyTable(data.yearly);
                } else {
                    showNoData(
                        "yearlyChartContainer",
                        "No yearly production data available"
                    );
                    showNoDataTable("yearlyProductionTableBody");
                }
            } catch (error) {
                console.error("Error processing production data:", error);
                showError("dailyChartContainer", "Error processing chart data");
                showError(
                    "yearlyChartContainer",
                    "Error processing chart data"
                );
            }
        })
        .catch((error) => {
            console.error("Error fetching production data:", error);
            showError("dailyChartContainer", "Error loading chart data");
            showError("yearlyChartContainer", "Error loading chart data");
            showErrorTable("dailyProductionTableBody");
            showErrorTable("yearlyProductionTableBody");
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
        tableBody.innerHTML = `
            <tr>
                <td colspan="33" class="text-center">
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
        tableBody.innerHTML = `
            <tr>
                <td colspan="33" class="text-center">
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

            // Add dataset
            datasets.push({
                label: series.name,
                data: seriesData,
                borderColor: colorPalette[index % colorPalette.length],
                backgroundColor: "transparent",
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
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

            // Add dataset
            datasets.push({
                label: series.name,
                data: numericData,
                backgroundColor: colorPalette[
                    index % colorPalette.length
                ].replace("1)", "0.7)"),
                borderColor: colorPalette[index % colorPalette.length],
                borderWidth: 1,
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
                        text: `${yearlyData.year} Monthly Production`,
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

        // Project name and summary columns with proper sticky classes
        let rowHtml = `
            <td class="sticky-col first-col">
                <span class="badge-dot" style="background-color: ${
                    colorPalette[index % colorPalette.length]
                }"></span>
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
    const grandTotal = monthData.table_data.reduce(
        (sum, project) => sum + project.total,
        0
    );
    let totalRowHtml = `
        <td class="sticky-col first-col">TOTAL</td>
        <td class="text-right sticky-col second-col">${grandTotal.toLocaleString(
            "en-US",
            { maximumFractionDigits: 1, minimumFractionDigits: 1 }
        )}</td>
    `;

    // Add total for each day
    sortedDates.forEach((date) => {
        const dailyTotal = monthData.table_data.reduce(
            (sum, project) => sum + (project.days[date] || 0),
            0
        );
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
    tableBody.innerHTML = ""; // Clear loading message

    if (yearlyData.table_data && yearlyData.table_data.length > 0) {
        yearlyData.table_data.forEach((project, index) => {
            try {
                // Make sure months is an array
                const monthsArray = Array.isArray(project.months)
                    ? project.months
                    : Object.values(project.months || {});

                // Calculate monthly average - only count months with data
                const monthsWithData =
                    monthsArray.filter((val) => val > 0).length || 1; // Avoid division by zero
                const monthlyAverage = (project.total / monthsWithData).toFixed(
                    2
                );

                // Create row
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>
                        <span class="badge-dot" style="background-color: ${
                            colorPalette[index % colorPalette.length]
                        }"></span>
                        ${project.name}
                    </td>
                    <td class="text-right">${project.total.toLocaleString()}</td>
                    <td class="text-right">${monthlyAverage.toLocaleString()}</td>
                `;
                tableBody.appendChild(row);
            } catch (e) {
                console.error(
                    "Error processing yearly table data for project:",
                    e
                );
            }
        });
    } else {
        tableBody.innerHTML =
            '<tr><td colspan="3" class="text-center">No yearly production data available</td></tr>';
    }
}
