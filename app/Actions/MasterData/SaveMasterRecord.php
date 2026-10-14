<?php

namespace App\Actions\MasterData;

use App\Actions\Audit\RecordAdminAudit;
use App\MasterData\MasterResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as ValidatorInstance;

class SaveMasterRecord
{
    public function __construct(private RecordAdminAudit $audit) {}

    /**
     * Validate and create or update one master record, auditing what changed.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function handle(User $admin, MasterResource $resource, array $input, ?Model $record = null): Model
    {
        $input = $resource->prepareForValidation($input, $record);

        $validated = Validator::make($input, $resource->rules($record), [], $resource->attributeLabels())
            ->after(fn (ValidatorInstance $validator) => $resource->after($validator, $input, $record))
            ->validate();

        $validated = $resource->beforeSave($validated);

        return DB::transaction(function () use ($admin, $resource, $validated, $record) {
            if ($record === null) {
                $created = $resource->model()::create($validated);
                $this->audit->handle($admin, 'master.created', $created, null, $created->only(array_keys($validated)));

                return $created;
            }

            $record->fill($validated);
            $changed = array_keys(array_diff_key($record->getDirty(), array_flip(['created_at', 'updated_at'])));

            if ($changed === []) {
                return $record;
            }

            $old = collect($changed)->mapWithKeys(fn (string $key) => [$key => $record->getOriginal($key)])->all();
            $record->save();
            $this->audit->handle($admin, 'master.updated', $record, $old, $record->only($changed));

            return $record;
        });
    }
}
