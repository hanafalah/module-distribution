<?php

namespace Hanafalah\ModuleDistribution\Schemas;

use Hanafalah\ModuleItem\Contracts\CardStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Hanafalah\LaravelSupport\Supports\PackageManagement;
use Hanafalah\ModuleDistribution\Contracts\Distribution as ContractsDistribution;
use Hanafalah\ModuleDistribution\Enums\Distribution\Flag;
use Hanafalah\ModuleDistribution\Enums\Distribution\Status;
use Hanafalah\ModuleDistribution\Resources\Distribution\ShowDistribution;
use Hanafalah\ModuleDistribution\Resources\Distribution\ViewDistribution;

class Distribution extends PackageManagement implements ContractsDistribution
{
    protected array $__guard   = [];
    protected array $__add     = [];
    protected string $__entity = 'Distribution';
    public $distribution_model;
    public $distribution_item_model;

    protected array $__resources = [
        'view' => ViewDistribution::class,
        'show' => ShowDistribution::class
    ];

    public function prepareStoreDistribution(?array $attributes = null): Model
    {
        $attributes ??= request()->all();
        if (isset($attributes['id'])) {
            $distribution = $this->DistributionModel()->findOrFail($attributes['id']);
            $attributes['flag'] = $distribution->flag;
            $attributes['receiver_id'] = $distribution->receiver_id;
            $attributes['receiver_type'] = $distribution->receiver_type;
            $attributes['sender_id'] = $distribution->sender_id;
            $attributes['sender_type'] = $distribution->sender_type;
            $attributes['author_receiver_id'] = $distribution->author_receiver_id;
            $attributes['author_receiver_type'] = $distribution->author_receiver_type;
        } else {
            $distribution = $this->DistributionModel()->firstOrCreate([
                'id' => $attributes['id'] ?? null
            ], [
                'flag'                 => $attributes['flag'] ?? Flag::DIRECT_DISTRIBUTION->value,
                'receiver_id'          => $attributes['receiver_id'],
                'receiver_type'        => $attributes['receiver_type'],
                'sender_id'            => $attributes['sender_id'],
                'sender_type'          => $attributes['sender_type'],
                'author_receiver_id'   => $attributes['author_receiver_id'] ?? null,
                'author_receiver_type' => $attributes['author_receiver_type'] ?? null
            ]);
        }

        if (isset($attributes['card_stocks']) && count($attributes['card_stocks']) > 0) {
            $transaction_id  = $distribution->transaction->getKey();
            $valid_direction = $distribution->flag == Flag::ORDER_DISTRIBUTION->value && $distribution->status == Status::DRAFT->value;

            if ($valid_direction) {
                $direction       = $this->MainMovementModel()::IN;
                $warehouse_id    = $attributes['receiver_id'];
                $warehouse_type  = $attributes['receiver_type'];
            } else {
                $direction       = $this->MainMovementModel()::OUT;
                $warehouse_id    = $attributes['sender_id'];
                $warehouse_type  = $attributes['sender_type'];
            }

            $attributes['direction'] = $direction;
            foreach ($attributes['card_stocks'] as $card_stock) {
                $card_stock['transaction_id'] = $transaction_id;
                $card_stock['direction']      = $direction;
                if ($direction == $this->MainMovementModel()::OUT) $card_stock['funding_id'] = $attributes['funding_id'] ?? null;
                $card_stock['warehouse_id']   = $warehouse_id;
                $card_stock['warehouse_type'] = $warehouse_type;
                $card_stock_model = $this->prepareStoreDistributionItems($card_stock);
            }
            $distribution->save();
        }

        return $this->distribution_model = $distribution;
    }

    public function prepareStoreDistributionItems(mixed $attributes = null): Model
    {
        $attributes ??= request()->all();

        if (!isset($attributes['transaction_id'])) {
            if (!isset($this->distribution_model)) {
                $distribution = $this->distribution_model;
            } else {
                $id = $attributes['distribution_id'] ?? null;
                if (!isset($id)) throw new \Exception('No distribution id provided', 422);
                $distribution = $this->DistributionModel()->find($id);
            }
            $attributes['transaction_id'] = $distribution->transaction->getKey();
        }
        $distribution_item = $this->schemaContract('card_stock')
            ->prepareStoreCardStock($attributes);
        return $this->distribution_item_model = $distribution_item;
    }

