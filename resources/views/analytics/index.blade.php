<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 30px; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #0066cc; }
        .nav a:hover { text-decoration: underline; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-value { font-size: 32px; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; margin-top: 5px; font-size: 14px; }
        .stat-card.warning { border-left: 4px solid #ff9800; }
        .stat-card.success { border-left: 4px solid #4CAF50; }
        .stat-card.info { border-left: 4px solid #2196F3; }
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .chart-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .chart-card h3 { color: #333; margin-bottom: 15px; }
        .chart-container { position: relative; height: 300px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; font-weight: 600; color: #333; }
        tr:hover { background-color: #f9f9f9; }
        .recent-cases h2 { color: #333; margin-top: 30px; margin-bottom: 15px; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-new { background-color: #e3f2fd; color: #1976d2; }
        .status-screening { background-color: #f3e5f5; color: #7b1fa2; }
        .status-in_inspection { background-color: #fff3e0; color: #e65100; }
        .status-on_hold { background-color: #ffe0b2; color: #e65100; }
        .status-released { background-color: #c8e6c9; color: #2e7d32; }
        .status-rejected { background-color: #ffcdd2; color: #c62828; }
        .status-closed { background-color: #e0e0e0; color: #424242; }
        .risk-high { color: #d32f2f; font-weight: bold; }
        .risk-medium { color: #ff9800; font-weight: bold; }
        .risk-low { color: #4CAF50; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Analytics Dashboard</h1>
        
        <div class="nav">
            <a href="/">Home</a> | 
            <a href="/cases">Cases</a> | 
            <a href="/inspections">Inspections</a> | 
            <a href="/documents">Documents</a> | 
            <a href="/analytics">Analytics</a>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card info">
                <div class="stat-value">{{ $totalCases }}</div>
                <div class="stat-label">Total Cases</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-value">{{ $highRiskCases }}</div>
                <div class="stat-label">High Risk Cases</div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-value">{{ number_format($averageRiskScore, 1) }}</div>
                <div class="stat-label">Average Risk Score</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-value">{{ $completedInspections }} / {{ $totalInspections }}</div>
                <div class="stat-label">Inspections Completed</div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-value">{{ $totalDocuments }}</div>
                <div class="stat-label">Total Documents</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <!-- Cases by Status Chart -->
            <div class="chart-card">
                <h3>Cases by Status</h3>
                <div class="chart-container">
                    <canvas id="casesByStatusChart"></canvas>
                </div>
            </div>

            <!-- Inspections by Type Chart -->
            <div class="chart-card">
                <h3>Inspections by Type</h3>
                <div class="chart-container">
                    <canvas id="inspectionsByTypeChart"></canvas>
                </div>
            </div>

            <!-- Inspection Decisions Chart -->
            <div class="chart-card">
                <h3>Inspection Decisions</h3>
                <div class="chart-container">
                    <canvas id="decisionsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Cases Table -->
        <div class="recent-cases">
            <h2>📋 Recent Cases</h2>
            <table>
                <thead>
                    <tr>
                        <th>Case ID</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Risk Score</th>
                        <th>Inspections</th>
                        <th>Documents</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCases as $case)
                    <tr>
                        <td>{{ $case->external_id }}</td>
                        <td>{{ $case->vehicle ? $case->vehicle->plate_no : 'N/A' }}</td>
                        <td>
                            <span class="status-badge status-{{ $case->status }}">
                                {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                            </span>
                        </td>
                        <td>
                            @if($case->risk_score >= 100)
                            <span class="risk-high">{{ $case->risk_score }}</span>
                            @elseif($case->risk_score >= 30)
                            <span class="risk-medium">{{ $case->risk_score }}</span>
                            @else
                            <span class="risk-low">{{ $case->risk_score }}</span>
                            @endif
                        </td>
                        <td>{{ $case->inspections->count() }}</td>
                        <td>{{ $case->documents->count() }}</td>
                        <td>{{ $case->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #999;">No cases yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Cases by Status Chart
        @if($casesByStatus->isNotEmpty())
        const caseStatusCtx = document.getElementById('casesByStatusChart').getContext('2d');
        new Chart(caseStatusCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($casesByStatus->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))->toArray()) !!},
                datasets: [{
                    data: {!! json_encode($casesByStatus->pluck('count')->toArray()) !!},
                    backgroundColor: ['#2196F3', '#FF9800', '#FF5722', '#F44336', '#4CAF50', '#9C27B0', '#607D8B'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        @endif

        // Inspections by Type Chart
        @if($inspectionsByType->isNotEmpty())
        const typeCtx = document.getElementById('inspectionsByTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($inspectionsByType->pluck('type')->map(fn($t) => ucfirst($t))->toArray()) !!},
                datasets: [{
                    label: 'Count',
                    data: {!! json_encode($inspectionsByType->pluck('count')->toArray()) !!},
                    backgroundColor: ['#2196F3', '#FF9800', '#4CAF50'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
        @endif

        // Inspection Decisions Chart
        @if($inspectionDecisions->isNotEmpty())
        const decisionsCtx = document.getElementById('decisionsChart').getContext('2d');
        new Chart(decisionsCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($inspectionDecisions->pluck('decision')->map(fn($d) => ucfirst($d))->toArray()) !!},
                datasets: [{
                    data: {!! json_encode($inspectionDecisions->pluck('count')->toArray()) !!},
                    backgroundColor: ['#4CAF50', '#FF9800', '#F44336'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        @endif
    </script>
</body>
</html>
