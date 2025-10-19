<!-- Roles Management Tab Content -->
<div class="roles-management-tab">
    <!-- Search and Filter Section -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="form-group">
                <label for="roles-search"><i class="fas fa-search"></i> Search Roles</label>
                <input type="text" id="roles-search" class="form-control" v-model="rolesSearchQuery" @input="filterRoles" placeholder="Search by name or description...">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="roles-status-filter"><i class="fas fa-filter"></i> Status</label>
                <select id="roles-status-filter" class="form-control" v-model="rolesStatusFilter" @change="filterRoles">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="roles-level-filter"><i class="fas fa-layer-group"></i> Level</label>
                <select id="roles-level-filter" class="form-control" v-model="rolesLevelFilter" @change="filterRoles">
                    <option value="">All Levels</option>
                    <option v-for="level in availableLevels" :key="level" :value="level">Level {{ level }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="d-flex">
                    <button @click="clearRolesFilters" class="btn btn-outline-secondary btn-sm mr-1">
                        <i class="fas fa-times"></i>
                    </button>
                    <button @click="syncPermissions" class="btn btn-info btn-sm mr-1" :disabled="syncingPermissions">
                        <i class="fas fa-sync-alt" :class="{ 'fa-spin': syncingPermissions }"></i>
                        <span v-if="syncingPermissions">Syncing...</span>
                        <span v-else>Sync Permissions</span>
                    </button>
                    <a href="{{ route('superadmin.roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-users mr-1"></i>
                Roles ({{ filteredRoles.length }})
            </h5>
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
                        <tr v-for="role in paginatedRoles" :key="role.id">
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
                                <div class="btn-group btn-group-sm">
                                    <button @click="selectRole(role)" class="btn btn-outline-primary" title="View Permissions">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a :href="`/superadmin/roles/${role.id}/edit`" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button @click="toggleRoleStatus(role)" class="btn btn-outline-warning" title="Toggle Status">
                                        <i class="fas fa-toggle-on"></i>
                                    </button>
                                    <button @click="deleteRole(role)" v-if="role.name !== 'super_admin'" class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredRoles.length === 0">
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No roles found matching your criteria.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination -->
        <div v-if="totalRolesPages > 1" class="card-footer">
            <nav aria-label="Role pagination">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: rolesCurrentPage === 1 }">
                        <a class="page-link" href="#" @click.prevent="changeRolesPage(rolesCurrentPage - 1)">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <li v-for="page in rolesVisiblePages" :key="page" class="page-item" :class="{ active: page === rolesCurrentPage }">
                        <a class="page-link" href="#" @click.prevent="changeRolesPage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: rolesCurrentPage === totalRolesPages }">
                        <a class="page-link" href="#" @click.prevent="changeRolesPage(rolesCurrentPage + 1)">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Role Permissions Modal -->
    <div class="modal fade" id="rolePermissionsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-key mr-1"></i>
                        Permissions for "{{ selectedRole ? selectedRole.display_name : '' }}"
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="rolePermissionsLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading permissions...</span>
                        </div>
                    </div>
                    <div v-else>
                        <div v-for="(categoryPermissions, category) in roleGroupedPermissions" :key="category" class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-folder mr-1"></i>
                                {{ category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                            </h6>
                            <div class="row">
                                <div v-for="permission in categoryPermissions" :key="permission.id" class="col-md-6 col-lg-4 mb-2">
                                    <div class="form-check">
                                        <input
                                            :id="`role-perm-${permission.id}`"
                                            v-model="selectedRolePermissions"
                                            :value="permission.id"
                                            type="checkbox"
                                            class="form-check-input"
                                            :disabled="savingRolePermissions"
                                        >
                                        <label :for="`role-perm-${permission.id}`" class="form-check-label">
                                            <small>{{ permission.display_name || permission.name }}</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <hr v-if="Object.keys(roleGroupedPermissions)[Object.keys(roleGroupedPermissions).length - 1] !== category">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button @click="saveRolePermissions" :disabled="savingRolePermissions" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        <span v-if="savingRolePermissions">Saving...</span>
                        <span v-else>Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
new Vue({
    el: '.roles-management-tab',
    data: {
        roles: [],
        filteredRoles: [],
        selectedRole: null,
        rolePermissions: [],
        selectedRolePermissions: [],
        rolesSearchQuery: '',
        rolesStatusFilter: '',
        rolesLevelFilter: '',
        rolesCurrentPage: 1,
        rolesItemsPerPage: 10,
        rolePermissionsLoading: false,
        savingRolePermissions: false,
        syncingPermissions: false
    },
    computed: {
        roleGroupedPermissions() {
            const grouped = {};
            this.rolePermissions.forEach(permission => {
                const category = permission.category || 'general';
                if (!grouped[category]) {
                    grouped[category] = [];
                }
                grouped[category].push(permission);
            });
            return grouped;
        },
        availableLevels() {
            const levels = [...new Set(this.roles.map(role => role.level))];
            return levels.sort((a, b) => a - b);
        },
        totalRolesPages() {
            return Math.ceil(this.filteredRoles.length / this.rolesItemsPerPage);
        },
        paginatedRoles() {
            const start = (this.rolesCurrentPage - 1) * this.rolesItemsPerPage;
            const end = start + this.rolesItemsPerPage;
            return this.filteredRoles.slice(start, end);
        },
        rolesVisiblePages() {
            const pages = [];
            const start = Math.max(1, this.rolesCurrentPage - 2);
            const end = Math.min(this.totalRolesPages, this.rolesCurrentPage + 2);

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        }
    },
    mounted() {
        this.loadRoles();
    },
    methods: {
        loadRoles() {
            axios.get('/api/roles')
                .then(response => {
                    this.roles = response.data.data;
                    this.filteredRoles = this.roles;
                })
                .catch(error => {
                    console.error('Error loading roles:', error);
                    this.showToast('Error loading roles', 'error');
                });
        },

        filterRoles() {
            let filtered = this.roles;

            if (this.rolesSearchQuery.trim()) {
                const query = this.rolesSearchQuery.toLowerCase();
                filtered = filtered.filter(role =>
                    role.name.toLowerCase().includes(query) ||
                    role.display_name.toLowerCase().includes(query) ||
                    (role.description && role.description.toLowerCase().includes(query))
                );
            }

            if (this.rolesStatusFilter) {
                const isActive = this.rolesStatusFilter === 'active';
                filtered = filtered.filter(role => role.is_active === isActive);
            }

            if (this.rolesLevelFilter) {
                filtered = filtered.filter(role => role.level == this.rolesLevelFilter);
            }

            this.filteredRoles = filtered;
            this.rolesCurrentPage = 1;
        },

        clearRolesFilters() {
            this.rolesSearchQuery = '';
            this.rolesStatusFilter = '';
            this.rolesLevelFilter = '';
            this.filterRoles();
        },

        changeRolesPage(page) {
            if (page >= 1 && page <= this.totalRolesPages) {
                this.rolesCurrentPage = page;
            }
        },

        selectRole(role) {
            this.selectedRole = role;
            this.loadRolePermissions();
            $('#rolePermissionsModal').modal('show');
        },

        loadRolePermissions() {
            if (!this.selectedRole) return;

            this.rolePermissionsLoading = true;
            this.selectedRolePermissions = [];

            axios.get('/api/permissions')
                .then(response => {
                    this.rolePermissions = response.data.data;
                    return axios.get(`/api/roles/${this.selectedRole.id}/permissions`);
                })
                .then(response => {
                    const rolePermissions = response.data.data;
                    this.selectedRolePermissions = Object.values(rolePermissions).flat().map(p => p.id);
                })
                .catch(error => {
                    console.error('Error loading role permissions:', error);
                    this.showToast('Error loading permissions', 'error');
                })
                .finally(() => {
                    this.rolePermissionsLoading = false;
                });
        },

        saveRolePermissions() {
            if (!this.selectedRole) return;

            this.savingRolePermissions = true;
            axios.post(`/api/roles/${this.selectedRole.id}/permissions`, {
                permissions: this.selectedRolePermissions
            })
            .then(response => {
                this.showToast('Role permissions updated successfully', 'success');
                this.selectedRole.permissions_count = response.data.data.permissions_count;
                $('#rolePermissionsModal').modal('hide');
            })
            .catch(error => {
                console.error('Error saving permissions:', error);
                this.showToast('Error saving permissions', 'error');
            })
            .finally(() => {
                this.savingRolePermissions = false;
            });
        },

        toggleRoleStatus(role) {
            if (role.name === 'super_admin') {
                this.showToast('Super Admin role status cannot be changed', 'error');
                return;
            }

            axios.patch(`/superadmin/roles/${role.id}/toggle-status`)
                .then(response => {
                    role.is_active = response.data.is_active;
                    this.showToast(`Role ${role.is_active ? 'activated' : 'deactivated'} successfully`, 'success');
                })
                .catch(error => {
                    console.error('Error toggling role status:', error);
                    this.showToast('Error updating role status', 'error');
                });
        },

        deleteRole(role) {
            if (role.name === 'super_admin') {
                this.showToast('Super Admin role cannot be deleted', 'error');
                return;
            }

            if (!confirm(`Are you sure you want to delete the role "${role.display_name}"?`)) {
                return;
            }

            axios.delete(`/superadmin/roles/${role.id}`)
                .then(response => {
                    this.showToast('Role deleted successfully', 'success');
                    this.loadRoles();
                })
                .catch(error => {
                    console.error('Error deleting role:', error);
                    this.showToast('Error deleting role', 'error');
                });
        },

        syncPermissions() {
            if (!confirm('Are you sure you want to sync permissions from controllers? This will detect and create new permissions automatically.')) {
                return;
            }

            this.syncingPermissions = true;
            axios.post('/api/permissions/sync')
                .then(response => {
                    this.showToast(`Permissions synced successfully! ${response.data.new_count || 0} new permissions added.`, 'success');
                    // Reload permissions if modal is open
                    if (this.selectedRole) {
                        this.loadRolePermissions();
                    }
                })
                .catch(error => {
                    console.error('Error syncing permissions:', error);
                    this.showToast('Error syncing permissions', 'error');
                })
                .finally(() => {
                    this.syncingPermissions = false;
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
