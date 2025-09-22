@extends('adminsite.layout')

@section('cabecera')
    @parent
    <!-- CSS adicional si es necesario -->
@stop

@section('ContenidoSite-01')
<div class="content-header">
    <ul class="nav-horizontal text-center">
        <li class="active">
            <a href="/sd/pages"><i class="fa fa-file-text"></i> Ver páginas</a>
        </li>
        <li>
            <a href="/sd/create-page"><i class="fa fa-file-o"></i> Crear página</a>
        </li>
    </ul>
</div>

@if(Session::has('status'))
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 topper">
    @if(Session::get('status') == 'ok_delete')
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong><i class="icon fa fa-check"></i> Éxito:</strong> {{ Session::get('message') }}
    </div>
    @endif
    
    @if(Session::get('status') == 'error')
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong><i class="icon fa fa-ban"></i> Error:</strong> {{ Session::get('message') }}
    </div>
    @endif
</div>
@endif

<div class="container">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading"><h3>Páginas</h3></div>
            <div class="panel-body">
                <table class="table table-condensed" style="border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>Página</th>
                            <th>Título</th>
                            <th>Visibilidad</th>
                            <th>Creación</th>
                            <th>Actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menu as $page)
                        <tr data-toggle="collapse" data-target="#page-{{$page->id}}" class="accordion-toggle">
                            <td>
                                <button class="btn btn-{{ count($page->subpaginas) > 0 ? 'success' : 'default' }} btn-xs">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </td>
                            <td>{{ $page->page }}</td>
                            <td>{{ $page->title }}</td>
                            <td>{{ $page->visibility == '1' ? 'Publicada' : 'No Publicada' }}</td>
                            <td>{{ $page->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $page->updated_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="/sd/editor?load={{$page->id}}" class="btn btn-warning btn-sm" title="Ver contenidos">
                                        <i class="gi gi-imac"></i>
                                    </a>
                                    <a href="/sd/create-subpage/{{ $page->id }}" class="btn btn-primary btn-sm" title="Crear subpágina">
                                        <i class="fa fa-files-o"></i>
                                    </a>
                                    <a href="/sd/pages/{{ $page->id }}/edit" class="btn btn-info btn-sm" title="Editar página">
                                        <i class="fa fa-pencil-square-o"></i>
                                    </a>
                                    <form action="{{ route('pages.destroy', $page->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar página" onclick="return confirm('¿Estás seguro de eliminar esta página?')">
                                            <i class="hi hi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        
                        @if(count($page->subpaginas) > 0)
                        <tr>
                            <td colspan="7" class="hiddenRow">
                                <div class="accordian-body collapse" id="page-{{$page->id}}">
                                    <table class="table table-striped">
                                        <tbody>
                                            @foreach($page->subpaginas->take(50) as $subpage)
                                            <tr>
                                                <td><button class="btn btn-default btn-xs"><i class="fa fa-check-square-o"></i></button></td>
                                                <td>{{ $subpage->page }}</td>
                                                <td>{{ $subpage->title }}</td>
                                                <td>{{ $subpage->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $subpage->updated_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="/sd/editor?load={{$subpage->id}}" class="btn btn-warning btn-sm" title="Ver contenidos">
                                                            <i class="gi gi-imac"></i>
                                                        </a>
                                                        <a href="/sd/pages/{{ $subpage->id }}/edit" class="btn btn-info btn-sm" title="Editar página">
                                                            <i class="fa fa-pencil-square-o"></i>
                                                        </a>
                                                        <form action="{{ route('pages.destroy', $subpage->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar página" onclick="return confirm('¿Estás seguro de eliminar esta página?')">
                                                                <i class="hi hi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
@stop

@section('scripts')
@parent

<script src="//cdn.datatables.net/1.10.1/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/plug-ins/be7019ee387/integration/bootstrap/3/dataTables.bootstrap.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar DataTables
        $('.table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
            }
        });
        
        // Tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Confirmación para eliminar
        $('.delete-btn').click(function(e) {
            if(!confirm('¿Estás seguro de eliminar esta página?')) {
                e.preventDefault();
            }
        });
    });
</script>
@stop











