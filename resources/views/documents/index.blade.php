<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Documents</h1>
    <a href="/">Home</a> | <a href="/vehicles">Vehicles</a> | <a href="/parties">Parties</a> | <a href="/cases">Cases</a> | <a href="/cargo-items">Cargo Items</a> | <a href="/inspections">Inspections</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>External ID</th>
                <th>Case</th>
                <th>Type</th>
                <th>File Path</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documents as $document)
            <tr>
                <td>{{ $document->id }}</td>
                <td>{{ $document->external_id }}</td>
                <td>{{ $document->case ? $document->case->external_id : 'N/A' }}</td>
                <td>{{ $document->type }}</td>
                <td>{{ $document->file_path }}</td>
                <td>{{ $document->uploaded_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>