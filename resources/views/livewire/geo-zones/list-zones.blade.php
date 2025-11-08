<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Geo Zones</h1>
            <small class="text-muted">Define and manage the operational areas you care about.</small>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('geo-zones.create') }}" class="btn btn-primary">Create zone</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label" for="search">Search</label>
                    <input id="search" type="text" class="form-control" placeholder="Search by name" wire:model.debounce.500ms="search">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="categoryFilter">Category</label>
                    <select id="categoryFilter" class="form-select" wire:model="categoryId">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" wire:click="clearFilters" wire:loading.attr="disabled">
                        Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-decoration-none" wire:click="sortBy('name')">
                                Name
                                @if ($sortField === 'name')
                                    <span class="ms-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </button>
                        </th>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-decoration-none" wire:click="sortBy('category')">
                                Category
                                @if ($sortField === 'category')
                                    <span class="ms-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </button>
                        </th>
                        <th>
                            <button type="button" class="btn btn-link p-0 text-decoration-none" wire:click="sortBy('updated_at')">
                                Last updated
                                @if ($sortField === 'updated_at')
                                    <span class="ms-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($zones as $zone)
                        <tr>
                            <td class="fw-semibold">{{ $zone->name }}</td>
                            <td>{{ $zone->category?->name ?? '—' }}</td>
                            <td>{{ $zone->updated_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('geo-zones.show', $zone) }}" class="btn btn-sm btn-outline-primary me-2">View</a>
                                <a href="{{ route('geo-zones.edit', $zone) }}" class="btn btn-sm btn-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No zones found. Try adjusting your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $zones->links() }}
        </div>
    </div>
</div>
