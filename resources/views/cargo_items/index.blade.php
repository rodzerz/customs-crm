<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargo Items</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Cargo Items</h1>
    <a href="/">Home</a> | <a href="/vehicles">Vehicles</a> | <a href="/parties">Parties</a> | <a href="/cases">Cases</a> | <a href="/inspections">Inspections</a> | <a href="/documents">Documents</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>External ID</th>
                <th>Case</th>
                <th>HS Code</th>
                <th>Description</th>
                <th>Weight</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cargoItems as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->external_id }}</td>
                <td>{{ $item->case ? $item->case->external_id : 'N/A' }}</td>
                <td>{{ $item->hs_code }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->weight }}</td>
                <td>{{ $item->value }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>