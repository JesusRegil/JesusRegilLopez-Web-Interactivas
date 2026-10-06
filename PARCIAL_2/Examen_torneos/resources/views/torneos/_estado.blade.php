@php $estado = $torneo->estadoReal(); @endphp
@switch($estado)
    @case('abierto') <span class="badge bg-success"><i class="bi bi-unlock-fill"></i> Abierto</span> @break
    @case('lleno') <span class="badge bg-warning text-dark"><i class="bi bi-people-fill"></i> Lleno</span> @break
    @case('cerrado') <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> Cerrado</span> @break
    @default <span class="badge bg-dark"><i class="bi bi-flag-fill"></i> Finalizado</span>
@endswitch