<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Case</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        form { background-color: #f5f5f5; padding: 20px; border-radius: 5px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .back-link { display: inline-block; margin-bottom: 20px; }
        .error { color: red; font-size: 12px; }
        .info { background-color: #e7f3fe; border-left: 4px solid #2196F3; padding: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/cases" class="back-link">← Back to Cases</a>
        
        <h1>Edit Case {{ $case->external_id }}</h1>
        
        <div class="info">
            <strong>Case Details:</strong><br>
            Vehicle: {{ $case->vehicle ? $case->vehicle->plate_no : 'N/A' }}<br>
            Current Status: <strong>{{ ucfirst(str_replace('_', ' ', $case->status)) }}</strong><br>
            Risk Score: {{ $case->risk_score ?? 'Not analyzed' }}
        </div>

        <form method="POST" action="{{ route('cases.update', $case->id) }}">
            @csrf
            @method('PUT')

            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="">-- Select Status --</option>
                <option value="new" @selected($case->status === 'new')>New</option>
                <option value="screening" @selected($case->status === 'screening')>Screening</option>
                <option value="in_inspection" @selected($case->status === 'in_inspection')>In Inspection</option>
                <option value="on_hold" @selected($case->status === 'on_hold')>On Hold</option>
                <option value="released" @selected($case->status === 'released')>Released</option>
                <option value="rejected" @selected($case->status === 'rejected')>Rejected</option>
                <option value="closed" @selected($case->status === 'closed')>Closed</option>
            </select>

            <label for="route">Route</label>
            <input type="text" name="route" id="route" value="{{ $case->route }}" placeholder="e.g., EU-LV">

            <label for="origin_country">Origin Country</label>
            <input type="text" name="origin_country" id="origin_country" value="{{ $case->origin_country }}" placeholder="e.g., DE">

            <label for="destination_country">Destination Country</label>
            <input type="text" name="destination_country" id="destination_country" value="{{ $case->destination_country }}" placeholder="e.g., LV">

            <label for="declared_value">Declared Value (EUR)</label>
            <input type="number" name="declared_value" id="declared_value" value="{{ $case->declared_value }}" step="0.01" placeholder="0.00">

            <label for="actual_value">Actual Value (EUR)</label>
            <input type="number" name="actual_value" id="actual_value" value="{{ $case->actual_value }}" step="0.01" placeholder="0.00">

            <button type="submit">Update Case</button>
        </form>
    </div>
</body>
</html>
