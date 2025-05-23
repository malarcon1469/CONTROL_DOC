<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaContratistaResource extends JsonResource
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
            'rut_empresa_contratista' => $this->rut_empresa_contratista,
            'nombre_empresa_contratista' => $this->nombre_empresa_contratista,
            'razon_social_contratista' => $this->razon_social_contratista,
            'direccion_contratista' => $this->direccion_contratista,
            'ciudad_contratista' => $this->ciudad_contratista,
            'telefono_contratista' => $this->telefono_contratista,
            'email_contratista' => $this->email_contratista,
            'nombre_representante_legal' => $this->nombre_representante_legal,
            'rut_representante_legal' => $this->rut_representante_legal,
            'email_representante_legal' => $this->email_representante_legal,
            'telefono_representante_legal' => $this->telefono_representante_legal,
            'activa' => (bool) $this->activa,
            'user_id' => $this->user_id,

            // Incluir información del usuario administrador si está cargada
            'usuario_administrador' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'roles' => $this->user->getRoleNames(), // Mostrar los roles del usuario
                ];
            }, null),

            // Incluir las condiciones de contratista maestro asignadas
            'condiciones_contratista' => $this->whenLoaded('condicionesContratistaMaestro', function () {
                // Usamos CondicionContratistaMaestroResource para formatear cada condición
                return CondicionContratistaMaestroResource::collection($this->condicionesContratistaMaestro);
            }),
            // Alternativamente, si no quieres usar el resource y solo quieres una lista simple:
            // 'condiciones_contratista_simple' => $this->whenLoaded('condicionesContratistaMaestro', function () {
            //     return $this->condicionesContratistaMaestro->map(function ($condicion) {
            //         return [
            //             'id' => $condicion->id,
            //             'nombre_condicion' => $condicion->nombre_condicion,
            //         ];
            //     });
            // }),


            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'eliminado_en' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
        ];
    }
}