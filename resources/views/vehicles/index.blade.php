<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Vehicles</h1>
    <a href="/">Home</a> | <a href="/parties">Parties</a> | <a href="/cases">Cases</a> | <a href="/cargo-items">Cargo Items</a> | <a href="/inspections">Inspections</a> | <a href="/documents">Documents</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>External ID</th>
                <th>Plate No</th>
                <th>Country</th>
                <th>Make</th>
                <th>Model</th>
                <th>VIN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $vehicle)
            <tr>
                <td>{{ $vehicle->id }}</td>
                <td>{{ $vehicle->external_id }}</td>
                <td>{{ $vehicle->plate_no }}</td>
                <td>{{ $vehicle->country }}</td>
                <td>{{ $vehicle->make }}</td>
                <td>{{ $vehicle->model }}</td>
                <td>{{ $vehicle->vin }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>