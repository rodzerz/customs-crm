<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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

        // Try to load from local file first, then API
        $dataFile = storage_path('app/api_data.json');
        if (file_exists($dataFile)) {
            $this->info('Loading data from local file...');
            $data = json_decode(file_get_contents($dataFile), true);
            $this->info('Data loaded from file successfully.');
        } else {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(120)  // Increased timeout
                    ->get('https://deskplan.lv/muita/app.json');
                if (!$response->ok()) {
                    $this->error('Failed to fetch data from API: ' . $response->status() . ' - ' . $response->body());
                    return;
                }
                $data = $response->json();
                // Save to file for future use
                file_put_contents($dataFile, json_encode($data));
                $this->info('Data fetched from API and saved locally.');
            } catch (\Exception $e) {
                $this->error('Exception during API call: ' . $e->getMessage());
                return;
            }
        }

        // Import without single transaction for large data
        $this->info('Starting import...');

        // ------------------------------
        // 1️⃣ Vehicles
        // ------------------------------
        $this->info('Importing vehicles...');
        $count = 0;
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
            $count++;
            if ($count % 500 == 0) {
                $this->info("Imported $count vehicles...");
            }
        }
        $this->info('Vehicles imported: ' . Vehicle::count());

        // ------------------------------
        // 2️⃣ Parties
        // ------------------------------
        $this->info('Importing parties...');
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
        $this->info('Parties imported: ' . Party::count());

        // ------------------------------
        // 3️⃣ Cases
        // ------------------------------
        $this->info('Importing cases...');
        foreach ($data['cases'] ?? [] as $c) {
            $case = CaseModel::updateOrCreate(
                ['external_id' => $c['id']],
                [
                    'vehicle_id' => Vehicle::where('external_id', $c['vehicle_id'])->first()?->id,
                    'status' => $c['status'],
                    'risk_score' => count($c['risk_flags'] ?? []), // Use count of risk flags as score
                    'arrived_at' => $c['arrival_ts'] ? Carbon::parse($c['arrival_ts'])->toDateTimeString() : null,
                ]
            );

            // Attach parties
            $partyIds = [];
            if (isset($c['declarant_id'])) {
                $party = Party::where('external_id', $c['declarant_id'])->first();
                if ($party) $partyIds[$party->id] = ['role' => 'declarant'];
            }
            if (isset($c['consignee_id'])) {
                $party = Party::where('external_id', $c['consignee_id'])->first();
                if ($party) $partyIds[$party->id] = ['role' => 'consignee'];
            }
            $case->parties()->syncWithoutDetaching($partyIds);
        }
        $this->info('Cases imported: ' . CaseModel::count());

        // ------------------------------
        // 4️⃣ Inspections
        // ------------------------------
        $this->info('Importing inspections...');
        foreach ($data['inspections'] ?? [] as $insp) {
            Inspection::updateOrCreate(
                ['external_id' => $insp['id']],
                [
                    'case_id' => CaseModel::where('external_id', $insp['case_id'])->first()?->id,
                    'type' => $insp['type'] ?? null,
                    'status' => null, // No result in API
                    'comment' => $insp['location'] ?? null,
                    'performed_at' => $insp['start_ts'] ? Carbon::parse($insp['start_ts'])->toDateTimeString() : null,
                ]
            );
        }
        $this->info('Inspections imported: ' . Inspection::count());

        // ------------------------------
        // 5️⃣ Documents
        // ------------------------------
        $this->info('Importing documents...');
        foreach ($data['documents'] ?? [] as $doc) {
            Document::updateOrCreate(
                ['external_id' => $doc['id']],
                [
                    'case_id' => CaseModel::where('external_id', $doc['case_id'])->first()?->id,
                    'type' => $doc['category'] ?? null,
                    'file_path' => $doc['filename'] ?? null,
                    'uploaded_at' => null, // No upload date in API
                ]
            );
        }
        $this->info('Documents imported: ' . Document::count());

        $this->info('All imports completed.');

        $this->info('Import completed successfully!');
        $this->info('Imported counts:');
        $this->info('Vehicles: ' . Vehicle::count());
        $this->info('Parties: ' . Party::count());
        $this->info('Cases: ' . CaseModel::count());
        $this->info('Cargo Items: ' . CaseCargoItem::count());
        $this->info('Inspections: ' . Inspection::count());
        $this->info('Documents: ' . Document::count());
    }
}
