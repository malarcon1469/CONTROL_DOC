<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CargoResource extends JsonResource
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
            'nombre_cargo' => $this->nombre_cargo,
            'descripcion_cargo' => $this->descripcion_cargo,
            'empresa_mandante_id' => $this->empresa_mandante_id,
            // Incluir información básica del mandante si está cargada
            'empresa_mandante' => $this->whenLoaded('empresaMandante', function () {
                return [
                    'id' => $this->empresaMandante->id,
                    'nombre_empresa_mandante' => $this->empresaMandante->nombre_empresa_mandante,
                ];
            }, null), // Devuelve null si la relación no fue cargada
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}