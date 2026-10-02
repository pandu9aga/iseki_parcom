<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$m = App\Models\ModelAi::firstOrCreate(
    ['Name_Model' => 'Shaft GC'],
    ['Path_Model' => '../storage/model/shaft_gc/']
);

$c = App\Models\Comparison::firstOrCreate(
    ['Name_Comparison' => 'Shaft GC'],
    ['Id_Model' => $m->Id_Model]
);

echo $c->Id_Comparison;
