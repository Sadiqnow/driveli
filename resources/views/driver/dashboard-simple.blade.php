<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - DriveLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">✅ Driver Authentication Working!</h4>
                    </div>
                    <div class="card-body">
                        <h5>Welcome, {{ $driver->full_name ?? $driver->first_name ?? 'Driver' }}!</h5>
                        <p class="text-muted">You have successfully logged in to your driver account.</p>
                        
                        <div class="mt-4">
                            <h6>Driver Details:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Driver ID:</strong> {{ $driver->driver_id ?? 'N/A' }}</li>
                                <li><strong>Email:</strong> {{ $driver->email ?? 'N/A' }}</li>
                                <li><strong>Phone:</strong> {{ $driver->phone ?? 'N/A' }}</li>
                                <li><strong>Registration Date:</strong> {{ $driver->created_at ? $driver->created_at->format('M d, Y') : 'N/A' }}</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4">
                            <form action="{{ route('driver.logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>