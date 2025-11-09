/**
 * Flower Builder - JavaScript Game Logic
 * Drag & Drop, Canvas manipulation
 */

// ============= GLOBAL VARIABLES =============
const canvas = document.getElementById('flower-canvas');
const ctx = canvas.getContext('2d');
const dropZone = document.getElementById('drop-zone');

let flowers = []; // Mảng lưu các hoa đã đặt
let selectedFlower = null;
let isDragging = false;
let dragOffsetX = 0;
let dragOffsetY = 0;
let history = [];
let historyIndex = -1;
let currentSize = 80;
let currentVase = { id: 1, name: 'Bó Tròn Cơ Bản', price: 0 };
const SERVICE_FEE = 30000;

// Preload images
const loadedImages = {};

// ============= INITIALIZATION =============
document.addEventListener('DOMContentLoaded', function() {
    initCanvas();
    initDragDrop();
    initTabs();
    initTools();
    initSizeSlider();
    saveState();
});

function initCanvas() {
    // Set canvas size
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    
    // Canvas mouse events
    canvas.addEventListener('mousedown', onCanvasMouseDown);
    canvas.addEventListener('mousemove', onCanvasMouseMove);
    canvas.addEventListener('mouseup', onCanvasMouseUp);
    canvas.addEventListener('mouseleave', onCanvasMouseUp);
    
    // Touch events for mobile
    canvas.addEventListener('touchstart', onCanvasTouchStart, { passive: false });
    canvas.addEventListener('touchmove', onCanvasTouchMove, { passive: false });
    canvas.addEventListener('touchend', onCanvasTouchEnd);
    
    // Keyboard events
    document.addEventListener('keydown', onKeyDown);
    
    // Draw initial state
    drawCanvas();
}

function resizeCanvas() {
    const wrapper = document.querySelector('.canvas-wrapper');
    const maxWidth = Math.min(600, wrapper.clientWidth - 40);
    const maxHeight = 700;
    
    canvas.style.width = maxWidth + 'px';
    canvas.style.height = maxHeight + 'px';
}

// ============= DRAG & DROP FROM PANEL =============
function initDragDrop() {
    const flowerItems = document.querySelectorAll('.flower-item:not(.vase-item)');
    
    flowerItems.forEach(item => {
        // Drag start
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', JSON.stringify({
                id: this.dataset.id,
                name: this.dataset.name,
                price: parseInt(this.dataset.price),
                image: this.dataset.image,
                category: this.dataset.category
            }));
            this.classList.add('dragging');
        });
        
        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
        
        // Click to add
        const addBtn = item.querySelector('.add-flower-btn');
        if (addBtn) {
            addBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const flowerData = {
                    id: item.dataset.id,
                    name: item.dataset.name,
                    price: parseInt(item.dataset.price),
                    image: item.dataset.image,
                    category: item.dataset.category
                };
                addFlowerToCanvas(flowerData, canvas.width / 2, canvas.height / 2);
            });
        }
    });
    
    // Canvas drop zone
    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('hidden');
    });
    
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const x = (e.clientX - rect.left) * scaleX;
        const y = (e.clientY - rect.top) * scaleY;
        
        try {
            const flowerData = JSON.parse(e.dataTransfer.getData('text/plain'));
            addFlowerToCanvas(flowerData, x, y);
        } catch (err) {
            console.error('Drop error:', err);
        }
    });
    
    // Vase selection
    const vaseItems = document.querySelectorAll('.vase-item');
    vaseItems.forEach(item => {
        const selectBtn = item.querySelector('.select-vase-btn');
        if (selectBtn) {
            selectBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                selectVase({
                    id: item.dataset.id,
                    name: item.dataset.name,
                    price: parseInt(item.dataset.price),
                    image: item.dataset.image
                });
            });
        }
        
        item.addEventListener('click', function() {
            selectVase({
                id: this.dataset.id,
                name: this.dataset.name,
                price: parseInt(this.dataset.price),
                image: this.dataset.image
            });
        });
    });
}

// ============= TABS =============
function initTabs() {
    const tabs = document.querySelectorAll('.tab-btn');
    const flowerItems = document.querySelectorAll('.flower-item');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.dataset.tab;
            
            // Filter items
            flowerItems.forEach(item => {
                if (category === 'vase') {
                    item.style.display = item.classList.contains('vase-item') ? 'flex' : 'none';
                } else {
                    if (item.classList.contains('vase-item')) {
                        item.style.display = 'none';
                    } else {
                        item.style.display = item.dataset.category === category ? 'flex' : 'none';
                    }
                }
            });
        });
    });
    
    // Show main flowers by default
    tabs[0].click();
}

