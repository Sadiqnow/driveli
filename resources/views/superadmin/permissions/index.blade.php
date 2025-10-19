@extends('adminlte::page')

@section('title', 'Permission Management')

@section('content_header')
    <h1>Permission Management</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
        <li class="active">Permissions</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Permissions</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('superadmin.permissions.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create Permission
                    </a>
                </div>
            </div>

            <div class="box-body">
                <!-- Search and Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search permissions..." v-model="searchQuery" @input="filterPermissions">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" v-model="categoryFilter" @change="filterPermissions">
                            <option value="">All Categories</option>
                            @foreach($categories as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" v-model="statusFilter" @change="filterPermissions">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-default btn-sm" @click="clearFilters">
                            <i class="fa fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div class="row mb-3" v-if="selectedPermissions.length > 0">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>{{ selectedPermissions.length }} permissions selected</strong>
                            <div class="btn-group ml-2">
                                <button class="btn btn-sm btn-success" @click="bulkAction('activate')">
                                    <i class="fa fa-check"></i> Activate
                                </button>
                                <button class="btn btn-sm btn-warning" @click="bulkAction('deactivate')">
                                    <i class="fa fa-ban"></i> Deactivate
                                </button>
                                <button class="btn btn-sm btn-danger" @click="bulkAction('delete')">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" v-model="selectAll" @change="toggleSelectAll">
                                </th>
                                <th>Name</th>
                                <th>Display Name</th>
                                <th>Category</th>
                                <th>Resource</th>
                                <th>Action</th>
                                <th>Roles</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="permission in filteredPermissions" :key="permission.id">
                                <td>
                                    <input type="checkbox" :value="permission.id" v-model="selectedPermissions">
                                </td>
                                <td>{{ permission.name }}</td>
                                <td>{{ permission.display_name }}</td>
                                <td>
                                    <span class="badge bg-blue">{{ permission.category }}</span>
                                </td>
                                <td>{{ permission.resource }}</td>
                                <td>{{ permission.action }}</td>
                                <td>
                                    <span class="badge bg-green">{{ permission.roles_count }}</span>
                                </td>
                                <td>
                                    <span class="badge" :class="permission.is_active ? 'bg-green' : 'bg-red'">
                                        {{ permission.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a :href="'{{ route('superadmin.permissions.show', '') }}/' + permission.id" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a :href="'{{ route('superadmin.permissions.edit', '') }}/' + permission.id" class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button class="btn btn-default btn-sm" @click="toggleStatus(permission)">
                                            <i class="fa" :class="permission.is_active ? 'fa-ban' : 'fa-check'"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="text-center" v-if="permissions.length > 0">
                    <pagination :data="permissions" @pagination-change-page="loadPermissions"></pagination>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
new Vue({
    el: '#app',
    data: {
        permissions: @json($permissions->items()),
        searchQuery: '',
        categoryFilter: '',
        statusFilter: '',
        selectedPermissions: [],
        selectAll: false,
        currentPage: {{ $permissions->currentPage() }},
        lastPage: {{ $permissions->lastPage() }}
    },
    computed: {
        filteredPermissions() {
            return this.permissions.filter(permission => {
                const matchesSearch = !this.searchQuery ||
                    permission.display_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    permission.name.toLowerCase().includes(this.searchQuery.toLowerCase());

                const matchesCategory = !this.categoryFilter || permission.category === this.categoryFilter;
                const matchesStatus = !this.statusFilter || permission.is_active == this.statusFilter;

                return matchesSearch && matchesCategory && matchesStatus;
            });
        }
    },
    watch: {
        selectedPermissions() {
            this.selectAll = this.selectedPermissions.length === this.filteredPermissions.length && this.filteredPermissions.length > 0;
        }
    },
    methods: {
        filterPermissions() {
            // Client-side filtering for now
        },
        clearFilters() {
            this.searchQuery = '';
            this.categoryFilter = '';
            this.statusFilter = '';
            this.selectedPermissions = [];
            this.selectAll = false;
        },
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedPermissions = this.filteredPermissions.map(p => p.id);
            } else {
                this.selectedPermissions = [];
            }
        },
        toggleStatus(permission) {
            axios.patch(`{{ route('superadmin.permissions.index') }}/${permission.id}/toggle-status`)
                .then(response => {
                    permission.is_active = response.data.is_active;
                    this.showToast('Permission status updated successfully', 'success');
                })
                .catch(error => {
                    this.showToast('Error updating permission status', 'error');
                });
        },
        bulkAction(action) {
            if (!confirm(`Are you sure you want to ${action} the selected permissions?`)) {
                return;
            }

            axios.post('{{ route('superadmin.permissions.bulk-action') }}', {
                action: action,
                permissions: this.selectedPermissions
            })
            .then(response => {
                this.showToast(response.data.message, 'success');
                this.loadPermissions();
                this.selectedPermissions = [];
                this.selectAll = false;
            })
            .catch(error => {
                this.showToast(error.response?.data?.message || 'Bulk action failed', 'error');
            });
        },
        loadPermissions() {
            // Reload page for now
            window.location.reload();
        },
        showToast(message, type) {
            // Simple toast implementation
            alert(message);
        }
    }
});
</script>
@endsection
