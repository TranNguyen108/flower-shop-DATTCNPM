<section class="footer">

    <div class="box-container">

        <div class="box">
            <h3>Liên Kết Nhanh</h3>
            <a href="../pages/home.php">Trang Chủ</a>
            <a href="../pages/about.php">Giới Thiệu</a>
            <a href="../pages/contact.php">Liên Hệ</a>
            <a href="../pages/shop.php">Cửa Hàng</a>
        </div>

        <div class="box">
            <h3>Liên Kết Khác</h3>
            <a href="../auth/login.php">Đăng Nhập</a>
            <a href="../auth/register.php">Đăng Ký</a>
            <a href="../pages/orders.php">Đơn Hàng Của Tôi</a>
            <a href="../pages/cart.php">Giỏ Hàng Của Tôi</a>
        </div>

        <div class="box">
            <h3>Thông Tin Liên Hệ</h3>
            <p> <i class="fas fa-phone"></i> +0328130448 </p>
            <p> <i class="fas fa-phone"></i> +0336822136 </p>
            <p> <i class="fas fa-envelope"></i> ngomaitam@gmail.com </p>
            <p> <i class="fas fa-map-marker-alt"></i> Bình Định, Việt Nam - 123-456 </p>
        </div>

        <div class="box">
            <h3>Theo Dõi Chúng Tôi</h3>
            <a href="#"><i class="fab fa-facebook-f"></i>Facebook</a>
            <a href="#"><i class="fab fa-twitter"></i>Twitter</a>
            <a href="#"><i class="fab fa-instagram"></i>Instagram</a>
            <a href="#"><i class="fab fa-linkedin"></i>LinkedIn</a>
        </div>

    </div>

    <div class="credit">
        &copy; Bản Quyền @ <?php echo date('Y'); ?>
    </div>

</section>

<?php 
// Include chat widget for logged in users
if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'user'){
    include '../chat/chat_widget.php';
}
?>
