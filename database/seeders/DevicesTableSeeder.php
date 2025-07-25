<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\Dependency;

class DevicesTableSeeder extends Seeder
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

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $data = array_combine($header, $row);

            // Asociar o crear dependencia
            $depName = trim($data['dependencia']);
            $dependency = Dependency::firstOrCreate(['name' => $depName]);

            Device::create([
                'device_name'     => $data['nombreEquipo'],
                'property'        => $data['propiedad'] ?: null,
                'status'          => $data['estado'] ?: null,
                'os'              => $data['so'] ?: null,
                'brand'           => $data['marca'] ?: null,
                'model'           => $data['modelo'] ?: null,
                'cpu'             => $data['cpu'] ?: null,
                'office_package'  => $data['paqueteOfimatica'] ?: null,
                'asset_tag'       => $data['activoFijo'] ?: null,
                'printer_asset'   => $data['activoFijoImpresora'] ?: null,
                'scanner_asset'   => $data['activoFijoEscaner'] ?: null,
                'ram'             => is_numeric($data['ram']) ? (int)$data['ram'] : null,
                'hdd'             => is_numeric($data['hdd']) ? (int)$data['hdd'] : null,
                'ip'              => $data['ip'] ?: null,
                'mac'             => $data['mac'] ?: null,
                'serial'          => $data['serial'] ?: null,
                'anydesk'         => $data['anydesk'] ?: null,
                'operator'        => $data['operador'] ?: null,
                'notes'           => $data['observaciones'] ?: null,
                'dependency_id'   => $dependency->id,
                'history'         => null,
            ]);
        }

        fclose($file);
        $this->command->info("Dispositivos importados correctamente.");
    }
}
