@extends('layouts.admin')

@section('content')
@php
$user = session('user'); 
$role_id = $user['role'];
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" id="searchBox" class="form-control w-25" placeholder="Search by user name">
         @if($role_id == 1)
            <div>
                <button class="btn btn-success me-2" id="approveSelected">✔ Approve</button>
                <button class="btn btn-danger rounded-2" id="rejectSelected">✖ Reject</button>
            </div> 
        @endif
    </div>

    <table class="table table-hover" id="leaveTable">
        <thead class="table-light">
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>Request Date</th>
                <th>User Name</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leaves as $leave)
            <tr>
                <td><input type="checkbox" class="select-leave" value="{{ $leave->id }}"></td>
                <td>{{ \Carbon\Carbon::parse($leave->created_at)->format('d/m/Y') }}</td>
                <td>{{ $leave->user->name ?? '-' }}</td>
                <td>{{ $leave->leave_type }}</td>
                 <td>{{ date('d-m-Y', strtotime($leave->start_date)) }} </td>
                <td>{{ date('d-m-Y', strtotime($leave->end_date)) }} </td>  
                <td>{{ $leave->description ?? '-' }}</td>
                <td>
                    @php
                        $statusLabel = ['Pending', 'Approved', 'Rejected'];
                        $badgeColor = ['warning', 'success', 'danger'];
                    @endphp
                    <span class="badge text-capitalize px-3 py-2 fw-semibold rounded-pill bg-{{ $badgeColor[$leave->status] ?? 'secondary' }}">
                        {{ $statusLabel[$leave->status] ?? 'Unknown' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Reject Reason Modal -->
<div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectReasonModalLabel">Reject Leave Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="rejectReason" class="form-label">Reason for Rejection</label>
                <textarea id="rejectReason" class="form-control" rows="3" placeholder="Enter reason"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmReject">Reject</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
 
<script>
    let selectedIds = [];

    // Search Filter
    document.getElementById('searchBox').addEventListener('keyup', function () {
        const value = this.value.toLowerCase();
        document.querySelectorAll('#leaveTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });

    // Select All
    document.getElementById('selectAll').addEventListener('change', function () {
        const checked = this.checked;
        document.querySelectorAll('.select-leave').forEach(cb => cb.checked = checked);
    });

    // Approve button
    document.getElementById('approveSelected').addEventListener('click', function () {
        updateLeaveStatus(1, null); // Approved, no reason needed
    });

    // Reject button - show modal
    document.getElementById('rejectSelected').addEventListener('click', function () {
        selectedIds = Array.from(document.querySelectorAll('.select-leave:checked')).map(cb => cb.value);

        if (selectedIds.length === 0) {
            alert('Please select at least one request.');
            return;
        }

        let modal = new bootstrap.Modal(document.getElementById('rejectReasonModal'));
        modal.show();
    });

    // Confirm Reject
    document.getElementById('confirmReject').addEventListener('click', function () {
        const reason = document.getElementById('rejectReason').value.trim();

        if (!reason) {
            alert('Please enter a reason.');
            return;
        }

        updateLeaveStatus(2, reason); // Rejected with reason
    });

    function updateLeaveStatus(status, reason) {
        let ids = selectedIds.length ? selectedIds : Array.from(document.querySelectorAll('.select-leave:checked')).map(cb => cb.value);
        if (ids.length === 0) {
            alert('Please select at least one request.');
            return;
        }

        fetch('{{ route("leave-requests.updateReason") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                leave_ids: ids,
                reason: reason,
                status: status
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                alert('Status updated successfully!');
                location.reload();
            } else {
                alert('Update failed: ' + res.message);
            }
        })
        .catch(error => {
            console.error(error);
            alert('Something went wrong while updating.');
        });
    }
</script>
@endsection
