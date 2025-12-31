<?php

namespace Hanafalah\ModuleDistribution\Resources\Distribution;

use Hanafalah\LaravelSupport\Resources\ApiResource;
use Hanafalah\ModulePeople\Resources\People\ViewPeople;

class ViewDistribution extends ApiResource
{
    public function toArray(\Illuminate\Http\Request $request): array
    {
        $arr = [
            'id'       => $this->id,
            'receiver' => $this->relationValidation('receiver', function () {
                return $this->receiver->toViewApi()->resolve();
            }),
            'sender' => $this->relationValidation('sender', function () {
                return $this->sender->toViewApi()->resolve();
            }),
            'transaction' => $this->relationValidation('transaction', function () {
                return $this->transaction->toViewApi()->resolve();
            }),
            'flag' => $this->flag,
            'order_no' => $this->order_no,
            'distribution_no' => $this->distribution_no,
            'distributed_at' => $this->distributed_at,
            'ordered_at' => $this->ordered_at
        ];

        return $arr;
    }
}
