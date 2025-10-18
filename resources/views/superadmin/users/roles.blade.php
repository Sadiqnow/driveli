@extends('layouts.admin_cdn')

@section('title', 'User Role Management - Superadmin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">User Role Management</h1>
            <p class="text-muted">Assign and manage roles for users</p>
        </div>
        <a href="{{ route('admin.superadmin.users') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
@endsection

@section('content')
<div id="user-role-management-app">
    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- User Selection -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user mr-1"></i>
                Select User
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="user-search">Search User</label>
                        <select id="user-search" class="form-control" v-model="selectedUserId" @change="loadUserRoles">
                            <option value="">Select a user...</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Quick Search</label>
                        <input type="text" class="form-control" v-model="searchQuery" @input="filterUsers" placeholder="Type to search users...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Role Management -->
    <div v-if="selectedUser" class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-user-tag mr-1"></i>
                Roles for "{{ selectedUser.name }}"
            </h3>
            <button @click="saveRoles" :disabled="saving" class="btn btn-success">
                <i class="fas fa-save"></i>
                <span v-if="saving">Saving...</span>
                <span v-else>Save Changes</span>
            </button>
        </div>
        <div class="card-body">
            <!-- Current Assigned Roles -->
            <div v-if="currentRoles.length > 0" class="mb-4">
                <h5 class="text-success">
                    <i class="fas fa-check-circle mr-1"></i>
                    Currently Assigned Roles ({{ currentRoles.length }})
                </h5>
                <div class="row">
                    <div v-for="role in currentRoles" :key="role.id" class="col-md-4 mb-2">
                        <div class="alert alert-success py-2">
                            <strong>{{ role.display_name }}</strong>
                            <br><small class="text-muted">{{ role.description }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Assignment Checkboxes -->
            <div>
                <h5 class="text-primary">
                    <i class="fas fa-list-check mr-1"></i>
                    Available Roles
                </h5>
                <div v-if="rolesLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading roles...</span>
                    </div>
                </div>
                <div v-else>
                    <div class="row">
                        <div v-for="role in allRoles" :key="role.id" class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input
                                            :id="`role-${role.id}`"
                                            v-model="selectedRoleIds"
                                            :value="role.id"
                                            type="checkbox"
                                            class="form-check-input"
                                            :disabled="saving"
                                        >
                                        <label :for="`role-${role.id}`" class="form-check-label">
                                            <strong>{{ role.display_name }}</strong>
                                            <br><small class="text-muted">{{ role.description }}</small>
                                            <br><small class="badge badge-info">Level {{ role.level }}</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- No User Selected Message -->
    <div v-if="!selectedUser && !loading" class="card mt-4">
        <div class="card-body text-center py-5">
            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Select a User</h4>
            <p class="text-muted">Choose a user from the dropdown above to manage their roles.</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
new Vue({
    el: '#user-role-management-app',
    data: {
        users: [],
        filteredUsers: [],
        selectedUserId: '',
        selectedUser: null,
        allRoles: [],
        currentRoles: [],
        selectedRoleIds: [],
        loading: true,
        rolesLoading: false,
        saving: false,
        searchQuery: ''
    },
    mounted() {
        this.loadUsers();
        this.loadAllRoles();
    },
    methods: {
        loadUsers() {
            this.loading = true;
            axios.get('/api/admin/users')
                .then(response => {
                    this.users = response.data.data || response.data;
                    this.filteredUsers = this.users;
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                    this.showToast('Error loading users', 'error');
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        loadAllRoles() {
            axios.get('/api/roles')
                .then(response => {
                    this.allRoles = response.data.data || response.data;
                })
                .catch(error => {
                    console.error('Error loading roles:', error);
                    this.showToast('Error loading roles', 'error');
                });
        },

        filterUsers() {
            if (!this.searchQuery.trim()) {
                this.filteredUsers = this.users;
                return;
            }

            const query = this.searchQuery.toLowerCase();
            this.filteredUsers = this.users.filter(user =>
                user.name.toLowerCase().includes(query) ||
                user.email.toLowerCase().includes(query)
            );
        },

        loadUserRoles() {
            if (!this.selectedUserId) {
                this.selectedUser = null;
                this.currentRoles = [];
                this.selectedRoleIds = [];
                return;
            }

            this.selectedUser = this.users.find(user => user.id == this.selectedUserId);
            this.rolesLoading = true;

            // Load user's current roles
            axios.get(`/api/admin/users/${this.selectedUserId}/roles`)
                .then(response => {
                    this.currentRoles = response.data.data || response.data;
                    this.selectedRoleIds = this.currentRoles.map(role => role.id);
                })
                .catch(error => {
                    console.error('Error loading user roles:', error);
                    this.showToast('Error loading user roles', 'error');
                })
                .finally(() => {
                    this.rolesLoading = false;
                });
        },

        saveRoles() {
            if (!this.selectedUser) return;

            this.saving = true;
            axios.post('/api/user/roles', {
                user_id: this.selectedUser.id,
                role_ids: this.selectedRoleIds
            })
            .then(response => {
                this.showToast('User roles updated successfully', 'success');
                // Refresh current roles
                this.currentRoles = this.allRoles.filter(role => this.selectedRoleIds.includes(role.id));
            })
            .catch(error => {
                console.error('Error saving roles:', error);
                const message = error.response?.data?.message || 'Error saving roles';
                this.showToast(message, 'error');
            })
            .finally(() => {
                this.saving = false;
            });
        },

        showToast(message, type = 'info') {
            // Simple toast implementation
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

.card-body .form-check-label {
    line-height: 1.4;
}

.badge {
    font-size: 0.8em;
}

.alert-success {
    border-left: 4px solid #28a745;
}

.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-body {
    padding: 1.5rem;
}
</style>
@endsection
