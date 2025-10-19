@extends('layouts.admin_cdn')

@section('title', 'Roles & Permissions Dashboard - Superadmin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Roles & Permissions Dashboard</h1>
            <p class="text-muted">Comprehensive role and permission management system</p>
        </div>
        <div class="btn-group">
            <button @click="refreshData" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <a href="{{ route('superadmin.audit-logs') }}" class="btn btn-outline-info">
                <i class="fas fa-history"></i> Audit Logs
            </a>
        </div>
    </div>
@endsection

@section('content')
<div id="roles-permissions-dashboard">
    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Dashboard Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ stats.total_roles }}</h4>
                            <small>Total Roles</small>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ stats.total_permissions }}</h4>
                            <small>Total Permissions</small>
                        </div>
                        <i class="fas fa-key fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ stats.total_users }}</h4>
                            <small>Total Users</small>
                        </div>
                        <i class="fas fa-user-friends fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ stats.recent_audit_logs }}</h4>
                            <small>Recent Changes</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="dashboardTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="roles-tab" data-toggle="tab" href="#roles" role="tab">
                        <i class="fas fa-users mr-1"></i> Roles Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions" role="tab">
                        <i class="fas fa-key mr-1"></i> Permissions Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="user-roles-tab" data-toggle="tab" href="#user-roles" role="tab">
                        <i class="fas fa-user-tag mr-1"></i> User Roles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="audit-tab" data-toggle="tab" href="#audit" role="tab">
                        <i class="fas fa-history mr-1"></i> Recent Activity
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="dashboardTabContent">
                <!-- Roles Management Tab -->
                <div class="tab-pane fade show active" id="roles" role="tabpanel">
                    @include('superadmin.dashboard.roles-tab')
                </div>

                <!-- Permissions Management Tab -->
                <div class="tab-pane fade" id="permissions" role="tabpanel">
                    @include('superadmin.dashboard.permissions-tab')
                </div>

                <!-- User Roles Tab -->
                <div class="tab-pane fade" id="user-roles" role="tabpanel">
                    @include('superadmin.dashboard.user-roles-tab')
                </div>

                <!-- Audit Tab -->
                <div class="tab-pane fade" id="audit" role="tabpanel">
                    @include('superadmin.dashboard.audit-tab')
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
    el: '#roles-permissions-dashboard',
    data: {
        loading: false,
        stats: {
            total_roles: 0,
            total_permissions: 0,
            total_users: 0,
            recent_audit_logs: 0
        }
    },
    mounted() {
        this.loadStats();
    },
    methods: {
        loadStats() {
            this.loading = true;
            axios.get('/api/admin/rbac/stats')
                .then(response => {
                    this.stats = response.data.data;
                })
                .catch(error => {
                    console.error('Error loading stats:', error);
                    this.showToast('Error loading dashboard statistics', 'error');
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        refreshData() {
            this.loadStats();
            // Emit event to child components to refresh their data
            this.$emit('refresh-data');
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

.card.bg-primary, .card.bg-success, .card.bg-info, .card.bg-warning {
    border: none;
}

.card.bg-primary .card-body, .card.bg-success .card-body, .card.bg-info .card-body, .card.bg-warning .card-body {
    background: inherit;
}

.opacity-75 {
    opacity: 0.75;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link.active {
    border-bottom-color: #007bff;
    background-color: transparent;
}

.tab-content {
    padding: 0;
}

.table-responsive {
    border-radius: 0.375rem;
}

.custom-control-label {
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
