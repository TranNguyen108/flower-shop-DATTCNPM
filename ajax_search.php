<?php
/**
 * Ajax Search Endpoint
 * Real-time product search with autocomplete
 */

@include 'config.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';
$limit = isset($_GET['limit']) ? min(10, (int)$_GET['limit']) : 5;

if(strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Query too short']);
    exit;
}

// Search in product name and details
$search_pattern = '%' . $query . '%';

$results = db_select($conn,
    "SELECT id, name, price, image, category, stock_quantity, stock_status, is_available 
     FROM products 
     WHERE (name LIKE ? OR details LIKE ?) AND is_available = 1
     ORDER BY 
        CASE 
            WHEN name LIKE ? THEN 1
            WHEN name LIKE ? THEN 2
            ELSE 3
        END,
        stock_status ASC,
        name ASC
     LIMIT ?",
    "ssssi",
    [$search_pattern, $search_pattern, $query . '%', '%' . $query . '%', $limit]
);

$products = [];
foreach($results as $row) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'price' => number_format($row['price'], 0, ',', '.'),
        'price_raw' => $row['price'],
        'image' => $row['image'],
        'category' => $row['category'],
        'stock' => $row['stock_quantity'],
        'stock_status' => $row['stock_status'],
        'url' => 'view_page.php?pid=' . $row['id']
    ];
}

echo json_encode([
    'success' => true,
    'query' => $query,
    'count' => count($products),
    'products' => $products
]);
