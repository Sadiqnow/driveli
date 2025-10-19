@extends('adminlte::page')

@section('title', 'Role Management')

@section('content_header')
    <h1>Role Management</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
        <li class="active">Roles</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Roles</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create Role
                    </a>
                </div>
            </div>

            <div class="box-body">
                <!-- Search and Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search roles..." v-model="searchQuery" @input="filterRoles">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" v-model="statusFilter" @change="filterRoles">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" v-model="levelFilter" @change="filterRoles">
                            <option value="">All Levels</option>
                            @foreach($roleLevels as $level => $name)
                                <option value="{{ $level }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-default btn-sm" @click="clearFilters">
                            <i class="fa fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Roles Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Display Name</th>
                                <th>Level</th>
                                <th>Users</th>
                                <th>Permissions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="role in filteredRoles" :key="role.id">
                                <td>{{ role.name }}</td>
                                <td>{{ role.display_name }}</td>
                                <td>
                                    <span class="badge" :class="getLevelBadgeClass(role.level)">
                                        Level {{ role.level }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-blue">{{ role.active_users_count }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-green">{{ role.permissions_count }}</span>
                                </td>
                                <td>
                                    <span class="badge" :class="role.is_active ? 'bg-green' : 'bg-red'">
                                        {{ role.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a :href="'{{ route('superadmin.roles.show', '') }}/' + role.id" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a :href="'{{ route('superadmin.roles.edit', '') }}/' + role.id" class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button v-if="!isSystemRole(role.name)" class="btn btn-danger btn-sm" @click="deleteRole(role)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <button class="btn btn-default btn-sm" @click="toggleStatus(role)">
                                            <i class="fa" :class="role.is_active ? 'fa-ban' : 'fa-check'"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="text-center" v-if="roles.length > 0">
                    <pagination :data="roles" @pagination-change-page="loadRoles"></pagination>
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
        roles: @json($roles->items()),
        searchQuery: '',
        statusFilter: '',
        levelFilter: '',
        currentPage: {{ $roles->currentPage() }},
        lastPage: {{ $roles->lastPage() }}
    },
    computed: {
        filteredRoles() {
            return this.roles.filter(role => {
                const matchesSearch = !this.searchQuery ||
                    role.display_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    role.name.toLowerCase().includes(this.searchQuery.toLowerCase());

                const matchesStatus = !this.statusFilter || role.is_active == this.statusFilter;
                const matchesLevel = !this.levelFilter || role.level == this.levelFilter;

                return matchesSearch && matchesStatus && matchesLevel;
            });
        }
    },
    methods: {
        filterRoles() {
            // Client-side filtering for now
            // In production, this should make AJAX calls
        },
        clearFilters() {
            this.searchQuery = '';
            this.statusFilter = '';
            this.levelFilter = '';
        },
        getLevelBadgeClass(level) {
            if (level >= 90) return 'bg-red';
            if (level >= 70) return 'bg-yellow';
            if (level >= 50) return 'bg-blue';
            return 'bg-gray';
        },
        isSystemRole(name) {
            return ['super_admin', 'admin'].includes(name);
        },
        deleteRole(role) {
            if (!confirm(`Are you sure you want to delete the role "${role.display_name}"?`)) {
                return;
            }

            axios.delete(`{{ route('superadmin.roles.index') }}/${role.id}`)
                .then(response => {
                    this.showToast('Role deleted successfully', 'success');
                    this.loadRoles();
                })
                .catch(error => {
                    this.showToast('Error deleting role', 'error');
                });
        },
        toggleStatus(role) {
            if (this.isSystemRole(role.name)) {
                this.showToast('Cannot modify system roles', 'warning');
                return;
            }

            axios.patch(`{{ route('superadmin.roles.index') }}/${role.id}/toggle-status`)
                .then(response => {
                    role.is_active = response.data.is_active;
                    this.showToast('Role status updated successfully', 'success');
                })
                .catch(error => {
                    this.showToast('Error updating role status', 'error');
                });
        },
        loadRoles() {
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
