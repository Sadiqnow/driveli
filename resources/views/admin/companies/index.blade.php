@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Company Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Companies</li>
                    </ol>
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
                            <h3 class="card-title">Companies</h3>
                            <div class="card-tools">
                                @can('create_companies')
                                <a href="{{ route('admin.companies.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add Company
                                </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($companies ?? [] as $company)
                                        <tr>
                                            <td>{{ $company->id }}</td>
                                            <td>{{ $company->name }}</td>
                                            <td>{{ $company->email }}</td>
                                            <td>{{ $company->phone }}</td>
                                            <td>
                                                @if($company->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($company->status == 'inactive')
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @else
                                                    <span class="badge bg-danger">Suspended</span>
                                                @endif
                                            </td>
                                            <td>
                                                @can('view_companies')
                                                <a href="{{ route('admin.companies.show', $company->id) }}" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endcan
                                                @can('edit_companies')
                                                <a href="{{ route('admin.companies.edit', $company->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                @can('delete_companies')
                                                <form action="{{ route('admin.companies.destroy', $company->id) }}" method="POST" style="display:inline-block">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this company?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                                @can('manage_companies')
                                                <form action="{{ route('admin.companies.toggle-status', $company->id) }}" method="POST" style="display:inline-block">
                                                    @csrf
                                                    <button class="btn btn-outline-secondary btn-sm" title="Toggle Status">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No companies found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            @if(isset($companies) && method_exists($companies, 'links'))
                                {{ $companies->links('pagination::bootstrap-5') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
