<?php
/**
 * Vehicles API
 * Handles API requests for vehicles
 */

// Check if accessed directly
if (!defined('API_KEY')) {
    header('HTTP/1.0 403 Forbidden');
    exit('No direct script access allowed');
}

// Create Vehicle object
$vehicle = new Vehicle();

// Handle request based on method
switch ($method) {
    case 'GET':
        if ($resource_id) {
            // Get vehicle by ID
            if ($vehicle->getById($resource_id)) {
                $vehicle_data = [
                    'id' => $vehicle->getId(),
                    'customer_id' => $vehicle->getCustomerId(),
                    'make' => $vehicle->getMake(),
                    'model' => $vehicle->getModel(),
                    'year' => $vehicle->getYear(),
                    'license_plate' => $vehicle->getLicensePlate(),
                    'vin' => $vehicle->getVin(),
                    'color' => $vehicle->getColor(),
                    'mileage' => $vehicle->getMileage(),
                    'status' => $vehicle->getStatus(),
                    'created_at' => $vehicle->getCreatedAt()
                ];
                
                // If action is specified
                if ($action === 'service-history') {
                    // Get vehicle service history
                    $report = new Report();
                    $service_history = $report->generateVehicleServiceReport($resource_id);
                    $vehicle_data['service_history'] = $service_history;
                }
                
                sendResponse(200, $vehicle_data);
            } else {
                sendResponse(404, ['message' => 'Vehicle not found']);
            }
        } else {
            // Get all vehicles with optional filtering
            $filters = [];
            
            // Apply filters from query parameters
            if (isset($query_params['customer_id'])) {
                $filters['customer_id'] = $query_params['customer_id'];
            }
            
            if (isset($query_params['make'])) {
                $filters['make'] = $query_params['make'];
            }
            
            if (isset($query_params['model'])) {
                $filters['model'] = $query_params['model'];
            }
            
            if (isset($query_params['year'])) {
                $filters['year'] = $query_params['year'];
            }
            
            if (isset($query_params['status'])) {
                $filters['status'] = $query_params['status'];
            }
            
            // Pagination
            $page = isset($query_params['page']) ? (int)$query_params['page'] : 1;
            $limit = isset($query_params['limit']) ? (int)$query_params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Get vehicles
            $vehicles = $vehicle->getAll($filters, $limit, $offset);
            
            // Get total count for pagination
            $total_count = $vehicle->count($filters);
            
            sendResponse(200, [
                'vehicles' => $vehicles,
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
        $required_fields = ['customer_id', 'make', 'model', 'year', 'license_plate'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                sendResponse(400, ['message' => "Field '{$field}' is required"]);
                return;
            }
        }
        
        // Create vehicle
        if ($vehicle->create($data)) {
            sendResponse(201, [
                'message' => 'Vehicle created successfully',
                'id' => $vehicle->getId()
            ]);
        } else {
            sendResponse(500, ['message' => 'Failed to create vehicle']);
        }
        break;
        
    case 'PUT':
        // Check if vehicle exists
        if (!$resource_id || !$vehicle->getById($resource_id)) {
            sendResponse(404, ['message' => 'Vehicle not found']);
            return;
        }
        
        // Update vehicle
        if ($vehicle->update($resource_id, $data)) {
            sendResponse(200, ['message' => 'Vehicle updated successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to update vehicle']);
        }
        break;
        
    case 'DELETE':
        // Check if vehicle exists
        if (!$resource_id || !$vehicle->getById($resource_id)) {
            sendResponse(404, ['message' => 'Vehicle not found']);
            return;
        }
        
        // Delete vehicle
        if ($vehicle->delete($resource_id)) {
            sendResponse(200, ['message' => 'Vehicle deleted successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to delete vehicle']);
        }
        break;
        
    default:
        sendResponse(405, ['message' => 'Method not allowed']);
        break;
}
?>
