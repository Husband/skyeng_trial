<?php
function sumPositiveNumbers($a, $b)
{
    if (!ctype_digit((string)$a) || !ctype_digit((string)$b)) {
        throw new InvalidArgumentException('Arguments must contains only digits');
    }
    $maxLength = max(strlen($a), strlen($b));
    $a = str_pad($a, $maxLength, '0', STR_PAD_LEFT);
    $b = str_pad($b, $maxLength, '0', STR_PAD_LEFT);
    $strSum = "";
    $carry = 0;
    for ($i = $maxLength-1; $i >= 0; $i--) {
        $sum = $a[$i] + $b[$i] + $carry;
        $strSum = ($sum % 10) . $strSum;
        $carry = floor($sum/10);
    }
    return $strSum;
}

echo sumPositiveNumbers('123', '29');
