<?php
// Asegurar cookie de sesión consistente para todo el sitio
session_set_cookie_params([ 'path' => '/', 'httponly' => true, 'samesite' => 'Lax' ]);
session_start();
require __DIR__ . '/../../conn.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? 'get';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];

try {
    if ($action === 'add') {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? 'Producto';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
        $qty = isset($_POST['qty']) ? max(1, intval($_POST['qty'])) : 1;
        if (!$id) throw new Exception('Producto inválido');
        $key = (string)$id;
        if (!isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key] = ['id'=>$id,'name'=>$name,'price'=>$price,'qty'=>$qty];
        } else {
            $_SESSION['cart'][$key]['qty'] += $qty;
        }
        echo json_encode(['ok'=>true,'cart_count'=>count($_SESSION['cart']), 'items'=>array_values($_SESSION['cart'])]);
        exit;
    }

    if ($action === 'remove') {
        $id = $_POST['id'] ?? null;
        if ($id && isset($_SESSION['cart'][(string)$id])) {
            unset($_SESSION['cart'][(string)$id]);
        }
        echo json_encode(['ok'=>true,'cart_count'=>count($_SESSION['cart']), 'items'=>array_values($_SESSION['cart'])]);
        exit;
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        echo json_encode(['ok'=>true,'cart_count'=>0,'items'=>[]]);
        exit;
    }

    // default: get
    echo json_encode(['ok'=>true,'cart_count'=>count($_SESSION['cart']),'items'=>array_values($_SESSION['cart'])]);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=> $e->getMessage()]);
    exit;
}
