@extends('layouts.app')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items: center;">
        <h1>Categories</h1>
    </div>

    <a href="{{ route('categories.create') }}" wire:navigate
        style="background: #1890ff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 20px;">Create
        New Category</a>

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
            @forelse ($categories as $category)
                <tr>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td><code
                            style="background: #eee; padding: 2px 4px; border-radius:3px;">{{ $category->unique_code }}</code>
                    </td>
                    <td>{{ $category->description }}</td>
                    <td>
                        <a href="{{ route('categories.edit', $category->id) }}" wire:navigate>Edit</a>
                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
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
                        Belum ada kategori yang dibuat.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <br>
    <a href="{{ url('/') }}" wire:navigate>Back to Dashboard</a>
@endsection
