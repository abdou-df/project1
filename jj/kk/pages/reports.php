<?php
// Reports page

// Sample data for reports
$monthlyRevenue = [
    'Jan' => 12500,
    'Feb' => 15000,
    'Mar' => 18500,
    'Apr' => 17200,
    'May' => 19800,
    'Jun' => 22000,
    'Jul' => 21500,
    'Aug' => 23000,
    'Sep' => 24500,
    'Oct' => 26000,
    'Nov' => 27500,
    'Dec' => 29000
];

$serviceTypes = [
    'Oil Change' => 35,
    'Brake Service' => 22,
    'Tire Replacement' => 18,
    'Engine Repair' => 12,
    'Transmission' => 8,
    'Other' => 5
];

$customerSatisfaction = [
    'Excellent' => 65,
    'Good' => 25,
    'Average' => 7,
    'Poor' => 3
];

$topMechanics = [
    ['name' => 'Mike Johnson', 'services' => 45, 'revenue' => 15000],
    ['name' => 'David Wilson', 'services' => 38, 'revenue' => 12500],
    ['name' => 'Robert Brown', 'services' => 32, 'revenue' => 10800],
    ['name' => 'James Davis', 'services' => 28, 'revenue' => 9500],
    ['name' => 'William Miller', 'services' => 25, 'revenue' => 8200]
];

$inventoryUsage = [
    'Oil Filters' => 120,
    'Engine Oil' => 350,
    'Brake Pads' => 85,
    'Spark Plugs' => 160,
    'Wiper Blades' => 75
];
?>

<!-- Page header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3">Reports</h2>
    <div>
        <button class="btn btn-outline-primary me-2"><i class="fas fa-print me-2"></i> Print</button>
        <button class="btn btn-outline-success"><i class="fas fa-file-excel me-2"></i> Export</button>
    </div>
</div>

<!-- Report filters -->
<div class="card mb-4">
    <div class="card-body">
        <form>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="reportType" class="form-label">Report Type</label>
                    <select class="form-select" id="reportType">
                        <option value="financial">Financial Reports</option>
                        <option value="service">Service Reports</option>
                        <option value="inventory">Inventory Reports</option>
                        <option value="customer">Customer Reports</option>
                        <option value="employee">Employee Reports</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dateRange" class="form-label">Date Range</label>
                    <select class="form-select" id="dateRange">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This Week</option>
                        <option value="last_week">Last Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="compareWith" class="form-label">Compare With</label>
                    <select class="form-select" id="compareWith">
                        <option value="none">None</option>
                        <option value="previous_period">Previous Period</option>
                        <option value="same_period_last_year">Same Period Last Year</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100">Generate Report</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Dashboard overview -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Revenue</h6>
                        <h4 class="mb-0">$<?php echo number_format(array_sum($monthlyRevenue), 2); ?></h4>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i> 15% from last year</small>
                    </div>
                    <div class="bg-light-primary rounded-circle p-2">
                        <i class="fas fa-dollar-sign text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Services</h6>
                        <h4 class="mb-0"><?php echo array_sum($serviceTypes); ?></h4>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i> 8% from last month</small>
                    </div>
                    <div class="bg-light-success rounded-circle p-2">
                        <i class="fas fa-tools text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Customer Satisfaction</h6>
                        <h4 class="mb-0"><?php echo $customerSatisfaction['Excellent'] + $customerSatisfaction['Good']; ?>%</h4>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i> 5% from last quarter</small>
                    </div>
                    <div class="bg-light-info rounded-circle p-2">
                        <i class="fas fa-smile text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Avg. Service Value</h6>
                        <h4 class="mb-0">$<?php echo number_format(array_sum($monthlyRevenue) / array_sum($serviceTypes), 2); ?></h4>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i> 12% from last year</small>
                    </div>
                    <div class="bg-light-warning rounded-circle p-2">
                        <i class="fas fa-chart-line text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue chart -->
