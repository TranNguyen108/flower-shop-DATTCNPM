<?php

@include '../config.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:../auth/login.php');
   exit;
}

// Xử lý submit đánh giá
if(isset($_POST['submit_review'])){
   if(!verify_csrf_token($_POST['csrf_token'] ?? '')){
        $message[] = 'Yêu cầu không hợp lệ';
   } else {
      include 'submit_review.php';
   }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đơn hàng của bạn</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/style-enhanced.css">

   <!-- Leaflet CSS -->
   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
   
   <style>
   /* Star Rating */
   .star-rating {
      display: flex;
      flex-direction: row-reverse;
      justify-content: flex-end;
      gap: 0.5rem;
   }
   
   .star-rating input[type="radio"] {
      display: none;
   }
   
   .star-rating label {
      font-size: 3rem;
      color: #ddd;
      cursor: pointer;
      transition: all 0.3s;
   }
   
   .star-rating input[type="radio"]:checked ~ label,
   .star-rating label:hover,
   .star-rating label:hover ~ label {
      color: #feca57;
   }
   
   /* Emoji icon cho bản đồ */
   .emoji-icon {
      background: transparent !important;
      border: none !important;
   }
   
   /* Map container */
   .leaflet-container {
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
   }
   </style>
</head>
<body>

<?php @include '../header.php'; ?>

<section class="heading">
    <h3>Đơn hàng của bạn</h3>
    <p><a href="./home.php">Trang chủ</a> / Đơn hàng</p>
</section>

<section class="placed-orders">

    <h1 class="title">Các đơn hàng đã đặt</h1>

    <div class="box-container">

    <?php
        $select_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE user_id = '$user_id'") or die('Lỗi truy vấn dữ liệu đơn hàng');

        if(mysqli_num_rows($select_orders) > 0){
            $undelivered_orders = [];

            while($fetch_orders = mysqli_fetch_assoc($select_orders)){
    ?>
        <div class="box">
            <p> Ngày đặt: <span><?php echo $fetch_orders['placed_on']; ?></span> </p>
            <p> Họ tên: <span><?php echo $fetch_orders['name']; ?></span> </p>
            <p> Số điện thoại: <span><?php echo $fetch_orders['number']; ?></span> </p>
            <p> Email: <span><?php echo $fetch_orders['email']; ?></span> </p>
            <p> Địa chỉ: <span><?php echo $fetch_orders['address']; ?></span> </p>
            <p> Phương thức thanh toán: 
               <span>
                  <?php
                     $method_vi = '';
                     switch($fetch_orders['method']){
                        case 'cash on delivery':
                                    $method_vi = 'Thanh toán khi nhận hàng';
                           break;
                        case 'credit card':
                                    $method_vi = 'Thẻ tín dụng';
                           break;
                        case 'momo':
                                    $method_vi = 'Ví MoMo';
                           break;
                        case 'bank transfer':
                                    $method_vi = 'Chuyển khoản ngân hàng';
                           break;
                        default:
                           $method_vi = $fetch_orders['method'];
                     }
                     echo $method_vi;
                  ?>
               </span>
            </p>
            <p> Sản phẩm đã đặt: <span><?php echo $fetch_orders['total_products']; ?></span> </p>
            <p> Tổng tiền: <span><?php echo number_format($fetch_orders['total_price'], 0, ',', '.') . ' ₫'; ?></span> </p>

            <p> Trạng thái thanh toán: <span style="color:<?php echo ($fetch_orders['payment_status'] == 'pending') ? 'tomato' : 'green'; ?>">
                <?php echo ($fetch_orders['payment_status'] == 'pending') ? 'Chưa thanh toán' : 'Đã thanh toán'; ?></span> </p>
            <p> Trạng thái giao hàng: <span style="color:blue;"><?php echo $fetch_orders['delivery_status']; ?></span></p>

            <?php if ($fetch_orders['delivery_status'] !== 'Đã giao') : ?>
                <div id="map_<?php echo $fetch_orders['id']; ?>" style="width: 100%; height: 200px; margin-top: 10px;"></div>

                <?php
                $undelivered_orders[] = [
                    'id' => $fetch_orders['id'],
                    'lat' => (float)$fetch_orders['delivery_lat'],
                    'lng' => (float)$fetch_orders['delivery_lng'],
                    'status' => $fetch_orders['delivery_status']
                ];
                ?>

            <?php else: ?>
                <p style="color:green; font-weight:bold;">Đơn hàng đã được giao.</p>
                
                <?php
                // Kiểm tra xem đã đánh giá chưa
                $order_id = $fetch_orders['id'];
                $check_review = mysqli_query($conn, "SELECT * FROM `order_reviews` WHERE order_id = '$order_id' AND user_id = '$user_id'");
                $existing_review = mysqli_fetch_assoc($check_review);
                ?>
                
                <!-- Nút mở form đánh giá -->
                <button onclick="toggleReviewForm(<?php echo $order_id; ?>)" class="btn" style="margin-top: 1rem;">
                    <?php echo $existing_review ? 'Chỉnh sửa đánh giá' : 'Đánh giá đơn hàng'; ?>
                </button>
                
                <!-- Form đánh giá -->
                <div id="review-form-<?php echo $order_id; ?>" style="display: none; margin-top: 2rem; padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                    <h3 style="margin-bottom: 1.5rem; color: #ff6b9d;">Đánh giá đơn hàng</h3>
                    <form action="" method="post" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Đánh giá sao:</label>
                            <div class="star-rating" id="star-rating-<?php echo $order_id; ?>">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>-<?php echo $order_id; ?>" 
                                        <?php echo ($existing_review && $existing_review['rating'] == $i) ? 'checked' : ''; ?> required>
                                    <label for="star<?php echo $i; ?>-<?php echo $order_id; ?>">?</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Nội dung đánh giá:</label>
                            <textarea name="message" rows="5" style="width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1.5rem;" placeholder="Chia s? tr?i nghi?m c?a b?n..." required><?php echo $existing_review ? $existing_review['message'] : ''; ?></textarea>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Hình ảnh (tối đa 5 ảnh, mỗi ảnh = 5MB):</label>
                            <input type="file" name="review_images[]" multiple accept="image/*" style="width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1.5rem;">
                            <?php if($existing_review && !empty($existing_review['images'])): ?>
                                <div style="margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                                    <?php 
                                    $images = explode(',', $existing_review['images']);
                                    foreach($images as $img): 
                                    ?>
                                        <img src="../assets/uploads/products/reviews/<?php echo $img; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #ff6b9d;">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Video (t?i da 2 video, m?i video = 50MB):</label>
                            <input type="file" name="review_videos[]" multiple accept="video/*" style="width: 100%; padding: 1rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1.5rem;">
                            <?php if($existing_review && !empty($existing_review['videos'])): ?>
                                <div style="margin-top: 1rem;">
                                    <?php 
                                    $videos = explode(',', $existing_review['videos']);
                                    foreach($videos as $vid): 
                                    ?>
                                        <video controls style="width: 200px; border-radius: 8px; border: 2px solid #ff6b9d; margin-right: 1rem;">
                                            <source src="../assets/uploads/products/reviews/<?php echo $vid; ?>">
                                        </video>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" name="submit_review" class="btn">Gửi đánh giá</button>
                            <button type="button" onclick="toggleReviewForm(<?php echo $order_id; ?>)" class="option-btn">H?y</button>
                        </div>
                    </form>
                </div>
                
                <!-- Hiển thị đánh giá đã có -->
                <?php if($existing_review): ?>
                <div style="margin-top: 2rem; padding: 2rem; background: white; border-radius: 12px; border: 2px solid #ff6b9d;">
                    <h4 style="color: #ff6b9d; margin-bottom: 1rem;">Đánh giá của bạn:</h4>
                    <div style="margin-bottom: 1rem;">
                        <span style="color: #feca57; font-size: 2rem;">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php echo $i <= $existing_review['rating'] ? '?' : '?'; ?>
                            <?php endfor; ?>
                        </span>
                    </div>
                    <p style="font-size: 1.5rem; margin-bottom: 1rem;"><?php echo $existing_review['message']; ?></p>
                    <?php if(!empty($existing_review['images'])): ?>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
                            <?php 
                            $images = explode(',', $existing_review['images']);
                            foreach($images as $img): 
                            ?>
                                <img src="../assets/uploads/products/reviews/<?php echo $img; ?>" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($existing_review['videos'])): ?>
                        <div style="margin-top: 1rem;">
                            <?php 
                            $videos = explode(',', $existing_review['videos']);
                            foreach($videos as $vid): 
                            ?>
                                <video controls style="max-width: 300px; border-radius: 8px; margin-right: 1rem;">
                                    <source src="../assets/uploads/products/reviews/<?php echo $vid; ?>">
                                </video>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php
            }
        }else{
            echo '<p class="empty">Bạn chưa đặt đơn hàng nào!</p>';
        }
    ?>
    </div>

