let userBox = document.querySelector('.header .flex .account-box');

document.querySelector('#user-btn').onclick = () =>{
    userBox.classList.toggle('active');
    navbar.classList.remove('active');
}

let navbar = document.querySelector('.header .flex .navbar');

document.querySelector('#menu-btn').onclick = () =>{
    navbar.classList.toggle('active');
    userBox.classList.remove('active');
}

window.onscroll = () =>{
    userBox.classList.remove('active');
    navbar.classList.remove('active');
}

// ============= INLINE QUANTITY SELECTOR =============

// Handle "Thêm vào giỏ" button clicks using event delegation
document.addEventListener('click', function(e) {
    // Check if clicked element or its parent has show-qty-btn class
    const showBtn = e.target.classList.contains('show-qty-btn') ? e.target : null;
    
    if(showBtn) {
        e.preventDefault();
        console.log('Button clicked!'); // Debug log
        
        const form = showBtn.closest('form');
        if(!form) {
            console.error('Form not found!');
            return;
        }
        
        const qtyInput = form.querySelector('.qty');
        const confirmBtn = form.querySelector('.confirm-qty-btn');
        
        if(!qtyInput || !confirmBtn) {
            console.error('Quantity input or confirm button not found!');
            return;
        }
        
        // Show quantity input and confirm button
        qtyInput.style.display = 'block';
        confirmBtn.style.display = 'inline-block';
        showBtn.style.display = 'none';
        
        // Focus on quantity input
        setTimeout(() => {
            qtyInput.focus();
            qtyInput.select();
        }, 100);
    }
});

// ============= ADVANCED SEARCH FUNCTIONALITY =============

const searchInput = document.getElementById('live-search');
const searchResults = document.getElementById('search-results');
let searchTimeout = null;

