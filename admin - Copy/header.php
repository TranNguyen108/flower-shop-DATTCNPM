<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">
   <div class="header-container">
      <!-- Logo & Brand -->
      <a href="dashboard.php" class="header-logo">
         <i class="fas fa-seedling"></i>
         <div class="logo-text">
            <span class="logo-main">FlowerShop</span>
            <span class="logo-sub">Admin Panel</span>
         </div>
      </a>

      <!-- Navigation Menu -->
      <nav class="header-nav">
         <a href="dashboard.php" class="nav-item">
            <i class="fas fa-tachometer-alt"></i>
            <span>Trang chủ</span>
         </a>
         <a href="products.php" class="nav-item">
            <i class="fas fa-box"></i>
            <span>Sản phẩm</span>
         </a>
         <a href="orders.php" class="nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>Đơn hàng</span>
         </a>
         <a href="vouchers.php" class="nav-item">
            <i class="fas fa-ticket-alt"></i>
            <span>Voucher</span>
         </a>
         <a href="stats.php" class="nav-item">
            <i class="fas fa-chart-line"></i>
            <span>Thống kê</span>
         </a>
         <a href="users.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Người dùng</span>
         </a>
         <a href="chat.php" class="nav-item">
            <i class="fas fa-comments"></i>
            <span>Chat</span>
         </a>
         <a href="reviews.php" class="nav-item">
            <i class="fas fa-star"></i>
            <span>Đánh giá</span>
         </a>
      </nav>

      <!-- User Actions -->
      <div class="header-actions">
         <div id="menu-btn" class="action-btn mobile-menu">
            <i class="fas fa-bars"></i>
         </div>
         <div id="user-btn" class="action-btn user-menu">
            <i class="fas fa-user-circle"></i>
            <span><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
         </div>
      </div>

      <!-- User Dropdown -->
      <div class="account-box">
         <div class="account-header">
            <i class="fas fa-user-shield"></i>
            <h3>Thông tin Admin</h3>
         </div>
         <div class="account-info">
            <p><i class="fas fa-user"></i> <strong>Tên:</strong> <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></p>
            <p><i class="fas fa-envelope"></i> <strong>Email:</strong> <?php echo $_SESSION['admin_email'] ?? ''; ?></p>
         </div>
         <a href="../auth/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
         </a>
      </div>
   </div>
</header>

<style>
/* Reset & Base */
* {
   margin: 0;
   padding: 0;
   box-sizing: border-box;
}

/* Header Container */
.header {
   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   box-shadow: 0 4px 20px rgba(0,0,0,0.1);
   position: sticky;
   top: 0;
   z-index: 1000;
}

.header-container {
   max-width: 1600px;
   margin: 0 auto;
   padding: 0 40px;
   display: flex;
   align-items: center;
   justify-content: space-between;
   height: 85px;
   gap: 35px;
}

/* Logo */
.header-logo {
   display: flex;
   align-items: center;
   gap: 15px;
   text-decoration: none;
   color: white;
   padding: 12px 25px;
   background: rgba(255,255,255,0.1);
   border-radius: 15px;
   transition: all 0.3s ease;
   flex-shrink: 0;
}

.header-logo:hover {
   background: rgba(255,255,255,0.2);
   transform: translateY(-2px);
}

.header-logo i {
   font-size: 2.5rem;
}

.logo-text {
   display: flex;
   flex-direction: column;
   line-height: 1.3;
}

.logo-main {
   font-size: 1.6rem;
   font-weight: 700;
   letter-spacing: 0.5px;
}

.logo-sub {
   font-size: 0.9rem;
   opacity: 0.9;
   font-weight: 400;
}

/* Navigation */
.header-nav {
   display: flex;
   align-items: center;
   gap: 8px;
   flex: 1;
   justify-content: center;
}

.nav-item {
   display: flex;
   align-items: center;
   gap: 10px;
   padding: 12px 20px;
   color: rgba(255,255,255,0.9);
   text-decoration: none;
   border-radius: 12px;
   font-size: 1.1rem;
   font-weight: 500;
   transition: all 0.3s ease;
   position: relative;
}

.nav-item i {
   font-size: 1.3rem;
}

.nav-item:hover {
   background: rgba(255,255,255,0.15);
   color: white;
   transform: translateY(-2px);
}

.nav-item.active {
   background: rgba(255,255,255,0.25);
   color: white;
}

