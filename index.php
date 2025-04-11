<?php
// Generates a svg path
function pathGenerator(){
    // max width of the path
    $width = 1000;
    // max height of the path
    $height = 100;

    // empty string to store the path in
    $path = "";
    // first point of the path with a random height
    $firstPoint = "M0 " . rand(30, 70) . " ";
    // ending of the path with the cloing loop to the start
    $lastPoint = "L" . $width . " 100 L0 100Z ";

    // array to store the svg path points in
    // ToDo: variable length of the path
    $points = [
        $first = [],
        $second = [],
        $third = [],
        $fourth = [],
        $fifth = [],
        $sixth = [],
        $seventh = [],
        $eighth = [],
        $nineth = []
    ];

    // Generate a number of bézier curves from the current position to x,y with x2,y2 as the end control point and a reflection of the previous curve command's end control point as the start control point
    for ($i=0; $i < count($points); $i++) {
        // $x
        // $x = ($i * ($width / (count($points) + 1))) + rand(75, 125);
        $x = ($i * ($width / (count($points)))) + rand(75, 125);
        // $y
        $y = rand(30, 70);
        // $x2
        $x2 = $x + rand(-50, -25);
        // $y2
        $y2 = $y + rand(-20, 20);
        // add the generated points to the array
        $points[$i] = [$x2, $y2, $x, $y];
    }

    // edit the last path point to be on the right edge of the svg
    $points[array_key_last($points)][2] = $width;
    $points[array_key_last($points)][3] = rand(30, 70);

    // add the points in the array to the path variable and format it as a functioning svg path
    for ($i=0; $i < count($points); $i++) { 
        $path .= "S" . $points[$i][0] . " " . $points[$i][1] . " " . $points[$i][2] . " " . $points[$i][3] . " ";
    }

    // add the starting and ending points in front and at the end of the path string
    $path = $firstPoint . $path . $lastPoint;

    // retunr the generated path
    return $path;
}

// Converts a given RGB color to the according HSV representation
function toHSV($red, $green, $blue) {
    $red = $red / 255;
    $green = $green / 255;
    $blue = $blue / 255;

    $min = min($red, $green, $blue);
    $max = max($red, $green, $blue);

    $value = $max;
    $delta = $max - $min;

    if ($delta == 0) {
        return [0, 0, (int) round($value * 100)];
    }

    $saturation = 0;

    if ($max != 0) {
        $saturation = $delta / $max;
    } else {
        $saturation = 0;
        $hue = -1;
        return [(int) round($hue), (int) round($saturation), (int) round($value)];
    }
    if ($red == $max) {
        $hue = ($green - $blue) / $delta;
    } else {
        if ($green == $max) {
            $hue = 2 + ($blue - $red) / $delta;
        } else {
            $hue = 4 + ($red - $green) / $delta;
        }
    }
    $hue *= 60;
    if ($hue < 0) {
        $hue += 360;
    }

    return [(int) round($hue), (int) round($saturation * 100), (int) round($value * 100)];
}

// Converts a given HSV color to the according RGB representation
function toRGB($hue, $saturation, $value) {
    $hue = $hue / 360;
    $saturation = $saturation / 100;
    $value = $value / 100;
    if ($saturation == 0) {
        $red = $value * 255;
        $green = $value * 255;
        $blue = $value * 255;
    } else {
        $var_h = $hue * 6;
        $var_i = floor($var_h);
        $var_1 = $value * (1 - $saturation);
        $var_2 = $value * (1 - $saturation * ($var_h - $var_i));
        $var_3 = $value * (1 - $saturation * (1 - ($var_h - $var_i)));

        if ($var_i == 0) {
            $var_r = $value;
            $var_g = $var_3;
            $var_b = $var_1;
        } elseif ($var_i == 1) {
            $var_r = $var_2;
            $var_g = $value;
            $var_b = $var_1;
        } elseif ($var_i == 2) {
            $var_r = $var_1;
            $var_g = $value;
            $var_b = $var_3;
        } elseif ($var_i == 3) {
            $var_r = $var_1;
            $var_g = $var_2;
            $var_b = $value;
        } else {
            if ($var_i == 4) {
                $var_r = $var_3;
                $var_g = $var_1;
                $var_b = $value;
            } else {
                $var_r = $value;
                $var_g = $var_1;
                $var_b = $var_2;
            }
        }

        $red = round($var_r * 255);
        $green = round($var_g * 255);
        $blue = round($var_b * 255);
    }
    return [(int) $red, (int) $green, (int) $blue];
}

