<?php

namespace Hanafalah\ModuleDistribution\Models\Distribution;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Hanafalah\LaravelHasProps\Concerns\HasProps;
use Hanafalah\LaravelSupport\Models\BaseModel;
use Hanafalah\ModuleDistribution\Enums\Distribution\Status;
use Hanafalah\ModuleDistribution\Resources\Distribution\{
    ViewDistribution,
    ShowDistribution
};
use Hanafalah\ModuleTransaction\Concerns\HasTransaction;

class Distribution extends BaseModel
{
    use HasUlids, HasProps, SoftDeletes, HasTransaction;

    public $incrementing  = false;
    protected $keyType    = 'string';
    protected $primaryKey = 'id';
    protected $list = [
        'id',
        'distribution_no',
        'order_no',
        'flag',
        'receiver_id',
        'receiver_type',
        'sender_id',
        'sender_type',
        'status',
        'ordered_at',
        'distributed_at'
    ];
    protected $show = [
        'author_sender_type',
        'author_sender_id',
        'author_receiver_type',
        'author_receiver_id'
    ];

    protected $casts = [
        'receiver_name'   => 'string',
        'sender_name'     => 'string',
        'ordered_at'      => 'date',
        'distributed_at'  => 'date',
        'distribution_no' => 'string',
        'order_no'        => 'string'
    ];

    public function getPropsQuery(): array
    {
        return [
            'receiver_name' => 'props->receiver_name',
            'sender_name'   => 'props->sender_name'
        ];
    }

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function ($query) {
            if (!isset($query->status)) $query->status = Status::DRAFT->value;
        });
        static::updating(function ($query) {
            if ($query->isDirty('ordered_at')) {
                if (!isset($query->order_no)) static::hasEncoding('ORDER_ITEM');
                if (!isset($query->status)) $query->status = Status::ORDERED->value;
            }
            if ($query->isDirty('distributed_at')) {
                if (!isset($query->distribution_no)) static::hasEncoding('DISTRIBUTION_ITEM');
                if (!isset($query->status)) $query->status = Status::DISTRIBUTED->value;
            }
        });
    }

    public function getShowResource()
    {
        return ShowDistribution::class;
    }

    public function getViewResource()
    {
        return ViewDistribution::class;
    }

    public function authorSender()
    {
        return $this->morphTo();
    }
    public function authorReceiver()
    {
        return $this->morphTo();
    }
    public function receiver()
    {
        return $this->morphTo();
    }
    public function sender()
    {
        return $this->morphTo();
    }
    public function cardStock()
    {
        return $this->hasOneThroughModel(
            'CardStock',
            'Transaction',
            'reference_id',
            $this->TransactionModel()->getForeignKey(),
            $this->getKeyName(),
            $this->TransactionModel()->getKeyName()
        )->where('reference_type', $this->getMorphClass());
    }

    public function cardStocks()
    {
        return $this->hasManyThroughModel(
            'CardStock',
            'Transaction',
            'reference_id',
            $this->TransactionModel()->getForeignKey(),
            $this->getKeyName(),
            $this->TransactionModel()->getKeyName()
        )->where('reference_type', $this->getMorphClass());
    }
    //END EIGER SECTION
}
