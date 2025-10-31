<?php
/**
 * Generate Flower Placeholder Images
 * Tạo ảnh SVG placeholder cho các loại hoa
 */

$flowers = [
    // Main flowers
    ['name' => 'rose_red', 'color' => '#e74c3c', 'type' => 'flower'],
    ['name' => 'rose_pink', 'color' => '#fd79a8', 'type' => 'flower'],
    ['name' => 'rose_white', 'color' => '#fff5f5', 'type' => 'flower'],
    ['name' => 'sunflower', 'color' => '#f1c40f', 'type' => 'sunflower'],
    ['name' => 'tulip_red', 'color' => '#c0392b', 'type' => 'tulip'],
    ['name' => 'tulip_yellow', 'color' => '#f39c12', 'type' => 'tulip'],
    ['name' => 'lily_white', 'color' => '#ffffff', 'type' => 'lily'],
    ['name' => 'carnation', 'color' => '#e91e63', 'type' => 'carnation'],
    ['name' => 'daisy', 'color' => '#ffffff', 'type' => 'daisy'],
    ['name' => 'orchid', 'color' => '#9b59b6', 'type' => 'orchid'],
    
    // Filler & Green
    ['name' => 'baby_breath', 'color' => '#ecf0f1', 'type' => 'babys'],
    ['name' => 'monstera', 'color' => '#27ae60', 'type' => 'leaf'],
    ['name' => 'fern', 'color' => '#2ecc71', 'type' => 'fern'],
    ['name' => 'eucalyptus', 'color' => '#1abc9c', 'type' => 'eucalyptus'],
    
    // Accessories
    ['name' => 'ribbon_red', 'color' => '#e74c3c', 'type' => 'ribbon'],
    ['name' => 'ribbon_pink', 'color' => '#fd79a8', 'type' => 'ribbon'],
    ['name' => 'wrap_kraft', 'color' => '#d4a574', 'type' => 'wrap'],
    ['name' => 'wrap_white', 'color' => '#f8f9fa', 'type' => 'wrap'],
];

$vases = [
    ['name' => 'bouquet_round', 'color' => '#f8f9fa'],
    ['name' => 'bouquet_long', 'color' => '#e8f4f8'],
    ['name' => 'basket', 'color' => '#d4a574'],
    ['name' => 'box_square', 'color' => '#ff6b9d'],
    ['name' => 'box_heart', 'color' => '#e74c3c'],
];

// Create directories
$flowersDir = __DIR__ . '/assets/images/flowers';
$vasesDir = __DIR__ . '/assets/images/vases';

if (!file_exists($flowersDir)) {
    mkdir($flowersDir, 0755, true);
}
if (!file_exists($vasesDir)) {
    mkdir($vasesDir, 0755, true);
}

// Generate flower SVGs
foreach ($flowers as $flower) {
    $svg = generateFlowerSVG($flower['color'], $flower['type']);
    file_put_contents($flowersDir . '/' . $flower['name'] . '.png', createPNGFromSVG($svg));
    echo "Created: {$flower['name']}.png\n";
}

// Generate vase SVGs
foreach ($vases as $vase) {
    $svg = generateVaseSVG($vase['color'], $vase['name']);
    file_put_contents($vasesDir . '/' . $vase['name'] . '.png', createPNGFromSVG($svg));
    echo "Created vase: {$vase['name']}.png\n";
}

// Generate placeholder
$placeholderFlower = generateFlowerSVG('#ccc', 'flower');
file_put_contents(__DIR__ . '/assets/images/placeholder-flower.png', createPNGFromSVG($placeholderFlower));

$placeholderVase = generateVaseSVG('#ccc', 'bouquet_round');
file_put_contents(__DIR__ . '/assets/images/placeholder-vase.png', createPNGFromSVG($placeholderVase));

echo "\nDone! All placeholder images created.\n";