// Takes an array and concatenates the values seperated by a ','
function arrayToString($array) {
    for ($i=0; $i < count($array); $i++) {
        $string .= $array[$i];
        if ($i < (count($array) - 1)) {
            $string .= ', ';
        }
    }
    return $string;
}

function HueShifter($input, $shiftOffset = 10, $direction = 'left') {
    switch ($direction) {
        case 'left': // decrease
            $shiftedInput = $input - $shiftOffset;
            if ($shiftedInput < 0) {
                $shiftedInput = ($input - $shiftOffset) + 360;
            }
            return $shiftedInput;
            break;

        case 'right': // increase
        default:
            $shiftedInput = $input + $shiftOffset;
            if ($shiftedInput > 360) {
                $shiftedInput = ($input + $shiftOffset) - 360;
            }
            return $shiftedInput;
            break;
    }
}

function Shifter($input, $shiftOffset = 10, $direction = 'right'){
    switch ($direction) {
        case 'left': // decrease
            $shiftedInput = $input - $shiftOffset;
            if ($shiftedInput < 0){
                return 0;
            }
            return $shiftedInput;
            break;

        case 'right': // increase
        default:
            $shiftedInput = $input + $shiftOffset;
            if ($shiftedInput > 100) {
                return 100;
            }
            return $shiftedInput;
            break;
    }
}

// Generates a color pallete from the given color (The default color would generate a yellow to orange pallet)  = [206, 44, 12]
function colorPallete($startColor = [], $shiftOffset = 5){
    // take the color as RGB
    // convert the color to HSB (hue, saturation, brightness)
    // shift hue up and brightness down, don't change saturation

    // Generate a random HSV color to use
    if (empty($startColor)) {
        $H = rand(0, 359);
        $S = rand(50, 70);
        $V = rand(40, 60);
    } else {
        $RGB = $startColor;

        // $HSV = RGBtoHSV($RGB[0], $RGB[1], $RGB[2]);
        $HSV = toHSV($RGB[0], $RGB[1], $RGB[2]);

        $H = trim(round($HSV[0], 0));
        $S = trim(round($HSV[1], 0));
        $V = trim(round($HSV[2], 0));
    }

    $first = [
        $H,
        $S,
        $V
    ];
    $second = [
        HueShifter($first[0], 10), 
        Shifter($first[1], $shiftOffset, 'left'),
        Shifter($first[2], $shiftOffset)
    ];
    $third = [
        HueShifter($second[0], 10), 
        Shifter($second[1], $shiftOffset, 'left'),
        Shifter($second[2], $shiftOffset)
    ];
    $fourth = [
        HueShifter($third[0], 10), 
        Shifter($third[1], $shiftOffset, 'left'),
        Shifter($third[2], $shiftOffset)
    ];
    $fifth = [
        HueShifter($fourth[0], 10),
        Shifter($fourth[1], $shiftOffset, 'left'),
        Shifter($fourth[2], $shiftOffset)
    ];

    $colorPallete = [
        arrayToString(toRGB($fifth[0], $fifth[1], $fifth[2])),
        arrayToString(toRGB($fourth[0], $fourth[1], $fourth[2])),
        arrayToString(toRGB($third[0], $third[1], $third[2])),
        arrayToString(toRGB($second[0], $second[1], $second[2])),
        arrayToString(toRGB($first[0], $first[1], $first[2]))
    ];

    // vardump($colorPallete);
    return $colorPallete;
}

// IMPORTANT: Since the color generator is currently only capable of generating five colors, it is not possible to increase the number of lines
function giveBackground(){
    $colors = colorPallete(array(8, 8, 8));
    $text = array("255, 68, 0", "240, 240, 240");

    $styling = '<style>.background{overflow:hidden;position:fixed;inset:0;z-index:-10;display:grid;}.background > .layer{display:grid;align-items:end}.background > .layer > svg{transform:translateY(1px)}</style>';

    echo($styling);
    echo('<div class="background">');
    for ($line=0; $line < 4; $line++) { 
        echo('
        <div class="layer" style="background-color: rgb(' . $colors[$line] . ')">
            <svg id="visual" viewBox="0 0 1000 100" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1">
                <path
                    d=" ' . pathGenerator() . ' "
                    fill=" rgb(' . $colors[($line + 1)] . ')" stroke-linecap="round" stroke-linejoin="miter"></path>
            </svg>
        </div>
        ');
    }
    echo('</div>');
}

giveBackground();
