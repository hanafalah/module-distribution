<?php

namespace Hanafalah\ModuleDistribution\Schemas;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Hanafalah\ModuleDistribution\Contracts\Order as ContractsOrder;
use Hanafalah\ModuleDistribution\Enums\Distribution\Flag;
use Hanafalah\ModuleDistribution\Enums\Distribution\Status;

class Order extends Distribution implements ContractsOrder
{
    protected string $__entity = 'Order';
    public $order_model;
    public $order_item_model;

    public function prepareStoreOrder(?array $attributes = null): Model
    {
        $attributes ??= request()->all();
        $attributes['flag'] = Flag::ORDER_DISTRIBUTION->value;

        $order = parent::prepareStoreDistribution($attributes);
        return $this->order_model = $order;
    }

    public function prepareStoreOrderItems(mixed $attributes = null): Model
    {
        $attributes ??= request()->all();
        $order_item = $this->prepareStoreDistributionItems($attributes);
        return $this->order_item_model = $order_item;
    }

    public function storeOrder(): array
    {
        return $this->transaction(function () {
            return $this->showOrder($this->prepareStoreOrder());
        });
    }

    public function getOrder(): ?Model
    {
        return $this->order_model;
    }

    public function prepareShowOrder(?Model $model = null, ?array $attributes = null): Model
    {
        $attributes ??= request()->all();

        $model ??= $this->getOrder();
        if (!isset($model)) {
            $id = $attributes['id'] ?? null;
            if (!isset($id)) throw new \Exception('No distribution id provided', 422);

            $model = $this->order()->with($this->showUsingRelation())->findOrFail($id);
        } else {
            $model->load($this->showUsingRelation());
        }
        return $this->order_model = $model;
    }

    public function showOrder(?Model $model = null): array
    {
        return $this->transforming($this->__resources['show'], function () use ($model) {
            return $this->prepareShowOrder($model);
        });
    }

    public function prepareViewOrderPaginate(int $perPage = 50, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?int $total = null): LengthAwarePaginator
    {
        $paginate_options = compact('perPage', 'columns', 'pageName', 'page', 'total');
        return $this->order()->paginate(...$this->arrayValues($paginate_options))
            ->appends(request()->all());
    }

    public function viewOrderPaginate(int $perPage = 50, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?int $total = null): array
    {
        $paginate_options = compact('perPage', 'columns', 'pageName', 'page', 'total');
        return $this->transforming($this->__resources['view'], function () use ($paginate_options) {
            return $this->prepareViewOrderPaginate(...$this->arrayValues($paginate_options));
        });
    }

    public function prepareReportOrder(?array $attributes = null): Model
    {
        $attributes ??= request()->all();
        if (!isset($attributes['id'])) throw new \Exception('No id provided', 422);

        $order = $this->OrderModel()->find($attributes['id']);
        if (isset($order->ordered_at)) throw new \Exception('Order already reported', 422);
        $order->ordered_at = now();
        $order->status = Status::ORDERED->value;
        $order->save();
        return $order;
    }

    public function reportOrder(): array
    {
        return $this->transaction(function () {
            return $this->showOrder($this->prepareReportOrder());
        });
    }

    public function prepareDeleteOrder(?array $attributes = null): bool
    {
        $attributes ??= request()->all();
        $id = $attributes['id'] ?? null;
        if (!isset($id)) throw new \Exception('No order id provided', 422);

        $order = $this->OrderModel()->findOrFail($id);
        if (isset($order->ordered_at)) throw new \Exception('Order already reported', 422);
        return $order->delete();
    }

    public function deleteOrder(): bool
    {
        return $this->transaction(function () {
            return $this->prepareDeleteOrder();
        });
    }

    public function order(mixed $conditionals = null): Builder
    {
        $this->booting();
        return $this->OrderModel()->conditionals($conditionals)
            ->withParameters()->with(['transaction', 'receiver', 'sender']);
    }
}
