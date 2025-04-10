<?php
/**
 * Services API
 * Handles API requests for services
 */

// Check if accessed directly
if (!defined('API_KEY')) {
    header('HTTP/1.0 403 Forbidden');
    exit('No direct script access allowed');
}

// Create Service object
$service = new Service();

// Handle request based on method
switch ($method) {
    case 'GET':
        if ($resource_id) {
            // Get service by ID
            if ($service->getById($resource_id)) {
                $service_data = [
                    'id' => $service->getId(),
                    'name' => $service->getName(),
                    'description' => $service->getDescription(),
                    'category' => $service->getCategory(),
                    'price' => $service->getPrice(),
                    'duration' => $service->getDuration(),
                    'status' => $service->getStatus(),
                    'created_at' => $service->getCreatedAt()
                ];
                
                // If action is specified
                if ($action === 'popularity') {
                    // Get service popularity
                    $service_data['popularity'] = $service->getPopularity($resource_id);
                }
                
                sendResponse(200, $service_data);
            } else {
                sendResponse(404, ['message' => 'Service not found']);
            }
        } else {
            // Get all services with optional filtering
            $filters = [];
            
            // Apply filters from query parameters
            if (isset($query_params['name'])) {
                $filters['name'] = $query_params['name'];
            }
            
            if (isset($query_params['category'])) {
                $filters['category'] = $query_params['category'];
            }
            
            if (isset($query_params['status'])) {
                $filters['status'] = $query_params['status'];
            }
            
            if (isset($query_params['min_price'])) {
                $filters['min_price'] = $query_params['min_price'];
            }
            
            if (isset($query_params['max_price'])) {
                $filters['max_price'] = $query_params['max_price'];
            }
            
            // Pagination
            $page = isset($query_params['page']) ? (int)$query_params['page'] : 1;
            $limit = isset($query_params['limit']) ? (int)$query_params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Get services
            $services = $service->getAll($filters, $limit, $offset);
            
            // Get total count for pagination
            $total_count = $service->count($filters);
            
            // Special actions
            if (isset($query_params['action'])) {
                switch ($query_params['action']) {
                    case 'categories':
                        // Get service categories
                        $categories = $service->getCategories();
                        sendResponse(200, ['categories' => $categories]);
                        return;
                        
                    case 'popular':
                        // Get popular services
                        $popular_services = $service->getPopularServices();
                        sendResponse(200, ['popular_services' => $popular_services]);
                        return;
                }
            }
            
            sendResponse(200, [
                'services' => $services,
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
        $required_fields = ['name', 'category', 'price'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                sendResponse(400, ['message' => "Field '{$field}' is required"]);
                return;
            }
        }
        
        // Create service
        if ($service->create($data)) {
            sendResponse(201, [
                'message' => 'Service created successfully',
                'id' => $service->getId()
            ]);
        } else {
            sendResponse(500, ['message' => 'Failed to create service']);
        }
        break;
        
    case 'PUT':
        // Check if service exists
        if (!$resource_id || !$service->getById($resource_id)) {
            sendResponse(404, ['message' => 'Service not found']);
            return;
        }
        
        // Update service
        if ($service->update($resource_id, $data)) {
            sendResponse(200, ['message' => 'Service updated successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to update service']);
        }
        break;
        
    case 'DELETE':
        // Check if service exists
        if (!$resource_id || !$service->getById($resource_id)) {
            sendResponse(404, ['message' => 'Service not found']);
            return;
        }
        
        // Delete service
        if ($service->delete($resource_id)) {
            sendResponse(200, ['message' => 'Service deleted successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to delete service']);
        }
        break;
        
    default:
        sendResponse(405, ['message' => 'Method not allowed']);
        break;
}
?>
