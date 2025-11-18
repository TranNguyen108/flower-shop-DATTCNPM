<?php
/**
 * 🎟️ Admin - Quản lý Voucher
 */

@include '../config.php';
require_once '../includes/voucher_functions.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:../auth/login.php');
   exit;
}

// Khởi tạo bảng
init_voucher_table($conn);

$message = [];

// Thêm voucher mới
if(isset($_POST['add_voucher'])){
    $code = strtoupper(sanitize_input($_POST['code']));
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description'] ?? '');
    $discount_type = sanitize_input($_POST['discount_type']);
    $discount_value = (float)$_POST['discount_value'];
    $min_order_value = (float)($_POST['min_order_value'] ?? 0);
    $max_discount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
    $usage_limit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
    $user_limit = (int)($_POST['user_limit'] ?? 1);
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    
    // Check code exists
    $check = mysqli_query($conn, "SELECT id FROM vouchers WHERE code = '$code'");
    if(mysqli_num_rows($check) > 0){
        $message[] = 'Mã voucher đã tồn tại!';
    } else {
        $sql = "INSERT INTO vouchers (code, name, description, discount_type, discount_value, min_order_value, max_discount, usage_limit, user_limit, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdddiiiss", $code, $name, $description, $discount_type, $discount_value, $min_order_value, $max_discount, $usage_limit, $user_limit, $start_date, $end_date);
        
        if($stmt->execute()){
            $message[] = 'Thêm voucher thành công!';
        } else {
            $message[] = 'Có lỗi xảy ra!';
        }
    }
}

// Xóa voucher
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM vouchers WHERE id = '$id'");
    header('location: vouchers.php');
    exit;
}

// Toggle active
if(isset($_GET['toggle'])){
    $id = (int)$_GET['toggle'];
    mysqli_query($conn, "UPDATE vouchers SET is_active = NOT is_active WHERE id = '$id'");
    header('location: vouchers.php');
    exit;
}

