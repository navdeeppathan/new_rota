@extends('layouts.admin')

@section('content')
@php
$user = session('user'); 
$role_id = $user['role'];
@endphp
<div class="container">
    
    <h4>Weekly Availabilities</h4>
    <div style="text-align: left; margin-top: 33px;">
        @if($role_id == 1)
        <a href="{{ route('availability.create') }}" class="btn btn-primary {{ Route::is('availability.create') ? 'active' : '' }}">
            <i class="fas fa-plus icon"></i> Add Availability
        </a>
        @endif
    </div>

    <table id="example" class="table">
        <thead>
            <tr>
                <th scope="col">User</th>
                <th scope="col">Days</th>
                <th scope="col">Shift</th>
                <th scope="col">Availability Time</th>
                <th scope="col">Day Off</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($availabilities as $availability)
                <tr>
                    <td>{{ $availability->user->name ?? 'N/A' }}</td>
                    <td>
                        @php
                            $daysArray = is_string($availability->days) ? json_decode($availability->days, true) : $availability->days;
                        @endphp
                        {{ $daysArray ? implode(', ', $daysArray) : 'N/A' }}
                    </td>
                    <td>{{ ucfirst($availability->shift) ?? 'N/A' }}</td>
                    <td>
                        {{ $availability->start_time ? date('h:i A', strtotime($availability->start_time)) : 'N/A' }}
                        -
                        {{ $availability->end_time ? date('h:i A', strtotime($availability->end_time)) : 'N/A' }}
                    </td>
                    <td>{{ $availability->is_day_off ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>  
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap4.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.3/js/dataTables.buttons.js"></script>
<script>
    new DataTable('#example', {
        layout: {
            topStart: {
                buttons: []
            }
        }
    });
</script>
@endsection
