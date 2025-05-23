<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VinculacionResource extends JsonResource
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
            'nombre_vinculacion' => $this->nombre_vinculacion,
            'descripcion_vinculacion' => $this->descripcion_vinculacion,
            'empresa_mandante_id' => $this->empresa_mandante_id,
            'parent_id' => $this->parent_id,
            
            // Incluir información básica del mandante si está cargada
            'empresa_mandante' => $this->whenLoaded('empresaMandante', function () {
                return [
                    'id' => $this->empresaMandante->id,
                    'nombre_empresa_mandante' => $this->empresaMandante->nombre_empresa_mandante,
                ];
            }, null),

            // Incluir información básica del padre si está cargada y no es el mismo recurso (para evitar recursión infinita simple)
            'parent' => $this->whenLoaded('parent', function () {
                // Evitar cargar el parent si es el mismo recurso para prevenir bucles en representaciones simples.
                // Esto es más una precaución; la carga recursiva completa se maneja con 'children'.
                if ($this->parent && $this->parent->id === $this->id) {
                    return null; 
                }
                return $this->parent ? new VinculacionResource($this->parent) : null;
            }), // Usamos 'null' como tercer argumento para que no aparezca si no se carga 'parent' o es null.

            // Incluir hijos si están cargados
            // Usamos 'self::collection' para aplicar este mismo resource a los hijos.
            'children' => $this->whenLoaded('children', function () {
                return VinculacionResource::collection($this->resource->children);
            }),
            // Podrías añadir un contador de hijos si es útil y no siempre cargas la colección completa
            // 'children_count' => $this->whenCounted('children', $this->children_count),

            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'eliminado_en' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
        ];
    }
}