// Lấy danh sách vouchers
$vouchers = mysqli_query($conn, "SELECT * FROM vouchers ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Voucher - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        .voucher-page {
            padding: 2rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            color: #2d3436;
        }
        
        .btn-add {
            padding: 12px 25px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .voucher-table {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .voucher-table th, .voucher-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .voucher-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2d3436;
        }
        
        .voucher-code {
            font-family: monospace;
            font-weight: bold;
            font-size: 1.1rem;
            color: #667eea;
            background: #667eea15;
            padding: 5px 10px;
            border-radius: 5px;
        }
        
        .voucher-value {
            font-weight: 600;
            color: #e74c3c;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-percent {
            background: #cce5ff;
            color: #004085;
        }
        
        .badge-fixed {
            background: #fff3cd;
            color: #856404;
        }
        
        .action-btns {
            display: flex;
            gap: 8px;
        }
        
        .action-btns a, .action-btns button {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        
        .btn-toggle {
            background: #17a2b8;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #636e72;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3436;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }
        
        .usage-info {
            font-size: 0.9rem;
            color: #636e72;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3436;
        }
        
        .stat-card .label {
            color: #636e72;
        }
        
        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr 1fr;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php @include './header.php'; ?>

<?php
if(!empty($message)){
    foreach($message as $msg){
        echo '<div class="message"><span>'.e($msg).'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
    }
}
?>

<section class="voucher-page">
    <div class="page-header">
        <h1><i class="fas fa-ticket-alt" style="color:#667eea;"></i> Quản lý Voucher</h1>
        <button class="btn-add" onclick="document.getElementById('addModal').classList.add('show')">
            <i class="fas fa-plus"></i> Thêm Voucher
        </button>
    </div>
    
    <!-- Stats -->
    <?php
    $total_vouchers = mysqli_num_rows($vouchers);
    $active_vouchers = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM vouchers WHERE is_active = 1"));
    $total_used = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(used_count) as total FROM vouchers"))['total'] ?? 0;
    ?>
    <div class="stats-cards">
        <div class="stat-card">
            <i class="fas fa-ticket-alt" style="color:#667eea;"></i>
            <div class="value"><?php echo $total_vouchers; ?></div>
            <div class="label">Tổng voucher</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle" style="color:#28a745;"></i>
            <div class="value"><?php echo $active_vouchers; ?></div>
            <div class="label">Đang hoạt động</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-ban" style="color:#dc3545;"></i>
            <div class="value"><?php echo $total_vouchers - $active_vouchers; ?></div>
            <div class="label">Tạm dừng</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-shopping-cart" style="color:#ff9800;"></i>
            <div class="value"><?php echo $total_used; ?></div>
            <div class="label">Lượt sử dụng</div>
        </div>
    </div>
    
    <!-- Table -->
    <table class="voucher-table">
        <thead>
            <tr>
                <th>Mã</th>
                <th>Tên</th>
                <th>Loại</th>
                <th>Giá trị</th>
                <th>Đơn tối thiểu</th>
                <th>Sử dụng</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            mysqli_data_seek($vouchers, 0);
            while($v = mysqli_fetch_assoc($vouchers)): 
            ?>
            <tr>
                <td><span class="voucher-code"><?php echo e($v['code']); ?></span></td>
                <td><?php echo e($v['name']); ?></td>
                <td>
                    <span class="badge <?php echo $v['discount_type'] == 'percent' ? 'badge-percent' : 'badge-fixed'; ?>">
                        <?php echo $v['discount_type'] == 'percent' ? 'Phần trăm' : 'Cố định'; ?>
                    </span>
                </td>
                <td class="voucher-value">
                    <?php 
                    if($v['discount_type'] == 'percent'){
                        echo $v['discount_value'] . '%';
                        if($v['max_discount']) echo '<br><small style="color:#666;">Tối đa: ' . number_format($v['max_discount'], 0, ',', '.') . '₫</small>';
                    } else {
                        echo number_format($v['discount_value'], 0, ',', '.') . '₫';
                    }
                    ?>
                </td>
                <td>
                    <?php echo $v['min_order_value'] > 0 ? number_format($v['min_order_value'], 0, ',', '.') . '₫' : '-'; ?>
                </td>
                <td class="usage-info">
                    <?php echo $v['used_count']; ?><?php echo $v['usage_limit'] ? '/' . $v['usage_limit'] : '/∞'; ?>
                </td>
                <td>
                    <span class="badge <?php echo $v['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                        <?php echo $v['is_active'] ? 'Hoạt động' : 'Tạm dừng'; ?>
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="?toggle=<?php echo $v['id']; ?>" class="btn-toggle" title="Bật/Tắt">
                            <i class="fas fa-power-off"></i>
                        </a>
                        <a href="?delete=<?php echo $v['id']; ?>" class="btn-delete" onclick="return confirm('Xóa voucher này?');" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</section>

<!-- Add Modal -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>🎟️ Thêm Voucher Mới</h2>
            <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('show')">&times;</button>
        </div>
        
        <form action="" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Mã voucher *</label>
                    <input type="text" name="code" required placeholder="VD: SALE20" style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label>Tên voucher *</label>
                    <input type="text" name="name" required placeholder="VD: Giảm 20%">
                </div>
            </div>
            
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" rows="2" placeholder="Mô tả chi tiết..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Loại giảm giá *</label>
                    <select name="discount_type" required>
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Số tiền cố định (₫)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Giá trị giảm *</label>
                    <input type="number" name="discount_value" required min="0" step="0.01" placeholder="VD: 20 hoặc 50000">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Đơn tối thiểu (₫)</label>
                    <input type="number" name="min_order_value" min="0" value="0" placeholder="0 = không giới hạn">
                </div>
                <div class="form-group">
                    <label>Giảm tối đa (₫)</label>
                    <input type="number" name="max_discount" min="0" placeholder="Để trống = không giới hạn">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Tổng lượt sử dụng</label>
                    <input type="number" name="usage_limit" min="0" placeholder="Để trống = không giới hạn">
                </div>
                <div class="form-group">
                    <label>Số lần/người dùng</label>
                    <input type="number" name="user_limit" min="0" value="1" placeholder="1 = mỗi người dùng 1 lần">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Ngày bắt đầu</label>
                    <input type="datetime-local" name="start_date">
                </div>
                <div class="form-group">
                    <label>Ngày kết thúc</label>
                    <input type="datetime-local" name="end_date">
                </div>
            </div>
            
            <button type="submit" name="add_voucher" class="btn-submit">
                <i class="fas fa-plus"></i> Thêm Voucher
            </button>
        </form>
    </div>
</div>

<script>
// Close modal on outside click
document.getElementById('addModal').addEventListener('click', function(e) {
    if(e.target === this) this.classList.remove('show');
});
</script>

</body>
</html>
