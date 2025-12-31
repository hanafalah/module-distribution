<?php

namespace Hanafalah\ModuleDistribution\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface Order extends Distribution
{
    public function prepareStoreOrder(?array $attributes = null): Model;
    public function prepareStoreOrderItems(mixed $attributes = null): Model;
    public function storeOrder(): array;
    public function getOrder(): ?Model;
    public function prepareShowOrder(?Model $model = null, ?array $attributes = null): Model;
    public function showOrder(?Model $model = null): array;
    public function prepareViewOrderPaginate(int $perPage = 50, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?int $total = null): LengthAwarePaginator;
    public function viewOrderPaginate(int $perPage = 50, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?int $total = null): array;
    public function prepareReportOrder(?array $attributes = null): Model;
    public function reportOrder(): array;
    public function prepareDeleteOrder(?array $attributes = null): bool;
    public function deleteOrder(): bool;
    public function order(mixed $conditionals = null): Builder;
}
