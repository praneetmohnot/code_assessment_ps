# Geo Zone Test Cases

This document enumerates the critical scenarios to cover for the "Add Geo Zone" workflow. Each case can be automated (Livewire feature test) or executed manually.

## 1. Successful Creation
- **Preconditions**: Categories seeded (War Risk, Country, Port).
- **Steps**:
  1. Navigate to `/geo-zones/create`.
  2. Enter `Test Zone` as the name.
  3. Select `War Risk` as the category.
  4. Draw a polygon (≥ 3 vertices, closed) on the map.
  5. Submit the form.
- **Expected**:
  - Redirect to `/geo-zones/{id}`.
  - Flash message: `GeoZone created successfully.`
  - Zone appears in list with correct category and geometry.

## 2. Missing Name Validation
- **Steps**: Attempt submission without entering a name (other fields valid).
- **Expected**: Validation error on `name` (`The name field is required.`); no record created.

## 3. Missing Category Validation
- **Steps**: Enter name + geometry, leave category unselected.
- **Expected**: Validation error on `categoryId`; no record created.

## 4. Missing Geometry Validation
- **Steps**: Enter name/category but do not draw geometry.
- **Expected**: Validation error on `geometry`; no record created.

## 5. Polygon With < 3 Vertices
- **Steps**: Draw a shape with only two distinct vertices (fails outer ring ≥4 coordinates) and submit.
- **Expected**: Error `Each polygon must contain at least three vertices.` from `assertMinimumVertices`.

## 6. Unsupported Geometry Type
- **Steps**: Paste a GeoJSON LineString into the hidden geometry field (via dev tools) and submit.
- **Expected**: Error `Only Polygon or MultiPolygon geometries are supported.`

## 7. Auto-Repair Invalid Geometry
- **Steps**: Provide a self-intersecting polygon that `ST_MakeValid` can fix.
- **Expected**: Submission succeeds; geometry stored as valid MultiPolygon in SRID 4326.

## 8. Polygon Auto-Multipolygon Conversion
- **Steps**: Submit a valid single Polygon GeoJSON.
- **Expected**: Stored geometry is MultiPolygon; viewing the zone renders the polygon correctly.

## 9. Duplicate Name Handling
- **Steps**: Create two zones with the same name but different geometries.
- **Expected**: Both records persist (no unique constraint), list shows duplicates.

## 10. Livewire UI Persistence
- **Steps**: Trigger a validation error (e.g., missing geometry).
- **Expected**: Previously entered name/category remain populated; map retains drawn shape.
