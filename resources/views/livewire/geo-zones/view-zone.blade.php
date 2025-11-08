<div class="container py-4" wire:ignore.self>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $zone->name }}</h1>
            <div class="text-muted">Category: {{ $zone->category?->name ?? '—' }}</div>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('geo-zones.edit', $zone) }}" class="btn btn-primary me-2">Edit</a>
            <a href="{{ route('geo-zones.index') }}" class="btn btn-outline-secondary">Back to list</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-0">
                    <div id="view-zone-map" style="height: 480px;" wire:ignore></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Details</h5>
                    <dl class="row mb-0">
                        <dt class="col-5">Name</dt>
                        <dd class="col-7 text-end">{{ $zone->name }}</dd>

                        <dt class="col-5">Category</dt>
                        <dd class="col-7 text-end">{{ $zone->category?->name ?? '—' }}</dd>

                        <dt class="col-5">Created</dt>
                        <dd class="col-7 text-end">{{ $zone->created_at?->format('d M Y H:i') ?? '—' }}</dd>

                        <dt class="col-5">Updated</dt>
                        <dd class="col-7 text-end">{{ $zone->updated_at?->format('d M Y H:i') ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', () => {
        const geometry = @json(json_decode($geometry, true));
        if (!geometry || typeof L === 'undefined') {
            return;
        }

        const map = L.map('view-zone-map');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const layer = L.geoJSON(geometry).addTo(map);
        map.fitBounds(layer.getBounds(), { padding: [24, 24] });
    });
</script>
@endpush
