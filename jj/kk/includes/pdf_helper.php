<?php
/**
 * PDF Helper
 * Provides helper functions for generating PDF files using FPDF
 */

// Include FPDF library
require_once '../vendor/fpdf/fpdf.php';

/**
 * GaragePDF class extends FPDF to provide custom functionality
 */
class GaragePDF extends FPDF {
    // Header
    public function Header() {
        // Logo
        $this->Image('../assets/images/logo.png', 10, 6, 30);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30, 10, 'Garage Management System', 0, 0, 'C');
        // Line break
        $this->Ln(20);
    }

    // Page footer
    public function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    
    // Colored table
    public function ColoredTable($header, $data, $widths = null) {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        
        // Calculate widths if not provided
        if ($widths === null) {
            $widths = array_fill(0, count($header), $this->GetPageWidth() / count($header) - 10);
        }
        
        // Header
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($widths[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        
        // Data
        $fill = false;
        foreach ($data as $row) {
            for ($i = 0; $i < count($row); $i++) {
                $this->Cell($widths[$i], 6, $row[$i], 'LR', 0, 'L', $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
        
        // Closing line
        $this->Cell(array_sum($widths), 0, '', 'T');
    }
}

/**
 * Generate invoice PDF
 * 
 * @param int $invoice_id Invoice ID
 * @return string PDF content
 */
function generateInvoicePDF($invoice_id) {
    // Create Invoice object
    $invoice_obj = new Invoice();
    
    // Get invoice data
    if (!$invoice_obj->getById($invoice_id)) {
        return false;
    }
    
    // Get invoice items
    $items = $invoice_obj->getItems($invoice_id);
    
    // Get customer data
    $customer = new Customer();
    $customer->getById($invoice_obj->getCustomerId());
    
    // Get vehicle data
    $vehicle = new Vehicle();
    $vehicle->getById($invoice_obj->getVehicleId());
    
    // Create PDF object
    $pdf = new GaragePDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    // Invoice title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'INVOICE', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Invoice details
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Invoice Number:', 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 10, $invoice_obj->getInvoiceNumber(), 0);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Date:', 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 10, date('d/m/Y', strtotime($invoice_obj->getInvoiceDate())), 0);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Due Date:', 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 10, date('d/m/Y', strtotime($invoice_obj->getDueDate())), 0);
    $pdf->Ln(15);
    
    // Customer details
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Customer Details', 0, 1);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 6, 'Name:', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, $customer->getName(), 0);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 6, 'Email:', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, $customer->getEmail(), 0);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 6, 'Phone:', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, $customer->getPhone(), 0);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 6, 'Address:', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, $customer->getAddress() . ', ' . $customer->getCity() . ', ' . $customer->getState() . ' ' . $customer->getZip(), 0);
    $pdf->Ln(10);
    
    // Vehicle details
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Vehicle Details', 0, 1);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 6, 'Make/Model:', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, $vehicle->getMake() . ' ' . $vehicle->getModel(), 0);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 6, 'Year:', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, $vehicle->getYear(), 0);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 6, 'License Plate:', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, $vehicle->getLicensePlate(), 0);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 6, 'VIN:', 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, $vehicle->getVin(), 0);
    $pdf->Ln(15);
    
    // Invoice items
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Invoice Items', 0, 1);
    
    // Table header
    $header = ['Item', 'Description', 'Quantity', 'Unit Price', 'Total'];
    
    // Table data
    $data = [];
    foreach ($items as $item) {
        $data[] = [
            $item['item_type'] == 'service' ? 'Service' : 'Part',
            $item['description'],
            $item['quantity'],
            number_format($item['unit_price'], 2),
            number_format($item['total'], 2)
        ];
    }
    
    // Column widths
    $widths = [30, 70, 25, 30, 30];
    
    // Output table
    $pdf->ColoredTable($header, $data, $widths);
    $pdf->Ln(10);
    
    // Totals
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(130, 6, 'Subtotal:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 6, number_format($invoice_obj->getSubtotal(), 2), 0, 1, 'R');
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(130, 6, 'Tax (' . $invoice_obj->getTaxRate() . '%):', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(30, 6, number_format($invoice_obj->getTaxAmount(), 2), 0, 1, 'R');
    
    if ($invoice_obj->getDiscountAmount() > 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(130, 6, 'Discount:', 0, 0, 'R');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(30, 6, number_format($invoice_obj->getDiscountAmount(), 2), 0, 1, 'R');
    }
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(130, 10, 'Total:', 0, 0, 'R');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 10, number_format($invoice_obj->getTotalAmount(), 2), 0, 1, 'R');
    
    // Payment status
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Payment Status: ' . $invoice_obj->getStatus(), 0, 1);
    
    if ($invoice_obj->getStatus() == INVOICE_STATUS_PAID) {
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Paid on: ' . date('d/m/Y', strtotime($invoice_obj->getPaymentDate())), 0, 1);
        $pdf->Cell(0, 6, 'Payment Method: ' . $invoice_obj->getPaymentMethod(), 0, 1);
    }
    
    // Notes
    if ($invoice_obj->getNotes()) {
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Notes:', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 6, $invoice_obj->getNotes(), 0, 'L');
    }
    
    // Terms and conditions
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Terms and Conditions:', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 6, 'Payment is due within 30 days. Late payments are subject to a 2% monthly fee. All services and parts are guaranteed for 90 days.', 0, 'L');
    
    // Output PDF
    return $pdf->Output('S');
}

/**
 * Generate service report PDF
 * 
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param array $filters Optional filters
 * @return string PDF content
 */
function generateServiceReportPDF($start_date, $end_date, $filters = []) {
    // Create Report object
    $report = new Report();
    
    // Get service report data
    $report_data = $report->generateServiceReport($start_date, $end_date, $filters);
    
    // Create PDF object
    $pdf = new GaragePDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    // Report title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'SERVICE REPORT', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Report period
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Period:', 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 10, date('d/m/Y', strtotime($start_date)) . ' to ' . date('d/m/Y', strtotime($end_date)), 0);
    $pdf->Ln(15);
    
    // Table header
    $header = ['Service', 'Category', 'Price', 'Appointments', 'Revenue'];
    
    // Table data
    $data = [];
    $total_appointments = 0;
    $total_revenue = 0;
    
    foreach ($report_data as $service) {
        $data[] = [
            $service['name'],
            $service['category'],
            number_format($service['price'], 2),
            $service['total_appointments'],
            number_format($service['total_revenue'], 2)
        ];
        
        $total_appointments += $service['total_appointments'];
        $total_revenue += $service['total_revenue'];
    }
    
    // Column widths
    $widths = [60, 40, 30, 30, 30];
    
    // Output table
    $pdf->ColoredTable($header, $data, $widths);
    $pdf->Ln(10);
    
    // Totals
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(130, 10, 'Total Appointments:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 10, $total_appointments, 0, 1, 'R');
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(130, 10, 'Total Revenue:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 10, number_format($total_revenue, 2), 0, 1, 'R');
    
    // Output PDF
    return $pdf->Output('S');
}

/**
 * Generate inventory report PDF
 * 
 * @param array $filters Optional filters
 * @return string PDF content
 */
function generateInventoryReportPDF($filters = []) {
    // Create Report object
    $report = new Report();
    
    // Get inventory report data
    $report_data = $report->generateInventoryReport($filters);
    
    // Create PDF object
    $pdf = new GaragePDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('L'); // Landscape
    
    // Report title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'INVENTORY REPORT', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Report date
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Date:', 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 10, date('d/m/Y'), 0);
    $pdf->Ln(15);
    
    // Table header
    $header = ['Part #', 'Name', 'Category', 'Quantity', 'Reorder Level', 'Cost', 'Selling Price', 'Stock Value', 'Profit Margin'];
    
    // Table data
    $data = [];
    $total_stock_value = 0;
    $total_items = 0;
    
    foreach ($report_data as $part) {
        $data[] = [
            $part['part_number'],
            $part['name'],
            $part['category'],
            $part['quantity'],
            $part['reorder_level'],
            number_format($part['cost_price'], 2),
            number_format($part['selling_price'], 2),
            number_format($part['stock_value'], 2),
            number_format($part['profit_margin'], 2) . '%'
        ];
        
        $total_stock_value += $part['stock_value'];
        $total_items += $part['quantity'];
    }
    
    // Column widths
    $widths = [30, 50, 30, 20, 30, 25, 25, 30, 30];
    
    // Output table
    $pdf->ColoredTable($header, $data, $widths);
    $pdf->Ln(10);
    
    // Totals
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(200, 10, 'Total Items:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 10, $total_items, 0, 1, 'R');
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(200, 10, 'Total Stock Value:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 10, number_format($total_stock_value, 2), 0, 1, 'R');
    
    // Output PDF
    return $pdf->Output('S');
}

/**
 * Generate customer report PDF
 * 
 * @param string $start_date Start date (YYYY-MM-DD)
 * @param string $end_date End date (YYYY-MM-DD)
 * @param array $filters Optional filters
 * @return string PDF content
 */
function generateCustomerReportPDF($start_date, $end_date, $filters = []) {
    // Create Report object
    $report = new Report();
    
    // Get customer report data
    $report_data = $report->generateCustomerReport($start_date, $end_date, $filters);
    
    // Create PDF object
    $pdf = new GaragePDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('L'); // Landscape
    
    // Report title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'CUSTOMER REPORT', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Report period
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Period:', 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 10, date('d/m/Y', strtotime($start_date)) . ' to ' . date('d/m/Y', strtotime($end_date)), 0);
    $pdf->Ln(15);
    
    // Table header
    $header = ['Name', 'Email', 'Phone', 'City', 'Vehicles', 'Appointments', 'Invoices', 'Total Spent', 'Last Visit'];
    
    // Table data
    $data = [];
    $total_spent = 0;
    
    foreach ($report_data as $customer) {
        $data[] = [
            $customer['name'],
            $customer['email'],
            $customer['phone'],
            $customer['city'] . ', ' . $customer['state'],
            $customer['total_vehicles'],
            $customer['total_appointments'],
            $customer['total_invoices'],
            number_format($customer['total_spent'], 2),
            $customer['last_invoice_date'] ? date('d/m/Y', strtotime($customer['last_invoice_date'])) : 'N/A'
        ];
        
        $total_spent += $customer['total_spent'];
    }
    
    // Column widths
    $widths = [40, 50, 30, 30, 20, 25, 20, 30, 30];
    
    // Output table
    $pdf->ColoredTable($header, $data, $widths);
    $pdf->Ln(10);
    
    // Totals
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(200, 10, 'Total Customers:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 10, count($report_data), 0, 1, 'R');
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(200, 10, 'Total Revenue:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 10, number_format($total_spent, 2), 0, 1, 'R');
    
    // Output PDF
    return $pdf->Output('S');
}
?>
