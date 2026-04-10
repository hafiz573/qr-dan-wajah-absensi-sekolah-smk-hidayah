<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$contact = App\Models\TeacherContact::updateOrCreate(
    ['class_name' => 'XI RPL'],
    ['name' => 'Guru XI RPL (Otomatis)', 'phone_number' => '089637426532']
);

echo "Berhasil menambahkan kontak guru untuk kelas XI RPL.\n";
