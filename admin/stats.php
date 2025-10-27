<?php
header('Content-Type: text/html; charset=UTF-8');
@include '../config.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:../auth/login.php');
   exit;
}

// Lấy khoảng thời gian từ form
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$date_filter = '';

switch($period) {
    case 'today':
        $date_filter = "AND DATE(placed_on) = CURDATE()";
        break;
    case 'week':
        $date_filter = "AND WEEK(placed_on) = WEEK(NOW()) AND YEAR(placed_on) = YEAR(NOW())";
        break;
    case 'month':
        $date_filter = "AND MONTH(placed_on) = MONTH(NOW()) AND YEAR(placed_on) = YEAR(NOW())";
        break;
    case 'year':
        $date_filter = "AND YEAR(placed_on) = YEAR(NOW())";
        break;
}

// Thống kê tổng quan
$total_revenue = 0;
$select_revenue = mysqli_query($conn, "SELECT SUM(total_price) as total FROM `orders` WHERE payment_status = 'completed' $date_filter");
if($row = mysqli_fetch_assoc($select_revenue)){
    $total_revenue = $row['total'];
}

$total_orders = 0;
$select_orders = mysqli_query($conn, "SELECT COUNT(*) as total FROM `orders` WHERE 1 $date_filter");
if($row = mysqli_fetch_assoc($select_orders)){
    $total_orders = $row['total'];
}

$completed_orders = 0;
$select_completed = mysqli_query($conn, "SELECT COUNT(*) as total FROM `orders` WHERE delivery_status = 'Đã giao' $date_filter");
if($row = mysqli_fetch_assoc($select_completed)){
    $completed_orders = $row['total'];
}

