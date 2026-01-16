<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Inspection Decision</title>
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
        .decision-option { margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/inspections" class="back-link">← Back to Inspections</a>
        
        <h1>Record Inspection Decision</h1>
        
        <div class="info">
            <strong>Inspection Details:</strong><br>
            Case: {{ $inspection->case->external_id }}<br>
            Type: <strong>{{ ucfirst($inspection->type) }}</strong><br>
            Status: <strong>{{ ucfirst($inspection->status) }}</strong><br>
            Comment: {{ $inspection->comment ?? 'None' }}
        </div>

        <form method="POST" action="{{ route('inspections.record', [$inspection->case_id, $inspection->id]) }}">
            @csrf

            <label for="decision">Inspection Decision <span style="color: red;">*</span></label>
            
            <div class="decision-option">
                <input type="radio" name="decision" id="release" value="release" required>
                <label for="release" style="margin-top: 0; font-weight: normal;">
                    <strong>Release</strong> - Goods are cleared for release
                </label>
            </div>

            <div class="decision-option">
                <input type="radio" name="decision" id="hold" value="hold" required>
                <label for="hold" style="margin-top: 0; font-weight: normal;">
                    <strong>Hold</strong> - Goods should be held for further investigation
                </label>
            </div>

            <div class="decision-option">
                <input type="radio" name="decision" id="reject" value="reject" required>
                <label for="reject" style="margin-top: 0; font-weight: normal;">
                    <strong>Reject</strong> - Goods are rejected and not cleared
                </label>
            </div>

            <label for="decision_reason">Decision Reason</label>
            <textarea name="decision_reason" id="decision_reason" rows="4" placeholder="Explain the reason for this decision..."></textarea>

            <button type="submit">Record Decision</button>
        </form>
    </div>
</body>
</html>
