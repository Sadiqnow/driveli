<!-- resources/views/admin/notifications/index.blade.php -->

@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Notification Center</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Message</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notifications as $notification)
                                    <tr>
                                        <td>{{ $notification->id }}</td>
                                        <td>{{ $notification->type }}</td>
                                        <td>{{ $notification->message }}</td>
                                        <td>{{ $notification->created_at }}</td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">View</a>
                                            <a href="#" class="btn btn-sm btn-danger">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
