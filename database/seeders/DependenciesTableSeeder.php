<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dependency;

class DependenciesTableSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('seeders/inventario.csv');
        if (! file_exists($csvPath)) {
            $this->command->error("CSV no encontrado en {$csvPath}");
            return;
        }

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file, 0, ';');
        $idx = array_search('dependencia', $header);
        if ($idx === false) {
            $this->command->error("Columna 'dependencia' no hallada en el CSV");
            fclose($file);
            return;
        }

        $seen = [];
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $name = trim($row[$idx]);
            if ($name !== '' && ! in_array($name, $seen, true)) {
                $seen[] = $name;
                Dependency::firstOrCreate(['name' => $name]);
            }
        }

        fclose($file);
        $this->command->info(count($seen)." dependencias importadas.");
    }
}
