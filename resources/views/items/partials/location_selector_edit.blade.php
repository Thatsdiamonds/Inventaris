<div id="locationModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Select Location</h3>
            <button type="button" class="btn btn-ghost btn-sm"
                onclick="document.getElementById('locationModal').style.display='none'">
                <svg class="icon icon-sm">
                    <use href="#icon-close"></use>
                </svg>
            </button>
        </div>

        <div class="modal-body">
            <div class="search-wrapper mb-3">
                <input type="text" id="locationSearch" placeholder="Search location..." onkeyup="filterLocations()">
                <svg class="icon icon-sm">
                    <use href="#icon-search"></use>
                </svg>
            </div>

            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="locationList">
                        @foreach ($locations as $loc)
                            <tr class="location-row" style="cursor: pointer;" data-name="{{ strtolower($loc->name) }}"
                                data-code="{{ strtolower($loc->unique_code) }}"
                                onclick="selectLocation({{ $loc->id }}, '{{ $loc->name }}', '{{ $loc->unique_code }}')">
                                <td><code>{{ $loc->unique_code }}</code></td>
                                <td><strong>{{ $loc->name }}</strong></td>
                                <td><small class="text-muted">{{ Str::limit($loc->description, 40) }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-footer">
            <a href="{{ route('locations.create') }}" target="_blank" class="btn btn-ghost btn-sm">
                <svg class="icon icon-sm">
                    <use href="#icon-plus"></use>
                </svg>
                Add New Location
            </a>
        </div>
    </div>
</div>

<script>
    function filterLocations() {
        const search = document.getElementById('locationSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.location-row');

        rows.forEach(row => {
            const name = row.dataset.name;
            const code = row.dataset.code;

            if (name.includes(search) || code.includes(search)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

<style>
    .location-row:hover {
        background: var(--color-bg-secondary);
    }
</style>
