<?php
/**
 * Invoices API
 * Handles API requests for invoices
 */

// Check if accessed directly
if (!defined('API_KEY')) {
    header('HTTP/1.0 403 Forbidden');
    exit('No direct script access allowed');
}

// Create Invoice object
$invoice = new Invoice();

// Handle request based on method
switch ($method) {
    case 'GET':
        if ($resource_id) {
            // Get invoice by ID
            if ($invoice->getById($resource_id)) {
                $invoice_data = [
                    'id' => $invoice->getId(),
                    'invoice_number' => $invoice->getInvoiceNumber(),
                    'customer_id' => $invoice->getCustomerId(),
                    'vehicle_id' => $invoice->getVehicleId(),
                    'user_id' => $invoice->getUserId(),
                    'invoice_date' => $invoice->getInvoiceDate(),
                    'due_date' => $invoice->getDueDate(),
                    'subtotal' => $invoice->getSubtotal(),
                    'tax_rate' => $invoice->getTaxRate(),
                    'tax_amount' => $invoice->getTaxAmount(),
                    'discount_amount' => $invoice->getDiscountAmount(),
                    'total_amount' => $invoice->getTotalAmount(),
                    'payment_method' => $invoice->getPaymentMethod(),
                    'payment_date' => $invoice->getPaymentDate(),
                    'notes' => $invoice->getNotes(),
                    'status' => $invoice->getStatus(),
                    'created_at' => $invoice->getCreatedAt()
                ];
                
                // If action is specified
                if ($action === 'items') {
                    // Get invoice items
                    $invoice_data['items'] = $invoice->getItems($resource_id);
                }
                
                sendResponse(200, $invoice_data);
            } else {
                sendResponse(404, ['message' => 'Invoice not found']);
            }
        } else {
            // Get all invoices with optional filtering
            $filters = [];
            
            // Apply filters from query parameters
            if (isset($query_params['customer_id'])) {
                $filters['customer_id'] = $query_params['customer_id'];
            }
            
            if (isset($query_params['vehicle_id'])) {
                $filters['vehicle_id'] = $query_params['vehicle_id'];
            }
            
            if (isset($query_params['user_id'])) {
                $filters['user_id'] = $query_params['user_id'];
            }
            
            if (isset($query_params['status'])) {
                $filters['status'] = $query_params['status'];
            }
            
            if (isset($query_params['payment_method'])) {
                $filters['payment_method'] = $query_params['payment_method'];
            }
            
            if (isset($query_params['start_date'])) {
                $filters['start_date'] = $query_params['start_date'];
            }
            
            if (isset($query_params['end_date'])) {
                $filters['end_date'] = $query_params['end_date'];
            }
            
            if (isset($query_params['min_amount'])) {
                $filters['min_amount'] = $query_params['min_amount'];
            }
            
            if (isset($query_params['max_amount'])) {
                $filters['max_amount'] = $query_params['max_amount'];
            }
            
            // Pagination
            $page = isset($query_params['page']) ? (int)$query_params['page'] : 1;
            $limit = isset($query_params['limit']) ? (int)$query_params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Get invoices
            $invoices = $invoice->getAll($filters, $limit, $offset);
            
            // Get total count for pagination
            $total_count = $invoice->count($filters);
            
            // Special actions
            if (isset($query_params['action'])) {
                switch ($query_params['action']) {
                    case 'stats':
                        // Get invoice statistics
                        $stats = $invoice->getStatistics();
                        sendResponse(200, ['statistics' => $stats]);
                        return;
                        
                    case 'overdue':
                        // Get overdue invoices
                        $overdue_invoices = $invoice->getOverdueInvoices();
                        sendResponse(200, ['overdue_invoices' => $overdue_invoices]);
                        return;
                }
            }
            
            sendResponse(200, [
                'invoices' => $invoices,
                'pagination' => [
                    'total' => $total_count,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total_count / $limit)
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Validate required fields
        $required_fields = ['customer_id', 'vehicle_id', 'user_id'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                sendResponse(400, ['message' => "Field '{$field}' is required"]);
                return;
            }
        }
        
        // Check if invoice items are provided
        if (!isset($data['items']) || empty($data['items'])) {
            sendResponse(400, ['message' => 'Invoice items are required']);
            return;
        }
        
        // Begin transaction
        $db = Database::getInstance();
        $db->beginTransaction();
        
        try {
            // Create invoice
            if (!$invoice->create($data)) {
                throw new Exception('Failed to create invoice');
            }
            
            $invoice_id = $invoice->getId();
            
            // Add invoice items
            foreach ($data['items'] as $item) {
                if (!$invoice->addItem($invoice_id, $item)) {
                    throw new Exception('Failed to add invoice item');
                }
            }
            
            // Create notification for invoice creation
            $notification = new Notification();
            $notification->createInvoiceNotification($invoice_id, 'created');
            
            // Commit transaction
            $db->commit();
            
            sendResponse(201, [
                'message' => 'Invoice created successfully',
                'id' => $invoice_id,
                'invoice_number' => $invoice->getInvoiceNumber()
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollback();
            sendResponse(500, ['message' => $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // Check if invoice exists
        if (!$resource_id || !$invoice->getById($resource_id)) {
            sendResponse(404, ['message' => 'Invoice not found']);
            return;
        }
        
        // Special actions
        if ($action === 'mark-paid') {
            // Mark invoice as paid
            if ($invoice->markAsPaid($resource_id, $data)) {
                // Create notification for invoice payment
                $notification = new Notification();
                $notification->createInvoiceNotification($resource_id, 'paid');
                
                sendResponse(200, ['message' => 'Invoice marked as paid']);
            } else {
                sendResponse(500, ['message' => 'Failed to mark invoice as paid']);
            }
            return;
        }
        
        // Update invoice
        if ($invoice->update($resource_id, $data)) {
            sendResponse(200, ['message' => 'Invoice updated successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to update invoice']);
        }
        break;
        
    case 'DELETE':
        // Check if invoice exists
        if (!$resource_id || !$invoice->getById($resource_id)) {
            sendResponse(404, ['message' => 'Invoice not found']);
            return;
        }
        
        // Delete invoice
        if ($invoice->delete($resource_id)) {
            sendResponse(200, ['message' => 'Invoice deleted successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to delete invoice']);
        }
        break;
        
    default:
        sendResponse(405, ['message' => 'Method not allowed']);
        break;
}
?>
