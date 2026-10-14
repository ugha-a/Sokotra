<?php

namespace App\MasterData;

use App\MasterData\Resources\BankResource;
use App\MasterData\Resources\BrokerResource;
use App\MasterData\Resources\ContentPageResource;
use App\MasterData\Resources\FollowerBracketResource;
use App\MasterData\Resources\InstrumentResource;
use App\MasterData\Resources\PayrollRuleResource;
use App\MasterData\Resources\ProductCategoryResource;
use App\MasterData\Resources\RankResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MasterRegistry
{
    /** @var list<class-string<MasterResource>> Order = admin menu order. */
    private const RESOURCES = [
        InstrumentResource::class,
        RankResource::class,
        PayrollRuleResource::class,
        FollowerBracketResource::class,
        BrokerResource::class,
        ProductCategoryResource::class,
        BankResource::class,
        ContentPageResource::class,
    ];

    /**
     * @return list<MasterResource>
     */
    public function all(): array
    {
        return array_map(fn (string $class) => app($class), self::RESOURCES);
    }

    public function findOrFail(string $key): MasterResource
    {
        foreach ($this->all() as $resource) {
            if ($resource->key() === $key) {
                return $resource;
            }
        }

        throw new NotFoundHttpException("Unknown master resource [{$key}].");
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function summaries(): array
    {
        return array_map(
            fn (MasterResource $resource) => ['key' => $resource->key(), 'label' => $resource->label()],
            $this->all(),
        );
    }
}
