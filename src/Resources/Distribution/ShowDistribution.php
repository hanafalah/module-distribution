<?php

namespace Hanafalah\ModuleDistribution\Resources\Distribution;

use Hanafalah\ModulePeople\Resources\People\ShowPeople;

class ShowDistribution extends ViewDistribution
{

    public function toArray(\Illuminate\Http\Request $request): array
    {
        $arr = [
            'author_receiver' => $this->relationValidation('authorReceiver', function () {
                return $this->authorReceiver->toViewApi()->resolve();
            }),
            'author_sender' => $this->relationValidation('authorSender', function () {
                return $this->authorSender->toViewApi()->resolve();
            }),
            'card_stocks' => $this->relationValidation('cardStocks', function () {
                return $this->cardStocks->transform(function ($cardStock) {
                    return $cardStock->toShowApi()->resolve();
                });
            })
        ];

        $arr = array_merge(parent::toArray($request), $arr);

        return $arr;
    }
}
