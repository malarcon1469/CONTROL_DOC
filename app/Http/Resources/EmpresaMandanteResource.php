<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaMandanteResource extends JsonResource
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
            'rut_empresa_mandante' => $this->rut_empresa_mandante,
            'nombre_empresa_mandante' => $this->nombre_empresa_mandante,
            'razon_social_mandante' => $this->razon_social_mandante,
            'direccion_mandante' => $this->direccion_mandante,
            'ciudad_mandante' => $this->ciudad_mandante,
            'telefono_mandante' => $this->telefono_mandante,
            'email_mandante' => $this->email_mandante,
            'nombre_contacto_mandante' => $this->nombre_contacto_mandante,
            'email_contacto_mandante' => $this->email_contacto_mandante,
            'telefono_contacto_mandante' => $this->telefono_contacto_mandante,
            'activa' => (bool) $this->activa,
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            // Aquí podríamos cargar relaciones si fuera necesario y eficiente, por ejemplo:
            // 'cargos_count' => $this->whenLoaded('cargos', fn() => $this->cargos->count()),
            // 'vinculaciones_count' => $this->whenLoaded('vinculaciones', fn() => $this->vinculaciones->count()),
        ];
    }
}