<?php
/**
 * Shopping Cart - Enhanced Security
 * CSRF Protection, Prepared Statements
 */

@include '../config.php';
require_once '../includes/voucher_functions.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

$message = [];

// Xóa một item
if(isset($_GET['delete'])){
    $delete_id = (int)$_GET['delete'];
    // Kiểm tra item thuộc về user hiện tại
    $check = db_fetch_one($conn, "SELECT id FROM cart WHERE id = ? AND user_id = ?", "ii", [$delete_id, $user_id]);
    if ($check) {
        db_delete($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?", "ii", [$delete_id, $user_id]);
        $message[] = 'Đã xóa sản phẩm khỏi giỏ hàng!';
    }
    header('location:cart.php');
    exit;
}

// Xóa tất cả
if(isset($_GET['delete_all'])){
    db_delete($conn, "DELETE FROM cart WHERE user_id = ?", "i", [$user_id]);
    $message[] = 'Đã xóa tất cả sản phẩm!';
    header('location:cart.php');
    exit;
}

// Cập nhật số lượng
if(isset($_POST['update_quantity'])){
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message[] = 'Lỗi bảo mật!';
    } else {
        $cart_id = (int)$_POST['cart_id'];
        $cart_quantity = max(1, (int)$_POST['cart_quantity']); // Tối thiểu 1
        
        // Kiểm tra cart item thuộc user
        $check = db_fetch_one($conn, "SELECT id FROM cart WHERE id = ? AND user_id = ?", "ii", [$cart_id, $user_id]);
        if ($check) {
            db_update($conn, "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", "iii", [$cart_quantity, $cart_id, $user_id]);
            $message[] = 'Cập nhật số lượng sản phẩm thành công!';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Giỏ hàng</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/style-enhanced.css">

   <style>
      .cart-page {
         padding: 30px 5%;
         max-width: 1400px;
         margin: 0 auto;
         background: #f8f9fa;
      }

      .cart-header {
         background: white;
         padding: 30px;
         border-radius: 15px;
         margin-bottom: 30px;
         box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      }

      .cart-header h1 {
         font-size: 2rem;
         color: #2c3e50;
         margin: 0 0 10px 0;
         display: flex;
         align-items: center;
         gap: 12px;
      }

      .cart-header h1 i {
         color: #667eea;
      }

      .cart-header p {
         margin: 0;
         color: #6c757d;
      }

      .cart-items {
         display: grid;
         gap: 20px;
         margin-bottom: 30px;
      }

      .cart-item {
         background: white;
         border-radius: 15px;
         padding: 25px;
         box-shadow: 0 2px 10px rgba(0,0,0,0.05);
         display: grid;
         grid-template-columns: 50px 120px 1fr auto;
         gap: 20px;
         align-items: center;
         transition: all 0.3s;
         position: relative;
         border: 2px solid transparent;
      }

      .cart-item:hover {
         box-shadow: 0 5px 20px rgba(0,0,0,0.1);
         transform: translateY(-2px);
      }
      
      .cart-item.selected {
         border-color: #667eea;
         background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
      }
      
      /* Checkbox styling */
      .item-checkbox {
         display: flex;
         align-items: center;
         justify-content: center;
      }
      
      .item-checkbox input[type="checkbox"] {
         width: 24px;
         height: 24px;
         cursor: pointer;
         accent-color: #667eea;
      }
      
      .select-all-wrapper {
         background: white;
         padding: 15px 25px;
         border-radius: 12px;
         margin-bottom: 15px;
         display: flex;
         align-items: center;
         gap: 15px;
         box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      }
      
      .select-all-wrapper input[type="checkbox"] {
         width: 22px;
         height: 22px;
         cursor: pointer;
         accent-color: #667eea;
      }
      
      .select-all-wrapper label {
         font-weight: 600;
         color: #2c3e50;
         cursor: pointer;
      }
      
      .selected-count {
         margin-left: auto;
         color: #667eea;
         font-weight: 600;
      }

      .item-image {
         position: relative;
      }

      .item-image img {
         width: 120px;
         height: 120px;
         object-fit: cover;
         border-radius: 12px;
         border: 3px solid #f0f0f0;
      }

      .item-details {
         display: flex;
         flex-direction: column;
         gap: 12px;
      }

      .item-name {
         font-size: 1.3rem;
         font-weight: 700;
         color: #2c3e50;
         margin: 0;
      }

      .item-price {
         font-size: 1.4rem;
         color: #667eea;
         font-weight: 700;
      }

      .item-actions {
         display: flex;
         flex-direction: column;
         gap: 15px;
         align-items: flex-end;
         min-width: 250px;
      }

      .quantity-control {
         display: flex;
         align-items: center;
         gap: 12px;
         background: #f8f9fa;
         padding: 8px 15px;
         border-radius: 10px;
      }

      .quantity-control label {
         font-weight: 600;
         color: #2c3e50;
         font-size: 0.9rem;
      }

      .quantity-control input[type="number"] {
         width: 70px;
         padding: 8px 12px;
         border: 2px solid #e9ecef;
         border-radius: 8px;
         text-align: center;
         font-size: 1.1rem;
         font-weight: 600;
      }

      .quantity-control input[type="number"]:focus {
         outline: none;
         border-color: #667eea;
      }

      .update-btn {
         padding: 8px 20px;
         background: #667eea;
         color: white;
         border: none;
         border-radius: 8px;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s;
         font-size: 0.9rem;
      }

      .update-btn:hover {
         background: #5568d3;
         transform: translateY(-2px);
      }

      .item-subtotal {
         font-size: 1.5rem;
         font-weight: 700;
         color: #2c3e50;
         padding: 12px 20px;
         background: #f8f9fa;
         border-radius: 10px;
      }

      .item-subtotal span {
         color: #667eea;
      }
      
      /* Nút thanh toán riêng cho từng sản phẩm */
      .item-checkout-btn {
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 8px;
         padding: 12px 24px;
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         color: white;
         text-decoration: none;
         border-radius: 10px;
         font-weight: 600;
         font-size: 0.95rem;
         transition: all 0.3s ease;
         box-shadow: 0 3px 10px rgba(238,77,45,0.2);
      }
      
      .item-checkout-btn:hover {
         background: linear-gradient(135deg, #d84315 0%, #ff5722 100%);
         transform: translateY(-3px);
         box-shadow: 0 6px 20px rgba(238,77,45,0.35);
         color: white;
      }
      
      .item-checkout-btn i {
         font-size: 1rem;
      }

      .item-remove {
         position: absolute;
         top: 15px;
         right: 15px;
         width: 35px;
         height: 35px;
         background: #fee;
         color: #dc3545;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.1rem;
         transition: all 0.3s;
         text-decoration: none;
      }

      .item-remove:hover {
         background: #dc3545;
         color: white;
         transform: rotate(90deg);
      }

      .item-view {
         position: absolute;
         top: 60px;
         right: 15px;
         width: 35px;
         height: 35px;
         background: #e3f2fd;
         color: #1976d2;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1rem;
         transition: all 0.3s;
         text-decoration: none;
      }

      .item-view:hover {
         background: #1976d2;
         color: white;
      }

      .cart-summary {
         background: white;
         border-radius: 15px;
         padding: 30px;
         box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      }

      .summary-header {
         font-size: 1.5rem;
         font-weight: 700;
         color: #2c3e50;
         margin: 0 0 20px 0;
         padding-bottom: 15px;
         border-bottom: 2px solid #f0f0f0;
      }

      .summary-total {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 20px;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         border-radius: 12px;
         margin-bottom: 25px;
      }

      .summary-total-label {
         font-size: 1.3rem;
         font-weight: 600;
         color: white;
      }

      .summary-total-amount {
         font-size: 2rem;
         font-weight: 700;
         color: white;
      }

      .summary-actions {
         display: grid;
         grid-template-columns: 1fr 1fr;
         gap: 15px;
      }

      .cart-btn {
         padding: 15px 25px;
         border-radius: 10px;
         font-weight: 600;
         font-size: 1rem;
         text-decoration: none;
         text-align: center;
         transition: all 0.3s;
         border: none;
         cursor: pointer;
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 8px;
      }

      .continue-shopping {
         background: #f8f9fa;
         color: #2c3e50;
         border: 2px solid #e9ecef;
      }

      .continue-shopping:hover {
         background: #e9ecef;
         transform: translateY(-2px);
      }

      .checkout-btn {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: white;
         grid-column: span 2;
      }

      .checkout-btn:hover {
         transform: translateY(-3px);
         box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
      }

      /* Custom bouquet preview */
      .custom-bouquet-preview {
         width: 140px;
         height: 140px;
         background: linear-gradient(180deg, #ffecd2 0%, #fcb69f 60%, #a8e6cf 100%);
         border-radius: 50% 50% 45% 45%;
         display: flex;
         flex-wrap: wrap;
         align-items: center;
         justify-content: center;
         gap: 2px;
         padding: 15px;
         box-shadow: 0 8px 25px rgba(0,0,0,0.15);
         position: relative;
         margin: 0 auto;
      }
      
      .custom-bouquet-preview::after {
         content: '';
         position: absolute;
         bottom: -15px;
         left: 50%;
         transform: translateX(-50%);
         width: 30px;
         height: 30px;
         background: linear-gradient(135deg, #dfe6e9 0%, #b2bec3 100%);
         border-radius: 5px 5px 15px 15px;
      }
      
      .preview-emoji {
         font-size: 1.8rem;
         animation: gentleBounce 2s ease-in-out infinite;
      }
      
      .preview-emoji:nth-child(odd) {
         animation-delay: 0.5s;
      }
      
      @keyframes gentleBounce {
         0%, 100% { transform: translateY(0); }
         50% { transform: translateY(-3px); }
      }
      
      .custom-items-list {
         margin: 8px 0;
         line-height: 1.6;
      }

      .checkout-btn.disabled {
         background: #e9ecef;
         color: #adb5bd;
         cursor: not-allowed;
         pointer-events: none;
      }
      
      /* Selected Summary Styles */
      .selected-summary {
         background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
         border: 2px solid #ff9800;
         border-radius: 12px;
         padding: 15px 20px;
         margin-bottom: 15px;
      }
      
      .selected-items-row, .selected-total-row {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 8px 0;
      }
      
      .selected-items-row span:first-child,
      .selected-total-row span:first-child {
         color: #e65100;
         font-weight: 600;
         display: flex;
         align-items: center;
         gap: 8px;
      }
      
      .selected-items-row span:first-child i,
      .selected-total-row span:first-child i {
         color: #ff9800;
      }
      
      #selected-items-count {
         font-weight: 700;
         color: #e65100;
      }
      
      .selected-total-value {
         font-size: 1.4rem;
         font-weight: 700;
         color: #e65100;
      }
      
      /* Cart item selected state */
      .cart-item.selected {
         border: 2px solid #667eea;
         background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
      }
      
      .cart-item:not(.selected) {
         opacity: 0.7;
      }
      
      .cart-item:not(.selected):hover {
         opacity: 1;
      }

      .clear-cart-btn {
         background: #fff;
         color: #dc3545;
         border: 2px solid #dc3545;
         grid-column: span 2;
         margin-top: 10px;
      }

      .clear-cart-btn:hover {
         background: #dc3545;
         color: white;
      }

      .clear-cart-btn.disabled {
         border-color: #e9ecef;
         color: #adb5bd;
         cursor: not-allowed;
         pointer-events: none;
      }

      /* Voucher Section - Pro Style */
      .voucher-section {
         background: white;
         border-radius: 12px;
         overflow: hidden;
         margin-bottom: 20px;
         box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      }
      
      .voucher-section-header {
         background: linear-gradient(135deg, #ee4d2d 0%, #ff7337 100%);
         color: white;
         padding: 15px 20px;
         display: flex;
         align-items: center;
         justify-content: space-between;
      }
      
      .voucher-section-header h4 {
         margin: 0;
         font-size: 1.1rem;
         display: flex;
         align-items: center;
         gap: 10px;
      }
      
      .voucher-section-header .voucher-link {
         color: white;
         text-decoration: none;
         font-size: 0.9rem;
         display: flex;
         align-items: center;
         gap: 5px;
         opacity: 0.9;
      }
      
      .voucher-section-header .voucher-link:hover {
         opacity: 1;
      }
      
      .voucher-body {
         padding: 20px;
      }

      .voucher-input-wrapper {
         display: flex;
         gap: 10px;
         margin-bottom: 15px;
      }

      .voucher-input-wrapper input {
         flex: 1;
         padding: 14px 16px;
         border: 2px solid #e8e8e8;
         border-radius: 8px;
         font-size: 1rem;
         text-transform: uppercase;
         letter-spacing: 1px;
         transition: all 0.3s;
      }

      .voucher-input-wrapper input:focus {
         outline: none;
         border-color: #ee4d2d;
         box-shadow: 0 0 0 3px rgba(238,77,45,0.1);
      }
      
      .voucher-input-wrapper input::placeholder {
         text-transform: none;
         letter-spacing: normal;
         color: #999;
      }

      .voucher-input-wrapper .btn-apply {
         padding: 14px 28px;
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         color: white;
         border: none;
         border-radius: 8px;
         font-weight: 700;
         cursor: pointer;
         transition: all 0.3s;
         white-space: nowrap;
      }

      .voucher-input-wrapper .btn-apply:hover {
         transform: translateY(-2px);
         box-shadow: 0 5px 20px rgba(238,77,45,0.35);
      }
      
      .voucher-input-wrapper .btn-apply:disabled {
         background: #ccc;
         cursor: not-allowed;
         transform: none;
         box-shadow: none;
      }

      /* Applied Voucher - Beautiful Card */
      .voucher-applied-card {
         background: linear-gradient(135deg, #e8fff3 0%, #d4f5e9 100%);
         border: 2px solid #26aa99;
         border-radius: 16px;
         padding: 0;
         display: flex;
         align-items: stretch;
         justify-content: space-between;
         animation: slideIn 0.3s ease;
         margin-bottom: 15px;
         overflow: hidden;
         box-shadow: 0 4px 15px rgba(38,170,153,0.15);
      }
      
      .voucher-applied-card .vac-left {
         display: flex;
         align-items: center;
         gap: 0;
         flex: 1;
      }
      
      .voucher-applied-card .vac-badge {
         min-width: 90px;
         padding: 20px 15px;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: white;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         text-align: center;
         position: relative;
      }
      
      .voucher-applied-card .vac-badge::after {
         content: '';
         position: absolute;
         right: -8px;
         top: 50%;
         transform: translateY(-50%);
         width: 16px;
         height: 16px;
         background: linear-gradient(135deg, #e8fff3 0%, #d4f5e9 100%);
         border-radius: 50%;
      }
      
      .voucher-applied-card .vac-badge.percent {
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
      }
      
      .voucher-applied-card .vac-badge .badge-value {
         font-size: 1.4rem;
         font-weight: 800;
         line-height: 1;
      }
      
      .voucher-applied-card .vac-badge .badge-label {
         font-size: 0.7rem;
         opacity: 0.9;
         margin-top: 4px;
         letter-spacing: 1px;
      }
      
      .voucher-applied-card .vac-info {
         padding: 15px 20px;
         flex: 1;
      }
      
      .voucher-applied-card .vac-code {
         font-family: 'Courier New', monospace;
         font-size: 1rem;
         color: #26aa99;
         font-weight: 700;
         display: flex;
         align-items: center;
         gap: 8px;
         margin-bottom: 4px;
      }
      
      .voucher-applied-card .vac-code i {
         color: #26aa99;
      }
      
      .voucher-applied-card .vac-name {
         font-size: 0.9rem;
         color: #555;
         margin-bottom: 8px;
      }
      
      .voucher-applied-card .vac-saving {
         font-size: 0.85rem;
         color: #26aa99;
         display: flex;
         align-items: center;
         gap: 6px;
      }
      
      .voucher-applied-card .vac-saving strong {
         color: #e74c3c;
         font-size: 1rem;
      }
      
      .voucher-applied-card .vac-remove {
         background: transparent;
         border: none;
         color: #999;
         font-size: 1.5rem;
         cursor: pointer;
         padding: 20px;
         transition: all 0.3s;
         display: flex;
         align-items: center;
      }
      
      .voucher-applied-card .vac-remove:hover {
         color: #e74c3c;
         transform: scale(1.1);
      }
      
      /* Savings Banner */
      .savings-banner {
         background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
         border: 1px solid #ffc107;
         border-radius: 10px;
         padding: 12px 18px;
         display: flex;
         align-items: center;
         gap: 12px;
         margin-bottom: 20px;
         animation: pulse 2s infinite;
      }
      
      @keyframes pulse {
         0%, 100% { box-shadow: 0 0 0 0 rgba(255,193,7,0.4); }
         50% { box-shadow: 0 0 0 8px rgba(255,193,7,0); }
      }
      
      .savings-banner i {
         font-size: 1.3rem;
         color: #f39c12;
      }
      
      .savings-banner .savings-text {
         font-size: 0.95rem;
         color: #856404;
      }
      
      .savings-banner .savings-amount {
         color: #e74c3c;
         font-weight: 700;
         font-size: 1.1rem;
      }
      
      @keyframes slideIn {
         from { opacity: 0; transform: translateY(-10px); }
         to { opacity: 1; transform: translateY(0); }
      }

      /* My Vouchers Slider */
      .my-vouchers-preview {
         margin-top: 15px;
         padding-top: 15px;
         border-top: 1px dashed #e0e0e0;
      }
      
      .my-vouchers-label {
         font-size: 0.9rem;
         color: #666;
         margin-bottom: 10px;
         display: flex;
         align-items: center;
         gap: 8px;
      }
      
      .my-vouchers-label i {
         color: #ee4d2d;
      }
      
      /* Voucher Chips Filter */
      .voucher-chips {
         display: flex;
         gap: 10px;
         overflow-x: auto;
         padding-bottom: 15px;
         margin-bottom: 15px;
         -webkit-overflow-scrolling: touch;
      }
      
      .voucher-chip {
         display: flex;
         align-items: center;
         gap: 8px;
         padding: 10px 18px;
         background: white;
         border: 2px solid #e8e8e8;
         border-radius: 25px;
         cursor: pointer;
         white-space: nowrap;
         transition: all 0.3s;
         min-width: fit-content;
         font-size: 0.9rem;
         font-weight: 500;
         color: #666;
      }
      
      .voucher-chip:hover {
         border-color: #ee4d2d;
         color: #ee4d2d;
         background: #fff5f5;
      }
      
      .voucher-chip.active {
         border-color: #ee4d2d;
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         color: white;
         box-shadow: 0 4px 15px rgba(238,77,45,0.3);
      }
      
      .voucher-chip.disabled {
         opacity: 0.5;
         cursor: not-allowed;
      }
      
      .voucher-chip .chip-discount {
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         color: white;
         padding: 4px 10px;
         border-radius: 4px;
         font-weight: 700;
         font-size: 0.85rem;
      }
      
      .voucher-chip.freeship .chip-discount {
         background: linear-gradient(135deg, #26aa99 0%, #20c997 100%);
      }
      
      .voucher-chip .chip-info {
         font-size: 0.85rem;
      }
      
      .voucher-chip .chip-info .chip-code {
         font-family: monospace;
         color: #333;
         font-weight: 600;
      }
      
      .voucher-chip .chip-info .chip-min {
         color: #999;
         font-size: 0.8rem;
      }
      
      .voucher-chip.active {
         border-color: #ee4d2d;
         background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
         box-shadow: 0 2px 8px rgba(238,77,45,0.2);
      }

      /* Voucher Input Group - Modern Style */
      .voucher-input-group {
         display: flex;
         align-items: center;
         background: #f8f9fa;
         border: 2px solid #e0e0e0;
         border-radius: 10px;
         overflow: hidden;
         transition: all 0.3s;
         margin-bottom: 15px;
      }
      
      .voucher-input-group:focus-within {
         border-color: #ee4d2d;
         box-shadow: 0 0 0 3px rgba(238,77,45,0.1);
      }
      
      .voucher-input-group .vig-icon {
         padding: 0 15px;
         color: #ee4d2d;
         font-size: 1.1rem;
      }
      
      .voucher-input-group input {
         flex: 1;
         border: none;
         background: transparent;
         padding: 14px 0;
         font-size: 1rem;
         text-transform: uppercase;
         letter-spacing: 1px;
      }
      
      .voucher-input-group input:focus {
         outline: none;
      }
      
      .voucher-input-group input::placeholder {
         text-transform: none;
         letter-spacing: normal;
         color: #999;
      }
      
      .voucher-input-group .vig-btn {
         padding: 14px 25px;
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         color: white;
         border: none;
         font-weight: 700;
         cursor: pointer;
         transition: all 0.3s;
      }
      
      .voucher-input-group .vig-btn:hover {
         background: linear-gradient(135deg, #d73211 0%, #ee4d2d 100%);
      }
      
      .voucher-input-group .vig-btn:disabled {
         background: #ccc;
         cursor: not-allowed;
      }

      /* Available Vouchers Dropdown */
      .voucher-dropdown {
         margin-top: 0;
         border: 2px solid #f0f0f0;
         border-radius: 16px;
         overflow: hidden;
         background: white;
         box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      }
      
      .voucher-dropdown-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 15px 20px;
         background: linear-gradient(135deg, #fff8f6 0%, #fff5f5 100%);
         border-bottom: 2px solid #f0f0f0;
      }
      
      .voucher-dropdown-header span {
         display: flex;
         align-items: center;
         gap: 10px;
         font-weight: 700;
         color: #ee4d2d;
         font-size: 1rem;
      }
      
      .voucher-dropdown-header .vdh-count {
         font-size: 0.85rem;
         color: white;
         font-weight: 600;
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         padding: 4px 12px;
         border-radius: 20px;
      }
      
      .voucher-dropdown-list {
         max-height: 400px;
         overflow-y: auto;
         padding: 10px;
      }
      
      .voucher-loading {
         padding: 40px;
         text-align: center;
         color: #999;
      }
      
      .voucher-loading i {
         font-size: 2rem;
         color: #ee4d2d;
         margin-bottom: 10px;
         display: block;
      }
      
      .voucher-empty {
         padding: 50px 20px;
         text-align: center;
         background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
         border-radius: 12px;
         margin: 10px;
      }
      
      .voucher-empty i {
         font-size: 4rem;
         color: #ddd;
         margin-bottom: 20px;
         display: block;
      }
      
      .voucher-empty p {
         color: #999;
         margin-bottom: 20px;
         font-size: 1rem;
      }
      
      .voucher-empty a {
         display: inline-block;
         padding: 12px 25px;
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         color: white;
         text-decoration: none;
         font-weight: 600;
         border-radius: 25px;
         transition: all 0.3s;
      }
      
      .voucher-empty a:hover {
         transform: translateY(-2px);
         box-shadow: 0 5px 20px rgba(238,77,45,0.35);
      }
      
      .voucher-dropdown-toggle {
         display: flex;
         align-items: center;
         justify-content: space-between;
         padding: 12px 15px;
         background: #f8f9fa;
         border-radius: 8px;
         cursor: pointer;
         transition: all 0.3s;
      }
      
      .voucher-dropdown-toggle:hover {
         background: #f0f0f0;
      }
      
      .voucher-dropdown-toggle span {
         display: flex;
         align-items: center;
         gap: 8px;
         color: #ee4d2d;
         font-weight: 600;
      }
      
      .voucher-dropdown-toggle i.arrow {
         transition: transform 0.3s;
      }
      
      .voucher-dropdown-toggle.active i.arrow {
         transform: rotate(180deg);
      }

      .voucher-list {
         display: none;
         margin-top: 10px;
         max-height: 350px;
         overflow-y: auto;
         border: 1px solid #e8e8e8;
         border-radius: 8px;
      }

      .voucher-list.show {
         display: block;
         animation: slideDown 0.3s ease;
      }
      
      @keyframes slideDown {
         from { opacity: 0; max-height: 0; }
         to { opacity: 1; max-height: 350px; }
      }

      .voucher-list-item {
         display: flex;
         border: 2px solid #f0f0f0;
         border-radius: 12px;
         margin-bottom: 10px;
         overflow: hidden;
         transition: all 0.3s;
         background: white;
      }
      
      .voucher-list-item:last-child {
         margin-bottom: 0;
      }
      
      .voucher-list-item:hover {
         border-color: #ee4d2d;
         box-shadow: 0 4px 15px rgba(238,77,45,0.15);
         transform: translateY(-2px);
      }
      
      .voucher-list-item.disabled {
         opacity: 0.6;
         background: #f8f9fa;
      }
      
      .voucher-list-item.disabled:hover {
         transform: none;
         box-shadow: none;
         border-color: #f0f0f0;
      }
      
      .vli-left {
         width: 90px;
         padding: 15px;
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         color: white;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         position: relative;
      }
      
      .vli-left::after {
         content: '';
         position: absolute;
         right: -6px;
         top: 50%;
         transform: translateY(-50%);
         width: 12px;
         height: 12px;
         background: white;
         border-radius: 50%;
      }
      
      .vli-left.freeship {
         background: linear-gradient(135deg, #26aa99 0%, #20c997 100%);
      }
      
      .vli-left .vli-icon {
         font-size: 1.2rem;
         margin-bottom: 3px;
      }
      
      .vli-left .vli-value {
         font-size: 1.3rem;
         font-weight: 800;
         line-height: 1;
      }
      
      .vli-left .vli-label {
         font-size: 0.7rem;
         opacity: 0.9;
      }
      
      /* Badge style for voucher list items */
      .vli-badge {
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         color: white;
         padding: 12px 10px;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         min-width: 80px;
         text-align: center;
      }
      
      .vli-badge.percent {
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
      }
      
      .vli-badge.fixed {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }
      
      .vli-badge.freeship {
         background: linear-gradient(135deg, #26aa99 0%, #20c997 100%);
      }
      
      .vli-badge .badge-value {
         font-size: 1.1rem;
         font-weight: 800;
         line-height: 1.2;
      }
      
      .vli-badge .badge-label {
         font-size: 0.65rem;
         opacity: 0.9;
         margin-top: 2px;
      }
      
      .vli-center {
         flex: 1;
         padding: 10px 15px;
         display: flex;
         flex-direction: column;
         justify-content: center;
         gap: 3px;
      }
      
      .vli-code {
         font-family: monospace;
         font-size: 0.9rem;
         color: #ee4d2d;
         font-weight: 700;
      }
      
      .vli-name {
         font-size: 0.85rem;
         color: #333;
         font-weight: 500;
      }
      
      .vli-conditions {
         display: flex;
         flex-wrap: wrap;
         gap: 8px;
         font-size: 0.75rem;
         color: #999;
      }
      
      .vli-conditions span {
         display: flex;
         align-items: center;
         gap: 4px;
      }
      
      .vli-progress {
         height: 4px;
         background: #e0e0e0;
         border-radius: 2px;
         margin-top: 6px;
         overflow: hidden;
      }
      
      .vli-progress-bar {
         height: 100%;
         background: linear-gradient(90deg, #26aa99, #20c997);
         border-radius: 2px;
         transition: width 0.3s ease;
      }
      
      .vli-remaining {
         font-size: 0.7rem;
         color: #26aa99;
         margin-top: 2px;
      }
      
      .vli-expiring {
         font-size: 0.7rem;
         color: #e74c3c;
         margin-top: 3px;
      }
      
      .vli-expiring i {
         margin-right: 3px;
      }
      
      .vli-right {
         padding: 15px;
         display: flex;
         align-items: center;
         justify-content: center;
      }
      
      .vli-btn {
         padding: 10px 20px;
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
         color: white;
         border: none;
         border-radius: 25px;
         font-weight: 700;
         font-size: 0.85rem;
         cursor: pointer;
         transition: all 0.3s;
         white-space: nowrap;
      }
      
      .vli-btn:hover {
         transform: scale(1.05);
         box-shadow: 0 4px 15px rgba(238,77,45,0.4);
      }
      
      .vli-btn.disabled {
         background: #ccc;
         font-size: 0.75rem;
         cursor: not-allowed;
      }
      
      .vli-btn.disabled:hover {
         transform: none;
         box-shadow: none;
      }
      
      /* Voucher Applied Card - New Design */
      .vac-left {
         display: flex;
         gap: 15px;
         align-items: center;
         flex: 1;
      }
      
      .vac-badge {
         background: linear-gradient(135deg, #26aa99 0%, #20c997 100%);
         color: white;
         padding: 15px;
         border-radius: 10px;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         min-width: 80px;
      }
      
      .vac-badge.percent {
         background: linear-gradient(135deg, #ee4d2d 0%, #ff6533 100%);
      }
      
      .vac-badge.fixed {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      }
      
      .vac-badge .badge-value {
         font-size: 1.2rem;
         font-weight: 800;
         line-height: 1;
      }
      
      .vac-badge .badge-label {
         font-size: 0.7rem;
         opacity: 0.9;
         margin-top: 3px;
      }
      
      .vac-info {
         flex: 1;
      }
      
      .vac-code {
         font-family: monospace;
         font-size: 1rem;
         font-weight: 700;
         color: #26aa99;
         display: flex;
         align-items: center;
         gap: 8px;
      }
      
      .vac-code i {
         color: #26aa99;
      }
      
      .vac-name {
         font-size: 0.9rem;
         color: #666;
         margin: 3px 0;
      }
      
      .vac-saving {
         font-size: 0.85rem;
         color: #155724;
      }
      
      .vac-saving i {
         color: #26aa99;
         margin-right: 5px;
      }
      
      .vac-saving strong {
         color: #e74c3c;
      }
      
      .vac-remove {
         background: none;
         border: none;
         color: #dc3545;
         font-size: 1.3rem;
         cursor: pointer;
         padding: 10px;
         opacity: 0.7;
         transition: all 0.3s;
      }
      
      .vac-remove:hover {
         opacity: 1;
         transform: scale(1.1);
      }

      .vli-info h5 {
         margin: 0 0 3px 0;
         font-size: 0.95rem;
         color: #333;
      }
      
      .vli-info .vli-code {
         font-family: monospace;
         color: #ee4d2d;
         font-size: 0.85rem;
         background: #fff5f5;
         padding: 2px 6px;
         border-radius: 3px;
         display: inline-block;
         margin-bottom: 3px;
      }
      
      .vli-info .vli-condition {
         font-size: 0.8rem;
         color: #999;
      }
      
      .vli-info .vli-expiry {
         font-size: 0.75rem;
         color: #e74c3c;
         margin-top: 3px;
      }
      
      .vli-btn {
         padding: 8px 18px;
         background: #ee4d2d;
         color: white;
         border: none;
         border-radius: 4px;
         font-weight: 600;
         font-size: 0.85rem;
         cursor: pointer;
         transition: all 0.2s;
      }
      
      .vli-btn:hover {
         background: #d73211;
      }
      
      .vli-btn:disabled {
         background: #ccc;
         cursor: not-allowed;
      }

      /* Summary with discount */
      .summary-row {
         display: flex;
         justify-content: space-between;
         padding: 15px 0;
         border-bottom: 1px dashed #e8e8e8;
         font-size: 1.05rem;
         color: #555;
      }
      
      .summary-row:last-of-type {
         border-bottom: none;
      }

      .summary-row.discount {
         color: #26aa99;
         background: linear-gradient(90deg, rgba(38,170,153,0.05) 0%, transparent 100%);
         margin: 0 -20px;
         padding: 15px 20px;
         border-radius: 8px;
      }

      .summary-row.discount .value {
         font-weight: 700;
         font-size: 1.1rem;
      }
      
      .summary-total {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: white;
         margin: 20px -20px -20px -20px;
         padding: 20px;
         border-radius: 0 0 12px 12px;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }
      
      .summary-total-label {
         font-size: 1.1rem;
         font-weight: 600;
      }
      
      .summary-total-amount {
         font-size: 1.8rem;
         font-weight: 800;
      }
      
      /* Summary Actions */
      .summary-actions {
         display: flex;
         flex-direction: column;
         gap: 12px;
         margin-top: 25px;
      }
      
      .summary-actions .cart-btn {
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 10px;
         padding: 16px 25px;
         border-radius: 12px;
         font-weight: 700;
         font-size: 1rem;
         text-decoration: none;
         transition: all 0.3s;
         border: none;
         cursor: pointer;
      }
      
      .summary-actions .checkout-btn {
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: white;
         box-shadow: 0 4px 15px rgba(102,126,234,0.4);
      }
      
      .summary-actions .checkout-btn:hover {
         transform: translateY(-2px);
         box-shadow: 0 8px 25px rgba(102,126,234,0.5);
      }
      
      .summary-actions .continue-shopping {
         background: white;
         color: #667eea;
         border: 2px solid #667eea;
      }
      
      .summary-actions .continue-shopping:hover {
         background: #f0f4ff;
      }
      
      .summary-actions .clear-cart-btn {
         background: white;
         color: #e74c3c;
         border: 2px solid #e74c3c;
      }
      
      .summary-actions .clear-cart-btn:hover {
         background: #fff5f5;
      }

      .empty-cart {
         background: white;
         padding: 80px 40px;
         border-radius: 15px;
         text-align: center;
         box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      }

      .empty-cart i {
         font-size: 5rem;
         color: #e9ecef;
         margin-bottom: 20px;
      }

      .empty-cart h3 {
         font-size: 1.8rem;
         color: #6c757d;
         margin: 0 0 15px 0;
      }

      .empty-cart p {
         color: #adb5bd;
         margin: 0 0 30px 0;
      }

      .empty-cart a {
         padding: 15px 40px;
         background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
         color: white;
         border-radius: 10px;
         text-decoration: none;
         font-weight: 600;
         display: inline-flex;
         align-items: center;
         gap: 10px;
         transition: all 0.3s;
      }

      .empty-cart a:hover {
         transform: translateY(-3px);
         box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
      }

      @media (max-width: 768px) {
         .cart-item {
            grid-template-columns: 1fr;
            text-align: center;
         }

         .item-image img {
            margin: 0 auto;
         }

         .item-actions {
            align-items: center;
            width: 100%;
         }

         .item-remove,
         .item-view {
            position: static;
            margin: 10px 5px;
            display: inline-flex;
         }

         .summary-actions {
            grid-template-columns: 1fr;
         }

         .checkout-btn,
         .clear-cart-btn {
            grid-column: span 1;
         }
      }
   </style>

</head>
<body>
   
<?php @include '../header.php'; ?>

<?php
if(!empty($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.e($msg).'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<section class="heading">
    <h3>Giỏ hàng của bạn</h3>
    <p> <a href="./home.php">Trang chủ</a> / Giỏ hàng </p>
</section>

<div class="cart-page">

    <div class="cart-header">
        <h1><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h1>
        <p>Xem lại và cập nhật sản phẩm trước khi thanh toán</p>
    </div>

    <?php
        $grand_total = 0;
        $select_cart = db_select($conn, "SELECT * FROM cart WHERE user_id = ?", "i", [$user_id]);
        if(mysqli_num_rows($select_cart) > 0){
    ?>

    <div class="cart-items">
        <!-- Select All -->
        <div class="select-all-wrapper">
            <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
            <label for="select-all">Chọn tất cả</label>
            <span class="selected-count" id="selected-count">Đã chọn: 0 sản phẩm</span>
        </div>
        
        <?php
            while($fetch_cart = mysqli_fetch_assoc($select_cart)){
                $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
                $grand_total += $sub_total;
        ?>
        <div class="cart-item" data-id="<?php echo $fetch_cart['id']; ?>" data-price="<?php echo $sub_total; ?>">
            <a href="./cart.php?delete=<?php echo $fetch_cart['id']; ?>" class="item-remove" onclick="return confirm('Xóa sản phẩm này khỏi giỏ hàng?');" title="Xóa">
                <i class="fas fa-times"></i>
            </a>
            <a href="./view_page.php?pid=<?php echo $fetch_cart['pid']; ?>" class="item-view" title="Xem chi tiết">
                <i class="fas fa-eye"></i>
            </a>
            
            <!-- Checkbox chọn sản phẩm -->
            <div class="item-checkbox">
                <input type="checkbox" class="cart-checkbox" value="<?php echo $fetch_cart['id']; ?>" data-price="<?php echo $sub_total; ?>" onchange="updateSelectedTotal()" checked>
            </div>

            <div class="item-image">
                <?php if($fetch_cart['is_custom'] == 1): ?>
                    <?php 
                    // Hiển thị emoji preview cho bó hoa tự thiết kế
                    $custom_data = json_decode($fetch_cart['custom_data'], true);
                    $emojis = [];
                    if(is_array($custom_data)){
                        foreach($custom_data as $item){
                            if(isset($item['emoji'])){
                                $emojis[] = $item['emoji'];
                            }
                        }
                    }
                    ?>
                    <div class="custom-bouquet-preview">
                        <?php if(!empty($emojis)): ?>
                            <?php foreach(array_slice($emojis, 0, 6) as $emoji): ?>
                                <span class="preview-emoji"><?php echo $emoji; ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="preview-emoji">💐</span>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <img src="../assets/uploads/products/<?php echo e($fetch_cart['image']); ?>" alt="<?php echo e($fetch_cart['name']); ?>">
                <?php endif; ?>
            </div>

            <div class="item-details">
                <h3 class="item-name"><?php echo e($fetch_cart['name']); ?></h3>
                <?php if($fetch_cart['is_custom'] == 1): ?>
                    <div class="custom-items-list">
                        <?php 
                        if(is_array($custom_data)){
                            $items_text = [];
                            foreach($custom_data as $item){
                                if(isset($item['name']) && isset($item['quantity'])){
                                    $items_text[] = $item['emoji'] . ' ' . $item['name'] . ' x' . $item['quantity'];
                                }
                            }
                            echo '<small style="color:#666; font-size:1.1rem;">' . implode(' • ', array_slice($items_text, 0, 4));
                            if(count($items_text) > 4) echo ' ...';
                            echo '</small>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <div class="item-price"><?php echo number_format($fetch_cart['price'], 0, ',', '.'); ?>₫</div>
            </div>

            <div class="item-actions">
                <form action="" method="post" style="width: 100%;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" value="<?php echo $fetch_cart['id']; ?>" name="cart_id">
                    <div class="quantity-control">
                        <label>Số lượng:</label>
                        <input type="number" min="1" max="99" value="<?php echo $fetch_cart['quantity']; ?>" name="cart_quantity">
                        <button type="submit" name="update_quantity" class="update-btn">
                            <i class="fas fa-sync-alt"></i> Cập nhật
                        </button>
                    </div>
                </form>

                <div class="item-subtotal">
                    Thành tiền: <span><?php echo number_format($sub_total, 0, ',', '.'); ?>₫</span>
                </div>
                
                <!-- Nút thanh toán riêng cho sản phẩm này -->
                <a href="./checkout.php?items=<?php echo $fetch_cart['id']; ?>" class="item-checkout-btn">
                    <i class="fas fa-credit-card"></i> Mua ngay
                </a>
            </div>
        </div>
        <?php
            }
        ?>
    </div>

    <div class="cart-summary">
        <h2 class="summary-header">Tổng giỏ hàng</h2>
        
        <!-- Voucher Section - Shopee Style -->
        <div class="voucher-section">
            <div class="voucher-section-header">
                <div class="vsh-left">
                    <i class="fas fa-ticket-alt"></i>
                    <span>FlowerShop Voucher</span>
                </div>
                <a href="./voucher_center.php" class="vsh-link">
                    <span>Kho voucher</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            
            <?php 
            $applied_voucher = $_SESSION['applied_voucher'] ?? null;
            $discount_amount = 0;
            
            if($applied_voucher):
                // Tính lại discount với tổng mới
                $recalc = validate_voucher($conn, $applied_voucher['code'], $user_id, $grand_total);
                if($recalc['success']){
                    $discount_amount = $recalc['discount'];
                    $_SESSION['applied_voucher']['discount'] = $discount_amount;
                } else {
                    unset($_SESSION['applied_voucher']);
                    $applied_voucher = null;
                }
            endif;
            
            if($applied_voucher): 
            ?>
            <!-- Voucher đã áp dụng - Style Shopee -->
            <div class="voucher-applied-card">
                <div class="vac-left">
                    <div class="vac-badge <?php echo ($applied_voucher['discount_type'] ?? 'fixed') === 'percent' ? 'percent' : 'fixed'; ?>">
                        <?php if(($applied_voucher['discount_type'] ?? 'fixed') === 'percent'): ?>
                            <span class="badge-value"><?php echo $applied_voucher['discount_value'] ?? ''; ?>%</span>
                            <span class="badge-label">GIẢM</span>
                        <?php else: ?>
                            <span class="badge-value"><?php echo number_format($applied_voucher['discount_value'] ?? 0, 0, ',', '.'); ?>₫</span>
                            <span class="badge-label">GIẢM</span>
                        <?php endif; ?>
                    </div>
                    <div class="vac-info">
                        <div class="vac-code"><i class="fas fa-check-circle"></i> <?php echo e($applied_voucher['code']); ?></div>
                        <div class="vac-name"><?php echo e($applied_voucher['name'] ?? 'Mã giảm giá'); ?></div>
                        <div class="vac-saving">
                            <i class="fas fa-piggy-bank"></i> Tiết kiệm: <strong><?php echo number_format($discount_amount, 0, ',', '.'); ?>₫</strong>
                        </div>
                    </div>
                </div>
                <button class="vac-remove" onclick="removeVoucher()">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
            
            <!-- Savings Banner -->
            <?php if($discount_amount > 0): ?>
            <div class="savings-banner">
                <i class="fas fa-gift"></i>
                <span class="savings-text">Bạn đang tiết kiệm <span class="savings-amount"><?php echo number_format($discount_amount, 0, ',', '.'); ?>₫</span> với voucher này!</span>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <!-- Chưa có voucher - Hiển thị danh sách -->
            <div class="voucher-chips">
                <div class="voucher-chip active" data-filter="all">Tất cả</div>
                <div class="voucher-chip" data-filter="percent">% Giảm giá</div>
                <div class="voucher-chip" data-filter="fixed">Giảm tiền</div>
                <div class="voucher-chip" data-filter="freeship">Freeship</div>
            </div>
            
            <div class="voucher-input-group">
                <div class="vig-icon"><i class="fas fa-ticket-alt"></i></div>
                <input type="text" id="voucher-code" placeholder="Nhập mã voucher..." maxlength="20">
                <button onclick="applyVoucher()" class="vig-btn">
                    Áp dụng
                </button>
            </div>
            
            <div class="voucher-dropdown" id="voucher-dropdown">
                <div class="voucher-dropdown-header">
                    <span><i class="fas fa-tags"></i> Chọn voucher có sẵn</span>
                    <span class="vdh-count" id="voucher-count">0 voucher</span>
                </div>
                <div class="voucher-dropdown-list" id="voucher-list">
                    <!-- Loaded via AJAX -->
                    <div class="voucher-loading">
                        <i class="fas fa-spinner fa-spin"></i> Đang tải voucher...
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Summary -->
        <div class="selected-summary" id="selected-summary">
            <div class="summary-row selected-items-row">
                <span><i class="fas fa-check-circle"></i> Sản phẩm đã chọn:</span>
                <span id="selected-items-count">0 sản phẩm</span>
            </div>
            <div class="summary-row selected-total-row">
                <span><i class="fas fa-shopping-basket"></i> Tổng tiền đã chọn:</span>
                <span id="selected-items-total" class="selected-total-value">0₫</span>
            </div>
        </div>
        
        <div class="summary-row">
            <span>Tạm tính (tất cả):</span>
            <span><?php echo number_format($grand_total, 0, ',', '.'); ?>₫</span>
        </div>
        
        <?php if($discount_amount > 0): ?>
        <div class="summary-row discount">
            <span><i class="fas fa-tag"></i> Giảm giá:</span>
            <span class="value">-<?php echo number_format($discount_amount, 0, ',', '.'); ?>₫</span>
        </div>
        <?php endif; ?>
        
        <?php $final_total = $grand_total - $discount_amount; ?>
        
        <div class="summary-total">
            <span class="summary-total-label">Tổng cộng:</span>
            <span class="summary-total-amount"><?php echo number_format($final_total, 0, ',', '.'); ?>₫</span>
        </div>

        <div class="summary-actions">
            <a href="./shop.php" class="cart-btn continue-shopping">
                <i class="fas fa-arrow-left"></i> Tiếp tục mua
            </a>
            <a href="./checkout.php" id="checkout-selected-btn" class="cart-btn checkout-btn <?php echo ($grand_total > 0)?'':'disabled' ?>" onclick="return checkoutSelected(event)">
                <i class="fas fa-credit-card"></i> Thanh toán đã chọn
            </a>
            <a href="./cart.php?delete_all" class="cart-btn clear-cart-btn <?php echo ($grand_total > 0)?'':'disabled' ?>" onclick="return confirm('Xóa toàn bộ sản phẩm khỏi giỏ hàng?');">
                <i class="fas fa-trash-alt"></i> Xóa tất cả
            </a>
        </div>
    </div>

    <?php
        } else {
    ?>
    <div class="empty-cart">
        <i class="fas fa-shopping-cart"></i>
        <h3>Giỏ hàng của bạn đang trống!</h3>
        <p>Hãy khám phá và thêm sản phẩm yêu thích vào giỏ hàng</p>
        <a href="./shop.php">
            <i class="fas fa-shopping-bag"></i> Mua sắm ngay
        </a>
    </div>
    <?php
        }
    ?>

</div>

<?php @include '../footer.php'; ?>

<script src="../js/script.js"></script>

<script>
// Voucher functions - Shopee Style
let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    // Load voucher list on page load
    loadVoucherList();
    
    // Setup filter chips
    document.querySelectorAll('.voucher-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            document.querySelectorAll('.voucher-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            loadVoucherList();
        });
    });
});

function applyVoucher() {
    const code = document.getElementById('voucher-code').value.trim().toUpperCase();
    if(!code) {
        showToast('Vui lòng nhập mã voucher!', 'warning');
        return;
    }
    
    const btn = document.querySelector('.vig-btn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    const orderTotal = <?php echo $grand_total; ?>;
    
    fetch('../ajax_voucher.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=apply&code=${encodeURIComponent(code)}&order_total=${orderTotal}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            showToast('🎉 ' + data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('❌ ' + data.message, 'error');
            btn.innerHTML = 'Áp dụng';
            btn.disabled = false;
        }
    })
    .catch(err => {
        showToast('Có lỗi xảy ra!', 'error');
        btn.innerHTML = 'Áp dụng';
        btn.disabled = false;
    });
}

function removeVoucher() {
    if(!confirm('Bạn có chắc muốn hủy voucher này?')) return;
    
    fetch('../ajax_voucher.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=remove'
    })
    .then(res => res.json())
    .then(data => {
        showToast('Đã hủy voucher', 'info');
        setTimeout(() => location.reload(), 500);
    });
}

function loadVoucherList() {
    const orderTotal = <?php echo $grand_total; ?>;
    const list = document.getElementById('voucher-list');
    const countEl = document.getElementById('voucher-count');
    
    list.innerHTML = '<div class="voucher-loading"><i class="fas fa-spinner fa-spin"></i> Đang tải voucher...</div>';
    
    fetch(`../ajax_voucher.php?action=list&order_total=${orderTotal}&filter=${currentFilter}`)
    .then(res => res.json())
    .then(data => {
        console.log('Voucher API response:', data); // Debug
        
        if(data.success && data.vouchers) {
            // Filter vouchers
            let vouchers = data.vouchers;
            if(currentFilter !== 'all') {
                vouchers = vouchers.filter(v => {
                    if(currentFilter === 'freeship') return v.code.toLowerCase().includes('ship') || v.name.toLowerCase().includes('ship');
                    return v.discount_type === currentFilter;
                });
            }
            
            if(countEl) countEl.textContent = vouchers.length + ' voucher';
            
            if(vouchers.length === 0) {
                list.innerHTML = `
                    <div class="voucher-empty">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Không có voucher phù hợp</p>
                        <a href="./voucher_center.php">Khám phá kho voucher</a>
                    </div>
                `;
                return;
            }
            
            list.innerHTML = vouchers.map(v => {
                const isPercent = v.discount_type === 'percent';
                const badgeClass = isPercent ? 'percent' : 'fixed';
                const badgeValue = isPercent ? v.discount_value + '%' : formatPrice(v.discount_value);
                const maxDiscount = isPercent && v.max_discount ? `Tối đa ${formatPrice(v.max_discount)}` : '';
                const minOrder = v.min_order_value > 0 ? `Đơn từ ${formatPrice(v.min_order_value)}` : 'Không giới hạn';
                
                // Calculate progress
                const progress = v.usage_limit > 0 ? Math.min(100, (v.usage_count / v.usage_limit) * 100) : 0;
                const remaining = v.usage_limit > 0 ? v.usage_limit - v.usage_count : '∞';
                
                // Check if expiring soon (within 3 days)
                const expireDate = new Date(v.end_date);
                const now = new Date();
                const daysLeft = Math.ceil((expireDate - now) / (1000 * 60 * 60 * 24));
                const expiringSoon = daysLeft <= 3 && daysLeft > 0;
                
                return `
                <div class="voucher-list-item ${v.can_use ? '' : 'disabled'}">
                    <div class="vli-left">
                        <div class="vli-badge ${badgeClass}">
                            <span class="badge-value">${badgeValue}</span>
                            <span class="badge-label">GIẢM</span>
                        </div>
                    </div>
                    <div class="vli-center">
                        <div class="vli-code">${v.code}</div>
                        <div class="vli-name">${v.name}</div>
                        <div class="vli-conditions">
                            <span><i class="fas fa-shopping-cart"></i> ${minOrder}</span>
                            ${maxDiscount ? `<span><i class="fas fa-hand-holding-usd"></i> ${maxDiscount}</span>` : ''}
                        </div>
                        ${v.usage_limit > 0 ? `
                        <div class="vli-progress">
                            <div class="vli-progress-bar" style="width: ${progress}%"></div>
                        </div>
                        <div class="vli-remaining">Còn ${remaining} lượt</div>
                        ` : ''}
                        ${expiringSoon ? `<div class="vli-expiring"><i class="fas fa-clock"></i> Còn ${daysLeft} ngày</div>` : ''}
                    </div>
                    <div class="vli-right">
                        ${v.can_use ? `
                            <button class="vli-btn" onclick="selectVoucher('${v.code}')">Dùng ngay</button>
                        ` : `
                            <button class="vli-btn disabled" disabled>${v.reason || 'Không đủ ĐK'}</button>
                        `}
                    </div>
                </div>
                `;
            }).join('');
        } else {
            // Không có voucher hoặc lỗi
            if(countEl) countEl.textContent = '0 voucher';
            list.innerHTML = `
                <div class="voucher-empty">
                    <i class="fas fa-ticket-alt"></i>
                    <p>${data.message || 'Không có voucher phù hợp'}</p>
                    <a href="./voucher_center.php">Khám phá kho voucher</a>
                </div>
            `;
        }
    })
    .catch(err => {
        console.error('Voucher load error:', err); // Debug
        list.innerHTML = '<div class="voucher-empty"><p>Không thể tải voucher</p></div>';
    });
}

function selectVoucher(code) {
    document.getElementById('voucher-code').value = code;
    applyVoucher();
}

function formatPrice(num) {
    return new Intl.NumberFormat('vi-VN').format(num) + '₫';
}

// Toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = message;
    toast.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#17a2b8'};
        color: ${type === 'warning' ? '#333' : '#fff'};
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        font-weight: 500;
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Enter key to apply voucher
document.getElementById('voucher-code')?.addEventListener('keypress', function(e) {
    if(e.key === 'Enter') applyVoucher();
});

// ===== CHỌN SẢN PHẨM THANH TOÁN =====
let originalTotal = <?php echo $grand_total; ?>;

// Chọn/bỏ chọn tất cả
function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.cart-checkbox');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
        cb.closest('.cart-item').classList.toggle('selected', cb.checked);
    });
    
    updateSelectedTotal();
}

// Cập nhật tổng tiền theo sản phẩm được chọn
function updateSelectedTotal() {
    const checkboxes = document.querySelectorAll('.cart-checkbox');
    const selectAll = document.getElementById('select-all');
    let selectedTotal = 0;
    let selectedCount = 0;
    let selectedIds = [];
    
    checkboxes.forEach(cb => {
        const cartItem = cb.closest('.cart-item');
        if(cb.checked) {
            selectedTotal += parseInt(cb.dataset.price);
            selectedCount++;
            selectedIds.push(cb.value);
            cartItem.classList.add('selected');
        } else {
            cartItem.classList.remove('selected');
        }
    });
    
    // Cập nhật select all checkbox
    selectAll.checked = selectedCount === checkboxes.length;
    selectAll.indeterminate = selectedCount > 0 && selectedCount < checkboxes.length;
    
    // Cập nhật hiển thị số lượng đã chọn
    document.getElementById('selected-count').textContent = `Đã chọn: ${selectedCount} sản phẩm`;
    document.getElementById('selected-items-count').textContent = `${selectedCount} sản phẩm`;
    document.getElementById('selected-items-total').textContent = formatPrice(selectedTotal);
    
    // Cập nhật tổng tiền cuối cùng
    const summaryTotal = document.querySelector('.summary-total-amount');
    
    if(summaryTotal) {
        // Nếu có voucher, tính lại
        const discountEl = document.querySelector('.summary-row.discount .value');
        let discount = 0;
        if(discountEl) {
            discount = parseInt(discountEl.textContent.replace(/[^\d]/g, '')) || 0;
        }
        const finalTotal = Math.max(0, selectedTotal - discount);
        summaryTotal.textContent = formatPrice(finalTotal);
    }
    
    // Lưu selected items vào session storage
    sessionStorage.setItem('selectedCartItems', JSON.stringify(selectedIds));
    
    // Cập nhật nút thanh toán
    const checkoutBtn = document.getElementById('checkout-selected-btn');
    if(checkoutBtn) {
        if(selectedCount === 0) {
            checkoutBtn.classList.add('disabled');
            checkoutBtn.innerHTML = '<i class="fas fa-credit-card"></i> Chọn sản phẩm để thanh toán';
        } else {
            checkoutBtn.classList.remove('disabled');
            checkoutBtn.innerHTML = `<i class="fas fa-credit-card"></i> Thanh toán ${selectedCount} sản phẩm`;
            checkoutBtn.href = `./checkout.php?items=${selectedIds.join(',')}`;
        }
    }
}

// Xử lý checkout sản phẩm đã chọn
function checkoutSelected(event) {
    const checkboxes = document.querySelectorAll('.cart-checkbox:checked');
    if(checkboxes.length === 0) {
        event.preventDefault();
        showToast('Vui lòng chọn ít nhất một sản phẩm để thanh toán!', 'warning');
        return false;
    }
    return true;
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', function() {
    // Mặc định chọn tất cả
    const checkboxes = document.querySelectorAll('.cart-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = true;
        cb.closest('.cart-item')?.classList.add('selected');
    });
    document.getElementById('select-all')?.checked === true;
    if(document.getElementById('select-all')) {
        document.getElementById('select-all').checked = true;
    }
    updateSelectedTotal();
});
</script>

</body>
</html>



