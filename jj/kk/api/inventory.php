<?php
/**
 * Inventory API
 * Handles API requests for inventory/parts
 */

// Check if accessed directly
if (!defined('API_KEY')) {
    header('HTTP/1.0 403 Forbidden');
    exit('No direct script access allowed');
}

// Create Inventory object
$inventory = new Inventory();

// Handle request based on method
switch ($method) {
    case 'GET':
        if ($resource_id) {
            // Get part by ID
            if ($inventory->getById($resource_id)) {
                $part_data = [
                    'id' => $inventory->getId(),
                    'part_number' => $inventory->getPartNumber(),
                    'name' => $inventory->getName(),
                    'description' => $inventory->getDescription(),
                    'category' => $inventory->getCategory(),
                    'supplier_id' => $inventory->getSupplierId(),
                    'cost_price' => $inventory->getCostPrice(),
                    'selling_price' => $inventory->getSellingPrice(),
                    'quantity' => $inventory->getQuantity(),
                    'reorder_level' => $inventory->getReorderLevel(),
                    'location' => $inventory->getLocation(),
                    'status' => $inventory->getStatus(),
                    'created_at' => $inventory->getCreatedAt()
                ];
                
                // If action is specified
                if ($action === 'stock-movements') {
                    // Get stock movements
                    $part_data['stock_movements'] = $inventory->getStockMovements($resource_id);
                }
                
                sendResponse(200, $part_data);
            } else {
                sendResponse(404, ['message' => 'Part not found']);
            }
        } else {
            // Get all parts with optional filtering
            $filters = [];
            
            // Apply filters from query parameters
            if (isset($query_params['name'])) {
                $filters['name'] = $query_params['name'];
            }
            
            if (isset($query_params['category'])) {
                $filters['category'] = $query_params['category'];
            }
            
            if (isset($query_params['supplier_id'])) {
                $filters['supplier_id'] = $query_params['supplier_id'];
            }
            
            if (isset($query_params['status'])) {
                $filters['status'] = $query_params['status'];
            }
            
            if (isset($query_params['low_stock'])) {
                $filters['low_stock'] = (bool)$query_params['low_stock'];
            }
            
            // Pagination
            $page = isset($query_params['page']) ? (int)$query_params['page'] : 1;
            $limit = isset($query_params['limit']) ? (int)$query_params['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            // Get parts
            $parts = $inventory->getAll($filters, $limit, $offset);
            
            // Get total count for pagination
            $total_count = $inventory->count($filters);
            
            // Special actions
            if (isset($query_params['action'])) {
                switch ($query_params['action']) {
                    case 'categories':
                        // Get part categories
                        $categories = $inventory->getCategories();
                        sendResponse(200, ['categories' => $categories]);
                        return;
                        
                    case 'low-stock':
                        // Get low stock parts
                        $low_stock_parts = $inventory->getLowStockParts();
                        sendResponse(200, ['low_stock_parts' => $low_stock_parts]);
                        return;
                        
                    case 'stock-value':
                        // Get total stock value
                        $stock_value = $inventory->getTotalStockValue();
                        sendResponse(200, ['stock_value' => $stock_value]);
                        return;
                }
            }
            
            sendResponse(200, [
                'parts' => $parts,
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
        $required_fields = ['name', 'category', 'cost_price', 'selling_price', 'quantity', 'reorder_level'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                sendResponse(400, ['message' => "Field '{$field}' is required"]);
                return;
            }
        }
        
        // Generate part number if not provided
        if (!isset($data['part_number']) || empty($data['part_number'])) {
            $data['part_number'] = $inventory->generatePartNumber($data['category']);
        }
        
        // Create part
        if ($inventory->create($data)) {
            // Check if quantity is below reorder level
            if ($data['quantity'] <= $data['reorder_level']) {
                // Create low stock notification
                $notification = new Notification();
                $notification->createInventoryNotification($inventory->getId(), 'low_stock');
            }
            
            sendResponse(201, [
                'message' => 'Part created successfully',
                'id' => $inventory->getId(),
                'part_number' => $inventory->getPartNumber()
            ]);
        } else {
            sendResponse(500, ['message' => 'Failed to create part']);
        }
        break;
        
    case 'PUT':
        // Check if part exists
        if (!$resource_id || !$inventory->getById($resource_id)) {
            sendResponse(404, ['message' => 'Part not found']);
            return;
        }
        
        // Special actions
        if ($action) {
            switch ($action) {
                case 'add-stock':
                    // Validate quantity
                    if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
                        sendResponse(400, ['message' => 'Valid quantity is required']);
                        return;
                    }
                    
                    // Add stock
                    if ($inventory->addStock($resource_id, $data['quantity'], $data['notes'] ?? null)) {
                        // Create restock notification
                        $notification = new Notification();
                        $notification->createInventoryNotification($resource_id, 'restock');
                        
                        sendResponse(200, [
                            'message' => 'Stock added successfully',
                            'new_quantity' => $inventory->getQuantity()
                        ]);
                    } else {
                        sendResponse(500, ['message' => 'Failed to add stock']);
                    }
                    return;
                    
                case 'remove-stock':
                    // Validate quantity
                    if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
                        sendResponse(400, ['message' => 'Valid quantity is required']);
                        return;
                    }
                    
                    // Remove stock
                    if ($inventory->removeStock($resource_id, $data['quantity'], $data['notes'] ?? null)) {
                        // Check if quantity is now below reorder level
                        if ($inventory->getQuantity() <= $inventory->getReorderLevel()) {
                            // Create low stock notification
                            $notification = new Notification();
                            $notification->createInventoryNotification($resource_id, 'low_stock');
                        }
                        
                        sendResponse(200, [
                            'message' => 'Stock removed successfully',
                            'new_quantity' => $inventory->getQuantity()
                        ]);
                    } else {
                        sendResponse(500, ['message' => 'Failed to remove stock']);
                    }
                    return;
            }
        }
        
        // Update part
        if ($inventory->update($resource_id, $data)) {
            sendResponse(200, ['message' => 'Part updated successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to update part']);
        }
        break;
        
    case 'DELETE':
        // Check if part exists
        if (!$resource_id || !$inventory->getById($resource_id)) {
            sendResponse(404, ['message' => 'Part not found']);
            return;
        }
        
        // Delete part
        if ($inventory->delete($resource_id)) {
            sendResponse(200, ['message' => 'Part deleted successfully']);
        } else {
            sendResponse(500, ['message' => 'Failed to delete part']);
        }
        break;
        
    default:
        sendResponse(405, ['message' => 'Method not allowed']);
        break;
}
?>
