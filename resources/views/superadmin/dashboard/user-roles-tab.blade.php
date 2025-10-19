<!-- User Roles Management Tab Content -->
<div class="user-roles-management-tab">
    <!-- User Selection Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-user mr-1"></i>
                Select User to Manage Roles
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="user-select">Search and Select User</label>
                        <select id="user-select" class="form-control" v-model="selectedUserId" @change="loadUserRoles">
                            <option value="">Select a user...</option>
                            <option v-for="user in filteredUsers" :key="user.id" :value="user.id">
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="user-search">Quick Search</label>
                        <input type="text" id="user-search" class="form-control" v-model="userSearchQuery" @input="filterUsers" placeholder="Type to search users...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Role Management -->
    <div v-if="selectedUser" class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-tag mr-1"></i>
                Roles for "{{ selectedUser.name }}"
            </h5>
            <button @click="saveUserRoles" :disabled="savingUserRoles" class="btn btn-success">
                <i class="fas fa-save"></i>
                <span v-if="savingUserRoles">Saving...</span>
                <span v-else>Save Changes</span>
            </button>
        </div>
        <div class="card-body">
            <!-- Current Assigned Roles -->
            <div v-if="currentUserRoles.length > 0" class="mb-4">
                <h6 class="text-success">
                    <i class="fas fa-check-circle mr-1"></i>
                    Currently Assigned Roles ({{ currentUserRoles.length }})
                </h6>
                <div class="row">
                    <div v-for="role in currentUserRoles" :key="role.id" class="col-md-4 mb-2">
                        <div class="alert alert-success py-2">
                            <strong>{{ role.display_name }}</strong>
                            <br><small class="text-muted">{{ role.description || 'No description' }}</small>
                            <br><small class="badge badge-info">Level {{ role.level }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Roles Checkboxes -->
            <div>
                <h6 class="text-primary">
                    <i class="fas fa-list-check mr-1"></i>
                    Available Roles
                </h6>
                <div v-if="userRolesLoading" class="text-center py-4">
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
                                            :id="`user-role-${role.id}`"
                                            v-model="selectedUserRoleIds"
                                            :value="role.id"
                                            type="checkbox"
                                            class="form-check-input"
                                            :disabled="savingUserRoles"
                                        >
                                        <label :for="`user-role-${role.id}`" class="form-check-label">
                                            <strong>{{ role.display_name }}</strong>
                                            <br><small class="text-muted">{{ role.description || 'No description' }}</small>
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
    <div v-if="!selectedUser && !loadingUsers" class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Select a User</h4>
            <p class="text-muted">Choose a user from the dropdown above to manage their roles.</p>
        </div>
    </div>

    <!-- Bulk User Role Management -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-users-cog mr-1"></i>
                Bulk Role Management
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bulk-role-select">Select Role</label>
                        <select id="bulk-role-select" class="form-control" v-model="bulkSelectedRoleId">
                            <option value="">Choose a role...</option>
                            <option v-for="role in allRoles" :key="role.id" :value="role.id">
                                {{ role.display_name }} (Level {{ role.level }})
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button @click="bulkAssignRole" :disabled="!bulkSelectedRoleId || bulkAssigning" class="btn btn-success mr-2">
                                <i class="fas fa-plus"></i>
                                <span v-if="bulkAssigning">Assigning...</span>
                                <span v-else>Bulk Assign</span>
                            </button>
                            <button @click="bulkRemoveRole" :disabled="!bulkSelectedRoleId || bulkRemoving" class="btn btn-danger">
                                <i class="fas fa-minus"></i>
                                <span v-if="bulkRemoving">Removing...</span>
                                <span v-else>Bulk Remove</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Note:</strong> Bulk operations will affect all users. Use with caution.
            </div>
        </div>
    </div>
</div>

<script>
new Vue({
    el: '.user-roles-management-tab',
    data: {
        users: [],
        filteredUsers: [],
        selectedUserId: '',
        selectedUser: null,
        allRoles: [],
        currentUserRoles: [],
        selectedUserRoleIds: [],
        userSearchQuery: '',
        loadingUsers: true,
        userRolesLoading: false,
        savingUserRoles: false,
        bulkSelectedRoleId: '',
        bulkAssigning: false,
        bulkRemoving: false
    },
    mounted() {
        this.loadUsers();
        this.loadAllRoles();
    },
    methods: {
        loadUsers() {
            this.loadingUsers = true;
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
                    this.loadingUsers = false;
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
            if (!this.userSearchQuery.trim()) {
                this.filteredUsers = this.users;
                return;
            }

            const query = this.userSearchQuery.toLowerCase();
            this.filteredUsers = this.users.filter(user =>
                user.name.toLowerCase().includes(query) ||
                user.email.toLowerCase().includes(query)
            );
        },

        loadUserRoles() {
            if (!this.selectedUserId) {
                this.selectedUser = null;
                this.currentUserRoles = [];
                this.selectedUserRoleIds = [];
                return;
            }

            this.selectedUser = this.users.find(user => user.id == this.selectedUserId);
            this.userRolesLoading = true;

            axios.get(`/api/admin/users/${this.selectedUserId}/roles`)
                .then(response => {
                    this.currentUserRoles = response.data.data || response.data;
                    this.selectedUserRoleIds = this.currentUserRoles.map(role => role.id);
                })
                .catch(error => {
                    console.error('Error loading user roles:', error);
                    this.showToast('Error loading user roles', 'error');
                })
                .finally(() => {
                    this.userRolesLoading = false;
                });
        },

        saveUserRoles() {
            if (!this.selectedUser) return;

            this.savingUserRoles = true;
            axios.post('/api/user/roles', {
                user_id: this.selectedUser.id,
                roles: this.selectedUserRoleIds
            })
            .then(response => {
                this.showToast('User roles updated successfully', 'success');
                this.currentUserRoles = this.allRoles.filter(role => this.selectedUserRoleIds.includes(role.id));
            })
            .catch(error => {
                console.error('Error saving roles:', error);
                const message = error.response?.data?.message || 'Error saving roles';
                this.showToast(message, 'error');
            })
            .finally(() => {
                this.savingUserRoles = false;
            });
        },

        bulkAssignRole() {
            if (!this.bulkSelectedRoleId) return;

            this.bulkAssigning = true;
            axios.post('/api/admin/users/bulk-assign-role', {
                role_id: this.bulkSelectedRoleId
            })
            .then(response => {
                this.showToast(`Role assigned to ${response.data.assigned_count} users successfully`, 'success');
                this.loadUsers(); // Refresh user list
                if (this.selectedUser) {
                    this.loadUserRoles(); // Refresh current user's roles
                }
            })
            .catch(error => {
                console.error('Error bulk assigning role:', error);
                this.showToast('Error assigning role to users', 'error');
            })
            .finally(() => {
                this.bulkAssigning = false;
            });
        },

        bulkRemoveRole() {
            if (!this.bulkSelectedRoleId) return;

            if (!confirm('Are you sure you want to remove this role from all users?')) {
                return;
            }

            this.bulkRemoving = true;
            axios.post('/api/admin/users/bulk-remove-role', {
                role_id: this.bulkSelectedRoleId
            })
            .then(response => {
                this.showToast(`Role removed from ${response.data.removed_count} users successfully`, 'success');
                this.loadUsers(); // Refresh user list
                if (this.selectedUser) {
                    this.loadUserRoles(); // Refresh current user's roles
                }
            })
            .catch(error => {
                console.error('Error bulk removing role:', error);
                this.showToast('Error removing role from users', 'error');
            })
            .finally(() => {
                this.bulkRemoving = false;
            });
        },

        showToast(message, type = 'info') {
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

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        }
    }
});
</script>
