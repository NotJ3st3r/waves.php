<?php
function pathGenerator(
    int $width = 1000,
    int $height = 50,
    int $segments = 9,
    array $yRange = [0.25, 0.75]
): string {
    $points = [];
    $yRange[0] = $height * $yRange[0];
    $yRange[1] = $height * $yRange[1];

    // Generate curve points
    for ($i = 0; $i < $segments; $i++) {
        $progress = ($i + 1) / $segments;

        $x = (int) round($progress * $width);
        $y = rand($yRange[0], $yRange[1]);

        // Control point slightly before end point
        $x2 = $x - rand(25, 50) + rand(-10, 10);
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
    int $maxSaturationDrop = 15,
    int $maxLightnessDrop = 30
): array {
    $clamp = fn($v, $min, $max) => max($min, min($max, $v));

    if ($startColor === null) {
        $H = random_int(0, 359);
        $S = random_int(55, 75);
        $L = random_int(50, 70);
    } else {
        [$r, $g, $b] = $startColor;
        [$H, $S, $L] = rgbToHsl($r, $g, $b);
    }

    $palette = [];

    // Prevent division by zero
    $tMax = max(1, $steps - 1);

    for ($i = 0; $i < $steps; $i++) {
        $t = $i / $tMax; // normalized 0 -> 1

        if ($S < 5) {
            // grayscale: only lightness changes
            $h = $H;
            $s = 0;
            $l = $clamp($L - ($t * 40), 3, 90);
        } else {
            // Hue progression
            $h = ($H - ($i * $hueStep)) % 360;

            // Distribute total drop across steps
            $s = $clamp($S - ($t * $maxSaturationDrop), 40, 100);
            $l = $clamp($L - ($t * $maxLightnessDrop), 20, 100);
        }

        $palette[] = [
            (int) round($h),
            (int) round($s),
            (int) round($l)
        ];
    }
    return $palette;
}

function Background(
    ?array $startColor = null,
    ?int $steps = 5,
    ?int $waveHeight = 50
): void {
    $colors = generatePalette(startColor: $startColor, steps: $steps);
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
            <svg id="visual-' . $line . '" viewBox="0 0 1000 ' . $waveHeight . '" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path
                    d="' . pathGenerator(height: $waveHeight) . '"
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