function generateFlowerSVG($color, $type) {
    $stroke = adjustBrightness($color, -30);
    
    switch ($type) {
        case 'sunflower':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <circle cx="50" cy="50" r="15" fill="#8B4513"/>
    <g fill="$color" stroke="$stroke" stroke-width="1">
        <ellipse cx="50" cy="20" rx="8" ry="18"/>
        <ellipse cx="50" cy="80" rx="8" ry="18"/>
        <ellipse cx="20" cy="50" rx="18" ry="8"/>
        <ellipse cx="80" cy="50" rx="18" ry="8"/>
        <ellipse cx="29" cy="29" rx="8" ry="16" transform="rotate(-45 29 29)"/>
        <ellipse cx="71" cy="29" rx="8" ry="16" transform="rotate(45 71 29)"/>
        <ellipse cx="29" cy="71" rx="8" ry="16" transform="rotate(45 29 71)"/>
        <ellipse cx="71" cy="71" rx="8" ry="16" transform="rotate(-45 71 71)"/>
    </g>
</svg>
SVG;

        case 'tulip':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 90 L50 50" stroke="#228B22" stroke-width="4"/>
    <ellipse cx="35" cy="35" rx="15" ry="25" fill="$color" stroke="$stroke" stroke-width="1"/>
    <ellipse cx="50" cy="30" rx="12" ry="28" fill="$color" stroke="$stroke" stroke-width="1"/>
    <ellipse cx="65" cy="35" rx="15" ry="25" fill="$color" stroke="$stroke" stroke-width="1"/>
</svg>
SVG;

        case 'lily':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 95 L50 55" stroke="#228B22" stroke-width="3"/>
    <g fill="$color" stroke="#ddd" stroke-width="1">
        <ellipse cx="50" cy="25" rx="10" ry="25"/>
        <ellipse cx="25" cy="40" rx="10" ry="22" transform="rotate(-30 25 40)"/>
        <ellipse cx="75" cy="40" rx="10" ry="22" transform="rotate(30 75 40)"/>
        <ellipse cx="30" cy="55" rx="10" ry="20" transform="rotate(-60 30 55)"/>
        <ellipse cx="70" cy="55" rx="10" ry="20" transform="rotate(60 70 55)"/>
    </g>
    <circle cx="50" cy="45" r="5" fill="#ffeb3b"/>
</svg>
SVG;

        case 'carnation':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 95 L50 60" stroke="#228B22" stroke-width="3"/>
    <g fill="$color" stroke="$stroke" stroke-width="0.5">
        <circle cx="50" cy="40" r="12"/>
        <circle cx="38" cy="35" r="10"/>
        <circle cx="62" cy="35" r="10"/>
        <circle cx="35" cy="45" r="9"/>
        <circle cx="65" cy="45" r="9"/>
        <circle cx="42" cy="28" r="8"/>
        <circle cx="58" cy="28" r="8"/>
        <circle cx="50" cy="25" r="7"/>
    </g>
</svg>
SVG;

        case 'daisy':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 95 L50 55" stroke="#228B22" stroke-width="3"/>
    <g fill="$color" stroke="#ddd" stroke-width="1">
        <ellipse cx="50" cy="20" rx="6" ry="15"/>
        <ellipse cx="50" cy="55" rx="6" ry="15"/>
        <ellipse cx="25" cy="37" rx="15" ry="6"/>
        <ellipse cx="75" cy="37" rx="15" ry="6"/>
        <ellipse cx="32" cy="24" rx="6" ry="13" transform="rotate(-45 32 24)"/>
        <ellipse cx="68" cy="24" rx="6" ry="13" transform="rotate(45 68 24)"/>
        <ellipse cx="32" cy="51" rx="6" ry="13" transform="rotate(45 32 51)"/>
        <ellipse cx="68" cy="51" rx="6" ry="13" transform="rotate(-45 68 51)"/>
    </g>
    <circle cx="50" cy="37" r="10" fill="#f1c40f"/>
</svg>
SVG;

        case 'orchid':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 95 L50 60" stroke="#228B22" stroke-width="3"/>
    <g fill="$color" stroke="$stroke" stroke-width="1">
        <ellipse cx="50" cy="25" rx="15" ry="20"/>
        <ellipse cx="30" cy="45" rx="12" ry="18" transform="rotate(-20 30 45)"/>
        <ellipse cx="70" cy="45" rx="12" ry="18" transform="rotate(20 70 45)"/>
        <ellipse cx="40" cy="55" rx="8" ry="12" transform="rotate(-10 40 55)"/>
        <ellipse cx="60" cy="55" rx="8" ry="12" transform="rotate(10 60 55)"/>
    </g>
    <circle cx="50" cy="45" r="8" fill="#fff" stroke="#9b59b6"/>
    <circle cx="50" cy="45" r="3" fill="#e74c3c"/>
</svg>
SVG;

        case 'babys':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 95 L50 50 M50 50 L30 30 M50 50 L70 30 M50 50 L40 25 M50 50 L60 25" stroke="#228B22" stroke-width="1"/>
    <g fill="$color" stroke="#ddd" stroke-width="0.5">
        <circle cx="30" cy="25" r="6"/><circle cx="70" cy="25" r="6"/>
        <circle cx="40" cy="18" r="5"/><circle cx="60" cy="18" r="5"/>
        <circle cx="50" cy="15" r="5"/>
        <circle cx="25" cy="35" r="4"/><circle cx="75" cy="35" r="4"/>
        <circle cx="35" cy="30" r="4"/><circle cx="65" cy="30" r="4"/>
    </g>
</svg>
SVG;

        case 'leaf':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 95 L50 50" stroke="#228B22" stroke-width="3"/>
    <path d="M50 50 Q20 30 30 10 Q50 20 50 50 Q50 20 70 10 Q80 30 50 50" fill="$color" stroke="$stroke" stroke-width="1"/>
    <path d="M50 50 L50 20" stroke="$stroke" stroke-width="1"/>
    <path d="M50 35 L40 25 M50 35 L60 25" stroke="$stroke" stroke-width="1" fill="none"/>
</svg>
SVG;

        case 'fern':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 95 L50 10" stroke="$color" stroke-width="2"/>
    <g stroke="$color" stroke-width="1.5" fill="none">
        <path d="M50 15 L35 20 M50 15 L65 20"/>
        <path d="M50 25 L30 32 M50 25 L70 32"/>
        <path d="M50 35 L25 45 M50 35 L75 45"/>
        <path d="M50 45 L22 58 M50 45 L78 58"/>
        <path d="M50 55 L20 70 M50 55 L80 70"/>
        <path d="M50 65 L25 80 M50 65 L75 80"/>
    </g>
</svg>
SVG;

        case 'eucalyptus':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 95 L50 10" stroke="#8B7355" stroke-width="2"/>
    <g fill="$color" stroke="$stroke" stroke-width="0.5">
        <ellipse cx="40" cy="20" rx="12" ry="8" transform="rotate(-20 40 20)"/>
        <ellipse cx="60" cy="30" rx="12" ry="8" transform="rotate(20 60 30)"/>
        <ellipse cx="38" cy="42" rx="13" ry="9" transform="rotate(-15 38 42)"/>
        <ellipse cx="62" cy="55" rx="13" ry="9" transform="rotate(15 62 55)"/>
        <ellipse cx="40" cy="68" rx="12" ry="8" transform="rotate(-10 40 68)"/>
        <ellipse cx="58" cy="80" rx="11" ry="7" transform="rotate(10 58 80)"/>
    </g>
</svg>
SVG;

        case 'ribbon':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M30 60 Q50 40 70 60 Q50 80 30 60" fill="$color" stroke="$stroke" stroke-width="1"/>
    <path d="M45 60 L35 90 M55 60 L65 90" stroke="$color" stroke-width="8"/>
    <path d="M35 90 L30 95 M65 90 L70 95" stroke="$color" stroke-width="6"/>
    <ellipse cx="50" cy="55" rx="15" ry="10" fill="$color" stroke="$stroke"/>
</svg>
SVG;

        case 'wrap':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M20 95 L30 30 Q50 20 70 30 L80 95 Z" fill="$color" stroke="$stroke" stroke-width="1"/>
    <path d="M25 90 L35 40 M50 90 L50 35 M75 90 L65 40" stroke="$stroke" stroke-width="0.5" opacity="0.3"/>
</svg>
SVG;

        default: // flower (rose)
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 95 L50 55" stroke="#228B22" stroke-width="4"/>
    <g fill="$color" stroke="$stroke" stroke-width="1">
        <ellipse cx="50" cy="40" rx="25" ry="20"/>
        <ellipse cx="35" cy="35" rx="15" ry="12"/>
        <ellipse cx="65" cy="35" rx="15" ry="12"/>
        <ellipse cx="50" cy="28" rx="18" ry="14"/>
        <ellipse cx="40" cy="25" rx="10" ry="8"/>
        <ellipse cx="60" cy="25" rx="10" ry="8"/>
        <circle cx="50" cy="35" r="8"/>
    </g>
</svg>
SVG;
    }
}