<div class="row mb-4">
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Revenue Trend</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active">Monthly</button>
                    <button type="button" class="btn btn-outline-secondary">Quarterly</button>
                    <button type="button" class="btn btn-outline-secondary">Yearly</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Service Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="serviceChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top mechanics and inventory usage -->
<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Top Performing Mechanics</h5>
                <a href="#" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>MECHANIC</th>
                                <th>SERVICES</th>
                                <th>REVENUE</th>
                                <th>EFFICIENCY</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topMechanics as $mechanic): ?>
                            <tr>
                                <td><?php echo $mechanic['name']; ?></td>
                                <td><?php echo $mechanic['services']; ?></td>
                                <td>$<?php echo number_format($mechanic['revenue'], 2); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1" style="height: 5px;">
                                            <?php $efficiency = min(100, round(($mechanic['revenue'] / 15000) * 100)); ?>
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $efficiency; ?>%;" aria-valuenow="<?php echo $efficiency; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="ms-2"><?php echo $efficiency; ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Top Inventory Usage</h5>
                <a href="#" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body">
                <canvas id="inventoryChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Customer satisfaction and recent reports -->
<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Customer Satisfaction</h5>
            </div>
            <div class="card-body">
                <canvas id="satisfactionChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Reports</h5>
                <a href="#" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Monthly Financial Report - March 2023</h6>
                            <small class="text-muted">3 days ago</small>
                        </div>
                        <p class="mb-1">Summary of all financial transactions for March 2023.</p>
                        <small class="text-muted">Generated by: John Smith</small>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Inventory Status Report - Q1 2023</h6>
                            <small class="text-muted">1 week ago</small>
                        </div>
                        <p class="mb-1">Overview of inventory levels and usage for Q1 2023.</p>
                        <small class="text-muted">Generated by: Emily Brown</small>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Employee Performance Report - February 2023</h6>
                            <small class="text-muted">2 weeks ago</small>
                        </div>
                        <p class="mb-1">Analysis of employee performance metrics for February 2023.</p>
                        <small class="text-muted">Generated by: John Smith</small>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Customer Satisfaction Survey Results - Q1 2023</h6>
                            <small class="text-muted">3 weeks ago</small>
                        </div>
                        <p class="mb-1">Analysis of customer feedback and satisfaction scores for Q1 2023.</p>
                        <small class="text-muted">Generated by: Sarah Williams</small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-light-primary {
    background-color: rgba(13, 110, 253, 0.1);
}
.bg-light-success {
    background-color: rgba(25, 135, 84, 0.1);
}
.bg-light-info {
    background-color: rgba(13, 202, 240, 0.1);
}
.bg-light-warning {
    background-color: rgba(255, 193, 7, 0.1);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    var revenueCtx = document.getElementById('revenueChart').getContext('2d');
    var revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue',
                data: <?php echo json_encode(array_values($monthlyRevenue)); ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Service Distribution Chart
    var serviceCtx = document.getElementById('serviceChart').getContext('2d');
    var serviceChart = new Chart(serviceCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($serviceTypes)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($serviceTypes)); ?>,
                backgroundColor: [
                    'rgba(13, 110, 253, 0.7)',
                    'rgba(25, 135, 84, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(13, 202, 240, 0.7)',
                    'rgba(111, 66, 193, 0.7)',
                    'rgba(220, 53, 69, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
    
    // Inventory Usage Chart
    var inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
    var inventoryChart = new Chart(inventoryCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($inventoryUsage)); ?>,
            datasets: [{
                label: 'Units Used',
                data: <?php echo json_encode(array_values($inventoryUsage)); ?>,
                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                borderColor: 'rgba(25, 135, 84, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Customer Satisfaction Chart
    var satisfactionCtx = document.getElementById('satisfactionChart').getContext('2d');
    var satisfactionChart = new Chart(satisfactionCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_keys($customerSatisfaction)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($customerSatisfaction)); ?>,
                backgroundColor: [
                    'rgba(25, 135, 84, 0.7)',
                    'rgba(13, 202, 240, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(220, 53, 69, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
