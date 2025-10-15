@extends ('adminsite.layout')
 
 @section('ContenidoSite-01')

<div class="content-header">
      <ul class="nav-horizontal text-center">
      <li class="active"> 
       <a href="/gestor/ver-templates"><i class="fa fa-desktop"></i> Ver templates</a>
      </li>
      <li>
       <a href="/gestion/logo-head"><i class="fa fa-arrow-circle-up"></i> Logo encabezado</a>
      </li>
      <li>
       <a href="/gestion/logo-footer"><i class="fa fa-arrow-circle-down"></i> Logo pie página</a>
      </li>
      <li>
       <a href="/gestion/whatsapp"><i class="fa fa-envelope"></i> Whatsapp</a>
      </li>
         <li>
       <a href="/gestion/redes-sociales"><i class="hi hi-bullhorn"></i> Redes sociales</a>
      </li>
          <li>
       <a href="/gestion/recaptcha"><i class="hi hi-bullhorn"></i> Recaptcha</a>
      </li>
      </li>
         <li>
       <a href="/gestion/ubicacion"><i class="gi gi-google_maps"></i></i> Ubicación</a>
      </li>
      </li>
         <li>
       <a href="/gestion/seo"><i class="gi gi-google_maps"></i></i>Seo</a>
      </li>
     </ul>
    </div>
<style>
    .template-card {
  border: none;
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.template-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.template-card img {
  height: 200px;
  object-fit: cover;
}

.selected-template {
    border: 3px solid #007bff; /* azul bootstrap */
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
}

</style>

 <div class="container">
  <?php $status=Session::get('status'); ?>
  @if($status=='ok_create')
   <div class="alert alert-success">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>>Template Registrado Con Éxito</strong> CMS...
   </div>
  @endif

  @if($status=='ok_delete')
   <div class="alert alert-danger">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Template Eliminado Con Éxito</strong> CMS...
   </div>
  @endif

  @if($status=='ok_update')
   <div class="alert alert-warning">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Template actualizado con éxito</strong> CMS...
   </div>
  @endif

 </div>

<div class="container my-4">
  @foreach($alltemplates->chunk(4) as $templateRow)
    <div class="row">
      @foreach($templateRow as $template)
        <div class="col-md-3 col-sm-6 mb-4" style="padding: 10px;">
          <div class="card h-100 template-card {{ $selected == $template->template ? 'selected-template' : '' }}">
            
            <!-- Imagen -->
            <img src="{{ $template->preview ?? $template->image }}" 
     alt="Preview {{ $template->template }}" 
     class="card-img-top">

            <!-- Cuerpo -->
            <div class="card-body d-flex flex-column justify-content-between">
              <h5 class="card-title text-center mb-3">{{ $template->template }}</h5>

              <div class="d-flex justify-content-between">
                <a 
                        href="/sd/templates/{{ $template->id }}/edit"
                        class="btn btn-success btn-sm w-100  btn-block"
                     >
                  <i class="fa fa-check"></i> Editar
                </a>
                <button type="button"
                        class="btn btn-primary btn-sm w-100 btn-select-template btn-block"
                        data-id="{{ $template->template }}">
                  <i class="fa fa-check"></i> Seleccionar
                </button>
                <a 
                        href="{{ $template->url }}"
                        class="btn btn-info btn-sm w-100  btn-block"
                     >
                  <i class="fa fa-check"></i> Ver template
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endforeach

  <!-- Paginador -->
  <div class="d-flex justify-content-center mt-4">
    {{ $alltemplates->links() }}
  </div>
</div>



<script>
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".btn-select-template");

    buttons.forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault(); // 👈 evita envío normal

            let templateId = this.getAttribute("data-id");

            fetch("{{ url('sd/update-template') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    template: templateId
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("HTTP error " + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("Respuesta del servidor:", data);

                if (data.status === "success") {
                    // Quitar resaltado de todas las tarjetas
                    document.querySelectorAll(".template-card").forEach(card => {
                        card.classList.remove("selected-template");
                    });

                    // Resaltar la tarjeta seleccionada
                    this.closest(".template-card").classList.add("selected-template");
                } else {
                    alert("Hubo un problema: " + data.message);
                }
            })
            .catch(error => console.error("Error de red o JS:", error));
        });
    });
});
</script>



<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>

    <script src="/adminsite/js/pages/tablesDatatables.js"></script>
        <script>$(function(){ TablesDatatables.init(); });</script>
  

  @stop