function generateVaseSVG($color, $type) {
    $stroke = adjustBrightness($color, -20);
    
    switch ($type) {
        case 'bouquet_long':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M30 95 L35 40 Q50 30 65 40 L70 95 Z" fill="$color" stroke="$stroke" stroke-width="2"/>
    <path d="M35 40 Q50 20 65 40" fill="none" stroke="$stroke" stroke-width="2"/>
    <ellipse cx="50" cy="95" rx="20" ry="3" fill="$stroke"/>
</svg>
SVG;

        case 'basket':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <ellipse cx="50" cy="60" rx="35" ry="15" fill="$color" stroke="$stroke" stroke-width="2"/>
    <path d="M15 60 L20 90 Q50 95 80 90 L85 60" fill="$color" stroke="$stroke" stroke-width="2"/>
    <path d="M25 20 Q50 5 75 20" fill="none" stroke="$stroke" stroke-width="3"/>
    <line x1="30" y1="60" x2="30" y2="85" stroke="$stroke" stroke-width="1"/>
    <line x1="50" y1="60" x2="50" y2="88" stroke="$stroke" stroke-width="1"/>
    <line x1="70" y1="60" x2="70" y2="85" stroke="$stroke" stroke-width="1"/>
</svg>
SVG;

        case 'box_square':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <rect x="15" y="40" width="70" height="55" rx="5" fill="$color" stroke="$stroke" stroke-width="2"/>
    <rect x="15" y="40" width="70" height="12" rx="3" fill="$stroke"/>
    <path d="M40 46 L50 35 L60 46" fill="none" stroke="white" stroke-width="2"/>
</svg>
SVG;

        case 'box_heart':
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <path d="M50 90 L15 55 Q5 35 25 25 Q45 20 50 40 Q55 20 75 25 Q95 35 85 55 Z" fill="$color" stroke="$stroke" stroke-width="2"/>
    <path d="M50 75 L25 50 Q20 40 30 35 Q40 32 50 45 Q60 32 70 35 Q80 40 75 50 Z" fill="$stroke" opacity="0.3"/>
</svg>
SVG;

        default: // bouquet_round
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
    <ellipse cx="50" cy="50" rx="40" ry="35" fill="$color" stroke="$stroke" stroke-width="2"/>
    <path d="M40 85 L45 95 L55 95 L60 85" fill="$color" stroke="$stroke" stroke-width="2"/>
    <ellipse cx="50" cy="50" rx="30" ry="25" fill="none" stroke="$stroke" stroke-width="1" stroke-dasharray="5,3"/>
</svg>
SVG;
    }
}

function adjustBrightness($hex, $percent) {
    $hex = ltrim($hex, '#');
    
    if (strlen($hex) == 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + ($r * $percent / 100)));
    $g = max(0, min(255, $g + ($g * $percent / 100)));
    $b = max(0, min(255, $b + ($b * $percent / 100)));
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

function createPNGFromSVG($svg) {
    // If GD is available, create PNG from SVG
    if (extension_loaded('gd')) {
        $im = @imagecreatefromstring($svg);
        if ($im) {
            ob_start();
            imagepng($im);
            $png = ob_get_clean();
            imagedestroy($im);
            return $png;
        }
    }
    
    // Fallback: just save SVG content but name it .png
    // Browser will still render it
    return $svg;
}
