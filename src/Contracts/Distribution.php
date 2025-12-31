<?php

namespace Hanafalah\ModuleDistribution\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Hanafalah\LaravelSupport\Contracts\Supports\DataManagement;

interface Distribution extends DataManagement
{
    public function prepareStoreDistribution(?array $attributes = null): Model;
    public function prepareStoreDistributionItems(mixed $attributes = null): Model;
    public function storeDistribution(): array;
    public function getDistribution(): ?Model;
    public function showUsingRelation(): array;
    public function prepareShowDistribution(?Model $model = null, ?array $attributes = null): Model;
    public function showDistribution(?Model $model = null): array;
    public function prepareViewDistributionPaginate(int $perPage = 50, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?int $total = null): LengthAwarePaginator;
    public function viewDistributionPaginate(int $perPage = 50, array $columns = ['*'], string $pageName = 'page', ?int $page = null, ?int $total = null): array;
    public function prepareReportDistribution(?array $attributes = null): Model;
    public function reportDistribution(): array;
    public function prepareDeleteDistribution(?array $attributes = null): mixed;
    public function deleteDistribution(): mixed;
    public function distribution(mixed $conditionals = null): Builder;
}