// ============= TOOLS =============
function initTools() {
    document.getElementById('undo-btn').addEventListener('click', undo);
    document.getElementById('redo-btn').addEventListener('click', redo);
    document.getElementById('clear-btn').addEventListener('click', clearCanvas);
    document.getElementById('rotate-btn').addEventListener('click', rotateSelected);
    document.getElementById('flip-btn').addEventListener('click', flipSelected);
    
    // Preview button
    document.getElementById('preview-btn').addEventListener('click', showPreview);
    
    // Close modal
    document.querySelector('.close-modal').addEventListener('click', closePreview);
    
    // Form submit
    document.getElementById('order-form').addEventListener('submit', function(e) {
        if (flowers.length === 0) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất 1 bông hoa!');
            return;
        }
        
        // Save canvas data
        document.getElementById('arrangement-data').value = JSON.stringify(flowers);
        document.getElementById('arrangement-image').value = canvas.toDataURL('image/png');
        document.getElementById('flower-list-input').value = getFlowerListText();
    });
}

function initSizeSlider() {
    const slider = document.getElementById('size-slider');
    const valueDisplay = document.getElementById('size-value');
    
    slider.addEventListener('input', function() {
        currentSize = parseInt(this.value);
        valueDisplay.textContent = currentSize + 'px';
        
        // Update selected flower size
        if (selectedFlower !== null && flowers[selectedFlower]) {
            flowers[selectedFlower].size = currentSize;
            drawCanvas();
            saveState();
        }
    });
}

// ============= ADD FLOWER TO CANVAS =============
function addFlowerToCanvas(flowerData, x, y) {
    const flower = {
        id: flowerData.id,
        name: flowerData.name,
        price: flowerData.price,
        image: flowerData.image,
        category: flowerData.category,
        x: x,
        y: y,
        size: currentSize,
        rotation: 0,
        flipped: false
    };
    
    flowers.push(flower);
    selectedFlower = flowers.length - 1;
    
    // Load image and draw
    loadImage(flowerData.image, flower.category).then(() => {
        drawCanvas();
        updateFlowerList();
        updatePrice();
        saveState();
    });
    
    // Hide drop zone hint
    dropZone.classList.add('hidden');
}

function loadImage(imageName, category) {
    return new Promise((resolve) => {
        const key = imageName;
        if (loadedImages[key]) {
            resolve(loadedImages[key]);
            return;
        }
        
        const img = new Image();
        img.crossOrigin = 'anonymous';
        
        // Determine path based on category
        let path = '../assets/images/flowers/' + imageName;
        if (category === 'wrap' || category === 'vase') {
            path = '../assets/images/vases/' + imageName;
        }
        
        img.onload = function() {
            loadedImages[key] = img;
            resolve(img);
        };
        
        img.onerror = function() {
            // Use placeholder
            const placeholder = new Image();
            placeholder.src = '../assets/images/placeholder-flower.svg';
            placeholder.onload = function() {
                loadedImages[key] = placeholder;
                resolve(placeholder);
            };
            placeholder.onerror = function() {
                // Create colored circle as fallback
                const canvas = document.createElement('canvas');
                canvas.width = 100;
                canvas.height = 100;
                const ctx = canvas.getContext('2d');
                ctx.beginPath();
                ctx.arc(50, 50, 40, 0, Math.PI * 2);
                ctx.fillStyle = getRandomColor();
                ctx.fill();
                ctx.strokeStyle = '#333';
                ctx.lineWidth = 2;
                ctx.stroke();
                
                const dataUrl = canvas.toDataURL();
                const fallbackImg = new Image();
                fallbackImg.src = dataUrl;
                loadedImages[key] = fallbackImg;
                resolve(fallbackImg);
            };
        };
        
        img.src = path;
    });
}

function getRandomColor() {
    const colors = ['#ff6b9d', '#ff9ff3', '#ffeaa7', '#fd79a8', '#74b9ff', '#00b894', '#e17055'];
    return colors[Math.floor(Math.random() * colors.length)];
}

// ============= DRAW CANVAS =============
function drawCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw background
    ctx.fillStyle = '#fafafa';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Draw vase/bouquet outline at bottom
    drawVaseOutline();
    
    // Draw all flowers
    flowers.forEach((flower, index) => {
        drawFlower(flower, index === selectedFlower);
    });
}

function drawVaseOutline() {
    ctx.save();
    ctx.strokeStyle = '#ddd';
    ctx.lineWidth = 2;
    ctx.setLineDash([5, 5]);
    
    // Simple bouquet shape
    ctx.beginPath();
    ctx.moveTo(canvas.width / 2 - 150, canvas.height - 50);
    ctx.quadraticCurveTo(canvas.width / 2 - 200, canvas.height - 200, canvas.width / 2 - 100, canvas.height - 350);
    ctx.quadraticCurveTo(canvas.width / 2, canvas.height - 450, canvas.width / 2 + 100, canvas.height - 350);
    ctx.quadraticCurveTo(canvas.width / 2 + 200, canvas.height - 200, canvas.width / 2 + 150, canvas.height - 50);
    ctx.stroke();
    
    ctx.restore();
}

