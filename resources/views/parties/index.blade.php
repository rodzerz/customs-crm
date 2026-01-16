<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parties</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Parties</h1>
    <a href="/">Home</a> | <a href="/vehicles">Vehicles</a> | <a href="/cases">Cases</a> | <a href="/cargo-items">Cargo Items</a> | <a href="/inspections">Inspections</a> | <a href="/documents">Documents</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>External ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Country</th>
                <th>Registration No</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parties as $party)
            <tr>
                <td>{{ $party->id }}</td>
                <td>{{ $party->external_id }}</td>
                <td>{{ $party->name }}</td>
                <td>{{ $party->type }}</td>
                <td>{{ $party->country }}</td>
                <td>{{ $party->registration_no }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>