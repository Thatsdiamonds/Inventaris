<div id="categoryModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Select Category</h3>
            <button type="button" class="btn btn-ghost btn-sm"
                onclick="document.getElementById('categoryModal').style.display='none'">
                <svg class="icon icon-sm">
                    <use href="#icon-close"></use>
                </svg>
            </button>
        </div>

        <div class="modal-body">
            <div class="search-wrapper mb-3">
                <input type="text" id="categorySearch" placeholder="Search category..." onkeyup="filterCategories()">
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
                    <tbody id="categoryList">
                        @foreach ($categories as $cat)
                            <tr class="category-row" style="cursor: pointer;" data-name="{{ strtolower($cat->name) }}"
                                data-code="{{ strtolower($cat->unique_code) }}"
                                onclick="selectCategory({{ $cat->id }}, '{{ $cat->name }}', '{{ $cat->unique_code }}')">
                                <td><code>{{ $cat->unique_code }}</code></td>
                                <td><strong>{{ $cat->name }}</strong></td>
                                <td><small class="text-muted">{{ Str::limit($cat->description, 40) }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-footer">
            <a href="{{ route('categories.create') }}" target="_blank" class="btn btn-ghost btn-sm">
                <svg class="icon icon-sm">
                    <use href="#icon-plus"></use>
                </svg>
                Add New Category
            </a>
        </div>
    </div>
</div>

<script>
    function filterCategories() {
        const search = document.getElementById('categorySearch').value.toLowerCase();
        const rows = document.querySelectorAll('.category-row');

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
    .category-row:hover {
        background: var(--color-bg-secondary);
    }
</style>
