@extends('layouts.admin_cdn')

@section('title', 'Role Management - Superadmin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Role Management</h1>
            <p class="text-muted">Manage roles and their permissions</p>
        </div>
        <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Role
        </a>
    </div>
@endsection

@section('content')
<div id="role-management-app">
    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Roles List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users mr-1"></i>
                Roles ({{ roles.length }})
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Level</th>
                            <th>Users</th>
                            <th>Permissions</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="role in roles" :key="role.id" :class="{ 'table-active': selectedRole && selectedRole.id === role.id }">
                            <td>
                                <strong>{{ role.display_name }}</strong>
                                <br><small class="text-muted">{{ role.name }}</small>
                            </td>
                            <td>{{ role.description || 'No description' }}</td>
                            <td>
                                <span class="badge badge-info">{{ role.level }}</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ role.users_count }}</span>
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ role.permissions_count }}</span>
                            </td>
                            <td>
                                <span :class="role.is_active ? 'badge badge-success' : 'badge badge-danger'">
                                    {{ role.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button @click="selectRole(role)" class="btn btn-sm btn-outline-primary mr-1">
                                    <i class="fas fa-eye"></i> View Permissions
                                </button>
                                <a :href="`/superadmin/roles/${role.id}/edit`" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Permissions Panel -->
    <div v-if="selectedRole" class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-key mr-1"></i>
                Permissions for "{{ selectedRole.display_name }}"
            </h3>
            <button @click="savePermissions" :disabled="saving" class="btn btn-success">
                <i class="fas fa-save"></i>
                <span v-if="saving">Saving...</span>
                <span v-else>Save Changes</span>
            </button>
        </div>
        <div class="card-body">
            <div v-if="permissionsLoading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading permissions...</span>
                </div>
            </div>
            <div v-else>
                <div v-for="(categoryPermissions, category) in groupedPermissions" :key="category" class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-folder mr-1"></i>
                        {{ category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                    </h5>
                    <div class="row">
                        <div v-for="permission in categoryPermissions" :key="permission.id" class="col-md-6 col-lg-4 mb-2">
                            <div class="form-check">
                                <input
                                    :id="`perm-${permission.id}`"
                                    v-model="selectedPermissions"
                                    :value="permission.id"
                                    type="checkbox"
                                    class="form-check-input"
                                >
                                <label :for="`perm-${permission.id}`" class="form-check-label">
                                    <strong>{{ permission.display_name }}</strong>
                                    <br><small class="text-muted">{{ permission.description }}</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr v-if="Object.keys(groupedPermissions)[Object.keys(groupedPermissions).length - 1] !== category">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
new Vue({
    el: '#role-management-app',
    data: {
        roles: [],
        selectedRole: null,
        permissions: [],
        selectedPermissions: [],
        loading: true,
        permissionsLoading: false,
        saving: false
    },
    computed: {
        groupedPermissions() {
            const grouped = {};
            this.permissions.forEach(permission => {
                if (!grouped[permission.category]) {
                    grouped[permission.category] = [];
                }
                grouped[permission.category].push(permission);
            });
            return grouped;
        }
    },
    mounted() {
        this.loadRoles();
    },
    methods: {
        loadRoles() {
            this.loading = true;
            axios.get('/api/roles')
                .then(response => {
                    this.roles = response.data.data;
                })
                .catch(error => {
                    console.error('Error loading roles:', error);
                    this.showToast('Error loading roles', 'error');
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        selectRole(role) {
            this.selectedRole = role;
            this.loadPermissions();
        },

        loadPermissions() {
            if (!this.selectedRole) return;

            this.permissionsLoading = true;
            this.selectedPermissions = [];

            // Load all permissions
            axios.get('/api/permissions')
                .then(response => {
                    this.permissions = response.data.data;

                    // Load role's current permissions
                    return axios.get(`/api/roles/${this.selectedRole.id}/permissions`);
                })
                .then(response => {
                    // Set selected permissions based on role's current permissions
                    const rolePermissions = response.data.data;
                    this.selectedPermissions = Object.values(rolePermissions).flat().map(p => p.id);
                })
                .catch(error => {
                    console.error('Error loading permissions:', error);
                    this.showToast('Error loading permissions', 'error');
                })
                .finally(() => {
                    this.permissionsLoading = false;
                });
        },

        savePermissions() {
            if (!this.selectedRole) return;

            this.saving = true;
            axios.post(`/api/roles/${this.selectedRole.id}/permissions`, {
                permissions: this.selectedPermissions
            })
            .then(response => {
                this.showToast('Permissions updated successfully', 'success');
                // Update the role's permission count
                this.selectedRole.permissions_count = response.data.data.permissions_count;
            })
            .catch(error => {
                console.error('Error saving permissions:', error);
                this.showToast('Error saving permissions', 'error');
            })
            .finally(() => {
                this.saving = false;
            });
        },

        showToast(message, type = 'info') {
            // Simple toast implementation - you can replace with a proper toast library
            const toastClass = type === 'error' ? 'alert-danger' : 'alert-success';
            const toast = document.createElement('div');
            toast.className = `alert ${toastClass} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            document.body.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        }
    }
});
</script>
@endsection

@section('css')
<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.table-responsive {
    border-radius: 0.375rem;
}

.form-check-label {
    line-height: 1.4;
}

.badge {
    font-size: 0.8em;
}

.btn-group .btn {
    margin-right: 2px;
}
</style>
@endsection
