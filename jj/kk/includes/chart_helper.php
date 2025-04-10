<?php
/**
 * Chart Helper
 * Provides helper functions for generating charts using Chart.js
 */

/**
 * Generate chart configuration
 * 
 * @param string $type Chart type (line, bar, pie, doughnut, radar, polarArea, bubble, scatter)
 * @param array $data Chart data
 * @param array $options Chart options
 * @return array Chart configuration
 */
function generateChartConfig($type, $data, $options = []) {
    // Validate chart type
    $valid_types = ['line', 'bar', 'pie', 'doughnut', 'radar', 'polarArea', 'bubble', 'scatter'];
    if (!in_array($type, $valid_types)) {
        $type = 'bar'; // Default to bar chart
    }
    
    // Default options
    $default_options = [
        'responsive' => true,
        'maintainAspectRatio' => true,
        'plugins' => [
            'legend' => [
                'position' => 'top',
            ],
            'title' => [
                'display' => true,
                'text' => 'Chart'
            ]
        ]
    ];
    
    // Merge options
    $options = array_merge($default_options, $options);
    
    // Return chart configuration
    return [
        'type' => $type,
        'data' => $data,
        'options' => $options
    ];
}

/**
 * Generate sales chart data
 * 
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param string $group_by Group by (day, week, month, year)
 * @return array Chart data
 */
function generateSalesChartData($start_date, $end_date, $group_by = 'month') {
    // Create Report object
    $report = new Report();
    
    // Get sales report data
    $report_data = $report->generateSalesReport($start_date, $end_date, ['group_by' => $group_by]);
    
    // Prepare labels and datasets
    $labels = [];
    $sales_data = [];
    $services_data = [];
    $parts_data = [];
    
    foreach ($report_data as $item) {
        // Format label based on group_by
        switch ($group_by) {
            case 'day':
                $labels[] = date('d M', strtotime($item['date']));
                break;
            case 'week':
                $labels[] = 'Week ' . date('W', strtotime($item['date']));
                break;
            case 'month':
                $labels[] = date('M Y', strtotime($item['date']));
                break;
            case 'year':
                $labels[] = date('Y', strtotime($item['date']));
                break;
            default:
                $labels[] = date('M Y', strtotime($item['date']));
        }
        
        // Add data points
        $sales_data[] = $item['total_sales'];
        $services_data[] = $item['service_sales'];
        $parts_data[] = $item['parts_sales'];
    }
    
    // Chart data
    $data = [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Total Sales',
                'data' => $sales_data,
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Service Sales',
                'data' => $services_data,
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Parts Sales',
                'data' => $parts_data,
                'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                'borderColor' => 'rgba(75, 192, 192, 1)',
                'borderWidth' => 1
            ]
        ]
    ];
    
    return $data;
}

/**
 * Generate service popularity chart data
 * 
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param int $limit Limit number of services (default 10)
 * @return array Chart data
 */
function generateServicePopularityChartData($start_date, $end_date, $limit = 10) {
    // Create Service object
    $service = new Service();
    
    // Get popular services
    $popular_services = $service->getPopularServices($start_date, $end_date, $limit);
    
    // Prepare labels and datasets
    $labels = [];
    $appointments_data = [];
    $revenue_data = [];
    
    foreach ($popular_services as $service) {
        $labels[] = $service['name'];
        $appointments_data[] = $service['total_appointments'];
        $revenue_data[] = $service['total_revenue'];
    }
    
    // Chart data
    $data = [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Appointments',
                'data' => $appointments_data,
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Revenue',
                'data' => $revenue_data,
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1,
                'yAxisID' => 'y1'
            ]
        ]
    ];
    
    return $data;
}

/**
 * Generate inventory value chart data
 * 
 * @param array $filters Optional filters
 * @return array Chart data
 */
function generateInventoryValueChartData($filters = []) {
    // Create Inventory object
    $inventory = new Inventory();
    
    // Get inventory by category
    $inventory_data = $inventory->getInventoryByCategory();
    
    // Prepare labels and datasets
    $labels = [];
    $quantity_data = [];
    $value_data = [];
    
    foreach ($inventory_data as $category) {
        $labels[] = $category['category'];
        $quantity_data[] = $category['total_quantity'];
        $value_data[] = $category['total_value'];
    }
    
    // Chart data
    $data = [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Quantity',
                'data' => $quantity_data,
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Value',
                'data' => $value_data,
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1,
                'yAxisID' => 'y1'
            ]
        ]
    ];
    
    return $data;
}