// Doanh thu theo ngày trong tháng (cho biểu đồ)
$revenue_by_day = [];
$days_query = mysqli_query($conn, "SELECT DATE(placed_on) as order_date, SUM(total_price) as daily_revenue 
    FROM `orders` 
    WHERE payment_status = 'completed' AND MONTH(placed_on) = MONTH(NOW()) AND YEAR(placed_on) = YEAR(NOW())
    GROUP BY DATE(placed_on)
    ORDER BY order_date ASC");

while($row = mysqli_fetch_assoc($days_query)){
    $revenue_by_day[] = $row;
}

// Sản phẩm bán chạy
$top_products = [];
$products_query = mysqli_query($conn, "
    SELECT p.name, p.image, p.price, COUNT(oi.id) as total_sold, SUM(oi.quantity) as total_quantity
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.payment_status = 'completed' $date_filter
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 5
");

while($row = mysqli_fetch_assoc($products_query)){
    $top_products[] = $row;
}

// Thống kê theo phương thức thanh toán
$payment_methods = [];
$payment_query = mysqli_query($conn, "SELECT payment_method, COUNT(*) as count, SUM(total_price) as revenue
    FROM `orders`
    WHERE payment_status = 'completed' $date_filter
    GROUP BY payment_method");

while($row = mysqli_fetch_assoc($payment_query)){
    $payment_methods[] = $row;
}

// Trạng thái đơn hàng
$order_status = [];
$status_query = mysqli_query($conn, "SELECT delivery_status, COUNT(*) as count
    FROM `orders`
    WHERE 1 $date_filter
    GROUP BY delivery_status");

while($row = mysqli_fetch_assoc($status_query)){
    $order_status[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Thống Kê & Báo Cáo</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <link rel="stylesheet" href="../css/admin-enhanced.css">
   
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   
   <style>
   .stats-container {
       padding: 2rem;
       max-width: 1400px;
       margin: 0 auto;
   }
   
   .period-filter {
       background: #fff;
       padding: 2rem;
       border-radius: 10px;
       margin-bottom: 2rem;
       box-shadow: 0 5px 15px rgba(0,0,0,0.1);
   }
   
   .period-filter h3 {
       margin-bottom: 1.5rem;
       color: #333;
   }
   
   .period-buttons {
       display: flex;
       gap: 1rem;
       flex-wrap: wrap;
   }
   
   .period-btn {
       padding: 1rem 2rem;
       border: none;
       background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
       color: white;
       border-radius: 5px;
       cursor: pointer;
       text-decoration: none;
       transition: all 0.3s;
   }
   
   .period-btn:hover {
       transform: translateY(-2px);
       box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
   }
   
   .period-btn.active {
       background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
   }
   
   .overview-stats {
       display: grid;
       grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
       gap: 2rem;
       margin-bottom: 3rem;
   }
   
   .stat-card {
       background: white;
       padding: 2rem;
       border-radius: 10px;
       box-shadow: 0 5px 15px rgba(0,0,0,0.1);
       text-align: center;
   }
   
   .stat-card i {
       font-size: 3rem;
       margin-bottom: 1rem;
   }
   
   .stat-card.revenue {
       background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
       color: white;
   }
   
   .stat-card.orders {
       background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
       color: white;
   }
   
   .stat-card.completed {
       background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
       color: white;
   }
   
   .stat-number {
       font-size: 2.5rem;
       font-weight: bold;
       margin: 1rem 0;
   }
   
   .stat-label {
       font-size: 1.2rem;
       opacity: 0.9;
   }
   
   .charts-section {
       display: grid;
       grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
       gap: 2rem;
       margin-bottom: 3rem;
   }
   
   .chart-card {
       background: white;
       padding: 2rem;
       border-radius: 10px;
       box-shadow: 0 5px 15px rgba(0,0,0,0.1);
   }
   
   .chart-card h3 {
       margin-bottom: 2rem;
       color: #333;
       text-align: center;
   }
   
   .top-products {
       background: white;
       padding: 2rem;
       border-radius: 10px;
       box-shadow: 0 5px 15px rgba(0,0,0,0.1);
   }
   
   .top-products h3 {
       margin-bottom: 2rem;
       color: #333;
   }
   
   .product-item {
       display: flex;
       align-items: center;
       gap: 1.5rem;
       padding: 1.5rem;
       margin-bottom: 1rem;
       background: #f8f9fa;
       border-radius: 8px;
       transition: all 0.3s;
   }
   
   .product-item:hover {
       transform: translateX(5px);
       box-shadow: 0 3px 10px rgba(0,0,0,0.1);
   }
   
   .product-item img {
       width: 60px;
       height: 60px;
       object-fit: cover;
       border-radius: 5px;
   }
   
   .product-info {
       flex: 1;
   }
   
   .product-info h4 {
       margin-bottom: 0.5rem;
       color: #333;
   }
   
   .product-info p {
       color: #666;
       font-size: 0.9rem;
   }
   
   .product-sales {
       text-align: right;
   }
   
   .product-sales .quantity {
       font-size: 1.5rem;
       font-weight: bold;
       color: #667eea;
   }
   
   .product-sales .label {
       font-size: 0.9rem;
       color: #666;
   }
   </style>
</head>
<body>
   
<?php @include './header.php'; ?>

<section class="stats-container">

   <div class="period-filter">
       <h3>Chọn khoảng thời gian</h3>
       <div class="period-buttons">
           <a href="?period=today" class="period-btn <?php echo $period == 'today' ? 'active' : ''; ?>">Hôm nay</a>
           <a href="?period=week" class="period-btn <?php echo $period == 'week' ? 'active' : ''; ?>">Tuần này</a>
           <a href="?period=month" class="period-btn <?php echo $period == 'month' ? 'active' : ''; ?>">Tháng này</a>
           <a href="?period=year" class="period-btn <?php echo $period == 'year' ? 'active' : ''; ?>">Năm này</a>
       </div>
   </div>

   <div class="overview-stats">
       <div class="stat-card revenue">
           <i class="fas fa-dollar-sign"></i>
           <div class="stat-number"><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</div>
           <div class="stat-label">Tổng doanh thu</div>
       </div>
       
       <div class="stat-card orders">
           <i class="fas fa-shopping-cart"></i>
           <div class="stat-number"><?php echo $total_orders; ?></div>
           <div class="stat-label">Tổng đơn hàng</div>
       </div>
       
       <div class="stat-card completed">
           <i class="fas fa-check-circle"></i>
           <div class="stat-number"><?php echo $completed_orders; ?></div>
           <div class="stat-label">Đơn hoàn thành</div>
       </div>
   </div>

   <div class="charts-section">
       <div class="chart-card">
           <h3>Doanh thu theo ngày</h3>
           <h3>Doanh thu theo ng�y</h3>
           <canvas id="revenueChart"></canvas>
       </div>

       <div class="chart-card">
           <h3>Phương thức thanh toán</h3>
           <canvas id="paymentChart"></canvas>
       </div>

       <div class="chart-card">
           <h3>Trạng thái đơn hàng</h3>
           <canvas id="statusChart"></canvas>
       </div>
   </div>

   <div class="top-products">
       <h3>Top 5 sản phẩm bán chạy</h3>
       <?php if(!empty($top_products)): ?>
           <?php foreach($top_products as $product): ?>
           <div class="product-item">
               <img src="../../assets/uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
               <div class="product-info">
                   <h4><?php echo $product['name']; ?></h4>
                   <p>Giá: <?php echo number_format($product['price'], 0, ',', '.'); ?>đ | Đã bán: <?php echo $product['total_sold']; ?> đơn</p>
               </div>
               <div class="product-sales">
                   <div class="quantity"><?php echo $product['total_quantity']; ?></div>
                   <div class="label">Sản phẩm</div>
               </div>
           </div>
           <?php endforeach; ?>
       <?php else: ?>
           <p style="text-align: center; color: #666;">Chưa có dữ liệu bán hàng</p>
       <?php endif; ?>
   </div>

</section>

<script>
// Dữ liệu cho biểu đồ doanh thu
const revenueData = <?php echo json_encode($revenue_by_day); ?>;
const revenueLabels = revenueData.map(item => item.order_date);
const revenueValues = revenueData.map(item => parseFloat(item.daily_revenue));

const revenueChart = new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: revenueLabels,
        datasets: [{
            label: 'Doanh thu (d)',
            data: revenueValues,
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + 'd';
                    }
                }
            }
        }
    }
});

// D? li?u cho bi?u d? phuong th?c thanh to�n
const paymentData = <?php echo json_encode($payment_methods); ?>;
const paymentLabels = paymentData.map(item => {
    const names = {
        'cod': 'Ti?n m?t',
        'momo': 'MoMo',
        'vnpay': 'VNPay',
        'banking': 'Chuy?n kho?n'
    };
    return names[item.payment_method] || item.payment_method;
});
const paymentValues = paymentData.map(item => parseInt(item.count));

const paymentChart = new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
        labels: paymentLabels,
        datasets: [{
            data: paymentValues,
            backgroundColor: [
                'rgba(102, 126, 234, 0.8)',
                'rgba(240, 147, 251, 0.8)',
                'rgba(79, 172, 254, 0.8)',
                'rgba(245, 87, 108, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// D? li?u cho bi?u d? tr?ng th�i don h�ng
const statusData = <?php echo json_encode($order_status); ?>;
const statusLabels = statusData.map(item => item.delivery_status);
const statusValues = statusData.map(item => parseInt(item.count));

const statusChart = new Chart(document.getElementById('statusChart'), {
    type: 'bar',
    data: {
        labels: statusLabels,
        datasets: [{
            label: 'S? don h�ng',
            data: statusValues,
            backgroundColor: [
                'rgba(102, 126, 234, 0.8)',
                'rgba(240, 147, 251, 0.8)',
                'rgba(79, 172, 254, 0.8)',
                'rgba(245, 87, 108, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<script src="../../js/admin_script.js"></script>

</body>
</html>

