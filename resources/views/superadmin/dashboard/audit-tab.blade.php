<!-- Audit Logs Tab Content -->
<div class="audit-logs-tab">
    <!-- Audit Logs Filters -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="form-group">
                <label for="audit-action-filter"><i class="fas fa-filter"></i> Action</label>
                <select id="audit-action-filter" class="form-control" v-model="auditActionFilter" @change="filterAuditLogs">
                    <option value="">All Actions</option>
                    <option v-for="action in availableActions" :key="action" :value="action">{{ action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="audit-user-filter"><i class="fas fa-user"></i> User</label>
                <select id="audit-user-filter" class="form-control" v-model="auditUserFilter" @change="filterAuditLogs">
                    <option value="">All Users</option>
                    <option v-for="user in availableUsers" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="audit-date-from"><i class="fas fa-calendar"></i> From</label>
                <input type="date" id="audit-date-from" class="form-control" v-model="auditDateFrom" @change="filterAuditLogs">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="audit-date-to"><i class="fas fa-calendar"></i> To</label>
                <input type="date" id="audit-date-to" class="form-control" v-model="auditDateTo" @change="filterAuditLogs">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="d-flex">
                    <button @click="clearAuditFilters" class="btn btn-outline-secondary btn-sm mr-1">
                        <i class="fas fa-times"></i>
                    </button>
                    <button @click="exportAuditLogs" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Logs List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-history mr-1"></i>
                Recent Activity Logs ({{ filteredAuditLogs.length }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in paginatedAuditLogs" :key="log.id" :class="getLogRowClass(log)">
                            <td>
                                <small class="text-muted">{{ formatDate(log.created_at) }}</small>
                            </td>
                            <td>
                                <strong>{{ log.user_name || 'System' }}</strong>
                                <br><small class="text-muted">{{ log.user_email || 'N/A' }}</small>
                            </td>
                            <td>
                                <span :class="getActionBadgeClass(log.action)">
                                    {{ log.action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ log.resource_type || 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="audit-description">
                                    {{ truncateText(log.description, 50) }}
                                </div>
                            </td>
                            <td>
                                <code class="small">{{ log.ip_address || 'N/A' }}</code>
                            </td>
                            <td>
                                <button @click="viewAuditDetails(log)" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredAuditLogs.length === 0">
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No audit logs found matching your criteria.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination -->
        <div v-if="totalAuditPages > 1" class="card-footer">
            <nav aria-label="Audit logs pagination">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item" :class="{ disabled: auditCurrentPage === 1 }">
                        <a class="page-link" href="#" @click.prevent="changeAuditPage(auditCurrentPage - 1)">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <li v-for="page in auditVisiblePages" :key="page" class="page-item" :class="{ active: page === auditCurrentPage }">
                        <a class="page-link" href="#" @click.prevent="changeAuditPage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: auditCurrentPage === totalAuditPages }">
                        <a class="page-link" href="#" @click.prevent="changeAuditPage(auditCurrentPage + 1)">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Audit Log Details Modal -->
    <div class="modal fade" id="auditDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle mr-1"></i>
                        Audit Log Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="selectedAuditLog">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Basic Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>ID:</strong></td>
                                        <td>{{ selectedAuditLog.id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Timestamp:</strong></td>
                                        <td>{{ formatDate(selectedAuditLog.created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>User:</strong></td>
                                        <td>{{ selectedAuditLog.user_name || 'System' }} ({{ selectedAuditLog.user_email || 'N/A' }})</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Action:</strong></td>
                                        <td>
                                            <span :class="getActionBadgeClass(selectedAuditLog.action)">
                                                {{ selectedAuditLog.action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Resource Type:</strong></td>
                                        <td>{{ selectedAuditLog.resource_type || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Resource ID:</strong></td>
                                        <td>{{ selectedAuditLog.resource_id || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>IP Address:</strong></td>
                                        <td><code>{{ selectedAuditLog.ip_address || 'N/A' }}</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>User Agent:</strong></td>
                                        <td><small>{{ selectedAuditLog.user_agent || 'N/A' }}</small></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Description</h6>
                                <p class="mb-3">{{ selectedAuditLog.description }}</p>

                                <div v-if="selectedAuditLog.old_values && Object.keys(selectedAuditLog.old_values).length > 0">
                                    <h6>Previous Values</h6>
                                    <pre class="bg-light p-2 rounded small">{{ JSON.stringify(selectedAuditLog.old_values, null, 2) }}</pre>
                                </div>

                                <div v-if="selectedAuditLog.new_values && Object.keys(selectedAuditLog.new_values).length > 0" class="mt-3">
                                    <h6>New Values</h6>
                                    <pre class="bg-light p-2 rounded small">{{ JSON.stringify(selectedAuditLog.new_values, null, 2) }}</pre>
                                </div>

                                <div v-if="selectedAuditLog.metadata && Object.keys(selectedAuditLog.metadata).length > 0" class="mt-3">
                                    <h6>Additional Metadata</h6>
                                    <pre class="bg-light p-2 rounded small">{{ JSON.stringify(selectedAuditLog.metadata, null, 2) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
new Vue({
    el: '.audit-logs-tab',
    data: {
        auditLogs: [],
        filteredAuditLogs: [],
        selectedAuditLog: null,
        availableActions: [],
        availableUsers: [],
        auditActionFilter: '',
        auditUserFilter: '',
        auditDateFrom: '',
        auditDateTo: '',
        auditCurrentPage: 1,
        auditItemsPerPage: 15,
        loadingAuditLogs: true
    },
    computed: {
        totalAuditPages() {
            return Math.ceil(this.filteredAuditLogs.length / this.auditItemsPerPage);
        },
        paginatedAuditLogs() {
            const start = (this.auditCurrentPage - 1) * this.auditItemsPerPage;
            const end = start + this.auditItemsPerPage;
            return this.filteredAuditLogs.slice(start, end);
        },
        auditVisiblePages() {
            const pages = [];
            const start = Math.max(1, this.auditCurrentPage - 2);
            const end = Math.min(this.totalAuditPages, this.auditCurrentPage + 2);

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        }
    },
    mounted() {
        this.loadAuditLogs();
    },
    methods: {
        loadAuditLogs() {
            this.loadingAuditLogs = true;
            axios.get('/api/admin/audit/rbac')
                .then(response => {
                    this.auditLogs = response.data.data || response.data;
                    this.filteredAuditLogs = this.auditLogs;

                    // Extract unique actions and users
                    this.availableActions = [...new Set(this.auditLogs.map(log => log.action))].sort();
                    this.availableUsers = [...new Set(this.auditLogs.map(log => ({ id: log.user_id, name: log.user_name })).filter(user => user.id))];
                })
                .catch(error => {
                    console.error('Error loading audit logs:', error);
                    this.showToast('Error loading audit logs', 'error');
                })
                .finally(() => {
                    this.loadingAuditLogs = false;
                });
        },

        filterAuditLogs() {
            let filtered = this.auditLogs;

            if (this.auditActionFilter) {
                filtered = filtered.filter(log => log.action === this.auditActionFilter);
            }

            if (this.auditUserFilter) {
                filtered = filtered.filter(log => log.user_id == this.auditUserFilter);
            }

            if (this.auditDateFrom) {
                const fromDate = new Date(this.auditDateFrom);
                filtered = filtered.filter(log => new Date(log.created_at) >= fromDate);
            }

            if (this.auditDateTo) {
                const toDate = new Date(this.auditDateTo);
                toDate.setHours(23, 59, 59, 999); // End of day
                filtered = filtered.filter(log => new Date(log.created_at) <= toDate);
            }

            this.filteredAuditLogs = filtered;
            this.auditCurrentPage = 1;
        },

        clearAuditFilters() {
            this.auditActionFilter = '';
            this.auditUserFilter = '';
            this.auditDateFrom = '';
            this.auditDateTo = '';
            this.filterAuditLogs();
        },

        changeAuditPage(page) {
            if (page >= 1 && page <= this.totalAuditPages) {
                this.auditCurrentPage = page;
            }
        },

        viewAuditDetails(log) {
            this.selectedAuditLog = log;
            $('#auditDetailsModal').modal('show');
        },

        exportAuditLogs() {
            const params = new URLSearchParams({
                action: this.auditActionFilter,
                user_id: this.auditUserFilter,
                date_from: this.auditDateFrom,
                date_to: this.auditDateTo
            });

            window.open(`/api/admin/audit/rbac/export?${params}`, '_blank');
        },

        getLogRowClass(log) {
            const actionClasses = {
                'created': 'table-success',
                'updated': 'table-warning',
                'deleted': 'table-danger',
                'assigned': 'table-info',
                'removed': 'table-secondary'
            };

            return actionClasses[log.action] || '';
        },

        getActionBadgeClass(action) {
            const badgeClasses = {
                'created': 'badge badge-success',
                'updated': 'badge badge-warning',
                'deleted': 'badge badge-danger',
                'assigned': 'badge badge-info',
                'removed': 'badge badge-secondary',
                'login': 'badge badge-primary',
                'logout': 'badge badge-light'
            };

            return badgeClasses[action] || 'badge badge-secondary';
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString();
        },

        truncateText(text, maxLength) {
            if (!text) return '';
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
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
