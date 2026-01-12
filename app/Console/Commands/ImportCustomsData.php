<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

use App\Models\Vehicle;
use App\Models\Party;
use App\Models\CaseModel;
use App\Models\CaseCargoItem;
use App\Models\Inspection;
use App\Models\Document;

class ImportCustomsData extends Command
{
    protected $signature = 'import:customs';
    protected $description = 'Import data from customs API';

    public function handle()
    {
        $this->info('Fetching data from customs API...');

        $response = Http::withoutVerifying()
            ->get('https://deskplan.lv/muita/app.json');

        if (!$response->ok()) {
            $this->error('Failed to fetch data from API');
            return;
        }

        $data = $response->json();

        DB::transaction(function () use ($data) {

            // ------------------------------
            // 1️⃣ Vehicles
            // ------------------------------
            foreach ($data['vehicles'] ?? [] as $v) {
                Vehicle::updateOrCreate(
                    ['external_id' => $v['id']],
                    [
                        'plate_no' => $v['plate_no'],
                        'country'  => $v['country'],
                        'make'     => $v['make'],
                        'model'    => $v['model'],
                        'vin'      => $v['vin'],
                    ]
                );
            }

            // ------------------------------
            // 2️⃣ Parties
            // ------------------------------
            foreach ($data['parties'] ?? [] as $p) {
                Party::updateOrCreate(
                    ['external_id' => $p['id']],
                    [
                        'name' => $p['name'],
                        'type' => $p['type'],
                        'country' => $p['country'],
                        'registration_no' => $p['registration_no'] ?? null,
                    ]
                );
            }

            // ------------------------------
            // 3️⃣ Cases
            // ------------------------------
            foreach ($data['cases'] ?? [] as $c) {
                $vehicle = Vehicle::where('external_id', $c['vehicle_id'])->first();

                $case = CaseModel::updateOrCreate(
                    ['external_id' => $c['id']],
                    [
                        'vehicle_id' => $vehicle?->id,
                        'status' => $c['status'],
                        'risk_score' => $c['risk_score'] ?? 0,
                        'arrived_at' => $c['arrived_at'] ?? null,
                    ]
                );

                // Piesaistīt parties pivot tabulai
                foreach ($c['parties'] ?? [] as $cp) {
                    $party = Party::where('external_id', $cp['id'])->first();
                    if ($party) {
                        $case->parties()->syncWithoutDetaching([
                            $party->id => ['role' => $cp['role'] ?? null]
                        ]);
                    }
                }

                // ------------------------------
                // 4️⃣ Cargo Items
                // ------------------------------
                foreach ($c['cargo_items'] ?? [] as $cargo) {
                    CaseCargoItem::updateOrCreate(
                        ['external_id' => $cargo['id']],
                        [
                            'case_id' => $case->id,
                            'hs_code' => $cargo['hs_code'] ?? null,
                            'description' => $cargo['description'] ?? null,
                            'weight' => $cargo['weight'] ?? null,
                            'value' => $cargo['value'] ?? null,
                        ]
                    );
                }

                // ------------------------------
                // 5️⃣ Inspections
                // ------------------------------
                foreach ($c['inspections'] ?? [] as $insp) {
                    Inspection::updateOrCreate(
                        ['external_id' => $insp['id']],
                        [
                            'case_id' => $case->id,
                            'type' => $insp['type'] ?? null,
                            'status' => $insp['status'] ?? null,
                            'comment' => $insp['comment'] ?? null,
                            'performed_at' => $insp['performed_at'] ?? null,
                        ]
                    );
                }

                // ------------------------------
                // 6️⃣ Documents
                // ------------------------------
                foreach ($c['documents'] ?? [] as $doc) {
                    Document::updateOrCreate(
                        ['external_id' => $doc['id']],
                        [
                            'case_id' => $case->id,
                            'type' => $doc['type'] ?? null,
                            'file_path' => $doc['file_path'] ?? null,
                            'uploaded_at' => $doc['uploaded_at'] ?? null,
                        ]
                    );
                }
            }
        });

        $this->info('Import completed successfully!');
    }
}
