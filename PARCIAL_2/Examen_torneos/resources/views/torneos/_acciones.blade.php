{{-- Botón según el rol y el estado. Variables: $torneo, $yaInscrito --}}
@guest
    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Inicia sesión para inscribirte</a>
@else
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.torneos.inscritos', $torneo) }}" class="btn btn-outline-secondary btn-sm">Ver inscritos</a>
    @elseif($yaInscrito)
        <span class="badge bg-info text-dark me-1"><i class="bi bi-check2-circle"></i> Inscrito</span>
        @unless($torneo->haPasado())
            <form method="POST" action="{{ route('inscripciones.destroy', $torneo) }}" class="d-inline"
                  onsubmit="return confirm('¿Seguro que quieres cancelar tu inscripción?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">Cancelar inscripción</button>
            </form>
        @endunless
    @elseif($torneo->estaDisponible())
        <form method="POST" action="{{ route('inscripciones.store', $torneo) }}" class="d-inline">
            @csrf
            <button class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Inscribirme</button>
        </form>
    @else
        <button class="btn btn-secondary btn-sm" disabled>No disponible</button>
    @endif
@endguest