    public function storeDistribution(): array
    {
        return $this->transaction(function () {
            return $this->showDistribution($this->prepareStoreDistribution());
        });
    }

    public function getDistribution(): ?Model
    {
        return $this->distribution_model;
    }

    public function showUsingRelation(): array
    {
        return [
            'receiver',
            'sender',
            'authorSender',
            'authorReceiver',
            'transaction',
            'cardStocks' => function ($query) {
                $query->with(['stockMovements' => function ($query) {
                    $query->with([
                        'itemStock',
                        'childs' => function ($query) {
                            $query->with([
                                'itemStock',
                                'batchMovements.batch'
                            ]);
                        },
                        'batchMovements.batch'
                    ]);
                }]);
            }
        ];
    }

    public function prepareShowDistribution(?Model $model = null, ?array $attributes = null): Model
    {
        $attributes ??= request()->all();

        $model ??= $this->getDistribution();
        if (!isset($model)) {
            $id = $attributes['id'] ?? null;
            if (!isset($id)) throw new \Exception('No distribution id provided', 422);

            $model = $this->distribution()->with($this->showUsingRelation())->findOrFail($id);
        } else {
            $model->load($this->showUsingRelation());
        }
        return $this->distribution_model = $model;
    }

    public function showDistribution(?Model $model = null): array
    {
        return $this->transforming($this->__resources['show'], function () use ($model) {
            return $this->prepareShowDistribution($model);
        });
    }

    public function prepareViewDistributionPaginate(int $perPage = 50, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?int $total = null): LengthAwarePaginator
    {
        $paginate_options = compact('perPage', 'columns', 'pageName', 'page', 'total');
        return $this->distribution()->paginate(...$this->arrayValues($paginate_options))
            ->appends(request()->all());
    }

    public function viewDistributionPaginate(int $perPage = 50, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?int $total = null): array
    {
        $paginate_options = compact('perPage', 'columns', 'pageName', 'page', 'total');
        return $this->transforming($this->__resources['view'], function () use ($paginate_options) {
            return $this->prepareViewDistributionPaginate(...$this->arrayValues($paginate_options));
        });
    }

    public function prepareReportDistribution(?array $attributes = null): Model
    {
        $attributes ??= request()->all();
        if (!isset($attributes['id'])) throw new \Exception('No id provided', 422);

        $distribution = $this->DistributionModel()->find($attributes['id']);
        if (isset($distribution->distributed_at)) throw new \Exception('Distribution already reported', 422);
        $distribution->distributed_at = now();
        $distribution->status = Status::DISTRIBUTED->value;
        $distribution->save();

        $card_stocks = $distribution->cardStocks;
        //UPDATING STOCK
        if (isset($card_stocks) && count($card_stocks) > 0) {
            foreach ($card_stocks as $card_stock) {
                $card_stock->reported_at = now();
                $card_stock->save();
            }
        }

        return $distribution;
    }

    public function reportDistribution(): array
    {
        return $this->transaction(function () {
            return $this->showDistribution($this->prepareReportDistribution());
        });
    }

    public function prepareDeleteDistribution(?array $attributes = null): mixed
    {
        $attributes ??= request()->all();
        $id = $attributes['id'] ?? null;
        if (!isset($id)) throw new \Exception('No distribution id provided', 422);

        $distribution = $this->DistributionModel()->findOrFail($id);
        if ($distribution->flag == Flag::DIRECT_DISTRIBUTION->value) {
            return $distribution->delete();
        } else {
            $distribution->status = Status::CANCELED->value;
            $distribution->save();
            return $distribution;
        }
    }

    public function deleteDistribution(): mixed
    {
        return $this->transaction(function () {
            return $this->prepareDeleteDistribution();
        });
    }

    public function distribution(mixed $conditionals = null): Builder
    {
        $this->booting();
        return $this->DistributionModel()->conditionals($conditionals)
            ->withParameters()->with(['transaction', 'receiver', 'sender'])
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('flag', Flag::ORDER_DISTRIBUTION->value)
                        ->where('status', '<>', Status::DRAFT->value);
                })->orWhere('flag', Flag::DIRECT_DISTRIBUTION->value);
            });
    }
}
