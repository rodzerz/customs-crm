<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cases</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Cases</h1>
    <div style="margin-bottom: 15px;">
        <a href="/">Home</a> | <a href="/vehicles">Vehicles</a> | <a href="/parties">Parties</a> | <a href="/cargo-items">Cargo Items</a> | <a href="/inspections">Inspections</a> | <a href="/documents">Documents</a> | <a href="/analytics">Analytics</a>
        @can('create cases')
        | <a href="{{ route('cases.create') }}" style="background-color: #4CAF50; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; display: inline-block;">+ Create Case</a>
        @endcan
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>External ID</th>
                <th>Vehicle</th>
                <th>Status</th>
                <th>Risk Score</th>
                <th>Arrived At</th>
                <th>Parties</th>
                <th>Cargo Items</th>
                <th>Inspections</th>
                <th>Documents</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cases as $case)
            <tr>
                <td>{{ $case->id }}</td>
                <td>{{ $case->external_id }}</td>
                <td>{{ $case->vehicle ? $case->vehicle->plate_no : 'N/A' }}</td>
                <td>{{ $case->status }}</td>
                <td>{{ $case->risk_score }}</td>
                <td>{{ $case->arrived_at }}</td>
                <td>
                    @foreach($case->parties as $party)
                        {{ $party->name }} ({{ $party->pivot->role }})<br>
                    @endforeach
                </td>
                <td>{{ $case->cargoItems->count() }}</td>
                <td>{{ $case->inspections->count() }}</td>
                <td>{{ $case->documents->count() }}</td>
                <td>
                    @can('update cases')
                    <a href="{{ route('cases.edit', $case->id) }}" style="background-color: #4CAF50; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; margin-right: 5px;">Edit</a>
                    @endcan
                    @can('perform inspections')
                    <a href="{{ route('inspections.create', $case->id) }}" style="background-color: #FF9800; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; margin-right: 5px;">Inspect</a>
                    @endcan
                    @can('submit declarations')
                    <a href="{{ route('documents.create', $case->id) }}" style="background-color: #2196F3; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Docs</a>
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>