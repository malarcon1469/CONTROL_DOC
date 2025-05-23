<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CondicionContratistaMaestroResource extends JsonResource
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
            'nombre_condicion' => $this->nombre_condicion,
            'descripcion_condicion' => $this->descripcion_condicion,
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            // Ejemplo de cómo cargar una relación si es necesario y fue cargada:
            // 'cantidad_empresas_asociadas' => $this->whenLoaded('empresasContratistas', function () {
            //     return $this->empresasContratistas->count();
            // }),
        ];
    }
}