/* Header Actions */
.header-actions {
   display: flex;
   align-items: center;
   gap: 15px;
}

.action-btn {
   display: flex;
   align-items: center;
   gap: 10px;
   padding: 12px 22px;
   background: rgba(255,255,255,0.1);
   color: white;
   border-radius: 25px;
   cursor: pointer;
   transition: all 0.3s ease;
   font-size: 1.05rem;
   font-weight: 500;
}

.action-btn:hover {
   background: rgba(255,255,255,0.2);
}

.action-btn i {
   font-size: 1.4rem;
}

.mobile-menu {
   display: none;
}

/* Account Dropdown */
.account-box {
   position: absolute;
   top: 95px;
   right: 40px;
   background: white;
   border-radius: 15px;
   box-shadow: 0 10px 40px rgba(0,0,0,0.15);
   width: 360px;
   padding: 0;
   opacity: 0;
   visibility: hidden;
   transform: translateY(-10px);
   transition: all 0.3s ease;
   overflow: hidden;
}

.account-box.active {
   opacity: 1;
   visibility: visible;
   transform: translateY(0);
}

.account-header {
   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   color: white;
   padding: 25px;
   display: flex;
   align-items: center;
   gap: 15px;
}

.account-header i {
   font-size: 2.3rem;
}

.account-header h3 {
   font-size: 1.4rem;
   font-weight: 600;
   margin: 0;
}

.account-info {
   padding: 25px;
}

.account-info p {
   display: flex;
   align-items: center;
   gap: 12px;
   padding: 12px 0;
   color: #2c3e50;
   font-size: 1.05rem;
   border-bottom: 1px solid #f0f0f0;
}

.account-info p:last-child {
   border-bottom: none;
}

.account-info p i {
   color: #667eea;
   width: 22px;
   font-size: 1.15rem;
}

.account-info strong {
   min-width: 60px;
   color: #6c757d;
}

.logout-btn {
   display: flex;
   align-items: center;
   justify-content: center;
   gap: 12px;
   padding: 18px;
   background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
   color: white;
   text-decoration: none;
   font-weight: 600;
   font-size: 1.15rem;
   transition: all 0.3s ease;
   margin: 0;
}

.logout-btn:hover {
   background: linear-gradient(135deg, #ff6a00 0%, #ee0979 100%);
}

.logout-btn i {
   font-size: 1.3rem;
}

/* Responsive */
@media (max-width: 1200px) {
   .nav-item span {
      display: none;
   }
   
   .nav-item {
      padding: 10px 12px;
   }
   
   .nav-item i {
      font-size: 1.3rem;
   }
}

@media (max-width: 768px) {
   .header-container {
      padding: 0 15px;
      height: 60px;
   }
   
   .logo-text {
      display: none;
   }
   
   .header-nav {
      position: fixed;
      top: 60px;
      left: -100%;
      width: 280px;
      height: calc(100vh - 60px);
      background: white;
      flex-direction: column;
      align-items: stretch;
      padding: 20px;
      gap: 10px;
      box-shadow: 4px 0 20px rgba(0,0,0,0.1);
      transition: left 0.3s ease;
      overflow-y: auto;
   }
   
   .header-nav.active {
      left: 0;
   }
   
   .nav-item {
      color: #2c3e50;
      padding: 15px;
      border-radius: 10px;
   }
   
   .nav-item span {
      display: inline;
   }
   
   .nav-item:hover {
      background: #f8f9fa;
   }
   
   .nav-item i {
      color: #667eea;
   }
   
   .mobile-menu {
      display: flex;
   }
   
   .user-menu span {
      display: none;
   }
   
   .account-box {
      right: 15px;
      width: calc(100% - 30px);
   }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // User dropdown toggle
   const userBtn = document.getElementById('user-btn');
   const accountBox = document.querySelector('.account-box');
   
   if(userBtn && accountBox) {
      userBtn.addEventListener('click', function(e) {
         e.stopPropagation();
         accountBox.classList.toggle('active');
      });
      
      document.addEventListener('click', function(e) {
         if(!accountBox.contains(e.target)) {
            accountBox.classList.remove('active');
         }
      });
   }
   
   // Mobile menu toggle
   const menuBtn = document.getElementById('menu-btn');
   const headerNav = document.querySelector('.header-nav');
   
   if(menuBtn && headerNav) {
      menuBtn.addEventListener('click', function() {
         headerNav.classList.toggle('active');
      });
   }
});
</script>

