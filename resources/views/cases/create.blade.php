<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Case</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        form { background-color: #f5f5f5; padding: 20px; border-radius: 5px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .back-link { display: inline-block; margin-bottom: 20px; }
        .info { background-color: #e3f2fd; border-left: 4px solid #2196F3; padding: 10px; margin-bottom: 20px; }
        .error { color: red; font-size: 12px; }
        .required { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/cases" class="back-link">← Back to Cases</a>
        
        <h1>Create New Case</h1>
        
        <div class="info">
            <strong>Instructions:</strong> Fill in the case details below. You can add cargo items, parties, and documents after the case is created.
        </div>

        @if ($errors->any())
        <div style="background-color: #ffebee; border-left: 4px solid #f44336; padding: 10px; margin-bottom: 20px; color: #c62828;">
            <strong>Validation Errors:</strong>
            <ul style="margin: 10px 0 0 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('cases.store') }}">
            @csrf

            <label for="external_id">
                Case ID <span class="required">*</span>
            </label>
            <input type="text" name="external_id" id="external_id" value="{{ old('external_id') }}" placeholder="e.g., CASE-2026-001" required>
            @error('external_id')
                <span class="error">{{ $message }}</span>
            @enderror

            <label for="vehicle_id">
                Vehicle <span class="required">*</span>
            </label>
            <select name="vehicle_id" id="vehicle_id" required>
                <option value="">-- Select a Vehicle --</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                        {{ $vehicle->plate_no }} - {{ $vehicle->make }} {{ $vehicle->model }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_id')
                <span class="error">{{ $message }}</span>
            @enderror

            <label for="route">Route</label>
            <input type="text" name="route" id="route" value="{{ old('route') }}" placeholder="e.g., EU-LV">
            @error('route')
                <span class="error">{{ $message }}</span>
            @enderror

            <label for="origin_country">Origin Country</label>
            <input type="text" name="origin_country" id="origin_country" value="{{ old('origin_country') }}" placeholder="e.g., DE (ISO code)">
            @error('origin_country')
                <span class="error">{{ $message }}</span>
            @enderror

            <label for="destination_country">Destination Country</label>
            <input type="text" name="destination_country" id="destination_country" value="{{ old('destination_country') }}" placeholder="e.g., LV (ISO code)">
            @error('destination_country')
                <span class="error">{{ $message }}</span>
            @enderror

            <label for="declared_value">Declared Value (EUR)</label>
            <input type="number" name="declared_value" id="declared_value" value="{{ old('declared_value') }}" step="0.01" placeholder="0.00">
            @error('declared_value')
                <span class="error">{{ $message }}</span>
            @enderror

            <button type="submit">Create Case</button>
        </form>
    </div>
</body>
</html>
