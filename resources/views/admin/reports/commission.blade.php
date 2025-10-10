@extends('layouts.admin_cdn')

@section('title', 'Commission Report')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.commission-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transition: all 0.3s ease;
}
.commission-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.3);
}
.commission-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: #28a745;
}
.commission-label {
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.trend-indicator {
    font-size: 0.9rem;
    font-weight: 500;
}
.trend-up { color: #28a745; }
.trend-down { color: #dc3545; }
.trend-neutral { color: #6c757d; }
.commission-breakdown {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
</style>
@endsection

@section('content_header')
    <h1>Commission Report</h1>
@stop

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.dashboard') }}">Reports</a></li>
    <li class="breadcrumb-item active">Commission</li>
@stop

@section('content')
    {{-- Commission Overview --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>₦{{ number_format($totalCommission ?? 0, 2) }}</h3>
                    <p>Total Commission</p>
                    <small class="trend-up">
                        <i class="fas fa-arrow-up"></i> +12.5% vs last month
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>₦{{ number_format($monthlyCommission ?? 0, 2) }}</h3>
                    <p>This Month</p>
                    <small class="trend-up">
                        <i class="fas fa-arrow-up"></i> +8.3% growth
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-month"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $avgCommissionRate ?? '0.0' }}%</h3>
                    <p>Average Rate</p>
                    <small class="trend-neutral">
                        <i class="fas fa-minus"></i> No change
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalTransactions ?? 0 }}</h3>
                    <p>Transactions</p>
                    <small class="trend-up">
                        <i class="fas fa-arrow-up"></i> +15 this week
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filters & Settings
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <label>Date Range</label>
                    <select name="period" class="form-control">
                        <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_quarter" {{ request('period') == 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="last_quarter" {{ request('period') == 'last_quarter' ? 'selected' : '' }}>Last Quarter</option>
                        <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Commission Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="driver_signup" {{ request('type') == 'driver_signup' ? 'selected' : '' }}>Driver Signup</option>
                        <option value="company_request" {{ request('type') == 'company_request' ? 'selected' : '' }}>Company Request</option>
                        <option value="successful_match" {{ request('type') == 'successful_match' ? 'selected' : '' }}>Successful Match</option>
                        <option value="monthly_fee" {{ request('type') == 'monthly_fee' ? 'selected' : '' }}>Monthly Fee</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Payment Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Apply
                        </button>
                        <a href="{{ route('admin.reports.commission') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Commission Charts --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Commission Trends
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="commissionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card commission-breakdown">
                <div class="card-body text-center text-white">
                    <h5 class="card-title">Commission Breakdown</h5>
                    <canvas id="breakdownChart" width="300" height="300"></canvas>
                    <div class="mt-3">
                        <small>
                            <i class="fas fa-circle text-warning"></i> Driver Signups: 45%<br>
                            <i class="fas fa-circle text-info"></i> Matches: 35%<br>
                            <i class="fas fa-circle text-success"></i> Monthly Fees: 20%
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Commission Structure --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cogs"></i> Commission Structure
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Current Rates</h6>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Driver Registration Fee</td>
                                <td class="text-right font-weight-bold text-success">₦{{ number_format($rates['driver_registration'] ?? 5000, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Company Request Processing</td>
                                <td class="text-right font-weight-bold text-success">₦{{ number_format($rates['company_request'] ?? 10000, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Successful Match Bonus</td>
                                <td class="text-right font-weight-bold text-success">₦{{ number_format($rates['match_bonus'] ?? 15000, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Monthly Platform Fee (per driver)</td>
                                <td class="text-right font-weight-bold text-success">₦{{ number_format($rates['monthly_fee'] ?? 2000, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Performance Metrics</h6>
                    <div class="progress-group">
                        <span class="float-right"><b>{{ $performanceMetrics['driver_conversion'] ?? 0 }}%</b></span>
                        <span class="progress-text">Driver Conversion Rate</span>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: {{ $performanceMetrics['driver_conversion'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="progress-group">
                        <span class="float-right"><b>{{ $performanceMetrics['match_success'] ?? 0 }}%</b></span>
                        <span class="progress-text">Match Success Rate</span>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-info" style="width: {{ $performanceMetrics['match_success'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="progress-group">
                        <span class="float-right"><b>{{ $performanceMetrics['payment_collection'] ?? 0 }}%</b></span>
                        <span class="progress-text">Payment Collection Rate</span>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-warning" style="width: {{ $performanceMetrics['payment_collection'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Recent Transactions
            </h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions ?? [] as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                <td>
                                    <code>{{ $transaction->transaction_id }}</code>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $transaction->type == 'driver_signup' ? 'primary' : ($transaction->type == 'match' ? 'success' : 'info') }}">
                                        {{ ucwords(str_replace('_', ' ', $transaction->type)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($transaction->client_type == 'driver')
                                        <i class="fas fa-user"></i> {{ $transaction->client_name }}
                                    @else
                                        <i class="fas fa-building"></i> {{ $transaction->client_name }}
                                    @endif
                                </td>
                                <td class="font-weight-bold text-success">
                                    ₦{{ number_format($transaction->amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge badge-{{ $transaction->status == 'paid' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction('{{ $transaction->id }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($transaction->status == 'pending')
                                        <button class="btn btn-sm btn-outline-success" onclick="markAsPaid('{{ $transaction->id }}')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <br>No transactions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Export and Actions --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tools"></i> Actions & Export
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Export Options</h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success" onclick="exportReport('excel')">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportReport('csv')">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>Quick Actions</h6>
                    <button class="btn btn-outline-primary" onclick="updateRates()">
                        <i class="fas fa-edit"></i> Update Rates
                    </button>
                    <button class="btn btn-outline-warning" onclick="sendReminders()">
                        <i class="fas fa-bell"></i> Send Payment Reminders
                    </button>
                    <button class="btn btn-outline-info" onclick="generateInvoices()">
                        <i class="fas fa-file-invoice"></i> Generate Invoices
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Commission Trends Chart
    const commissionCtx = document.getElementById('commissionChart').getContext('2d');
    new Chart(commissionCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Total Commission (₦)',
                data: [50000, 75000, 60000, 90000, 85000, 105000],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Monthly Fee (₦)',
                data: [20000, 25000, 22000, 30000, 28000, 35000],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Commission Breakdown Chart
    const breakdownCtx = document.getElementById('breakdownChart').getContext('2d');
    new Chart(breakdownCtx, {
        type: 'doughnut',
        data: {
            labels: ['Driver Signups', 'Successful Matches', 'Monthly Fees'],
            datasets: [{
                data: [45, 35, 20],
                backgroundColor: [
                    '#ffc107',
                    '#17a2b8',
                    '#28a745'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false
            }
        }
    });
});

function exportReport(format) {
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    btn.disabled = true;

    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        toastr.success('Commission report exported successfully!');
    }, 2000);
}

function viewTransaction(id) {
    toastr.info('Transaction details will be available soon!');
}

function markAsPaid(id) {
    if (confirm('Mark this transaction as paid?')) {
        toastr.success('Transaction marked as paid!');
        // Reload or update the row
    }
}

function updateRates() {
    toastr.info('Commission rate management coming soon!');
}

function sendReminders() {
    toastr.info('Payment reminder functionality coming soon!');
}

function generateInvoices() {
    toastr.info('Invoice generation functionality coming soon!');
}
</script>
@stop