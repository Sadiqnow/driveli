<!-- Permissions Management Tab Content -->
<div class="permissions-management-tab">
    <!-- Search and Filter Section -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="form-group">
                <label for="permissions-search"><i class="fas fa-search"></i> Search Permissions</label>
                <input type="text" id="permissions-search" class="form-control" v-model="permissionsSearchQuery" @input="filterPermissions" placeholder="Search by name or description...">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="permissions-category-filter"><i class="fas fa-filter"></i> Category</label>
                <select id="permissions-category-filter" class="form-control" v-model="permissionsCategoryFilter" @change="filterPermissions">
                    <option value="">All Categories</option>
                    <option v-for="category in availableCategories" :key="category" :value="category">{{ category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="permissions-status-filter"><i class="fas fa-toggle-on"></i> Status</label>
                <select id="permissions-status-filter" class="form-control" v-model="permissionsStatusFilter" @change="filterPermissions">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="d-flex">
                    <button @click="clearPermissionsFilters" class="btn btn-outline-secondary btn-sm mr-1">
                        <i class="fas fa-times"></i>
                    </button>
                    <a href="{{ route('superadmin.permissions.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-key mr-1"></i>
                Permissions ({{ filteredPermissions.length }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Permission Name</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Resource</th>
                            <th>Action</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="permission in paginatedPermissions" :key="permission.id">
                            <td>
                                <strong>{{ permission.display_name }}</strong>
                                <br><small class="text-muted">{{ permission.name }}</small>
                            </td>
                            <td>{{ permission.description || 'No description' }}</td>
                            <td>
                                <span class="badge badge-info">{{ permission.category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</span>
                            </td>
                            <td>{{ permission.resource || '-' }}</td>
                            <td>{{ permission.action || '-' }}</td>
                            <td>
                                <span class="badge badge-secondary">{{ permission.roles_count }}</span>
                            </td>
                            <td>
                                <span :class="permission.is_active ? 'badge badge-success' : 'badge badge-danger'">
                                    {{ permission.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button @click="viewPermissionDetails(permission)" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a :href="`/superadmin/permissions/${permission.id}/edit`" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button @click="togglePermissionStatus(permission)" class="btn btn-outline-warning" title="Toggle Status">
                                        <i class="fas fa-toggle-on"></i>
                                    </button>
                                    <button @click="deletePermission(permission)" class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredPermissions.length === 0">
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No permissions found matching your criteria.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination -->
        <div v-if="totalPermissionsPages > 1" class="card-footer">
            <nav aria-label="Permission pagination">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: permissionsCurrentPage === 1 }">
                        <a class="page-link" href="#" @click.prevent="changePermissionsPage(permissionsCurrentPage - 1)">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <li v-for="page in permissionsVisiblePages" :key="page" class="page-item" :class="{ active: page === permissionsCurrentPage }">
                        <a class="page-link" href="#" @click.prevent="changePermissionsPage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: permissionsCurrentPage === totalPermissionsPages }">
                        <a class="page-link" href="#" @click.prevent="changePermissionsPage(permissionsCurrentPage + 1)">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Permission Details Modal -->
    <div class="modal fade" id="permissionDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle mr-1"></i>
                        Permission Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="selectedPermission">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Basic Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td>{{ selectedPermission.name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Display Name:</strong></td>
                                        <td>{{ selectedPermission.display_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Description:</strong></td>
                                        <td>{{ selectedPermission.description || 'No description' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Category:</strong></td>
                                        <td>{{ selectedPermission.category.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Resource:</strong></td>
                                        <td>{{ selectedPermission.resource || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Action:</strong></td>
                                        <td>{{ selectedPermission.action || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span :class="selectedPermission.is_active ? 'badge badge-success' : 'badge badge-danger'">
                                                {{ selectedPermission.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Associated Roles ({{ selectedPermission.roles_count }})</h6>
                                <div v-if="permissionRolesLoading" class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div v-else-if="permissionRoles.length > 0" class="permission-roles-list">
                                    <div v-for="role in permissionRoles" :key="role.id" class="mb-2">
                                        <span class="badge badge-primary mr-1">{{ role.display_name }}</span>
                                        <small class="text-muted">Level {{ role.level }}</small>
                                    </div>
                                </div>
                                <div v-else class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    No roles assigned to this permission
                                </div>
                            </div>
                        </div>
                        <div v-if="selectedPermission.meta && Object.keys(selectedPermission.meta).length > 0" class="mt-3">
                            <h6>Additional Metadata</h6>
                            <pre class="bg-light p-2 rounded">{{ JSON.stringify(selectedPermission.meta, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a v-if="selectedPermission" :href="`/superadmin/permissions/${selectedPermission.id}/edit`" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Permission
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
new Vue({
    el: '.permissions-management-tab',
    data: {
        permissions: [],
        filteredPermissions: [],
        selectedPermission: null,
        permissionRoles: [],
        permissionsSearchQuery: '',
        permissionsCategoryFilter: '',
        permissionsStatusFilter: '',
        permissionsCurrentPage: 1,
        permissionsItemsPerPage: 10,
        permissionRolesLoading: false
    },
    computed: {
        availableCategories() {
            const categories = [...new Set(this.permissions.map(permission => permission.category))];
            return categories.sort();
        },
        totalPermissionsPages() {
            return Math.ceil(this.filteredPermissions.length / this.permissionsItemsPerPage);
        },
        paginatedPermissions() {
            const start = (this.permissionsCurrentPage - 1) * this.permissionsItemsPerPage;
            const end = start + this.permissionsItemsPerPage;
            return this.filteredPermissions.slice(start, end);
        },
        permissionsVisiblePages() {
            const pages = [];
            const start = Math.max(1, this.permissionsCurrentPage - 2);
            const end = Math.min(this.totalPermissionsPages, this.permissionsCurrentPage + 2);

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        }
    },
    mounted() {
        this.loadPermissions();
    },
    methods: {
        loadPermissions() {
            axios.get('/api/permissions')
                .then(response => {
                    this.permissions = response.data.data;
                    this.filteredPermissions = this.permissions;
                })
                .catch(error => {
                    console.error('Error loading permissions:', error);
                    this.showToast('Error loading permissions', 'error');
                });
        },

        filterPermissions() {
            let filtered = this.permissions;

            if (this.permissionsSearchQuery.trim()) {
                const query = this.permissionsSearchQuery.toLowerCase();
                filtered = filtered.filter(permission =>
                    permission.name.toLowerCase().includes(query) ||
                    permission.display_name.toLowerCase().includes(query) ||
                    (permission.description && permission.description.toLowerCase().includes(query))
                );
            }

            if (this.permissionsCategoryFilter) {
                filtered = filtered.filter(permission => permission.category === this.permissionsCategoryFilter);
            }

            if (this.permissionsStatusFilter) {
                const isActive = this.permissionsStatusFilter === 'active';
                filtered = filtered.filter(permission => permission.is_active === isActive);
            }

            this.filteredPermissions = filtered;
            this.permissionsCurrentPage = 1;
        },

        clearPermissionsFilters() {
            this.permissionsSearchQuery = '';
            this.permissionsCategoryFilter = '';
            this.permissionsStatusFilter = '';
            this.filterPermissions();
        },

        changePermissionsPage(page) {
            if (page >= 1 && page <= this.totalPermissionsPages) {
                this.permissionsCurrentPage = page;
            }
        },

        viewPermissionDetails(permission) {
            this.selectedPermission = permission;
            this.loadPermissionRoles();
            $('#permissionDetailsModal').modal('show');
        },

        loadPermissionRoles() {
            if (!this.selectedPermission) return;

            this.permissionRolesLoading = true;
            axios.get(`/superadmin/permissions/${this.selectedPermission.id}/roles`)
                .then(response => {
                    this.permissionRoles = response.data.data || [];
                })
                .catch(error => {
                    console.error('Error loading permission roles:', error);
                    this.permissionRoles = [];
                })
                .finally(() => {
                    this.permissionRolesLoading = false;
                });
        },

        togglePermissionStatus(permission) {
            axios.patch(`/superadmin/permissions/${permission.id}/toggle-status`)
                .then(response => {
                    permission.is_active = response.data.is_active;
                    this.showToast(`Permission ${permission.is_active ? 'activated' : 'deactivated'} successfully`, 'success');
                })
                .catch(error => {
                    console.error('Error toggling permission status:', error);
                    this.showToast('Error updating permission status', 'error');
                });
        },

        deletePermission(permission) {
            if (permission.roles_count > 0) {
                this.showToast('Cannot delete permission that is assigned to roles', 'error');
                return;
            }

            if (!confirm(`Are you sure you want to delete the permission "${permission.display_name}"?`)) {
                return;
            }

            axios.delete(`/superadmin/permissions/${permission.id}`)
                .then(response => {
                    this.showToast('Permission deleted successfully', 'success');
                    this.loadPermissions();
                })
                .catch(error => {
                    console.error('Error deleting permission:', error);
                    this.showToast('Error deleting permission', 'error');
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
