<?php

namespace App\Actions\MasterData;

use App\Actions\Audit\RecordAdminAudit;
use App\MasterData\MasterResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Master records are never deleted; they are switched off (docs/logic/master-data.md).
 */
class ToggleMasterRecord
{
    public function __construct(private RecordAdminAudit $audit) {}

    public function handle(User $admin, MasterResource $resource, Model $record): Model
    {
        $attribute = $resource->activeAttribute();
        $previous = (bool) $record->getAttribute($attribute);

        $record->setAttribute($attribute, ! $previous)->save();

        $this->audit->handle($admin, 'master.toggled', $record, [$attribute => $previous], [$attribute => ! $previous]);

        return $record;
    }
}