function drawFlower(flower, isSelected) {
    const img = loadedImages[flower.image];
    if (!img) return;
    
    ctx.save();
    
    // Move to flower position
    ctx.translate(flower.x, flower.y);
    
    // Apply rotation
    ctx.rotate(flower.rotation * Math.PI / 180);
    
    // Apply flip
    if (flower.flipped) {
        ctx.scale(-1, 1);
    }
    
    // Draw flower
    const size = flower.size;
    ctx.drawImage(img, -size / 2, -size / 2, size, size);
    
    // Draw selection indicator
    if (isSelected) {
        ctx.strokeStyle = '#667eea';
        ctx.lineWidth = 3;
        ctx.setLineDash([5, 3]);
        ctx.strokeRect(-size / 2 - 5, -size / 2 - 5, size + 10, size + 10);
        
        // Draw rotation handle
        ctx.beginPath();
        ctx.arc(0, -size / 2 - 20, 8, 0, Math.PI * 2);
        ctx.fillStyle = '#667eea';
        ctx.fill();
    }
    
    ctx.restore();
}

// ============= CANVAS MOUSE EVENTS =============
function onCanvasMouseDown(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const mouseX = (e.clientX - rect.left) * scaleX;
    const mouseY = (e.clientY - rect.top) * scaleY;
    
    // Check if clicked on a flower (reverse order for top-most)
    selectedFlower = null;
    for (let i = flowers.length - 1; i >= 0; i--) {
        const flower = flowers[i];
        const dist = Math.sqrt(Math.pow(mouseX - flower.x, 2) + Math.pow(mouseY - flower.y, 2));
        if (dist < flower.size / 2) {
            selectedFlower = i;
            isDragging = true;
            dragOffsetX = mouseX - flower.x;
            dragOffsetY = mouseY - flower.y;
            
            // Move to top
            const selected = flowers.splice(i, 1)[0];
            flowers.push(selected);
            selectedFlower = flowers.length - 1;
            
            // Update size slider
            document.getElementById('size-slider').value = flower.size;
            document.getElementById('size-value').textContent = flower.size + 'px';
            currentSize = flower.size;
            
            break;
        }
    }
    
    drawCanvas();
}

function onCanvasMouseMove(e) {
    if (!isDragging || selectedFlower === null) return;
    
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const mouseX = (e.clientX - rect.left) * scaleX;
    const mouseY = (e.clientY - rect.top) * scaleY;
    
    flowers[selectedFlower].x = mouseX - dragOffsetX;
    flowers[selectedFlower].y = mouseY - dragOffsetY;
    
    drawCanvas();
}

function onCanvasMouseUp() {
    if (isDragging) {
        isDragging = false;
        saveState();
    }
}

// ============= TOUCH EVENTS =============
function onCanvasTouchStart(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousedown', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
}

function onCanvasTouchMove(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousemove', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
}

function onCanvasTouchEnd() {
    const mouseEvent = new MouseEvent('mouseup', {});
    canvas.dispatchEvent(mouseEvent);
}

// ============= KEYBOARD EVENTS =============
function onKeyDown(e) {
    if (selectedFlower === null) return;
    
    switch(e.key) {
        case 'Delete':
        case 'Backspace':
            deleteSelected();
            break;
        case 'ArrowLeft':
            flowers[selectedFlower].x -= 5;
            drawCanvas();
            break;
        case 'ArrowRight':
            flowers[selectedFlower].x += 5;
            drawCanvas();
            break;
        case 'ArrowUp':
            flowers[selectedFlower].y -= 5;
            drawCanvas();
            break;
        case 'ArrowDown':
            flowers[selectedFlower].y += 5;
            drawCanvas();
            break;
        case 'r':
        case 'R':
            rotateSelected();
            break;
    }
}

// ============= TOOLS FUNCTIONS =============
function deleteSelected() {
    if (selectedFlower !== null) {
        flowers.splice(selectedFlower, 1);
        selectedFlower = null;
        drawCanvas();
        updateFlowerList();
        updatePrice();
        saveState();
    }
}

function rotateSelected() {
    if (selectedFlower !== null) {
        flowers[selectedFlower].rotation = (flowers[selectedFlower].rotation + 45) % 360;
        drawCanvas();
        saveState();
    }
}

function flipSelected() {
    if (selectedFlower !== null) {
        flowers[selectedFlower].flipped = !flowers[selectedFlower].flipped;
        drawCanvas();
        saveState();
    }
}

