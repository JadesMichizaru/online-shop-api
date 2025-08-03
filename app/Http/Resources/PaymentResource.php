<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => [
                'id' => $this->orderItems->id,
                'user_id' => [
                    'id' => $this->orderItems->users->id,
                    'name' => $this->orderItems->users->name,
                ],
                'product_id' => [
                    'id' => $this->orderItems->products->id,
                    'name' => $this->orderItems->products->name,
                ],
                'quantity' => $this->orderItems->quantity
            ],
            'payment_method' => $this->payment_method,
            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'amount' => $this->amount,
            'status' => $this->status,
            'proof_url' => $this->proof_url,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
