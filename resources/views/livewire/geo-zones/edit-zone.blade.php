<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $isEditing ? 'Edit Geo Zone' : 'Create Geo Zone' }}</h1>
            <small class="text-muted">Draw or adjust the polygon, then save your changes.</small>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ $isEditing && $zone ? route('geo-zones.show', $zone) : route('geo-zones.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form wire:submit.prevent="save">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="name">Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="e.g. Lagos Exclusion Zone">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="category">Category</label>
                            <select id="category" class="form-select @error('categoryId') is-invalid @enderror" wire:model="categoryId">
                                <option value="">Select category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('categoryId')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Geometry</label>
                            <div class="form-text">Draw the polygon using the map. Single polygons will be saved as multipolygons automatically.</div>
                            @error('geometry')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">{{ $isEditing ? 'Update zone' : 'Create zone' }}</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-0">
                        <div id="edit-zone-map" style="height: 520px;" wire:ignore></div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" wire:model="geometry">
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', () => {
        if (typeof L === 'undefined') {
            return;
        }

        const component = Livewire.find('{{ $this->id }}');
        if (!component) {
            return;
        }

        const map = L.map('edit-zone-map').setView([0, 0], 2);
        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const drawControl = new L.Control.Draw({
            edit: {
                featureGroup: drawnItems,
            },
            draw: {
                polygon: {
                    allowIntersection: false,
                    showArea: true,
                },
                rectangle: false,
                circle: false,
                circlemarker: false,
                marker: false,
                polyline: false,
            },
        });

        map.addControl(drawControl);

        const existingGeometry = @json($geometry ? json_decode($geometry, true) : null);
        if (existingGeometry) {
            const layer = L.geoJSON(existingGeometry);
            layer.eachLayer((segment) => drawnItems.addLayer(segment));
            map.fitBounds(layer.getBounds(), { padding: [24, 24] });
        }

        const persistGeometry = () => {
            const collection = drawnItems.toGeoJSON();
            if (!collection.features.length) {
                component.set('geometry', '');
                return;
            }

            const multiPolygon = {
                type: 'MultiPolygon',
                coordinates: [],
            };

            collection.features.forEach((feature) => {
                if (!feature.geometry) {
                    return;
                }
                if (feature.geometry.type === 'Polygon') {
                    multiPolygon.coordinates.push(feature.geometry.coordinates);
                } else if (feature.geometry.type === 'MultiPolygon') {
                    feature.geometry.coordinates.forEach((coords) => multiPolygon.coordinates.push(coords));
                }
            });

            component.set('geometry', JSON.stringify(multiPolygon));
        };

        map.on(L.Draw.Event.CREATED, (event) => {
            drawnItems.addLayer(event.layer);
            persistGeometry();
        });

        map.on(L.Draw.Event.EDITED, persistGeometry);
        map.on(L.Draw.Event.DELETED, persistGeometry);
    });
</script>
@endpush
