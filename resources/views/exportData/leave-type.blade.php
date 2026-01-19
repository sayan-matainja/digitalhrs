<table>
    <thead>
    <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Leave Allocated</th>
    </tr>
    </thead>
    <tbody>
    @foreach($types as $key => $data)
        <tr>
            <td>{{$data->id}}</td>
            <td>{{ $data->name }}</td>
            <td>{{ $data->leave_allocated ?? 0 }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
