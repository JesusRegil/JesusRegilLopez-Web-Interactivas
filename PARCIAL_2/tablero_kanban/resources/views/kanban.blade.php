<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kanban</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0">Tablero Kanban</h1>
        <button id="btn-nueva" class="btn btn-primary">+ Nueva tarea</button>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-md-6"><input id="f-q" class="form-control" placeholder="Buscar por nombre..."></div>
        <div class="col-md-3">
            <select id="f-status" class="form-select">
                <option value="">Todos los estados</option>
                <option value="por_hacer">Por hacer</option>
                <option value="en_curso">En curso</option>
                <option value="hecha">Hecha</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="f-priority" class="form-select">
                <option value="">Todas las prioridades</option>
                <option value="alta">Alta</option>
                <option value="media">Media</option>
                <option value="baja">Baja</option>
            </select>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4"><div class="card"><div class="card-header fw-bold">Por hacer <span class="badge bg-secondary" id="n-por_hacer">0</span></div><div class="card-body" id="col-por_hacer"></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-header fw-bold">En curso <span class="badge bg-secondary" id="n-en_curso">0</span></div><div class="card-body" id="col-en_curso"></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-header fw-bold">Hechas <span class="badge bg-secondary" id="n-hecha">0</span></div><div class="card-body" id="col-hecha"></div></div></div>
    </div>
</div>

<div class="modal fade" id="modal" tabindex="-1">
    <div class="modal-dialog">
        <form id="form" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Nueva tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="t-id">
                <div class="mb-3"><label class="form-label">Título</label><input id="t-title" class="form-control" required maxlength="255"></div>
                <div class="mb-3"><label class="form-label">Descripción</label><textarea id="t-description" class="form-control" rows="3"></textarea></div>
                <div class="row">
                    <div class="col-6 mb-3"><label class="form-label">Estado</label>
                        <select id="t-status" class="form-select">
                            <option value="por_hacer">Por hacer</option>
                            <option value="en_curso">En curso</option>
                            <option value="hecha">Hecha</option>
                        </select>
                    </div>
                    <div class="col-6 mb-3"><label class="form-label">Prioridad</label>
                        <select id="t-priority" class="form-select">
                            <option value="alta">Alta</option>
                            <option value="media" selected>Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3"><label class="form-label">Vencimiento (opcional)</label><input type="date" id="t-due" class="form-control"></div>
                <div id="form-error" class="text-danger small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content'), 'Accept': 'application/json' } });

    var estados = { por_hacer: 'Por hacer', en_curso: 'En curso', hecha: 'Hecha' };
    var colores = { alta: 'danger', media: 'warning', baja: 'success' };
    var modal = new bootstrap.Modal('#modal');
    var tareas = {};

    function esc(s) { return $('<div>').text(s || '').html(); }

    function cargar() {
        $.get('/tasks', { q: $('#f-q').val(), status: $('#f-status').val(), priority: $('#f-priority').val() }, function (data) {
            tareas = {};
            $.each(estados, function (k) { $('#col-' + k).empty(); $('#n-' + k).text(0); });
            var cuenta = { por_hacer: 0, en_curso: 0, hecha: 0 };
            $.each(data, function (_, t) {
                tareas[t.id] = t;
                cuenta[t.status]++;
                var botones = '';
                $.each(estados, function (k, nombre) {
                    if (k !== t.status) botones += '<button class="btn btn-sm btn-outline-primary me-1 mover" data-id="' + t.id + '" data-to="' + k + '">&rarr; ' + nombre + '</button>';
                });
                var venc = t.due_date ? '<div class="small text-muted mt-1">Vence: ' + t.due_date + '</div>' : '';
                $('#col-' + t.status).append(
                    '<div class="card mb-2"><div class="card-body p-2">' +
                    '<div class="d-flex justify-content-between"><strong>' + esc(t.title) + '</strong>' +
                    '<span class="badge bg-' + colores[t.priority] + '">' + t.priority + '</span></div>' + venc +
                    '<div class="mt-2">' + botones + '</div>' +
                    '<div class="mt-2"><button class="btn btn-sm btn-outline-secondary me-1 editar" data-id="' + t.id + '">Editar</button>' +
                    '<button class="btn btn-sm btn-outline-danger eliminar" data-id="' + t.id + '">Eliminar</button></div>' +
                    '</div></div>');
            });
            $.each(cuenta, function (k, n) { $('#n-' + k).text(n); });
        });
    }

    $('#btn-nueva').on('click', function () {
        $('#form')[0].reset();
        $('#t-id').val('');
        $('#form-error').text('');
        $('#modal-title').text('Nueva tarea');
        modal.show();
    });

    $(document).on('click', '.editar', function () {
        var t = tareas[$(this).data('id')];
        $('#t-id').val(t.id);
        $('#t-title').val(t.title);
        $('#t-description').val(t.description);
        $('#t-status').val(t.status);
        $('#t-priority').val(t.priority);
        $('#t-due').val(t.due_date);
        $('#form-error').text('');
        $('#modal-title').text('Editar tarea');
        modal.show();
    });

    $('#form').on('submit', function (e) {
        e.preventDefault();
        var id = $('#t-id').val();
        $.ajax({
            url: id ? '/tasks/' + id : '/tasks',
            method: id ? 'PUT' : 'POST',
            data: {
                title: $('#t-title').val(),
                description: $('#t-description').val(),
                status: $('#t-status').val(),
                priority: $('#t-priority').val(),
                due_date: $('#t-due').val()
            },
            success: function () { modal.hide(); cargar(); },
            error: function (xhr) {
                var errs = xhr.responseJSON && xhr.responseJSON.errors;
                $('#form-error').text(errs ? Object.values(errs).join(' ') : 'Error al guardar');
            }
        });
    });

    $(document).on('click', '.mover', function () {
        $.ajax({ url: '/tasks/' + $(this).data('id'), method: 'PUT', data: { status: $(this).data('to') }, success: cargar });
    });

    $(document).on('click', '.eliminar', function () {
        if (confirm('¿Eliminar esta tarea?')) {
            $.ajax({ url: '/tasks/' + $(this).data('id'), method: 'DELETE', success: cargar });
        }
    });

    $('#f-q').on('input', cargar);
    $('#f-status, #f-priority').on('change', cargar);

    cargar();
});
</script>
</body>
</html>