if(searchInput && searchResults) {
    // Real-time search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if(query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        // Debounce search requests
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if(!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // Show results when input is focused and has value
    searchInput.addEventListener('focus', function() {
        if(this.value.trim().length >= 2) {
            searchResults.style.display = 'block';
        }
    });
}

function performSearch(query) {
    fetch(`ajax_search.php?q=${encodeURIComponent(query)}&limit=8`)
        .then(response => response.json())
        .then(data => {
            if(data.success && data.products.length > 0) {
                displaySearchResults(data.products, query);
            } else {
                displayNoResults(query);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            searchResults.innerHTML = '<div style="padding: 1rem; text-align: center; color: #ef4444;">Lỗi khi tìm kiếm</div>';
            searchResults.style.display = 'block';
        });
}

function displaySearchResults(products, query) {
    let html = '<div style="padding: 1rem;">';
    html += `<div style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">Tìm thấy ${products.length} kết quả cho "<strong>${escapeHtml(query)}</strong>"</div>`;
    
    products.forEach(product => {
        const stockBadge = getStockBadge(product.stock_status, product.stock);
        
        html += `
        <a href="${product.url}" style="display: flex; align-items: center; gap: 1rem; padding: 0.8rem; border-radius: 8px; text-decoration: none; color: inherit; transition: all 0.3s;" 
           onmouseover="this.style.background='#f3f4f6'" 
           onmouseout="this.style.background='transparent'">
            <img src="uploaded_img/${product.image}" alt="${escapeHtml(product.name)}" 
                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
            <div style="flex: 1;">
                <div style="font-weight: bold; margin-bottom: 0.3rem;">${highlightQuery(product.name, query)}</div>
                <div style="color: #667eea; font-weight: bold;">${product.price}đ</div>
                ${stockBadge}
            </div>
        </a>
        `;
    });
    
    html += `<a href="search_page.php?search_box=${encodeURIComponent(query)}" 
                style="display: block; text-align: center; padding: 1rem; color: #667eea; font-weight: bold; text-decoration: none; border-top: 1px solid #e5e7eb; margin-top: 1rem;">
                Xem tất cả kết quả <i class="fas fa-arrow-right"></i>
             </a>`;
    
    html += '</div>';
    searchResults.innerHTML = html;
    searchResults.style.display = 'block';
}

function displayNoResults(query) {
    searchResults.innerHTML = `
        <div style="padding: 2rem; text-align: center;">
            <i class="fas fa-search" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
            <p style="color: #666;">Không tìm thấy sản phẩm nào cho "<strong>${escapeHtml(query)}</strong>"</p>
            <a href="shop.php" style="display: inline-block; margin-top: 1rem; padding: 0.8rem 2rem; background: #667eea; color: white; border-radius: 25px; text-decoration: none;">
                Xem tất cả sản phẩm
            </a>
        </div>
    `;
    searchResults.style.display = 'block';
}

function getStockBadge(status, quantity) {
    if(status === 'out_of_stock') {
        return '<span style="font-size: 0.85rem; color: #ef4444;">❌ Hết hàng</span>';
    } else if(status === 'low_stock') {
        return `<span style="font-size: 0.85rem; color: #f59e0b;">⚠️ Chỉ còn ${quantity}</span>`;
    } else {
        return '<span style="font-size: 0.85rem; color: #10b981;">✅ Còn hàng</span>';
    }
}

function highlightQuery(text, query) {
    const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
    return escapeHtml(text).replace(regex, '<mark style="background: #fef3c7; padding: 0 2px;">$1</mark>');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// ==================== ENHANCED SECURITY & VALIDATION ====================

// Email validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Phone validation (Vietnamese format)
function validatePhone(phone) {
    const re = /^(0|\+84)(\s|\.)?((3[2-9])|(5[689])|(7[06-9])|(8[1-689])|(9[0-46-9]))(\d)(\s|\.)?(\d{3})(\s|\.)?(\d{3})$/;
    return re.test(phone);
}

// Password strength check
function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    return strength;
}

// Error handling
function showError(element, message) {
    clearError(element);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.cssText = 'color: red; font-size: 1.4rem; margin-top: 0.5rem;';
    element.parentElement.appendChild(errorDiv);
    element.style.borderColor = '#f44336';
}

function clearError(element) {
    const errorDiv = element.parentElement.querySelector('.error-message');
    if (errorDiv) errorDiv.remove();
    element.style.borderColor = '';
}

// Login form validation
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form[action*="login"]');
    if (loginForm) {
        const emailInput = loginForm.querySelector('input[name="email"]');
        const passInput = loginForm.querySelector('input[name="pass"]');
        
        loginForm.addEventListener('submit', function(e) {
            let valid = true;
            clearError(emailInput);
            clearError(passInput);
            
            if (!validateEmail(emailInput.value)) {
                showError(emailInput, 'Email không hợp lệ!');
                valid = false;
            }
            
            if (passInput.value.length < 6) {
                showError(passInput, 'Mật khẩu phải có ít nhất 6 ký tự!');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    }
    
    // Register form validation
    const registerForm = document.querySelector('form[action*="register"]');
    if (registerForm) {
        const passInput = registerForm.querySelector('input[name="pass"]');
        const cpassInput = registerForm.querySelector('input[name="cpass"]');
        
        if (passInput) {
            passInput.addEventListener('input', function() {
                const strength = checkPasswordStrength(this.value);
                clearError(this);
                
                let message = '';
                let color = '';
                
                if (this.value.length > 0) {
                    if (strength <= 2) {
                        message = 'Mật khẩu yếu';
                        color = 'red';
                    } else if (strength <= 4) {
                        message = 'Mật khẩu trung bình';
                        color = 'orange';
                    } else {
                        message = 'Mật khẩu mạnh';
                        color = 'green';
                    }
                    
                    showError(this, message);
                    const errorDiv = this.parentElement.querySelector('.error-message');
                    errorDiv.style.color = color;
                }
            });
        }
        
        registerForm.addEventListener('submit', function(e) {
            let valid = true;
            
            const nameInput = this.querySelector('input[name="name"]');
            const emailInput = this.querySelector('input[name="email"]');
            
            clearError(nameInput);
            clearError(emailInput);
            clearError(passInput);
            clearError(cpassInput);
            
            if (nameInput.value.length < 3) {
                showError(nameInput, 'Tên phải có ít nhất 3 ký tự!');
                valid = false;
            }
            
            if (!validateEmail(emailInput.value)) {
                showError(emailInput, 'Email không hợp lệ!');
                valid = false;
            }
            
            if (passInput.value.length < 6) {
                showError(passInput, 'Mật khẩu phải có ít nhất 6 ký tự!');
                valid = false;
            }
            
            if (passInput.value !== cpassInput.value) {
                showError(cpassInput, 'Mật khẩu xác nhận không khớp!');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    }
    
    // Contact form validation
    const contactForm = document.querySelector('form[action*="contact"]');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            let valid = true;
            
            const emailInput = this.querySelector('input[name="email"]');
            const phoneInput = this.querySelector('input[name="number"]');
            
            if (emailInput && !validateEmail(emailInput.value)) {
                showError(emailInput, 'Email không hợp lệ!');
                valid = false;
            }
            
            if (phoneInput && !validatePhone(phoneInput.value)) {
                showError(phoneInput, 'Số điện thoại không hợp lệ!');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    }
    
    // Quantity validation
    const qtyInputs = document.querySelectorAll('input[type="number"].qty');
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            if (this.value > 999) this.value = 999;
        });
    });
    
    // Loading state
    const allForms = document.querySelectorAll('form');
    allForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('input[type="submit"], button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('no-loading')) {
                submitBtn.disabled = true;
                submitBtn.value = submitBtn.value === 'Đăng nhập' || submitBtn.value === 'Đăng ký' 
                    ? 'Đang xử lý...' 
                    : 'Vui lòng đợi...';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });
    
    // Auto-hide messages
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
    
    // Image preview
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('File quá lớn! Kích thước tối đa 2MB.');
                    this.value = '';
                    return;
                }
                
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)!');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = input.parentElement.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'image-preview';
                        preview.style.maxWidth = '200px';
                        preview.style.marginTop = '10px';
                        preview.style.borderRadius = '5px';
                        input.parentElement.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Delete confirmation
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    deleteLinks.forEach(link => {
        if (!link.onclick) {
            link.addEventListener('click', function(e) {
                if (!confirm('Bạn có chắc chắn muốn xóa?')) {
                    e.preventDefault();
                }
            });
        }
    });
    
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}