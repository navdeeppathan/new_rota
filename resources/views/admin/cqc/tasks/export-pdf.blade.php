

<h3>{{ $section }} Tasks ({{ ucfirst($range) }})</h3>

<table width="100%" border="1" cellspacing="0" cellpadding="6">
    <thead>
        <tr>
            <th>#</th>
            <th>Description</th>
            <th>Progress</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tasks as $task)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $task->description }}</td>
            <td>{{ ucfirst($task->progress) }}</td>
            <td>{{ $task->created_at->format('d M Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


