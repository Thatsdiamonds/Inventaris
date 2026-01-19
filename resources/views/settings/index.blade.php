<h1>Settings</h1>

@if (session('success'))
    <div style="background: #e6ffed; border: 1px solid #52c41a; color: #52c41a; padding: 10px; margin-bottom: 15px;">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('settings.update') }}" method="POST"
    style="max-width: 600px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
    @csrf

    <div style="margin-bottom: 20px;">
        <label><strong>Nama Organisasi / Gereja:</strong></label><br>
        <input type="text" name="nama_gereja" value="{{ $settings->nama_gereja ?? 'Inventaris Management' }}"
            style="width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc;">
    </div>

    <div style="margin-bottom: 20px;">
        <label><strong>Maintenance Warning Threshold (Days):</strong></label><br>
        <input type="number" name="maintenance_threshold" value="{{ $settings->maintenance_threshold ?? 30 }}"
            style="width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc;">
        <small style="color: #666;">Tentukan berapa hari sebelumnya barang akan muncul di tab "Akan Datang".</small>
    </div>

    <div style="margin-bottom: 20px;">
        <label><strong>Default Currency Spirit:</strong></label><br>
        <select name="currency"
            style="width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc;">
            <option value="IDR" {{ ($settings->currency ?? '') == 'IDR' ? 'selected' : '' }}>IDR (Rupiah)</option>
            <option value="USD" {{ ($settings->currency ?? '') == 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
        </select>
    </div>

    <div style="margin-bottom: 20px;">
        <label><strong>Notification Settings:</strong></label><br>
        <label><input type="checkbox" checked disabled> Email Alert for Overdue Maintenance</label><br>
        <label><input type="checkbox" checked disabled> Browser Notification for New Service</label>
    </div>

    <button type="submit"
        style="background: #1890ff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Save
        Settings</button>
</form>

<br>
<a href="{{ url('/') }}">Back to Dashboard</a>
