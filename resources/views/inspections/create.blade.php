<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Inspection</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        form { background-color: #f5f5f5; padding: 20px; border-radius: 5px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .back-link { display: inline-block; margin-bottom: 20px; }
        .info { background-color: #e7f3fe; border-left: 4px solid #2196F3; padding: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/inspections" class="back-link">← Back to Inspections</a>
        
        <h1>Create New Inspection</h1>
        
        <div class="info">
            <strong>Case Details:</strong><br>
            Case ID: {{ $case->external_id }}<br>
            Vehicle: {{ $case->vehicle ? $case->vehicle->plate_no : 'N/A' }}<br>
            Current Status: <strong>{{ ucfirst(str_replace('_', ' ', $case->status)) }}</strong>
        </div>

        <form method="POST" action="{{ route('inspections.store', $case->id) }}">
            @csrf

            <label for="type">Inspection Type <span style="color: red;">*</span></label>
            <select name="type" id="type" required>
                <option value="">-- Select Type --</option>
                <option value="document">Document Review</option>
                <option value="RTG">RTG Scan</option>
                <option value="physical">Physical Inspection</option>
            </select>

            <label for="comment">Comment</label>
            <textarea name="comment" id="comment" rows="4" placeholder="Add any notes about this inspection..."></textarea>

            <button type="submit">Create Inspection</button>
        </form>
    </div>
</body>
</html>