function clearCanvas() {
    if (confirm('Bạn có chắc muốn xóa tất cả?')) {
        flowers = [];
        selectedFlower = null;
        drawCanvas();
        updateFlowerList();
        updatePrice();
        saveState();
        dropZone.classList.remove('hidden');
    }
}

// ============= HISTORY (UNDO/REDO) =============
function saveState() {
    // Remove future states if we're in the middle of history
    history = history.slice(0, historyIndex + 1);
    
    // Save current state
    history.push(JSON.stringify(flowers));
    historyIndex = history.length - 1;
    
    // Limit history size
    if (history.length > 50) {
        history.shift();
        historyIndex--;
    }
}

function undo() {
    if (historyIndex > 0) {
        historyIndex--;
        flowers = JSON.parse(history[historyIndex]);
        selectedFlower = null;
        
        // Reload images
        flowers.forEach(flower => {
            loadImage(flower.image, flower.category);
        });
        
        setTimeout(() => {
            drawCanvas();
            updateFlowerList();
            updatePrice();
        }, 100);
    }
}

function redo() {
    if (historyIndex < history.length - 1) {
        historyIndex++;
        flowers = JSON.parse(history[historyIndex]);
        selectedFlower = null;
        
        // Reload images
        flowers.forEach(flower => {
            loadImage(flower.image, flower.category);
        });
        
        setTimeout(() => {
            drawCanvas();
            updateFlowerList();
            updatePrice();
        }, 100);
    }
}

// ============= VASE SELECTION =============
function selectVase(vaseData) {
    currentVase = vaseData;
    
    // Update display
    const vaseDisplay = document.getElementById('current-vase');
    vaseDisplay.innerHTML = `
        <img src="../assets/images/vases/${vaseData.image}" alt="${vaseData.name}" 
             onerror="this.src='../assets/images/placeholder-vase.svg'">
        <span>${vaseData.name}</span>
    `;
    
    updatePrice();
}

// ============= UPDATE UI =============
function updateFlowerList() {
    const listEl = document.getElementById('selected-flowers-list');
    
    if (flowers.length === 0) {
        listEl.innerHTML = '<li class="empty-msg">Chưa có hoa nào</li>';
        return;
    }
    
    // Count flowers by type
    const flowerCounts = {};
    flowers.forEach(f => {
        const key = f.name;
        if (!flowerCounts[key]) {
            flowerCounts[key] = { count: 0, price: f.price };
        }
        flowerCounts[key].count++;
    });
    
    let html = '';
    for (const [name, data] of Object.entries(flowerCounts)) {
        html += `
            <li>
                <span>${name}</span>
                <span class="flower-count">x${data.count}</span>
            </li>
        `;
    }
    
    listEl.innerHTML = html;
}

function updatePrice() {
    // Calculate flower prices
    let flowersPrice = 0;
    flowers.forEach(f => {
        flowersPrice += f.price;
    });
    
    const vasePrice = currentVase.price;
    const total = flowersPrice + vasePrice + SERVICE_FEE;
    
    // Update display
    document.getElementById('flowers-price').textContent = formatPrice(flowersPrice);
    document.getElementById('vase-price').textContent = formatPrice(vasePrice);
    document.getElementById('total-price').textContent = formatPrice(total);
    document.getElementById('total-price-input').value = total;
}

function formatPrice(price) {
    return price.toLocaleString('vi-VN') + '₫';
}

function getFlowerListText() {
    const counts = {};
    flowers.forEach(f => {
        counts[f.name] = (counts[f.name] || 0) + 1;
    });
    
    return Object.entries(counts)
        .map(([name, count]) => `${name} x${count}`)
        .join(', ');
}

// ============= PREVIEW =============
function showPreview() {
    if (flowers.length === 0) {
        alert('Vui lòng thêm ít nhất 1 bông hoa!');
        return;
    }
    
    const modal = document.getElementById('preview-modal');
    const previewImg = document.getElementById('preview-img');
    const previewFlowers = document.getElementById('preview-flowers');
    const previewTotal = document.getElementById('preview-total');
    
    // Generate preview image
    previewImg.src = canvas.toDataURL('image/png');
    
    // Update info
    previewFlowers.textContent = 'Hoa: ' + getFlowerListText() + ' | Kiểu: ' + currentVase.name;
    
    const total = flowers.reduce((sum, f) => sum + f.price, 0) + currentVase.price + SERVICE_FEE;
    previewTotal.textContent = formatPrice(total);
    
    modal.classList.add('active');
}

function closePreview() {
    document.getElementById('preview-modal').classList.remove('active');
}

function downloadImage() {
    const link = document.createElement('a');
    link.download = 'my-flower-arrangement.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

// Close modal on outside click
document.getElementById('preview-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});