/**
 * Generate invoice status chart data
 * 
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @return array Chart data
 */
function generateInvoiceStatusChartData($start_date, $end_date) {
    // Create Invoice object
    $invoice = new Invoice();
    
    // Get invoice statistics
    $invoice_stats = $invoice->getInvoiceStatistics($start_date, $end_date);
    
    // Prepare data
    $labels = ['Paid', 'Unpaid', 'Overdue', 'Cancelled'];
    $data = [
        $invoice_stats['paid_count'],
        $invoice_stats['unpaid_count'],
        $invoice_stats['overdue_count'],
        $invoice_stats['cancelled_count']
    ];
    
    // Colors
    $background_colors = [
        'rgba(75, 192, 192, 0.2)',  // Green for Paid
        'rgba(54, 162, 235, 0.2)',  // Blue for Unpaid
        'rgba(255, 99, 132, 0.2)',  // Red for Overdue
        'rgba(201, 203, 207, 0.2)'  // Grey for Cancelled
    ];
    
    $border_colors = [
        'rgba(75, 192, 192, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 99, 132, 1)',
        'rgba(201, 203, 207, 1)'
    ];
    
    // Chart data
    $chart_data = [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Invoice Status',
                'data' => $data,
                'backgroundColor' => $background_colors,
                'borderColor' => $border_colors,
                'borderWidth' => 1
            ]
        ]
    ];
    
    return $chart_data;
}

/**
 * Generate customer activity chart data
 * 
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param string $group_by Group by (day, week, month, year)
 * @return array Chart data
 */
function generateCustomerActivityChartData($start_date, $end_date, $group_by = 'month') {
    // Create Customer object
    $customer = new Customer();
    
    // Get customer activity data
    $activity_data = $customer->getActivityData($start_date, $end_date, $group_by);
    
    // Prepare labels and datasets
    $labels = [];
    $new_customers_data = [];
    $appointments_data = [];
    $invoices_data = [];
    
    foreach ($activity_data as $item) {
        // Format label based on group_by
        switch ($group_by) {
            case 'day':
                $labels[] = date('d M', strtotime($item['date']));
                break;
            case 'week':
                $labels[] = 'Week ' . date('W', strtotime($item['date']));
                break;
            case 'month':
                $labels[] = date('M Y', strtotime($item['date']));
                break;
            case 'year':
                $labels[] = date('Y', strtotime($item['date']));
                break;
            default:
                $labels[] = date('M Y', strtotime($item['date']));
        }
        
        // Add data points
        $new_customers_data[] = $item['new_customers'];
        $appointments_data[] = $item['appointments'];
        $invoices_data[] = $item['invoices'];
    }
    
    // Chart data
    $data = [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'New Customers',
                'data' => $new_customers_data,
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Appointments',
                'data' => $appointments_data,
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Invoices',
                'data' => $invoices_data,
                'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                'borderColor' => 'rgba(75, 192, 192, 1)',
                'borderWidth' => 1
            ]
        ]
    ];
    
    return $data;
}

/**
 * Generate technician performance chart data
 * 
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param int $limit Limit number of technicians (default 10)
 * @return array Chart data
 */
function generateTechnicianPerformanceChartData($start_date, $end_date, $limit = 10) {
    // Create Report object
    $report = new Report();
    
    // Get technician performance data
    $performance_data = $report->generateTechnicianReport($start_date, $end_date, ['limit' => $limit]);
    
    // Prepare labels and datasets
    $labels = [];
    $completed_data = [];
    $revenue_data = [];
    $rating_data = [];
    
    foreach ($performance_data as $tech) {
        $labels[] = $tech['name'];
        $completed_data[] = $tech['completed_services'];
        $revenue_data[] = $tech['total_revenue'];
        $rating_data[] = $tech['average_rating'];
    }
    
    // Chart data
    $data = [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Completed Services',
                'data' => $completed_data,
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 1,
                'yAxisID' => 'y'
            ],
            [
                'label' => 'Revenue',
                'data' => $revenue_data,
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1,
                'yAxisID' => 'y1'
            ],
            [
                'label' => 'Average Rating',
                'data' => $rating_data,
                'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                'borderColor' => 'rgba(75, 192, 192, 1)',
                'borderWidth' => 1,
                'yAxisID' => 'y2'
            ]
        ]
    ];
    
    return $data;
}

