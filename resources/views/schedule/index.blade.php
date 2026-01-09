@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Staff Schedule</h2>
    
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-success">
                <tr>
                    <th>MASTER</th>
                    @foreach ($days as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($schedule as $group => $people)
                    <tr class="table-secondary fw-bold">
                        <td colspan="{{ count($days) + 1 }}">{{ $group }}</td>
                    </tr>
                    @foreach ($people as $name => $entries)
                        <tr>
                            <td>{{ $name }}</td>
                            @foreach ($entries as $entry)
                                <td class="{{ str_contains($entry, 'Meds') ? 'text-danger fw-bold' : '' }}">
                                    {{ $entry }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach

                <tr class="table-warning fw-bold">
                    <td>No' · IN</td>
                    @foreach ($totalIn as $total)
                        <td>{{ $total }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
