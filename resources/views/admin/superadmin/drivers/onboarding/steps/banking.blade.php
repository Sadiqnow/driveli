<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Banking Information:</strong> This information is required for commission payments and financial transactions.
    All banking details will be verified for security purposes.
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="account_name">Account Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('account_name') is-invalid @enderror"
                   id="account_name" name="account_name"
                   value="{{ old('account_name', $driver->primaryBankingDetail?->account_name) }}" required
                   placeholder="Full name as it appears on bank account">
            @error('account_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="account_number">Account Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                   id="account_number" name="account_number"
                   value="{{ old('account_number', $driver->primaryBankingDetail?->account_number) }}" required
                   placeholder="10-digit account number" maxlength="10" pattern="\d{10}">
            @error('account_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="bank_name">Bank Name <span class="text-danger">*</span></label>
            <select class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" required>
                <option value="">Select Bank</option>
                <option value="Access Bank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Access Bank' ? 'selected' : '' }}>Access Bank</option>
                <option value="First Bank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'First Bank' ? 'selected' : '' }}>First Bank</option>
                <option value="GTBank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'GTBank' ? 'selected' : '' }}>GTBank</option>
                <option value="UBA" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'UBA' ? 'selected' : '' }}>United Bank for Africa (UBA)</option>
                <option value="Zenith Bank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Zenith Bank' ? 'selected' : '' }}>Zenith Bank</option>
                <option value="Fidelity Bank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Fidelity Bank' ? 'selected' : '' }}>Fidelity Bank</option>
                <option value="Ecobank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Ecobank' ? 'selected' : '' }}>Ecobank</option>
                <option value="Sterling Bank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Sterling Bank' ? 'selected' : '' }}>Sterling Bank</option>
                <option value="Union Bank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Union Bank' ? 'selected' : '' }}>Union Bank</option>
                <option value="Wema Bank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Wema Bank' ? 'selected' : '' }}>Wema Bank</option>
                <option value="Diamond Bank" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Diamond Bank' ? 'selected' : '' }}>Diamond Bank</option>
                <option value="Stanbic IBTC" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Stanbic IBTC' ? 'selected' : '' }}>Stanbic IBTC</option>
                <option value="Standard Chartered" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Standard Chartered' ? 'selected' : '' }}>Standard Chartered</option>
                <option value="Other" {{ old('bank_name', $driver->primaryBankingDetail?->bank_name) == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('bank_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="bank_code">Bank Code (Sort Code)</label>
            <input type="text" class="form-control @error('bank_code') is-invalid @enderror"
                   id="bank_code" name="bank_code"
                   value="{{ old('bank_code', $driver->primaryBankingDetail?->bank_code) }}"
                   placeholder="e.g., 044" maxlength="3">
            @error('bank_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Optional: 3-digit bank code for transfers</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="account_type">Account Type</label>
            <select class="form-control @error('account_type') is-invalid @enderror" id="account_type" name="account_type">
                <option value="">Select Account Type</option>
                <option value="savings" {{ old('account_type', $driver->primaryBankingDetail?->account_type) == 'savings' ? 'selected' : '' }}>Savings Account</option>
                <option value="current" {{ old('account_type', $driver->primaryBankingDetail?->account_type) == 'current' ? 'selected' : '' }}>Current Account</option>
                <option value="business" {{ old('account_type', $driver->primaryBankingDetail?->account_type) == 'business' ? 'selected' : '' }}>Business Account</option>
            </select>
            @error('account_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="branch_name">Branch Name</label>
            <input type="text" class="form-control @error('branch_name') is-invalid @enderror"
                   id="branch_name" name="branch_name"
                   value="{{ old('branch_name', $driver->primaryBankingDetail?->branch_name) }}"
                   placeholder="Bank branch location">
            @error('branch_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <div class="custom-control custom-checkbox">
        <input class="custom-control-input" type="checkbox" id="is_primary" name="is_primary" value="1" checked>
        <label for="is_primary" class="custom-control-label">
            Set as primary banking account
        </label>
    </div>
    <small class="form-text text-muted">This will be used as the default account for all payments and commissions</small>
</div>

@if($driver->primaryBankingDetail)
<div class="card border-info">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0">
            <i class="fas fa-info-circle text-info"></i> Current Banking Information
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Account Name:</strong> {{ $driver->primaryBankingDetail->account_name }}</p>
                <p><strong>Account Number:</strong> {{ $driver->primaryBankingDetail->account_number }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Bank:</strong> {{ $driver->primaryBankingDetail->bank_name }}</p>
                <p><strong>Status:</strong>
                    <span class="badge badge-{{ $driver->primaryBankingDetail->is_verified ? 'success' : 'warning' }}">
                        {{ $driver->primaryBankingDetail->is_verified ? 'Verified' : 'Pending Verification' }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>
@endif

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Security Notice:</strong>
    <ul class="mb-0 mt-2">
        <li>Ensure the account name matches exactly as it appears on your bank statement</li>
        <li>Double-check account number - payments cannot be reversed once processed</li>
        <li>Banking information will be verified before any payments are made</li>
        <li>You can update banking details later, but verification may be required again</li>
    </ul>
</div>