/**
 * Render chart HTML
 * 
 * @param string $canvas_id Canvas ID
 * @param array $chart_config Chart configuration
 * @param array $attributes Additional canvas attributes
 * @return string Chart HTML
 */
function renderChartHTML($canvas_id, $chart_config, $attributes = []) {
    // Default attributes
    $default_attributes = [
        'width' => 400,
        'height' => 200,
        'class' => 'chart'
    ];
    
    // Merge attributes
    $attributes = array_merge($default_attributes, $attributes);
    
    // Build attributes string
    $attr_str = '';
    foreach ($attributes as $key => $value) {
        $attr_str .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    // Convert chart config to JSON
    $chart_config_json = json_encode($chart_config);
    
    // Generate HTML
    $html = '
    <div class="chart-container">
        <canvas id="' . $canvas_id . '"' . $attr_str . '></canvas>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("' . $canvas_id . '").getContext("2d");
        var chart = new Chart(ctx, ' . $chart_config_json . ');
    });
    </script>
    ';
    
    return $html;
}

/**
 * Include Chart.js library
 * 
 * @return string HTML script tag
 */
function includeChartJS() {
    return '<script src="../vendor/chartjs/Chart.min.js"></script>';
}

/**
 * Generate dashboard charts
 * 
 * @return array Array of chart HTML
 */
function generateDashboardCharts() {
    // Date ranges
    $today = date('Y-m-d');
    $start_of_month = date('Y-m-01');
    $end_of_month = date('Y-m-t');
    $start_of_year = date('Y-01-01');
    $end_of_year = date('Y-12-31');
    
    // Sales chart
    $sales_data = generateSalesChartData($start_of_year, $end_of_year, 'month');
    $sales_config = generateChartConfig('bar', $sales_data, [
        'plugins' => [
            'title' => [
                'display' => true,
                'text' => 'Monthly Sales'
            ]
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'title' => [
                    'display' => true,
                    'text' => 'Amount ($)'
                ]
            ]
        ]
    ]);
    $sales_chart = renderChartHTML('salesChart', $sales_config, [
        'width' => 600,
        'height' => 300
    ]);
    
    // Service popularity chart
    $service_data = generateServicePopularityChartData($start_of_year, $end_of_year, 5);
    $service_config = generateChartConfig('bar', $service_data, [
        'plugins' => [
            'title' => [
                'display' => true,
                'text' => 'Most Popular Services'
            ]
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'position' => 'left',
                'title' => [
                    'display' => true,
                    'text' => 'Appointments'
                ]
            ],
            'y1' => [
                'beginAtZero' => true,
                'position' => 'right',
                'grid' => {
                    'drawOnChartArea' => false
                },
                'title' => [
                    'display' => true,
                    'text' => 'Revenue ($)'
                ]
            }
        ]
    ]);
    $service_chart = renderChartHTML('serviceChart', $service_config, [
        'width' => 600,
        'height' => 300
    ]);
    
    // Invoice status chart
    $invoice_data = generateInvoiceStatusChartData($start_of_year, $end_of_year);
    $invoice_config = generateChartConfig('doughnut', $invoice_data, [
        'plugins' => [
            'title' => [
                'display' => true,
                'text' => 'Invoice Status'
            }
        ]
    ]);
    $invoice_chart = renderChartHTML('invoiceChart', $invoice_config, [
        'width' => 300,
        'height' => 300
    ]);
    
    // Inventory value chart
    $inventory_data = generateInventoryValueChartData();
    $inventory_config = generateChartConfig('bar', $inventory_data, [
        'plugins' => [
            'title' => [
                'display' => true,
                'text' => 'Inventory by Category'
            }
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'position' => 'left',
                'title' => [
                    'display' => true,
                    'text' => 'Quantity'
                ]
            ],
            'y1' => [
                'beginAtZero' => true,
                'position' => 'right',
                'grid' => {
                    'drawOnChartArea' => false
                },
                'title' => [
                    'display' => true,
                    'text' => 'Value ($)'
                ]
            }
        ]
    ]);
    $inventory_chart = renderChartHTML('inventoryChart', $inventory_config, [
        'width' => 600,
        'height' => 300
    ]);
    
    // Return all charts
    return [
        'sales_chart' => $sales_chart,
        'service_chart' => $service_chart,
        'invoice_chart' => $invoice_chart,
        'inventory_chart' => $inventory_chart
    ];
}
?>
