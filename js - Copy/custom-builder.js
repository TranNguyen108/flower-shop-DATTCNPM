/**
 * Custom Flower Builder - JavaScript
 * Tự thiết kế bó hoa - Logic xử lý
 */

class FlowerBuilder {
    constructor() {
        this.selectedItems = {}; // {id: {name, price, quantity, emoji, type}}
        this.selectedWrap = null;
        
        this.init();
    }
    
    init() {
        this.bindQuantityControls();
        this.bindWrapSelection();
        this.bindFormSubmit();
        this.updateUI();
    }
    
    // Xử lý nút +/- số lượng
    bindQuantityControls() {
        document.querySelectorAll('.quantity-control').forEach(control => {
            const minusBtn = control.querySelector('.minus');
            const plusBtn = control.querySelector('.plus');
            const valueEl = control.querySelector('.qty-value');
            const card = control.closest('.item-card');
            
            minusBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                let current = parseInt(valueEl.textContent) || 0;
                if (current > 0) {
                    current--;
                    valueEl.textContent = current;
                    this.updateItemFromCard(card, current);
                    this.updateCardState(card, current);
                    this.updateMinusButton(minusBtn, current);
                }
            });
            
            plusBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                let current = parseInt(valueEl.textContent) || 0;
                if (current < 99) {
                    current++;
                    valueEl.textContent = current;
                    this.updateItemFromCard(card, current);
                    this.updateCardState(card, current);
                    this.updateMinusButton(minusBtn, current);
                }
            });
        });
    }
    
    updateMinusButton(btn, qty) {
        btn.disabled = qty <= 0;
    }
    
    // Cập nhật trạng thái hiển thị của card
    updateCardState(card, quantity) {
        if (quantity > 0) {
            card.classList.add('selected');
        } else {
            card.classList.remove('selected');
        }
    }
    
    // Cập nhật item từ card data
    updateItemFromCard(card, quantity) {
        const id = card.dataset.id;
        const name = card.dataset.name;
        const price = parseInt(card.dataset.price) || 0;
        const emoji = card.dataset.emoji || '';
        const type = card.dataset.type || 'flower';
        
        if (quantity > 0) {
            this.selectedItems[id] = {
                id: id,
                name: name,
                price: price,
                quantity: quantity,
                emoji: emoji,
                type: type
            };
        } else {
            delete this.selectedItems[id];
        }
        this.updateUI();
    }
    
    // Xử lý chọn kiểu gói
    bindWrapSelection() {
        document.querySelectorAll('.wrap-card').forEach(card => {
            card.addEventListener('click', () => {
                // Bỏ chọn wrap cũ
                document.querySelectorAll('.wrap-card').forEach(c => c.classList.remove('selected'));
                
                // Chọn wrap mới
                card.classList.add('selected');
                
                const id = card.dataset.id;
                const name = card.dataset.name;
                const price = parseInt(card.dataset.price) || 0;
                const emoji = card.dataset.emoji || '';
                
                this.selectedWrap = {
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    emoji: emoji,
                    type: 'wrap'
                };
                
                this.updateUI();
            });
        });
    }
    
    // Cập nhật giao diện
    updateUI() {
        this.updatePreview();
        this.updateSelectedList();
        this.updatePriceBreakdown();
        this.updateSubmitButton();
        this.updateFormData();
    }
    
    // Cập nhật preview visual - Bouquet Style
    updatePreview() {
        const previewEl = document.getElementById('visual-preview');
        if (!previewEl) return;
        
        const items = Object.values(this.selectedItems);
        const flowers = items.filter(i => i.type === 'flower');
        const fillers = items.filter(i => i.type === 'filler');
        const accessories = items.filter(i => i.type === 'accessory');
        
        if (items.length === 0 && !this.selectedWrap) {
            previewEl.innerHTML = `
                <div class="preview-empty">
                    <i class="fas fa-seedling"></i>
                    <p>Bó hoa của bạn sẽ hiển thị ở đây</p>
                </div>
            `;
            return;
        }
        
        // Build bouquet HTML
        let flowersHtml = '';
        let fillersHtml = '';
        let accessoriesHtml = '';
        
        // Flowers (max 12 shown)
        let flowerCount = 0;
        flowers.forEach(item => {
            const qty = Math.min(item.quantity, 4);
            for (let i = 0; i < qty && flowerCount < 12; i++) {
                flowersHtml += `<span class="flower-item">${item.emoji}</span>`;
                flowerCount++;
            }
        });
        
        // Fillers (max 6 shown)
        let fillerCount = 0;
        fillers.forEach(item => {
            const qty = Math.min(item.quantity, 3);
            for (let i = 0; i < qty && fillerCount < 6; i++) {
                fillersHtml += `<span class="filler-item">${item.emoji}</span>`;
                fillerCount++;
            }
        });
        
        // Accessories (max 3 shown)
        let accCount = 0;
        accessories.forEach(item => {
            if (accCount < 3) {
                accessoriesHtml += `<span class="accessory-item">${item.emoji}</span>`;
                accCount++;
            }
        });
        
        // Wrap style
        let wrapClass = '';
        let wrapEmoji = '🎀';
        if (this.selectedWrap) {
            wrapEmoji = this.selectedWrap.emoji;
            const wrapId = parseInt(this.selectedWrap.id);
            if (wrapId === 30) wrapClass = 'kraft';
            else if (wrapId === 31) wrapClass = 'korean';
            else if (wrapId === 32) wrapClass = 'transparent';
            else if (wrapId === 33) wrapClass = 'box';
            else if (wrapId === 34) wrapClass = 'heart';
            else if (wrapId === 35) wrapClass = 'basket';
        }
        
        // Check for ribbon accessory
        const hasRibbon = accessories.some(a => a.name.includes('Nơ'));
        const ribbonEmoji = accessories.find(a => a.name.includes('Nơ'))?.emoji || '';
        
        previewEl.innerHTML = `
            <div class="bouquet-preview">
                ${accessoriesHtml ? `<div class="accessories-area">${accessoriesHtml}</div>` : ''}
                
                <div class="flowers-area" style="z-index: 2;">
                    ${fillersHtml ? `<div class="fillers-area">${fillersHtml}</div>` : ''}
                    ${flowersHtml || '<span class="flower-item">🌸</span>'}
                </div>
                
                ${hasRibbon ? `<div class="ribbon-area">${ribbonEmoji}</div>` : ''}
                
                <div class="wrap-area ${wrapClass}">
                    <span class="wrap-emoji">${this.selectedWrap ? wrapEmoji : ''}</span>
                </div>
            </div>
        `;
    }
    
    // Cập nhật danh sách đã chọn
    updateSelectedList() {
        const listEl = document.getElementById('selected-list');
        if (!listEl) return;
        
        const items = Object.values(this.selectedItems);
        
        if (items.length === 0 && !this.selectedWrap) {
            listEl.innerHTML = '<li class="empty-msg">Chưa chọn gì</li>';
            return;
        }
        
        let html = '';
        
        // List items
        items.forEach(item => {
            const subtotal = item.quantity * item.price;
            html += `
                <li>
                    <span class="item-name">
                        ${item.emoji} ${item.name}
                        <span class="item-qty">x${item.quantity}</span>
                    </span>
                    <span class="item-subtotal">${this.formatPrice(subtotal)}</span>
                </li>
            `;
        });
        
        // Wrap
        if (this.selectedWrap) {
            html += `
                <li>
                    <span class="item-name">
                        ${this.selectedWrap.emoji} ${this.selectedWrap.name}
                        <span class="item-qty">x1</span>
                    </span>
                    <span class="item-subtotal">${this.formatPrice(this.selectedWrap.price)}</span>
                </li>
            `;
        }
        
        listEl.innerHTML = html;
    }
    
    // Tính và cập nhật giá
    updatePriceBreakdown() {
        let flowerTotal = 0;
        let fillerTotal = 0;
        let accessoryTotal = 0;
        let wrapPrice = 0;
        
        // Tính giá theo loại
        Object.values(this.selectedItems).forEach(item => {
            const subtotal = item.quantity * item.price;
            
            if (item.type === 'flower') {
                flowerTotal += subtotal;
            } else if (item.type === 'filler') {
                fillerTotal += subtotal;
            } else if (item.type === 'accessory') {
                accessoryTotal += subtotal;
            }
        });
        
        // Giá gói
        if (this.selectedWrap) {
            wrapPrice = this.selectedWrap.price;
        }
        
        const total = flowerTotal + fillerTotal + wrapPrice + accessoryTotal;
        
        // Debug log
        console.log('Price breakdown:', {flowerTotal, fillerTotal, wrapPrice, accessoryTotal, total});
        
        // Cập nhật hiển thị
        const flowerEl = document.getElementById('price-flowers');
        const fillerEl = document.getElementById('price-fillers');
        const wrapEl = document.getElementById('price-wrap');
        const accessoryEl = document.getElementById('price-accessories');
        const totalEl = document.getElementById('price-total');
        
        if (flowerEl) flowerEl.textContent = this.formatPrice(flowerTotal);
        if (fillerEl) fillerEl.textContent = this.formatPrice(fillerTotal);
        if (wrapEl) wrapEl.textContent = this.formatPrice(wrapPrice);
        if (accessoryEl) accessoryEl.textContent = this.formatPrice(accessoryTotal);
        if (totalEl) totalEl.textContent = this.formatPrice(total);
    }
    
    // Cập nhật trạng thái nút submit
    updateSubmitButton() {
        const submitBtn = document.getElementById('btn-submit');
        if (!submitBtn) return;
        
        const hasItems = Object.keys(this.selectedItems).length > 0;
        
        // Chỉ cần có ít nhất 1 item là được (kiểu gói không bắt buộc)
        submitBtn.disabled = !hasItems;
    }
    
    // Cập nhật hidden form data
    updateFormData() {
        const nameInput = document.getElementById('form-bouquet-name');
        const itemsInput = document.getElementById('form-selected-items');
        const totalInput = document.getElementById('form-total-price');
        
        // Tính tổng
        let total = 0;
        Object.values(this.selectedItems).forEach(item => {
            total += item.quantity * item.price;
        });
        if (this.selectedWrap) {
            total += this.selectedWrap.price;
        }
        
        // Tạo mảng items để gửi
        const allItems = [...Object.values(this.selectedItems)];
        if (this.selectedWrap) {
            allItems.push(this.selectedWrap);
        }
        
        if (totalInput) totalInput.value = total;
        if (itemsInput) itemsInput.value = JSON.stringify(allItems);
    }
    
    // Format giá tiền
    formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price) + '₫';
    }
    
    // Xử lý submit form
    bindFormSubmit() {
        const form = document.getElementById('order-form');
        if (!form) {
            console.error('Form not found!');
            return;
        }
        
        form.addEventListener('submit', (e) => {
            console.log('Form submitting...');
            
            // Lấy tên bó hoa từ input
            const nameDisplay = document.getElementById('bouquet-name');
            const nameHidden = document.getElementById('form-bouquet-name');
            
            if (nameDisplay && nameHidden) {
                nameHidden.value = nameDisplay.value || 'Bó hoa tự thiết kế';
            }
            
            // Validate - chỉ cần có hoa
            if (Object.keys(this.selectedItems).length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một loại hoa!');
                return;
            }
            
            // Update form data trước khi submit
            this.updateFormData();
            
            console.log('Form data:', {
                name: nameHidden?.value,
                items: document.getElementById('form-selected-items')?.value,
                total: document.getElementById('form-total-price')?.value
            });
            
            // Form will submit normally
        });
    }
    
    reset() {
        // Reset data
        this.selectedItems = {};
        this.selectedWrap = null;
        
        // Reset UI - quantity controls
        document.querySelectorAll('.qty-value').forEach(el => {
            el.textContent = '0';
        });
        
        // Reset minus buttons
        document.querySelectorAll('.qty-btn.minus').forEach(btn => {
            btn.disabled = true;
        });
        
        // Reset UI - cards
        document.querySelectorAll('.item-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Reset name input
        const nameInput = document.getElementById('bouquet-name');
        if (nameInput) nameInput.value = '';
        
        // Update UI
        this.updateUI();
    }
}

// Global instance
let flowerBuilder;

// Khởi tạo khi DOM ready
document.addEventListener('DOMContentLoaded', () => {
    flowerBuilder = new FlowerBuilder();
});

// Global reset function
function resetAll() {
    if (confirm('Bạn có chắc muốn làm lại từ đầu?')) {
        flowerBuilder.reset();
    }
}
