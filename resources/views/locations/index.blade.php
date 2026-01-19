<div style="display:flex; justify-content: space-between; align-items: center;">
    <h1>Locations</h1>
    <nav>
        <a href="{{ url('/') }}" style="text-decoration: none; font-weight: bold; color: #1890ff;">Dashboard</a> |
        <a href="{{ route('items.index') }}" style="text-decoration: none; font-weight: bold; color: #1890ff;">Items</a> |
        <a href="{{ route('services.index') }}"
            style="text-decoration: none; font-weight: bold; color: #1890ff;">Services</a> |
        <a href="{{ route('categories.index') }}"
            style="text-decoration: none; font-weight: bold; color: #1890ff;">Categories</a> |
        <a href="{{ route('locations.index') }}"
            style="text-decoration: underline; font-weight: bold; color: #1890ff;">Locations</a> |
        <a href="{{ route('settings.index') }}"
            style="text-decoration: none; font-weight: bold; color: #52c41a;">Settings</a>
    </nav>
</div>

<a href="#" onclick="replacePage('{{ route('locations.create') }}')"
    style="background: #1890ff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 20px;">Create
    New Location</a>

@if (session('success'))
    <div style="color: green; background: #e6ffed; padding: 10px; border: 1px solid green; margin-bottom: 15px;">
        {{ session('success') }}</div>
@endif

<table border="1" cellspacing="0" cellpadding="8" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr style="background: #f5f5f5;">
            <th>Name</th>
            <th>Unique Code</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($locations as $location)
            <tr>
                <td><strong>{{ $location->name }}</strong></td>
                <td><code
                        style="background: #eee; padding: 2px 4px; border-radius:3px;">{{ $location->unique_code }}</code>
                </td>
                <td>{{ $location->description }}</td>
                <td>
                    <a href="#" onclick="replacePage('{{ route('locations.edit', $location->id) }}')">Edit</a>
                    <form action="{{ route('locations.destroy', $location->id) }}" method="POST"
                        style="display:inline; margin-left: 10px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Are you sure?')"
                            style="background: none; border: none; color: red; cursor: pointer; padding: 0;">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px; color: #666;">
                    Belum ada lokasi yang dibuat.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<script>
    function replacePage(url) {
        window.location.replace(url);
    }
</script>

<br>
<a href="{{ url('/') }}">Back to Dashboard</a>
