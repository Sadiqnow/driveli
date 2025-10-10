@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Reports & Analytics</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Reports Overview</h3>
                        </div>
                        <div class="card-body">
                            <p>Select a report type from the navigation or dashboard.</p>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-chart-bar fa-3x text-primary mb-2"></i>
                                            <h5>Dashboard</h5>
                                            <a href="{{ route('admin.reports.dashboard') }}" class="btn btn-primary btn-sm">View</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-dollar-sign fa-3x text-success mb-2"></i>
                                            <h5>Commissions</h5>
                                            <a href="{{ route('admin.reports.commission') }}" class="btn btn-success btn-sm">View</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-tachometer-alt fa-3x text-info mb-2"></i>
                                            <h5>Driver Performance</h5>
                                            <a href="{{ route('admin.reports.driver-performance') }}" class="btn btn-info btn-sm">View</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-building fa-3x text-warning mb-2"></i>
                                            <h5>Company Activity</h5>
                                            <a href="{{ route('admin.reports.company-activity') }}" class="btn btn-warning btn-sm">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
