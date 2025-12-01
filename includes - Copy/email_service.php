<?php
/**
 * Email Service - PHPMailer Integration
 * Handles all email notifications for the Flower Store
 */

// Don't require config.php here - it's already loaded by main files

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Change this
define('SMTP_PASSWORD', 'your-app-password'); // Change this - Use App Password, not regular password
define('SMTP_FROM_EMAIL', 'noreply@flowerstore.com');
define('SMTP_FROM_NAME', 'Flower Store Vietnam');
define('SMTP_ENCRYPTION', 'tls'); // tls or ssl

/**
 * Send email using PHP mail() function (fallback)
 */
function send_simple_email($to, $subject, $message) {
    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Send welcome email to new users
 */
function send_welcome_email($user_email, $user_name) {
    $subject = "Chào mừng đến với Flower Store! 🌸";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌸 Chào mừng đến với Flower Store!</h1>
            </div>
            <div class='content'>
                <h2>Xin chào " . htmlspecialchars($user_name) . "!</h2>
                <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>Flower Store</strong>.</p>
                <p>Chúng tôi rất vui mừng được phục vụ bạn với những bó hoa tươi đẹp nhất!</p>
                
                <h3>🎁 Ưu đãi đặc biệt cho khách hàng mới:</h3>
                <ul>
                    <li>Giảm <strong>10%</strong> cho đơn hàng đầu tiên</li>
                    <li>Miễn phí giao hàng nội thành</li>
                    <li>Tích điểm thưởng cho mỗi đơn hàng</li>
                </ul>
                
                <p style='text-align: center;'>
                    <a href='" . get_site_url() . "/shop.php' class='button'>Khám phá sản phẩm ngay! 🌺</a>
                </p>
                
                <p><strong>Email của bạn:</strong> " . htmlspecialchars($user_email) . "</p>
                <p>Nếu bạn có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với chúng tôi!</p>
            </div>
            <div class='footer'>
                <p>© 2025 Flower Store Vietnam. All rights reserved.</p>
                <p>Hotline: 1900-xxxx | Email: support@flowerstore.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_simple_email($user_email, $subject, $message);
}

/**
 * Send order confirmation email
 */
function send_order_confirmation($order_id, $user_email, $user_name, $order_details) {
    $subject = "Xác nhận đơn hàng #" . $order_id . " - Flower Store 🌸";
    
    $products_html = "";
    foreach($order_details['items'] as $item) {
        $products_html .= "
        <tr>
            <td style='padding: 10px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($item['name']) . "</td>
            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . $item['quantity'] . "</td>
            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>" . number_format($item['price'], 0, ',', '.') . "đ</td>
            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>" . number_format($item['price'] * $item['quantity'], 0, ',', '.') . "đ</td>
        </tr>
        ";
    }
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 700px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; }
            .order-info { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; background: white; }
            th { background: #667eea; color: white; padding: 12px; text-align: left; }
            .total { font-size: 18px; font-weight: bold; color: #667eea; text-align: right; padding: 15px; background: #e8f0fe; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ Đơn hàng đã được xác nhận!</h1>
                <p style='font-size: 18px;'>Đơn hàng #" . $order_id . "</p>
            </div>
            <div class='content'>
                <h2>Xin chào " . htmlspecialchars($user_name) . "!</h2>
                <p>Cảm ơn bạn đã đặt hàng tại <strong>Flower Store</strong>. Chúng tôi đã nhận được đơn hàng của bạn và đang xử lý.</p>
                
                <div class='order-info'>
                    <h3>📋 Thông tin đơn hàng:</h3>
                    <p><strong>Mã đơn hàng:</strong> #" . $order_id . "</p>
                    <p><strong>Ngày đặt:</strong> " . date('d/m/Y H:i') . "</p>
                    <p><strong>Trạng thái:</strong> <span style='color: #f59e0b;'>Đang xử lý</span></p>
                    <p><strong>Phương thức thanh toán:</strong> " . htmlspecialchars($order_details['payment_method']) . "</p>
                </div>
                
                <h3>🛒 Chi tiết sản phẩm:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th style='text-align: center;'>Số lượng</th>
                            <th style='text-align: right;'>Đơn giá</th>
                            <th style='text-align: right;'>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        " . $products_html . "
                    </tbody>
                </table>
                
                <div class='total'>
                    Tổng cộng: " . number_format($order_details['total'], 0, ',', '.') . "đ
                </div>
                
                <div class='order-info'>
                    <h3>📍 Địa chỉ giao hàng:</h3>
                    <p>" . htmlspecialchars($order_details['address']) . "</p>
                    <p><strong>SĐT:</strong> " . htmlspecialchars($order_details['phone']) . "</p>
                </div>
                
                <p><strong>📦 Thời gian giao hàng dự kiến:</strong> 2-3 ngày làm việc</p>
                <p>Chúng tôi sẽ gửi email thông báo khi đơn hàng được giao.</p>
                
                <p style='text-align: center; margin-top: 30px;'>
                    <a href='" . get_site_url() . "/orders.php' style='display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Xem chi tiết đơn hàng</a>
                </p>
            </div>
            <div class='footer'>
                <p>© 2025 Flower Store Vietnam. All rights reserved.</p>
                <p>Nếu bạn có thắc mắc, vui lòng liên hệ: support@flowerstore.com | 1900-xxxx</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_simple_email($user_email, $subject, $message);
}

/**
 * Send order status update email
 */
if (!function_exists('send_order_status_update')) {
function send_order_status_update($order_id, $user_email, $user_name, $status, $tracking_url = '') {
    $status_messages = [
        'Đang xử lý' => ['title' => '⏳ Đơn hàng đang được xử lý', 'color' => '#f59e0b', 'message' => 'Chúng tôi đang chuẩn bị đơn hàng của bạn.'],
        'Đang giao' => ['title' => '🚚 Đơn hàng đang được giao', 'color' => '#3b82f6', 'message' => 'Shipper đang trên đường giao hàng đến bạn!'],
        'Đã giao' => ['title' => '✅ Đơn hàng đã được giao', 'color' => '#10b981', 'message' => 'Đơn hàng đã được giao thành công! Cảm ơn bạn đã mua hàng.'],
        'Đã hủy' => ['title' => '❌ Đơn hàng đã bị hủy', 'color' => '#ef4444', 'message' => 'Đơn hàng của bạn đã bị hủy. Vui lòng liên hệ để biết thêm chi tiết.']
    ];
    
    $status_info = $status_messages[$status] ?? $status_messages['Đang xử lý'];
    $subject = $status_info['title'] . " - Đơn hàng #" . $order_id;
    
    $tracking_html = '';
    if(!empty($tracking_url)) {
        $tracking_html = "
        <p style='text-align: center; margin-top: 20px;'>
            <a href='" . htmlspecialchars($tracking_url) . "' style='display: inline-block; padding: 12px 30px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px;'>Theo dõi đơn hàng 📍</a>
        </p>
        ";
    }
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: " . $status_info['color'] . "; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .status-box { background: white; padding: 20px; border-left: 4px solid " . $status_info['color'] . "; margin: 20px 0; border-radius: 5px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . $status_info['title'] . "</h1>
                <p style='font-size: 18px;'>Đơn hàng #" . $order_id . "</p>
            </div>
            <div class='content'>
                <h2>Xin chào " . htmlspecialchars($user_name) . "!</h2>
                <div class='status-box'>
                    <h3 style='color: " . $status_info['color'] . "; margin-top: 0;'>Trạng thái mới: " . htmlspecialchars($status) . "</h3>
                    <p>" . $status_info['message'] . "</p>
                    <p><strong>Cập nhật lúc:</strong> " . date('d/m/Y H:i') . "</p>
                </div>
                
                " . $tracking_html . "
                
                <p style='text-align: center; margin-top: 30px;'>
                    <a href='" . get_site_url() . "/orders.php' style='display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Xem chi tiết đơn hàng</a>
                </p>
            </div>
            <div class='footer'>
                <p>© 2025 Flower Store Vietnam. All rights reserved.</p>
                <p>Hotline: 1900-xxxx | Email: support@flowerstore.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_simple_email($user_email, $subject, $message);
}
} // End function_exists send_order_status_update

/**
 * Send password reset email
 */
function send_password_reset_email($user_email, $user_name, $reset_token) {
    $subject = "Đặt lại mật khẩu - Flower Store 🔐";
    $reset_url = get_site_url() . "/reset_password.php?token=" . urlencode($reset_token);
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Đặt lại mật khẩu</h1>
            </div>
            <div class='content'>
                <h2>Xin chào " . htmlspecialchars($user_name) . "!</h2>
                <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                
                <p style='text-align: center;'>
                    <a href='" . htmlspecialchars($reset_url) . "' class='button'>Đặt lại mật khẩu ngay</a>
                </p>
                
                <div class='warning'>
                    <strong>⚠️ Lưu ý quan trọng:</strong>
                    <ul style='margin: 10px 0;'>
                        <li>Link chỉ có hiệu lực trong <strong>1 giờ</strong></li>
                        <li>Không chia sẻ link này với bất kỳ ai</li>
                        <li>Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này</li>
                    </ul>
                </div>
                
                <p style='font-size: 12px; color: #666;'>Hoặc copy link sau vào trình duyệt:<br>
                <code style='background: #e5e7eb; padding: 5px 10px; display: inline-block; margin-top: 5px; word-break: break-all;'>" . htmlspecialchars($reset_url) . "</code></p>
            </div>
            <div class='footer'>
                <p>© 2025 Flower Store Vietnam. All rights reserved.</p>
                <p>Hotline: 1900-xxxx | Email: support@flowerstore.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_simple_email($user_email, $subject, $message);
}

/**
 * Send contact form reply email
 */
function send_contact_reply($user_email, $user_name, $admin_reply) {
    $subject = "Phản hồi từ Flower Store - Cảm ơn bạn đã liên hệ! 💬";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .reply-box { background: white; padding: 20px; border-left: 4px solid #10b981; margin: 20px 0; border-radius: 5px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>💬 Phản hồi từ Flower Store</h1>
            </div>
            <div class='content'>
                <h2>Xin chào " . htmlspecialchars($user_name) . "!</h2>
                <p>Cảm ơn bạn đã liên hệ với chúng tôi. Chúng tôi rất vui được hỗ trợ bạn!</p>
                
                <div class='reply-box'>
                    <h3 style='color: #10b981; margin-top: 0;'>📩 Phản hồi của chúng tôi:</h3>
                    <p>" . nl2br(htmlspecialchars($admin_reply)) . "</p>
                </div>
                
                <p>Nếu bạn có thêm câu hỏi, đừng ngần ngại liên hệ lại với chúng tôi!</p>
                
                <p style='text-align: center; margin-top: 30px;'>
                    <a href='" . get_site_url() . "/contact.php' style='display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Liên hệ lại</a>
                </p>
            </div>
            <div class='footer'>
                <p>© 2025 Flower Store Vietnam. All rights reserved.</p>
                <p>Hotline: 1900-xxxx | Email: support@flowerstore.com</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_simple_email($user_email, $subject, $message);
}

/**
 * Get site URL helper
 */
function get_site_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host;
}

/**
 * Log email send attempts
 */
function log_email($to, $subject, $status) {
    global $conn;
    $log_entry = date('Y-m-d H:i:s') . " | TO: $to | SUBJECT: $subject | STATUS: " . ($status ? 'SUCCESS' : 'FAILED') . "\n";
    error_log($log_entry, 3, 'logs/email.log');
}
