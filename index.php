<?php
function pathGenerator(
    int $width = 1000,
    int $height = 100,
    int $segments = 9,
    array $yRange = [30, 70]
): string {
    $points = [];

    // Generate curve points
    for ($i = 0; $i < $segments; $i++) {
        $progress = ($i + 1) / $segments;

        $x = (int) round($progress * $width);
        $y = rand($yRange[0], $yRange[1]);

        // Control point slightly before end point
        $x2 = $x - rand(25, 50);
        $y2 = $y + rand(-20, 20);

        $points[] = [$x2, $y2, $x, $y];
    }

    // Ensure last point is exactly at right edge
    $points[array_key_last($points)][2] = $width;

    // Start path
    $startY = rand($yRange[0], $yRange[1]);
    $path = "M0 {$startY} ";

    // Add smooth curves
    foreach ($points as [$x2, $y2, $x, $y]) {
        $path .= "S{$x2} {$y2} {$x} {$y} ";
    }

    // Close shape at bottom
    $path .= "L{$width} {$height} L0 {$height} Z";

    return trim($path);
}

// Converts a given RGB color to the corresponding HSL representation
function rgbToHsl(
    $r, 
    $g, 
    $b
): array {
    // Normalize RGB to [0,1]
    $r /= 255;
    $g /= 255;
    $b /= 255;

    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $l = ($max + $min) / 2;

    $delta = $max - $min;

    // Hue calculation
    if ($delta == 0) {
        $h = 0; // grayscale
        $s = 0;
    } else {
        // Saturation calculation for HSL
        if ($l < 0.5) {
            $s = $delta / ($max + $min);
        } else {
            $s = $delta / (2 - $max - $min);
        }

        if ($r == $max) {
            $h = ($g - $b) / $delta;
        } elseif ($g == $max) {
            $h = 2 + ($b - $r) / $delta;
        } else {
            $h = 4 + ($r - $g) / $delta;
        }

        $h *= 60;
        if ($h < 0) {
            $h += 360;
        }
    }

    return [
        (int) round($h),        // Hue 0-360
        (int) round($s * 100),  // Saturation 0-100%
        (int) round($l * 100)   // Lightness 0-100%
    ];
}

function generatePalette(
    ?array $startColor = null,
    int $steps = 5,
    int $hueStep = 8,
    int $lightnessDrop = 8
): array {
    // Clamp helper
    $clamp = fn($v, $min, $max) => max($min, min($max, $v));

    // Base color (HSL)
    if ($startColor === null) {
        $H = random_int(0, 359);      // Hue
        $S = random_int(55, 75);      // Saturation
        $L = random_int(50, 70);      // Lightness
    } else {
        [$r, $g, $b] = $startColor;
        [$H, $S, $L] = rgbToHsl($r, $g, $b); // Convert input RGB to HSL
    }

    $palette = [];

    for ($i = 0; $i < $steps; $i++) {

        if ($S < 5) {
            // Grayscale handling
            $h = $H; // Hue irrelevant
            $s = 0;
            $l = $clamp($L - ($i * 10), 3, 90);
        } else {
            // Normal analogous behavior
            $h = ($H + ($i * $hueStep)) % 360;
            $s = $clamp($S - $i * 2, 40, 100);
            $l = $clamp($L - $i * $lightnessDrop, 20, 100);
        }

        // [$r, $g, $b] = hslToRgb($h, $s, $l); // Convert HSL to RGB for output
        // $palette[] = [$r, $g, $b];
        $palette[] = [$h, $s, $l];
    }

    return $palette;
}

function Background(
    ?array $startColor = null,
    ?int $steps = 5
): void {
    // $colors = generatePalette(startColor: [120, 120, 120], steps: $steps);
    $colors = generatePalette(startColor: $startColor, steps: $steps);

    // $styling = '<style>.background{overflow:hidden;position:fixed;inset:0;z-index:-10;display:grid;}.background > .layer{display:grid;align-items:end}.background > .layer > svg{transform:translateY(1px)}</style>';
    $styling = '<style>.background{overflow:hidden;position:fixed;inset:0;z-index:-10;display:flex;flex-flow:column nowrap;justify-content: space-evenly;}.background > .layer{display:grid;align-items:end;}.background .filler{position: fixed;height: 100%;width: 100%;}.background > .layer > svg{transform:translateY(1px);}</style>';

    echo $styling;
    echo '<div class="background">';

    $layers = count($colors) - 1;

    for ($line = 0; $line < $layers; $line++) {
        [$h1, $s1, $l1] = $colors[$line];
        [$h2, $s2, $l2] = $colors[$line + 1];

        echo '
        <div class="layer">
            <div class="filler" style="background-color: hsl(' . $h1 . ', ' . $s1 . '%, ' . $l1 . '%);z-index: -' . $line . ';"></div>
            <svg id="visual-' . $line . '" viewBox="0 0 1000 100" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path
                    d="' . pathGenerator() . '"
                    fill="hsl(' . $h2 . ', ' . $s2 . '%, ' . $l2 . '%)"
                    stroke-linecap="round"
                    stroke-linejoin="miter">
                </path>
            </svg>
        </div>';
    }
    echo '<div class="filler" style="background-color: hsl(' . $h2 . ', ' . $s2 . '%, ' . $l2 . '%);z-index: -' . $line . ';"></div>';
    echo '</div>';
}
