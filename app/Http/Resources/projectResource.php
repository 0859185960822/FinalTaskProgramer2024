<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class projectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sisa_waktu = Carbon::now()->diffInDays(Carbon::parse($this->end_date), false);
        return [
            'project_id' => $this->project_id,
            'project_name' => $this->project_name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'pm_id' => Auth::user()->user_id,
            'sisa_waktu' => $sisa_waktu,
        ];
    }
}