</section>

<?php @include '../footer.php'; ?>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
function toggleReviewForm(orderId) {
   const form = document.getElementById('review-form-' + orderId);
   if(form.style.display === 'none' || form.style.display === '') {
      form.style.display = 'block';
      form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
   } else {
      form.style.display = 'none';
   }
}

document.addEventListener("DOMContentLoaded", function () {
   const storeLat = 14.054510;
   const storeLng = 109.042130;

   const undeliveredOrders = <?php echo json_encode($undelivered_orders ?? []); ?>;

   undeliveredOrders.forEach(order => {
      const map = L.map('map_' + order.id).setView([storeLat, storeLng], 15);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
         attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      const route = [
         [storeLat, storeLng],
         [storeLat + 0.0005, storeLng + 0.0005],
         [storeLat + 0.0010, storeLng + 0.0010],
         [order.lat, order.lng]
      ];

      // Dùng emoji icon thay vì file ảnh
      const motorbikeIcon = L.divIcon({
         html: '<div style="font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">🛵</div>',
         iconSize: [40, 40],
         iconAnchor: [20, 20],
         className: 'emoji-icon'
      });

      const storeIcon = L.divIcon({
         html: '<div style="font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">🏪</div>',
         iconSize: [35, 35],
         iconAnchor: [17, 17],
         className: 'emoji-icon'
      });

      L.marker([storeLat, storeLng], { icon: storeIcon })
        .addTo(map)
        .bindPopup('Cửa hàng (Cát Thành, Phù Cát, Bình Định)');

      let marker = L.marker(route[0], { icon: motorbikeIcon }).addTo(map);
    marker.bindPopup("Đang giao hàng").openPopup();

      let traveledPath = L.polyline([route[0]], {
         color: '#ff0000',
         weight: 5,
         opacity: 0.9,
         dashArray: null
      }).addTo(map);

      let index = 0;

      function moveMarker() {
         index++;
         if(index >= route.length) {
            marker.setLatLng(route[route.length - 1]);
            marker.bindPopup("Đang giao hàng").openPopup();
            return;
         }

         const latlng = route[index];
         marker.setLatLng(latlng);

         let latlngs = traveledPath.getLatLngs();
         latlngs.push(latlng);
         traveledPath.setLatLngs(latlngs);

         marker.bindPopup("Đang giao hàng").openPopup();
         map.panTo(latlng);

         setTimeout(moveMarker, 5000);
      }

      setTimeout(moveMarker, 5000);
   });
});
</script>

</body>
</html>


