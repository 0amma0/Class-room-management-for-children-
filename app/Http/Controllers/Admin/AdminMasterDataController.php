<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminMasterDataController extends Controller
{
    public function store(Request $request, string $resource): RedirectResponse
    {
        $model = $this->modelFor($resource);
        $data = $this->validatedData($request, $resource);
        if ($resource === 'classes' && $request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('class-photos', 'public');
        }
        unset($data['photo']);

        $record = DB::transaction(function () use ($request, $model, $data, $resource): Model {
            $record = $model->newQuery()->create($data);
            $this->writeAuditLog($request, 'created', $resource, $record->getKey());

            return $record;
        });

        return redirect()->route('admin.'.$resource)->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $model = $this->modelFor($resource);
        $record = $model->newQuery()->findOrFail($id);
        $data = $this->validatedData($request, $resource, $id);
        $previousPhoto = $resource === 'classes' ? $record->photo_path : null;
        if ($resource === 'classes' && $request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('class-photos', 'public');
        }
        unset($data['photo']);

        DB::transaction(function () use ($request, $record, $data, $resource): void {
            $record->update($data);
            $this->writeAuditLog($request, 'updated', $resource, $record->getKey());
        });
        if (isset($data['photo_path']) && $previousPhoto !== null && $previousPhoto !== $data['photo_path']) {
            Storage::disk('public')->delete($previousPhoto);
        }

        return redirect()->route('admin.'.$resource)->with('success', 'Data berhasil diperbarui.');
    }

    public function deactivate(Request $request, string $resource, int $id): RedirectResponse
    {
        $record = $this->modelFor($resource)->newQuery()->findOrFail($id);
        $status = $resource === 'classes' ? 'inactive' : null;

        DB::transaction(function () use ($request, $record, $resource, $status): void {
            $record->update($status === null ? ['is_active' => false] : ['status' => $status]);
            $this->writeAuditLog($request, 'deactivated', $resource, $record->getKey());
        });

        return redirect()->route('admin.'.$resource)->with('success', 'Data berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, string $resource, int $id): RedirectResponse
    {
        $record = $this->modelFor($resource)->newQuery()->findOrFail($id);
        $hasDependents = match ($resource) {
            'regions' => DB::table('clusters')->where('region_id', $id)->exists(),
            'clusters' => DB::table('environments')->where('cluster_id', $id)->exists()
                || DB::table('activities')->where('cluster_id', $id)->exists(),
            'environments' => DB::table('classes')->where('environment_id', $id)->exists()
                || DB::table('activities')->where('environment_id', $id)->exists(),
            'classes' => DB::table('students')->where('class_id', $id)->exists()
                || DB::table('activities')->where('class_id', $id)->exists()
                || DB::table('attendances')->where('class_id', $id)->exists()
                || DB::table('class_curriculum_progress')->where('class_id', $id)->exists()
                || DB::table('class_teachers')->where('class_id', $id)->exists()
                || DB::table('lesson_plans')->where('class_id', $id)->exists()
                || DB::table('parent_questions')->where('class_id', $id)->exists()
                || DB::table('parent_visits')->where('class_id', $id)->exists()
                || DB::table('teacher_reflections')->where('class_id', $id)->exists(),
        };

        if ($hasDependents) {
            throw ValidationException::withMessages([
                'delete' => 'Data tidak dapat dihapus permanen karena masih memiliki data terkait. Nonaktifkan data untuk mempertahankan riwayat.',
            ]);
        }

        $photoPath = $resource === 'classes' ? $record->photo_path : null;

        DB::transaction(function () use ($request, $record, $resource): void {
            $this->writeAuditLog($request, 'deleted', $resource, $record->getKey());
            $record->delete();
        });

        if ($photoPath !== null) {
            Storage::disk('public')->delete($photoPath);
        }

        return redirect()->route('admin.'.$resource)->with('success', 'Data berhasil dihapus permanen.');
    }

    private function modelFor(string $resource): Model
    {
        $class = match ($resource) {
            'regions' => Region::class,
            'clusters' => Cluster::class,
            'environments' => Environment::class,
            'classes' => Classes::class,
            default => abort(404),
        };

        return new $class;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, string $resource, ?int $id = null): array
    {
        $uniqueCode = match ($resource) {
            'regions' => Rule::unique('regions', 'code')->ignore($id),
            'clusters' => Rule::unique('clusters', 'code')
                ->where('region_id', $request->integer('region_id'))
                ->ignore($id),
            'environments' => Rule::unique('environments', 'code')
                ->where('cluster_id', $request->integer('cluster_id'))
                ->ignore($id),
            'classes' => Rule::unique('classes', 'code')->ignore($id),
        };

        return $request->validate(match ($resource) {
            'regions' => [
                'name' => ['required', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:255', $uniqueCode],
                'description' => ['nullable', 'string', 'max:5000'],
                'is_active' => ['sometimes', 'boolean'],
                'is_development' => ['sometimes', 'boolean'],
            ],
            'clusters' => [
                'region_id' => ['required', 'integer', 'exists:regions,id'],
                'name' => ['required', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:255', $uniqueCode],
                'description' => ['nullable', 'string', 'max:5000'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            'environments' => [
                'cluster_id' => ['required', 'integer', 'exists:clusters,id'],
                'name' => ['required', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:255', $uniqueCode],
                'description' => ['nullable', 'string', 'max:5000'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            'classes' => [
                'environment_id' => ['required', 'integer', 'exists:environments,id'],
                'grade_id' => ['nullable', 'integer', 'exists:grades,id'],
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:255', $uniqueCode],
                'location' => ['nullable', 'string', 'max:255'],
                'schedule' => ['nullable', 'string', 'max:255'],
                'photo' => ['nullable', 'image', 'max:5120'],
                'status' => ['required', Rule::in(['active', 'inactive', 'archived'])],
            ],
        });
    }

    private function writeAuditLog(Request $request, string $action, string $resource, int|string $id): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => $resource,
            'entity_id' => $id,
            'ip' => $request->ip(),
        ]);
    }
}
