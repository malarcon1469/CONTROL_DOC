<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TipoDocumentoResource extends JsonResource
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
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'es_vencible' => (bool) $this->es_vencible, // Aseguramos que sea booleano en el JSON
            'requiere_archivo' => (bool) $this->requiere_archivo, // Aseguramos que sea booleano
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            // 'deleted_at' => $this->when($this->deleted_at, fn() => $this->deleted_at ? $this->deleted_at->toIso8601String() : null), // Opcional: mostrar si está soft-deleted
        ];
    }
}