<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspections</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Inspections</h1>
    <a href="/">Home</a> | <a href="/vehicles">Vehicles</a> | <a href="/parties">Parties</a> | <a href="/cases">Cases</a> | <a href="/cargo-items">Cargo Items</a> | <a href="/documents">Documents</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>External ID</th>
                <th>Case</th>
                <th>Type</th>
                <th>Status</th>
                <th>Decision</th>
                <th>Comment</th>
                <th>Performed At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inspections as $inspection)
            <tr>
                <td>{{ $inspection->id }}</td>
                <td>{{ $inspection->external_id }}</td>
                <td>{{ $inspection->case ? $inspection->case->external_id : 'N/A' }}</td>
                <td>{{ $inspection->type }}</td>
                <td>{{ $inspection->status }}</td>
                <td>{{ $inspection->decision ?? 'Pending' }}</td>
                <td>{{ $inspection->comment }}</td>
                <td>{{ $inspection->performed_at }}</td>
                <td>
                    @can('perform inspections')
                        @if($inspection->status === 'pending')
                        <a href="{{ route('inspections.decision', [$inspection->case_id, $inspection->id]) }}" style="background-color: #2196F3; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Record Decision</a>
                        @else
                        <span style="color: green;">Completed</span>
                        @endif
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>