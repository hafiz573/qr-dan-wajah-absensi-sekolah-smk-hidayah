<?php

$romanMap = [
    'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6,
    'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12
];
$numToRoman = array_flip($romanMap);

$testClasses = [
    'X RPL',
    'XI TKJ',
    'XII MM',
    'VI A',
    'V B',
    'VII Bangsa',
    'IX Negara',
    'I',
];

$maxGrade = 12; // Example for SMK

foreach ($testClasses as $oldClass) {
    if (preg_match('/^([IVX]+)(.*)$/i', $oldClass, $matches)) {
        $currentRoman = strtoupper($matches[1]);
        $suffix = $matches[2];

        if (isset($romanMap[$currentRoman])) {
            $currentLevel = $romanMap[$currentRoman];
            $nextLevel = $currentLevel + 1;

            if ($nextLevel > $maxGrade) {
                $newClass = 'Lulus ' . $oldClass;
            } else {
                $nextRoman = $numToRoman[$nextLevel];
                $newClass = $nextRoman . $suffix;
            }
            echo "{$oldClass} -> {$newClass}\n";
        }
    }
}
