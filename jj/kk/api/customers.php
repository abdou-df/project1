<?php
/**
 * Customers API
 * Handles API requests for customers
 */

// Check if accessed directly
if (!defined('API_KEY')) {
    header('HTTP/1.0 403 Forbidden');
    exit('No direct script access allowed');
}

// Create Customer object
$customer = new Customer();

// Handle request based on method
switch ($method) {
    case 'GET':
        if ($resource_id) {
            // Get customer by ID
            if ($customer->getById($resource_id)) {
                $customer_data = [
                    'id' => $customer->getId(),
                    'name' => $customer->getName(),
                    'email' => $customer->getEmail(),
                    'phone' => $customer->getPhone(),
                    'address' => $customer->getAddress(),
                    'city' => $customer->getCity(),
                    'state' => $customer->getState(),
                    'zip' => $customer->getZip(),
                    'status' => $customer->getStatus(),
                    'created_at' => $customer->getCreatedAt()
                ];
                
                // If action is specified
                if ($action) {
                    switch ($action) {
                        case 'vehicles':
                            // Get customer vehicles
                            $customer_data['vehicles'] = $customer->getVehicles($resource_id);
                            break;
                            
                        case 'appointments':
                            // Get customer appointments
                            $customer_data['appointments'] = $customer->getAppointments($resource_id);
                            break;
                            
                        case 'invoices':
                            // Get customer invoices
                            $customer_data['invoices'] = $customer->getInvoices($resource_id);
                            break;
                            
                        case 'spending':
                            // Get customer total spending
                            $customer_data['total_spending'] = $customer->getTotalSpending($resource_id);
                            break;
                            
                        default:
                            sendResponse(400, ['message' => 'Invalid action']);
                            return;
                    }
                }
                
                sendResponse(200, $customer_data);
            } else {
                sendResponse(404, ['message' => 'Customer not found']);
            }
        } else {
            // Get all customers with optional filtering
            $filters = [];
            
            // Apply filters from query parameters
            if (isset($query_params['name'])) {
                $filters['name'] = $query_params['name'];
            }
            
            if (isset($query_params['email'])) {
                $filters['email'] = $query_params['email'];
            }
            
            if (isset($query_params['phone'])) {
                $filters['phone'] = $query_params['phone'];
            }
            
            if (isset($query_params['city'])) {
                $filters['city'] = $query_params['city'];
            }
            
            if (isset($query_params['state'])) {
                $filters['state'] = $query_params['state'];
            }
            
            if (isset($query_params['status'])) {
                $filters['status'] = $query_params['status'];
            }
            
            // Pagination
            $page = isset($query_params['page']) ? (int)$query_params['page'] : 1;
            $limit = isset($query_params['limit']) ? (int)$query_params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Get customers
            $customers = $customer->getAll($filters, $limit, $offset);
            
            // Get total count for pagination
            $total_count = $customer->count($filters);
            
            sendResponse(200, [
                'customers' => $customers,
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
        $required_fields = ['name', 'email', 'phone'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                sendResponse(400, ['message' => "Field '{$field}' is required"]);
                return;
            }
        }
        
        // Create customer
        if ($customer->create($data)) {
            sendResponse(201, [
                'message' => 'Customer created successfully',
                'id' => $customer->getId()
            ]);
        } else {
            sendResponse(500, ['message' => 'Failed to create customer']);
        }
        break;
        
    case 'PUT':
        // Check if customer exists
        if (!$resource_id || !$customer->getById($resource_id)) {
            sendResponse(404, ['message' => 'Customer not found']);
            return;
        }
        
        // Update customer
        if ($customer->update($resource_id, $data)) {
            sendResponse(200, ['message' => 'Customer updated successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to update customer']);
        }
        break;
        
    case 'DELETE':
        // Check if customer exists
        if (!$resource_id || !$customer->getById($resource_id)) {
            sendResponse(404, ['message' => 'Customer not found']);
            return;
        }
        
        // Delete customer
        if ($customer->delete($resource_id)) {
            sendResponse(200, ['message' => 'Customer deleted successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to delete customer']);
        }
        break;
        
    default:
        sendResponse(405, ['message' => 'Method not allowed']);
        break;
}
?>
