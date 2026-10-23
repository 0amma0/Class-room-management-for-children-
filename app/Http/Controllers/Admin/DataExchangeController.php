<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Classes;
use App\Models\Cluster;
use App\Models\Environment;
use App\Models\Region;
use App\Models\Student;
use App\Models\User;
use App\Support\GuruClassAccess;
use App\Support\XlsxWorkbook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataExchangeController extends Controller
{
    private const IMPORT_COLUMNS = [
        'region_code',
        'region_name',
        'cluster_code',
        'cluster_name',
        'environment_code',
        'environment_name',
        'class_code',
        'class_name',
        'student_code',
        'full_name',
        'nickname',
        'gender',
        'birth_date',
        'address',
        'parent_name',
        'parent_phone',
    ];

    public function export(Request $request, XlsxWorkbook $workbook): BinaryFileResponse
    {
        $filters = $request->validate([
            'data_type' => ['nullable', 'in:all,classes,students,admins'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'cluster_id' => ['nullable', 'integer', 'exists:clusters,id'],
            'environment_id' => ['nullable', 'integer', 'exists:environments,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
        ]);
        $dataType = $filters['data_type'] ?? 'all';
        $classQuery = Classes::query()
            ->where('status', 'active')
            ->with('environment.cluster.region')
            ->withCount(['students' => fn ($query) => $query->where('status', 'active')])
            ->when(isset($filters['region_id']), fn ($query) => $query->whereHas('environment.cluster', fn ($query) => $query->where('region_id', $filters['region_id'])))
            ->when(isset($filters['cluster_id']), fn ($query) => $query->whereHas('environment', fn ($query) => $query->where('cluster_id', $filters['cluster_id'])))
            ->when(isset($filters['environment_id']), fn ($query) => $query->where('environment_id', $filters['environment_id']))
            ->when(isset($filters['class_id']), fn ($query) => $query->whereKey($filters['class_id']));
        $classes = $classQuery->orderBy('environment_id')->orderBy('name')->get();
        $sheets = [];

        if (in_array($dataType, ['all', 'classes'], true)) {
            $classRows = [['Region', 'Cluster', 'Environment', 'Class', 'Class code', 'Active participants', 'Schedule']];
            foreach ($classes as $class) {
                $classRows[] = [
                    $class->environment?->cluster?->region?->name ?? '',
                    $class->environment?->cluster?->name ?? '',
                    $class->environment?->name ?? '',
                    $class->name,
                    $class->code,
                    $class->students_count,
                    $class->schedule ?? '',
                ];
            }
            $sheets[] = ['name' => 'Ringkasan kelas', 'rows' => $classRows];
        }

        if (in_array($dataType, ['all', 'students'], true)) {
            $students = Student::query()
                ->where('status', 'active')
                ->whereIn('class_id', $classes->modelKeys())
                ->with('class.environment.cluster.region')
                ->orderBy('full_name')
                ->get();
            $participantRows = [['Region code', 'Region', 'Cluster code', 'Cluster', 'Environment code', 'Environment', 'Class code', 'Class', 'Participant code', 'Full name', 'Nickname', 'Gender', 'Birth date', 'Address', 'Parent / guardian', 'Parent phone']];
            foreach ($students as $student) {
                $class = $student->class;
                $environment = $class?->environment;
                $cluster = $environment?->cluster;
                $region = $cluster?->region;
                $participantRows[] = [
                    $region?->code ?? '',
                    $region?->name ?? '',
                    $cluster?->code ?? '',
                    $cluster?->name ?? '',
                    $environment?->code ?? '',
                    $environment?->name ?? '',
                    $class?->code ?? '',
                    $class?->name ?? '',
                    $student->student_code ?? '',
                    $student->full_name,
                    $student->nickname ?? '',
                    $student->gender ?? '',
                    $student->birth_date?->format('Y-m-d') ?? '',
                    $student->address ?? '',
                    $student->parent_name ?? '',
                    $student->parent_phone ?? '',
                ];
            }
            $sheets[] = ['name' => 'Data peserta', 'rows' => $participantRows];
        }

        if (in_array($dataType, ['all', 'admins'], true)) {
            $adminRows = [['Name', 'Username', 'Email', 'Phone', 'Status']];
            foreach (User::query()->where('role', 'admin')->orderBy('name')->get(['name', 'username', 'email', 'phone', 'status']) as $admin) {
                $adminRows[] = [$admin->name, $admin->username, $admin->email, $admin->phone ?? '', $admin->status];
            }
            $sheets[] = ['name' => 'Data Admin', 'rows' => $adminRows];
        }

        return $this->downloadWorkbook($workbook, 'ekspor-data-'.now()->format('Ymd-His').'.xlsx', $sheets);
    }

    public function exportGuru(Request $request, XlsxWorkbook $workbook, GuruClassAccess $access): BinaryFileResponse
    {
        $data = $request->validate([
            'class_id' => ['nullable', 'integer'],
        ]);
        $classesQuery = $access->classesFor($request->user())
            ->with('environment.cluster.region')
            ->when(isset($data['class_id']), fn ($query) => $query->whereKey($data['class_id']));
        if (isset($data['class_id'])) {
            $classesQuery->firstOrFail();
        }
        $classes = $classesQuery->orderBy('name')->get();
        $students = Student::query()
            ->where('status', 'active')
            ->whereIn('class_id', $classes->modelKeys())
            ->with('class.environment.cluster.region')
            ->orderBy('full_name')
            ->get();
        $rows = [['Region', 'Cluster', 'Environment', 'Class', 'Participant code', 'Full name', 'Nickname', 'Gender', 'Birth date']];
        foreach ($students as $student) {
            $class = $student->class;
            $environment = $class?->environment;
            $cluster = $environment?->cluster;
            $rows[] = [
                $cluster?->region?->name ?? '',
                $cluster?->name ?? '',
                $environment?->name ?? '',
                $class?->name ?? '',
                $student->student_code ?? '',
                $student->full_name,
                $student->nickname ?? '',
                $student->gender ?? '',
                $student->birth_date?->format('Y-m-d') ?? '',
            ];
        }

        return $this->downloadWorkbook($workbook, 'data-kelas-guru-'.now()->format('Ymd-His').'.xlsx', [
            ['name' => 'Peserta kelas', 'rows' => $rows],
        ]);
    }

    public function template(XlsxWorkbook $workbook): BinaryFileResponse
    {
        return $this->downloadWorkbook($workbook, 'template-impor-peserta.xlsx', [
            ['name' => 'Data peserta', 'rows' => [self::IMPORT_COLUMNS]],
            ['name' => 'Petunjuk', 'rows' => [
                ['Kolom', 'Petunjuk'],
                ['Kode wilayah sampai kelas', 'Wajib unik dalam hierarki dan konsisten dengan data yang sudah ada.'],
                ['Kode peserta', 'Wajib unik; baris dengan kode yang sudah ada akan ditolak.'],
                ['Gender', 'Opsional; gunakan L atau P.'],
                ['Tanggal lahir', 'Opsional; format YYYY-MM-DD dan tidak boleh di masa depan.'],
            ]],
        ]);
    }

    public function import(Request $request, XlsxWorkbook $workbook): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ]);

        try {
            $sheetRows = $workbook->readFirstSheet($validated['file']->getRealPath());
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages(['file' => $exception->getMessage()]);
        }

        if ($sheetRows === []) {
            throw ValidationException::withMessages(['file' => 'Lembar Excel tidak memiliki baris header.']);
        }

        $headerValues = array_map('trim', array_values($sheetRows[0]['values']));
        $columnPositions = array_flip($headerValues);
        $missingColumns = array_diff(self::IMPORT_COLUMNS, array_keys($columnPositions));
        if ($missingColumns !== []) {
            throw ValidationException::withMessages([
                'file' => 'Header tidak lengkap. Unduh template dan lengkapi kolom: '.implode(', ', $missingColumns).'.',
            ]);
        }
        if (count($sheetRows) > 5001) {
            throw ValidationException::withMessages(['file' => 'Maksimal 5.000 baris data dapat diimpor sekaligus.']);
        }

        $rows = [];
        $errors = [];
        $seenCodes = [];
        foreach (array_slice($sheetRows, 1) as $sheetRow) {
            $data = [];
            foreach (self::IMPORT_COLUMNS as $column) {
                $value = trim($sheetRow['values'][$columnPositions[$column] + 1] ?? '');
                $data[$column] = $value === '' ? null : $value;
            }
            if (count(array_filter($data, fn (?string $value): bool => filled($value))) === 0) {
                continue;
            }

            $validator = Validator::make($data, [
                'region_code' => ['required', 'string', 'max:255'],
                'region_name' => ['required', 'string', 'max:255'],
                'cluster_code' => ['required', 'string', 'max:255'],
                'cluster_name' => ['required', 'string', 'max:255'],
                'environment_code' => ['required', 'string', 'max:255'],
                'environment_name' => ['required', 'string', 'max:255'],
                'class_code' => ['required', 'string', 'max:255'],
                'class_name' => ['required', 'string', 'max:255'],
                'student_code' => ['required', 'string', 'max:255'],
                'full_name' => ['required', 'string', 'max:255'],
                'nickname' => ['nullable', 'string', 'max:255'],
                'gender' => ['nullable', 'in:L,P'],
                'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
                'address' => ['nullable', 'string', 'max:5000'],
                'parent_name' => ['nullable', 'string', 'max:255'],
                'parent_phone' => ['nullable', 'string', 'max:255'],
            ]);
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = 'Baris '.$sheetRow['row'].': '.$message;
                }

                continue;
            }
            if (isset($seenCodes[$data['student_code']]) || Student::query()->where('student_code', $data['student_code'])->exists()) {
                $errors[] = 'Baris '.$sheetRow['row'].': kode peserta '.$data['student_code'].' sudah digunakan.';

                continue;
            }

            $seenCodes[$data['student_code']] = true;
            $rows[] = $data;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', array_slice($errors, 0, 20))]);
        }
        if ($rows === []) {
            throw ValidationException::withMessages(['file' => 'Tidak ada baris peserta valid untuk diimpor.']);
        }

        DB::transaction(function () use ($rows, $request): void {
            foreach ($rows as $data) {
                $region = Region::query()->firstOrCreate(
                    ['code' => $data['region_code']],
                    ['name' => $data['region_name'], 'is_active' => true, 'is_development' => false],
                );
                $this->ensureSameName($region->name, $data['region_name'], 'wilayah');

                $cluster = Cluster::query()->firstOrCreate(
                    ['region_id' => $region->id, 'code' => $data['cluster_code']],
                    ['name' => $data['cluster_name'], 'is_active' => true],
                );
                $this->ensureSameName($cluster->name, $data['cluster_name'], 'klaster');

                $environment = Environment::query()->firstOrCreate(
                    ['cluster_id' => $cluster->id, 'code' => $data['environment_code']],
                    ['name' => $data['environment_name'], 'is_active' => true],
                );
                $this->ensureSameName($environment->name, $data['environment_name'], 'lingkungan');

                $class = Classes::query()->firstOrCreate(
                    ['code' => $data['class_code']],
                    ['environment_id' => $environment->id, 'name' => $data['class_name'], 'status' => 'active'],
                );
                if ((int) $class->environment_id !== (int) $environment->id || $class->name !== $data['class_name']) {
                    throw ValidationException::withMessages(['file' => 'Kode kelas '.$data['class_code'].' sudah terdaftar pada wilayah atau nama yang berbeda.']);
                }

                Student::create([
                    'class_id' => $class->id,
                    'student_code' => $data['student_code'],
                    'full_name' => $data['full_name'],
                    'nickname' => $data['nickname'] ?: null,
                    'gender' => $data['gender'] ?: null,
                    'birth_date' => $data['birth_date'] ?: null,
                    'address' => $data['address'] ?: null,
                    'parent_name' => $data['parent_name'] ?: null,
                    'parent_phone' => $data['parent_phone'] ?: null,
                    'status' => 'active',
                ]);
            }

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'imported',
                'entity_type' => 'students',
                'meta' => ['count' => count($rows)],
                'ip' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.students')->with('success', count($rows).' data peserta berhasil diimpor.');
    }

    /**
     * @param  list<array{name: string, rows: list<list<int|float|string|null>>}>  $sheets
     */
    private function downloadWorkbook(XlsxWorkbook $workbook, string $filename, array $sheets): BinaryFileResponse
    {
        $path = $workbook->create($sheets);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend(true);
    }

    private function ensureSameName(string $existingName, string $importedName, string $type): void
    {
        if ($existingName !== $importedName) {
            throw ValidationException::withMessages(['file' => "Kode {$type} sudah digunakan untuk nama yang berbeda."]);
        }
    }
}
