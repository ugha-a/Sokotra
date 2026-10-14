<?php

namespace App\Http\Controllers\Admin;

use App\Actions\MasterData\SaveMasterRecord;
use App\Actions\MasterData\ToggleMasterRecord;
use App\Http\Controllers\Controller;
use App\MasterData\MasterRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function __construct(private MasterRegistry $registry) {}

    public function index(string $resource): Response
    {
        $master = $this->registry->findOrFail($resource);

        return Inertia::render('admin/master/index', [
            'resource' => $master->toArray(),
            'resources' => $this->registry->summaries(),
            'records' => $master->query()->get()->map(fn (Model $record) => $master->toRecord($record))->all(),
        ]);
    }

    public function store(Request $request, string $resource, SaveMasterRecord $save): RedirectResponse
    {
        $master = $this->registry->findOrFail($resource);

        $save->handle($request->user(), $master, $request->all());

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$master->label()} berhasil ditambahkan."]);

        return to_route('admin.master.index', $resource);
    }

    public function update(Request $request, string $resource, int $record, SaveMasterRecord $save): RedirectResponse
    {
        $master = $this->registry->findOrFail($resource);

        $save->handle($request->user(), $master, $request->all(), $master->model()::findOrFail($record));

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$master->label()} berhasil diperbarui."]);

        return to_route('admin.master.index', $resource);
    }

    public function toggle(Request $request, string $resource, int $record, ToggleMasterRecord $toggle): RedirectResponse
    {
        $master = $this->registry->findOrFail($resource);

        $toggle->handle($request->user(), $master, $master->model()::findOrFail($record));

        return to_route('admin.master.index', $resource);
    }
}
