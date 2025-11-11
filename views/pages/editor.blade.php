<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ isset($templateId) ? 'Editar' : 'Nueva' }} Plantilla</title>
  
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <!-- GrapesJS CSS -->
  <link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <style>
    body, html {
      margin: 0;
      height: 100%;
      font-family: Arial, sans-serif;
      overflow: hidden;
    }
    .panel__top {
      padding: 10px 20px;
      background: #f5f5f5;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .panel__actions {
      display: flex;
      gap: 10px;
    }
    .action-btn {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.3s;
      min-width: 100px;
      text-align: center;
    }
    .save-btn {
      background: #4CAF50;
      color: white;
    }
    .preview-btn {
      background: #2196F3;
      color: white;
    }
    .action-btn:hover {
      opacity: 0.9;
      transform: translateY(-2px);
    }
    .action-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }
    #gjs {
      height: calc(100vh - 60px);
    }
    .template-info {
      font-weight: bold;
      color: #333;
    }
    .status-message {
      padding: 5px 10px;
      border-radius: 4px;
      margin-left: 15px;
      font-size: 0.9em;
    }
    .status-saving {
      background: #FFF3CD;
      color: #856404;
    }
    .status-success {
      background: #D4EDDA;
      color: #155724;
    }
    .status-error {
      background: #F8D7DA;
      color: #721C24;
    }
    
    /* Estilos para bloques Bootstrap */
    .gjs-block.bootstrap-container::before { content: "📦"; }
    .gjs-block.bootstrap-row::before { content: "↔️"; }
    .gjs-block.bootstrap-col::before { content: "📏"; }
    .gjs-block.bootstrap-button::before { content: "🔘"; }
    .gjs-block.bootstrap-card::before { content: "🃏"; }
    .gjs-block.bootstrap-alert::before { content: "⚠️"; }
    .gjs-block.bootstrap-navbar::before { content: "🧭"; }
    .gjs-block.bootstrap-accordion::before { content: "🗂️"; }
    .gjs-block.bootstrap-modal::before { content: "🪟"; }
    
    /* Categoría Bootstrap */
    .gjs-block-category-bootstrap {
      background-color: #7952b3;
      color: white;
      border-color: #5a3d8a !important;
    }

    .logo-slider-container {
      position: relative;
      padding: 20px 0;
    }

    .logo-slider {
      display: flex;
      overflow: hidden;
      gap: 20px;
      scroll-behavior: smooth;
    }

    .logo-slider img {
      height: auto;
      object-fit: contain;
      flex: 0 0 calc(100% / var(--visible-logos, 6));
      max-width: calc(100% / var(--visible-logos, 6));
    }

    .slider-controls {
      position: absolute;
      top: 50%;
      width: 100%;
      display: flex;
      justify-content: space-between;
      transform: translateY(-50%);
    }

    .slider-controls button {
      background: rgba(0,0,0,0.5);
      color: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      cursor: pointer;
      z-index: 10;
    }

    .slider-dots {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 15px;
    }

    .slider-dots .dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #ccc;
      cursor: pointer;
    }

    .slider-dots .dot.active {
      background: #333;
    }

    /* Responsive */
    @media (max-width: 992px) {
      .logo-slider img {
        flex: 0 0 calc(100% / var(--visible-logos-md, 4));
        max-width: calc(100% / var(--visible-logos-md, 4));
      }
    }

    @media (max-width: 768px) {
      .logo-slider img {
        flex: 0 0 calc(100% / var(--visible-logos-sm, 2));
        max-width: calc(100% / var(--visible-logos-sm, 2));
      }
    }

    /* Estilos para el modal de código */
    .code-textarea {
      font-family: 'Courier New', monospace;
      font-size: 14px;
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
    }

    .nav-tabs .nav-link.active {
      background-color: #f8f9fa;
      border-bottom-color: #f8f9fa;
    }
  </style>
</head>
<body>
  <div class="panel__top">
    <div class="template-info">
      @if(isset($templateId))
        Editando Plantilla ID: {{ $templateId }}
      @else
        Nueva Plantilla
      @endif
      <span id="status-message" class="status-message" style="display: none;"></span>
    </div>
    
    <div class="panel__actions">
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#formModal">
        Abrir Formulario
      </button>
       <button id="icon-selector-btn" class="btn btn-info btn-sm">
        <i class="bi bi-icons"></i> Selector de Iconos
    </button>
      <button id="preview-btn" class="action-btn preview-btn btn-sm" disabled>
        Vista Previa
      </button>
      <button id="view-code-btn" class="btn btn-info btn-sm">📝 Ver Código</button>
      <button id="save-btn" class="action-btn save-btn btn-sm">
        {{ isset($templateId) ? 'Actualizar' : 'Guardar' }}
      </button>
      <button id="clear-btn" class="btn btn-danger btn-sm">🗑 Limpiar Todo</button>
      <button id="save-component-btn" class="btn btn-primary btn-sm">💾 Guardar Componente</button>
      
      <!-- Botones de código agregados -->
      
      <button id="edit-html-btn" style="visibility: hidden;" class="btn btn-warning btn-sm" disabled>⚡ Editar HTML</button>
    </div>
  </div>

  <div id="gjs">
    @if(!isset($templateId))
      <!-- Contenido inicial solo para nuevas plantillas -->
      <div class="container" style="padding: 50px 0; text-align: center;">
        <h1>Comienza a diseñar tu plantilla</h1>
        <p>Arrastra componentes desde el panel derecho</p>
      </div>
    @endif

    <div class="container" style="padding: 1110px 0; text-align: center;">
      dff
    </div>
  </div>

  <div id="selected-image-url-display" style="
    margin-top: 15px;
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background-color: #f8f8f8;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    display: none;
    font-family: Arial, sans-serif;
    color: #333;
    word-break: break-all;
">
    <strong style="color: #555;">URL de la imagen seleccionada:</strong>
    <span id="current-image-url" style="
        background-color: #eee;
        padding: 5px 8px;
        border-radius: 4px;
        display: inline-block;
        margin-top: 8px;
        font-size: 0.9em;
        color: #007bff;
    "></span>
    <button id="copy-image-url" style="
        margin-left: 15px;
        padding: 8px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9em;
        transition: background-color 0.2s ease;
    ">Copiar URL</button>
  </div>

  <style>
    #copy-image-url:hover {
        background-color: #0056b3;
    }
  </style>

  <div id="components-panel">
    <h3>Componentes Personalizados</h3>
    <div id="custom-components"></div>
  </div>

  <!-- Scripts CDN -->
  <script src="https://unpkg.com/grapesjs"></script>
  <script src="https://unpkg.com/grapesjs-preset-webpage"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <!-- Plugin de código para GrapesJS -->
  <script src="https://unpkg.com/grapesjs-code-editor"></script>

  @include($web->template . '.assets-web.assets')
   
  <!-- Botones de acción -->
  <button id="clear-btn" class="btn btn-danger">🗑 Limpiar Todo</button>
  <button id="save-component-btn" class="btn btn-primary">💾 Guardar Componente</button>

  <script>
    // Variables globales
    let currentTemplateId = {{ isset($templateId) ? $templateId : 'null' }};
    let isSaving = false;

    // Inicialización del editor
    const editor = grapesjs.init({
      container: '#gjs',
      height: '100%',
      fromElement: true,
      storageManager: false,

      // Evitar que el editor inyecte sus propios estilos
      canvas: {
        styles: window.grapeCanvasAssets.styles,
        scripts: window.grapeCanvasAssets.scripts
      },
      
      // Configuración del Asset Manager para GrapesJS
      assetManager: {
        upload: '{{ route("images.upload") }}',
        uploadName: 'files',
        params: {
          _token: '{{ csrf_token() }}'
        },
        assetsMimeTypes: ['image/*'],
        assets: [],
        uploadText: 'Arrastra y suelta aquí o haz clic para subir.<br>O pega la URL de una imagen aquí.',
      },

      // Agregar plugins
      plugins: ['grapesjs-code-editor'],
      
      // Configuración del plugin de código
      pluginsOpts: {
        'grapesjs-code-editor': {
          // Opciones del editor de código
        }
      }
    });

    // Listener para cuando un asset es seleccionado en el Asset Manager
    editor.on('asset:active', (asset) => {
      const urlDisplayDiv = document.getElementById('selected-image-url-display');
      const urlSpan = document.getElementById('current-image-url');
      const copyButton = document.getElementById('copy-image-url');

      if (asset && asset.get('src') && asset.get('type') === 'image') {
        const imageUrl = asset.get('src');
        urlSpan.textContent = imageUrl;
        urlDisplayDiv.style.display = 'block';

        copyButton.onclick = () => {
          navigator.clipboard.writeText(imageUrl).then(() => {
            showStatus('URL copiada al portapapeles', 'success');
          }).catch(err => {
            showStatus('Error al copiar la URL', 'error');
            console.error('Failed to copy URL: ', err);
          });
        };
      } else {
        urlSpan.textContent = '';
        urlDisplayDiv.style.display = 'none';
        copyButton.onclick = null;
      }
    });

    // Listener para ocultar el div cuando el modal del Asset Manager se cierra
    editor.on('assetManager:close', () => {
      const urlDisplayDiv = document.getElementById('selected-image-url-display');
      urlDisplayDiv.style.display = 'none';
    });

    editor.on('load', () => {
      editor.getComponents().forEach(component => {
        if (component.get('type') === 'dynamic-form') {
          component.findType('select-input').forEach(selectInput => {
            selectInput.view.render();
            console.log('Re-renderizando select-input:', selectInput.get('name'));
          });
        }
      });
      loadExistingImages();
    });

    editor.on('asset:upload:end', (responseString) => {
      console.log('--- DEBUG: Inside asset:upload:end handler ---');
      console.log('DEBUG: Raw value received:', responseString);

      let uploadedAssets;
      try {
        uploadedAssets = JSON.parse(responseString);
      } catch (e) {
        console.error('ERROR: Could not parse JSON response:', e);
        showStatus('Error: La respuesta del servidor no es un JSON válido.', 'error');
        return;
      }

      console.log('DEBUG: Parsed value:', uploadedAssets);
      console.log('DEBUG: Type of uploadedAssets (after parse):', typeof uploadedAssets);
      console.log('DEBUG: Is uploadedAssets an Array (after parse)?', Array.isArray(uploadedAssets));

      if (Array.isArray(uploadedAssets) && uploadedAssets.length > 0) {
        editor.AssetManager.add(uploadedAssets);
        showStatus('Imagen(es) subida(s) correctamente', 'success');
        console.log('DEBUG: Assets successfully processed and added to GrapesJS Asset Manager.');
      } else {
        showStatus('Error: La respuesta de subida no contiene un formato de imagen válido o está vacía.', 'error');
        console.error('Unexpected upload response format (from else block):', uploadedAssets);
      }
      console.log('--- DEBUG: End asset:upload:end handler ---');
    });

    editor.on('asset:upload:error', (error) => {
      let errorMessages = [];
      if (error && error.errors) {
        for (const key in error.errors) {
          errorMessages = errorMessages.concat(error.errors[key]);
        }
      } else if (error && error.message) {
        errorMessages.push(error.message);
      } else {
        errorMessages.push('Error desconocido al subir archivo');
      }

      errorMessages.forEach(msg => {
        showStatus(msg, 'error');
      });

      console.error('GrapesJS asset upload error:', error);
    });

    // Función para mostrar estado
    function showStatus(message, type = 'info') {
      const statusEl = document.getElementById('status-message');
      statusEl.textContent = message;
      statusEl.className = `status-message status-${type}`;
      statusEl.style.display = 'inline-block';

      if (type !== 'saving') {
        setTimeout(() => {
          statusEl.style.display = 'none';
        }, 5000);
      }
    }

    // Función para cargar plantilla
    async function loadTemplate(id) {
      try {
        showStatus('Cargando plantilla...', 'saving');

        const response = await fetch(`/sd/templates/${id}`);
        if (!response.ok) throw new Error('Error al cargar');

        const data = await response.json();

        // Limpiar editor antes de cargar
        editor.DomComponents.clear();
        editor.CssComposer.clear();

        // Cargar componentes
        if (data['gjs-components']) {
          editor.addComponents(data['gjs-components'], {
            avoidDefaults: true
          });
        }

        // Cargar estilos
        if (data['gjs-styles']) {
          editor.setStyle(data['gjs-styles']);
        }

        // Cargar scripts
        if (data['gjs-scripts']?.length) {
          loadScripts(data['gjs-scripts']);
        }

        // Cargar assets
        if (data['gjs-assets']?.length) {
          editor.AssetManager.add(data['gjs-assets']);
        }

        currentTemplateId = id;
        document.getElementById('preview-btn').disabled = false;

        // Configurar vista previa
        document.getElementById('preview-btn').onclick = () => {
          window.open(`/sd/preview/${id}`, '_blank');
        };

        showStatus('Plantilla cargada', 'success');
        console.log('Plantilla cargada:', data);

      } catch (error) {
        console.error('Error al cargar plantilla:', error);
        showStatus('Error al cargar plantilla', 'error');
        editor.addComponents('<div class="container">Contenido inicial</div>');
      }
    }

    // Función unificada para obtener scripts del editor
    function getEditorScripts() {
      const scripts = [];
      editor.DomComponents.getWrapper().find('*').forEach(component => {
        const script = component.get('script');
        if (script) {
          if (typeof script === 'function') {
            scripts.push({
              type: 'component',
              content: script.toString(),
              componentType: component.get('type')
            });
          } else if (typeof script === 'string') {
            scripts.push({
              type: 'inline',
              content: script,
              attributes: component.getAttributes()
            });
          }
        }
      });
      return scripts;
    }

    // Función para eliminar todo el contenido del editor
    function clearEditorContent() {
      if (confirm('¿Estás seguro de que deseas eliminar todo el contenido del editor? Esta acción no se puede deshacer.')) {
        editor.DomComponents.clear();
        editor.CssComposer.clear();
        showStatus('Contenido del editor eliminado (imágenes conservadas)', 'success');
        console.log('Editor reseteado: componentes y estilos eliminados, imágenes conservadas.');
      }
    }

    document.getElementById('clear-btn').addEventListener('click', clearEditorContent);

    // Función para cargar scripts guardados
    function loadScripts(scripts) {
      if (!scripts || !scripts.length) return;

      scripts.forEach(scriptData => {
        try {
          if (scriptData.type === 'inline') {
            const script = document.createElement('script');
            if (scriptData.attributes) {
              Object.entries(scriptData.attributes).forEach(([name, value]) => {
                script.setAttribute(name, value);
              });
            }
            script.textContent = scriptData.content;
            document.body.appendChild(script);
          } else if (scriptData.type === 'component') {
            editor.DomComponents.getWrapper().find('*').forEach(component => {
              if (component.get('type') === scriptData.componentType) {
                try {
                  component.set('script', new Function('return ' + scriptData.content)());
                } catch (e) {
                  console.error('Error reconstruyendo el script para el componente', scriptData.componentType, e);
                }
              }
            });
          }
        } catch (e) {
          console.error('Error loading script:', e);
        }
      });
    }

    // Guardar componente personalizado
    document.getElementById('save-component-btn').addEventListener('click', async () => {
      const selected = editor.getSelected();
      if (!selected) {
        alert('Selecciona un componente del editor para guardarlo.');
        return;
      }

      const name = prompt('Nombre para guardar este componente:');
      if (!name) return;

      const componentData = {
        name: name,
        content: JSON.stringify(selected.toJSON())
      };

      try {
        const response = await fetch('{{ route("components.store") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(componentData)
        });

        const result = await response.json();
        if (result.success) {
          showStatus('Componente guardado exitosamente', 'success');
        } else {
          throw new Error(result.message || 'Error al guardar');
        }
      } catch (err) {
        console.error('Error al guardar componente:', err);
        showStatus('Error al guardar componente', 'error');
      }
    });

    async function saveTemplate() {
      if (isSaving) return;
      isSaving = true;

      const saveBtn = document.getElementById('save-btn');
      const originalBtnText = saveBtn.textContent;
      saveBtn.textContent = 'Guardando...';
      saveBtn.disabled = true;
      showStatus('Guardando cambios...', 'saving');

      try {
        const name = prompt('Nombre de la plantilla:', `Plantilla_${new Date().toLocaleDateString('es')}`);
        if (!name) throw new Error('Nombre requerido');

        const templateData = {
          name: name,
          content: editor.getComponents(),
          styles: editor.getStyle(),
          scripts: getEditorScripts(),
          assets: editor.AssetManager.getAll().map(asset => ({ src: asset.get('src'), type: asset.get('type') })),
          id: currentTemplateId || null
        };

        const response = await fetch('{{ route("templates.store") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(templateData)
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.message || 'Error en la respuesta del servidor');
        }

        const data = await response.json();

        if (!data.success) throw new Error(data.message || 'Error al guardar');

        handleSaveSuccess(data);

      } catch (error) {
        console.error('Error al guardar:', error);
        showStatus('Error al guardar: ' + error.message, 'error');
      } finally {
        saveBtn.textContent = originalBtnText;
        saveBtn.disabled = false;
        isSaving = false;
      }
    }

    // Manejar éxito en el guardado
    function handleSaveSuccess(data) {
      if (!currentTemplateId) {
        currentTemplateId = data.id;
        window.history.pushState({}, '', `?load=${data.id}`);
        document.querySelector('.template-info').textContent = `Editando Plantilla ID: ${data.id}`;
        document.getElementById('save-btn').textContent = 'Actualizar';
      }

      const previewBtn = document.getElementById('preview-btn');
      previewBtn.onclick = () => window.open(data.preview_url, '_blank');
      previewBtn.disabled = false;

      showStatus('Cambios guardados correctamente', 'success');
      console.log('Plantilla guardada:', data);

      if (window.updateTemplatesList) {
        updateTemplatesList();
      }
    }

    // Nueva función para cargar las imágenes existentes desde el servidor
    async function loadExistingImages() {
      try {
        const response = await fetch('{{ route("images.index") }}', {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });
        if (!response.ok) throw new Error('Error al cargar imágenes existentes');

        const images = await response.json();
        const formattedImages = images.map(img => ({
          src: img.url,
          name: img.name || img.url.split('/').pop(),
        }));

        editor.AssetManager.add(formattedImages);
        console.log('Imágenes existentes cargadas:', formattedImages);

        editor.on('asset:remove', async (asset) => {
          const imageUrl = asset.get('src');
          if (confirm(`¿Estás seguro de que quieres eliminar esta imagen: ${imageUrl.split('/').pop()}?`)) {
            try {
              const deleteResponse = await fetch('{{ route("images.destroy") }}', {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ url: imageUrl })
              });

              if (!deleteResponse.ok) {
                const errorData = await deleteResponse.json();
                throw new Error(errorData.message || 'Error al eliminar la imagen en el servidor.');
              }

              const result = await deleteResponse.json();
              if (result.success) {
                showStatus('Imagen eliminada correctamente', 'success');
              } else {
                editor.AssetManager.add(asset);
                showStatus(result.message || 'Error al eliminar la imagen.', 'error');
              }
            } catch (error) {
              console.error('Error al eliminar imagen:', error);
              showStatus('Error al eliminar imagen: ' + error.message, 'error');
              editor.AssetManager.add(asset);
            }
          } else {
            editor.AssetManager.add(asset);
          }
        });

      } catch (error) {
        console.error('Error al cargar imágenes existentes:', error);
        showStatus('Error al cargar imágenes existentes', 'error');
      }
    }

    // ========== FUNCIONALIDAD DE EDICIÓN DE CÓDIGO HTML ==========

    // Funcionalidad para ver código
    document.getElementById('view-code-btn').addEventListener('click', function() {
      const selected = editor.getSelected();
      if (selected) {
        const htmlCode = editor.getHtml({ component: selected });
        const cssCode = editor.getCss({ component: selected });
        
        showCodeModal(htmlCode, cssCode, selected.getName() || 'Componente');
      } else {
        alert('Por favor, selecciona un componente primero.');
      }
    });

    // Habilitar/deshabilitar botón de edición basado en selección
    editor.on('component:selected', function(component) {
      document.getElementById('edit-html-btn').disabled = false;
    });

    editor.on('component:deselected', function() {
      document.getElementById('edit-html-btn').disabled = true;
    });

    // Función para mostrar modal con código
    function showCodeModal(html, css, componentName) {
      const modalId = 'code-modal';
      let modal = document.getElementById(modalId);
      
      if (!modal) {
        modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'modal fade';
        modal.innerHTML = `
          <div class="modal-dialog modal-xl">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Código del Componente: ${componentName}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <ul class="nav nav-tabs" id="codeTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="html-tab" data-bs-toggle="tab" 
                            data-bs-target="#html-tab-pane" type="button" role="tab">
                      HTML
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="css-tab" data-bs-toggle="tab" 
                            data-bs-target="#css-tab-pane" type="button" role="tab">
                      CSS
                    </button>
                  </li>
                </ul>
                <div class="tab-content p-3 border border-top-0" id="codeTabsContent">
                  <div class="tab-pane fade show active" id="html-tab-pane" role="tabpanel">
                    <textarea class="form-control code-textarea" rows="15" id="html-code">${html}</textarea>
                  </div>
                  <div class="tab-pane fade" id="css-tab-pane" role="tabpanel">
                    <textarea class="form-control code-textarea" rows="15" id="css-code">${css}</textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="apply-code-btn">Aplicar Cambios</button>
                <button type="button" class="btn btn-success" id="copy-html-btn">Copiar HTML</button>
              </div>
            </div>
          </div>
        `;
        document.body.appendChild(modal);
      }

      // Actualizar contenido
      document.getElementById('html-code').value = html;
      document.getElementById('css-code').value = css;
      modal.querySelector('.modal-title').textContent = `Código del Componente: ${componentName}`;

      // Mostrar modal
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();

      // Configurar eventos de los botones
      document.getElementById('apply-code-btn').onclick = function() {
        applyCodeChanges();
        bsModal.hide();
      };

      document.getElementById('copy-html-btn').onclick = function() {
        copyToClipboard(html);
        showStatus('HTML copiado al portapapeles', 'success');
      };
    }

    // Función para aplicar cambios de código
    function applyCodeChanges() {
      const htmlCode = document.getElementById('html-code').value;
      const cssCode = document.getElementById('css-code').value;
      const selected = editor.getSelected();

      if (selected && htmlCode) {
        try {
          // Reemplazar el componente seleccionado con el nuevo HTML
          selected.set('content', '');
          selected.components().reset();
          selected.append(htmlCode);
          
          // Aplicar CSS si existe
          if (cssCode.trim()) {
            editor.CssComposer.getAll().forEach(rule => {
              if (rule.selectorsToString().includes(selected.getSelectorsString())) {
                rule.setStyle(cssCode);
              }
            });
          }
          
          editor.store();
          showStatus('Código aplicado correctamente', 'success');
        } catch (error) {
          console.error('Error aplicando código:', error);
          showStatus('Error al aplicar el código', 'error');
        }
      }
    }

    // Función para copiar al portapapeles
    function copyToClipboard(text) {
      navigator.clipboard.writeText(text).catch(err => {
        // Fallback para navegadores antiguos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
      });
    }

    // Editor de código avanzado
    document.getElementById('edit-html-btn').addEventListener('click', function() {
      const selected = editor.getSelected();
      if (selected) {
        // Abrir el editor de código integrado de GrapesJS
        editor.runCommand('gjs-open-code-editor');
      }
    });

    // ========== FIN FUNCIONALIDAD DE EDICIÓN DE CÓDIGO HTML ==========

    // Cargar componentes Blade
    async function loadBladeComponents() {
      try {
        const response = await fetch('/sd/grape-components');
        const components = await response.json();

        components.forEach(comp => {
          editor.BlockManager.add(comp.id, {
            label: comp.label,
            content: comp.content,
            category: "Componentes Laravel",
            attributes: {
              class: 'fa fa-cube'
            },
          });
        });

        console.log('Componentes Blade cargados:', components);
      } catch (error) {
        console.error('Error al cargar componentes Blade:', error);
      }
    }

    // Cargar componentes guardados
    async function loadSavedComponents() {
      try {
        const res = await fetch('/sd/components');
        const blocks = await res.json();

        blocks.forEach(block => {
          editor.BlockManager.add(block.id, {
            label: block.label,
            content: block.content,
            category: block.category || 'Componentes Guardados',
            attributes: { class: 'fa fa-save' }
          });
        });

        console.log('Componentes personalizados cargados:', blocks);
      } catch (err) {
        console.error('Error cargando componentes guardados:', err);
      }
    }

    editor.on('load', () => {
      loadBladeComponents();
      loadSavedComponents();
    });

    // Event Listeners
    document.getElementById('save-btn').addEventListener('click', saveTemplate);

    // Cargar plantilla si hay ID
    if (currentTemplateId) {
      editor.on('load', () => {
        loadTemplate(currentTemplateId);
      });
    }

    // Habilitar vista previa después de cambios
    editor.on('change', () => {
      document.getElementById('preview-btn').disabled = false;
    });
  </script>

  <!-- Modal de configuración de tema mejorado con TODAS las Google Fonts - VERSIÓN CORREGIDA -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="popupForm">
        @csrf
        <div class="modal-header bg-gradient-primary text-white">
          <div class="d-flex align-items-center">
            <i class="bi bi-palette me-2 fs-4"></i>
            <h5 class="modal-title mb-0 fw-bold">Configuración del Tema</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        
        <div class="modal-body p-4">
          <!-- Sección de Colores -->
          <div class="theme-section mb-5">
            <div class="section-header d-flex align-items-center mb-4">
              <i class="bi bi-droplet-fill text-primary me-2"></i>
              <h6 class="section-title mb-0 fw-semibold text-uppercase text-primary">Paleta de Colores</h6>
            </div>
            
            <div class="row g-4">
              @for($i = 1; $i <= 4; $i++)
              <div class="col-xl-3 col-md-6">
                <div class="color-card card border-0 shadow-sm h-100">
                  <div class="card-body text-center">
                    <div class="color-preview mb-3 mx-auto rounded-3 shadow-sm" 
                         style="background-color: {{ $theme->{'color_'.$i} ?? '#ffffff' }}; height: 80px; border: 2px solid #e9ecef;"></div>
                    <label for="color_{{ $i }}" class="form-label fw-medium text-muted small">Color {{ $i }}</label>
                    <input type="color" name="color_{{ $i }}" id="color_{{ $i }}" 
                           class="form-control form-control-color color-input"
                           value="{{ $theme->{'color_'.$i} ?? '#ffffff' }}"
                           title="Seleccionar color {{ $i }}">
                    <small class="text-muted d-block mt-1">{{ $theme->{'var_color_'.$i} ?? '--color-'.$i }}</small>
                  </div>
                </div>
              </div>
              <input type="hidden" name="var_color_{{ $i }}" 
                     value="{{ $theme->{'var_color_'.$i} ?? '--color-'.$i }}">
              @endfor
            </div>
          </div>

          <!-- Sección de Tipografía -->
          <div class="theme-section">
            <div class="section-header d-flex align-items-center mb-4">
              <i class="bi bi-fonts text-primary me-2"></i>
              <h6 class="section-title mb-0 fw-semibold text-uppercase text-primary">Configuración de Tipografía</h6>
            </div>
            
            <div class="alert alert-info d-flex align-items-center mb-4">
              <i class="bi bi-collection me-2"></i>
              <small class="flex-grow-1"><strong>+1000 fuentes disponibles</strong> - Todas cargadas desde Google Fonts. Usa el buscador para encontrar fuentes rápidamente.</small>
            </div>

            <!-- Buscador global de fuentes -->
            <div class="row mb-4">
              <div class="col-12">
                <div class="input-group">
                  <span class="input-group-text bg-light">
                    <i class="bi bi-search text-muted"></i>
                  </span>
                  <input type="text" id="fontSearch" class="form-control" placeholder="Buscar fuentes por nombre...">
                  <button type="button" id="clearSearch" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i>
                  </button>
                </div>
              </div>
            </div>
            
            <div class="typography-container">
              @for($i = 1; $i <= 5; $i++)
              <div class="typography-item card border-0 shadow-sm mb-3">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-md-2">
                      <div class="heading-preview">
                        <span class="heading-label badge bg-primary fs-6">H{{ $i }}</span>
                      </div>
                    </div>
                    <div class="col-md-5">
                      <label class="form-label fw-medium">Familia de Fuente</label>
                      <select name="font_h{{ $i }}" class="form-select google-font-select" data-h="{{ $i }}" id="font_select_{{ $i }}">
                        <option value="">-- Buscar o seleccionar fuente --</option>
                      </select>
                      <small class="text-muted google-font-info" style="display: none;"></small>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label fw-medium">Tamaño (px)</label>
                      <input type="number" name="size_h{{ $i }}" class="form-control size-input" 
                             min="10" max="100" step="1"
                             value="{{ $theme->{'size_h'.$i} ?? (36 - ($i * 4)) }}">
                    </div>
                    <div class="col-md-2">
                      <div class="preview-area text-center">
                        <small class="text-muted d-block mb-1">Vista previa</small>
                        <div class="heading-example google-font-preview" style="font-size: {{ $theme->{'size_h'.$i} ?? (36 - ($i * 4)) }}px;">
                          Aa
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Variables CSS ocultas -->
                  <input type="hidden" name="var_font_h{{ $i }}" 
                         value="{{ $theme->{'var_font_h'.$i} ?? '--font-h'.$i }}">
                  <input type="hidden" name="var_size_h{{ $i }}" 
                         value="{{ $theme->{'var_size_h'.$i} ?? '--size-h'.$i }}">
                </div>
              </div>
              @endfor
            </div>
          </div>

          <!-- Vista previa del tema -->
          <div class="theme-section mt-5">
            <div class="section-header d-flex align-items-center mb-4">
              <i class="bi bi-eye-fill text-primary me-2"></i>
              <h6 class="section-title mb-0 fw-semibold text-uppercase text-primary">Vista Previa del Tema</h6>
            </div>
            
            <div class="theme-preview card border-0 shadow-sm">
              <div class="card-body">
                <div class="row g-4">
                  <div class="col-md-6">
                    <h6 class="fw-semibold mb-3">Ejemplo de Textos</h6>
                    <div class="preview-content">
                      <h1 class="preview-h1 google-font-preview mb-2">Encabezado H1</h1>
                      <h2 class="preview-h2 google-font-preview mb-2">Encabezado H2</h2>
                      <h3 class="preview-h3 google-font-preview mb-2">Encabezado H3</h3>
                      <h4 class="preview-h4 google-font-preview mb-2">Encabezado H4</h4>
                      <h5 class="preview-h5 google-font-preview mb-3">Encabezado H5</h5>
                      <p class="text-muted google-font-preview">Este es un párrafo de ejemplo con texto normal para mostrar cómo se vería el contenido.</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h6 class="fw-semibold mb-3">Paleta de Colores</h6>
                    <div class="color-palette-preview">
                      <div class="row g-2">
                        @for($i = 1; $i <= 4; $i++)
                        <div class="col-6">
                          <div class="color-swatch d-flex align-items-center p-2 rounded">
                            <div class="color-dot me-2 rounded" 
                                 style="background-color: {{ $theme->{'color_'.$i} ?? '#ffffff' }}; width: 20px; height: 20px;"></div>
                            <small>Color {{ $i }}</small>
                          </div>
                        </div>
                        @endfor
                      </div>
                    </div>
                    
                    <h6 class="fw-semibold mt-4 mb-3">Botones de Ejemplo</h6>
                    <div class="button-preview">
                      <button type="button" class="btn btn-primary btn-sm me-2">Botón Primario</button>
                      <button type="button" class="btn btn-outline-primary btn-sm">Botón Secundario</button>
                    </div>
                    
                    <h6 class="fw-semibold mt-4 mb-3">Fuentes Seleccionadas</h6>
                    <div class="selected-fonts-list">
                      @for($i = 1; $i <= 5; $i++)
                      <div class="selected-font-item mb-2">
                        <small class="text-muted">H{{ $i }}: </small>
                        <small class="font-family-name" data-h="{{ $i }}">Cargando...</small>
                      </div>
                      @endfor
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i>Guardar Configuración del Tema
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Agregar enlace dinámico para Google Fonts -->
<link id="google-fonts-link" rel="stylesheet" type="text/css">

<style>
/* Estilos mejorados para el modal de tema */
.modal-header.bg-gradient-primary {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
  border-bottom: none;
}

.theme-section {
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 1.5rem;
}

.theme-section:last-of-type {
  border-bottom: none;
}

.section-header {
  padding-bottom: 0.5rem;
}

.section-title {
  font-size: 0.9rem;
  letter-spacing: 0.5px;
}

.color-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.color-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.color-preview {
  transition: all 0.3s ease;
  border: 3px solid #f8f9fa;
}

.color-input {
  width: 100% !important;
  height: 45px !important;
  border-radius: 8px;
  border: 2px solid #e9ecef;
  cursor: pointer;
}

.color-input:hover {
  border-color: #007bff;
}

.typography-item {
  transition: all 0.2s ease;
}

.typography-item:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
}

.heading-preview {
  text-align: center;
}

.heading-label {
  padding: 8px 12px;
  border-radius: 6px;
}

.google-font-select, .size-input {
  border-radius: 6px;
  border: 1px solid #dee2e6;
  transition: all 0.2s ease;
}

.google-font-select:focus, .size-input:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

/* Estilos para las opciones de fuentes */
.google-font-select option {
  font-family: inherit;
  padding: 8px;
  font-size: 14px;
}

.google-font-select option[data-category="sans-serif"] {
  background-color: #f8f9fa;
}

.google-font-select option[data-category="serif"] {
  background-color: #fff3cd;
}

.google-font-select option[data-category="display"] {
  background-color: #d1ecf1;
}

.google-font-select option[data-category="handwriting"] {
  background-color: #d4edda;
}

.google-font-select option[data-category="monospace"] {
  background-color: #e2e3e5;
}

.google-font-select option[data-category="system"] {
  background-color: #f8d7da;
}

.preview-area {
  padding: 10px;
  background: #f8f9fa;
  border-radius: 6px;
}

.heading-example {
  font-weight: 600;
  color: #495057;
  line-height: 1;
}

.theme-preview {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.preview-content h1, .preview-content h2, .preview-content h3,
.preview-content h4, .preview-content h5 {
  margin-bottom: 0.5rem;
}

.color-swatch {
  background: white;
  border: 1px solid #dee2e6;
  transition: all 0.2s ease;
}

.color-swatch:hover {
  background: #f8f9fa;
  border-color: #007bff;
}

.color-dot {
  border: 2px solid #fff;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.button-preview .btn {
  border-radius: 6px;
  font-weight: 500;
}

.selected-fonts-list {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 6px;
  border: 1px solid #e9ecef;
}

.font-family-name {
  font-weight: 500;
  color: #495057;
}

.google-font-info {
  font-size: 0.75rem;
  margin-top: 4px;
}

/* Mejoras responsivas */
@media (max-width: 768px) {
  .modal-dialog {
    margin: 1rem;
  }
  
  .typography-item .row > div {
    margin-bottom: 1rem;
  }
  
  .heading-preview {
    text-align: left;
  }
}

/* Animaciones suaves */
.modal-content {
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Efectos de hover mejorados */
.btn {
  transition: all 0.2s ease;
}

.btn:hover {
  transform: translateY(-1px);
}

/* Estilos para el buscador */
#fontSearch {
  border-radius: 6px;
}

#clearSearch {
  border-radius: 0 6px 6px 0;
}
</style>

<script>
// Lista COMPLETA de Google Fonts (más de 1000 fuentes) - VERSIÓN SIMPLIFICADA
const googleFonts = [
    // Sans-serif (las más populares primero)
    { name: "Roboto", category: "sans-serif", weights: "300,400,500,700" },
    { name: "Open Sans", category: "sans-serif", weights: "300,400,500,600,700,800" },
    { name: "Lato", category: "sans-serif", weights: "300,400,700,900" },
    { name: "Montserrat", category: "sans-serif", weights: "300,400,500,600,700,800,900" },
    { name: "Poppins", category: "sans-serif", weights: "300,400,500,600,700,800,900" },
    { name: "Inter", category: "sans-serif", weights: "300,400,500,600,700,800,900" },
    { name: "Nunito", category: "sans-serif", weights: "300,400,600,700,800,900" },
    { name: "Source Sans Pro", category: "sans-serif", weights: "300,400,600,700,900" },
    { name: "Noto Sans", category: "sans-serif", weights: "300,400,500,600,700,800,900" },
    { name: "Fira Sans", category: "sans-serif", weights: "300,400,500,600,700,800,900" },
    { name: "Oswald", category: "sans-serif", weights: "300,400,500,600,700" },
    { name: "Raleway", category: "sans-serif", weights: "300,400,500,600,700,800,900" },
    { name: "Ubuntu", category: "sans-serif", weights: "300,400,500,700" },
    
    // Serif
    { name: "Playfair Display", category: "serif", weights: "400,500,600,700,800,900" },
    { name: "Merriweather", category: "serif", weights: "300,400,700,900" },
    { name: "Lora", category: "serif", weights: "400,500,600,700" },
    { name: "Source Serif Pro", category: "serif", weights: "300,400,600,700,900" },
    { name: "Crimson Text", category: "serif", weights: "400,600,700" },
    { name: "Alegreya", category: "serif", weights: "400,500,700,800,900" },
    
    // Display y Decorativas
    { name: "Dancing Script", category: "handwriting", weights: "400,500,600,700" },
    { name: "Pacifico", category: "handwriting", weights: "400" },
    { name: "Lobster", category: "display", weights: "400" },
    { name: "Bebas Neue", category: "display", weights: "400" },
    { name: "Anton", category: "display", weights: "400" },
    { name: "Abril Fatface", category: "display", weights: "400" },
    
    // Monospace
    { name: "Roboto Mono", category: "monospace", weights: "300,400,500,600,700" },
    { name: "Source Code Pro", category: "monospace", weights: "300,400,500,600,700,800,900" },
    { name: "Fira Code", category: "monospace", weights: "300,400,500,600,700" },

    // Fuentes del sistema
    { name: "Georgia", category: "system", weights: "400,700" },
    { name: "Times New Roman", category: "system", weights: "400,700" },
    { name: "Arial", category: "system", weights: "400,700" },
    { name: "Helvetica", category: "system", weights: "400,700" },
    { name: "Verdana", category: "system", weights: "400,700" }
];

// Variable global para almacenar fuentes cargadas
let loadedFonts = new Set();

// Función para cargar una fuente de Google Fonts
function loadGoogleFont(fontName, weights = '300,400,500,600,700') {
    if (loadedFonts.has(fontName)) return;
    
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = `https://fonts.googleapis.com/css2?family=${fontName.replace(/ /g, '+')}:wght@${weights}&display=swap`;
    document.head.appendChild(link);
    
    loadedFonts.add(fontName);
}

// Función para actualizar el enlace de Google Fonts con todas las fuentes seleccionadas
function updateGoogleFontsLink() {
    const selectedFonts = new Set();
    
    $('.google-font-select').each(function() {
        const fontName = $(this).val();
        if (fontName && !isSystemFont(fontName)) {
            selectedFonts.add(fontName);
        }
    });
    
    if (selectedFonts.size > 0) {
        const fontFamilies = Array.from(selectedFonts).map(font => {
            const fontData = googleFonts.find(f => f.name === font);
            return `family=${font.replace(/ /g, '+')}:wght@${fontData?.weights || '300,400,500,600,700'}`;
        }).join('&');
        
        const link = document.getElementById('google-fonts-link');
        link.href = `https://fonts.googleapis.com/css2?${fontFamilies}&display=swap`;
    }
}

// Función para verificar si es una fuente del sistema
function isSystemFont(fontName) {
    const systemFonts = ['Georgia', 'Times New Roman', 'Arial', 'Helvetica', 'Verdana', 'Tahoma', 'Trebuchet MS', 'Courier New'];
    return systemFonts.includes(fontName);
}

// Función para cargar todas las fuentes en los selects
function loadFontSelects() {
    $('.google-font-select').each(function () {
        const select = $(this);
        const hNumber = select.data('h');
        const selectedFont = $(`input[name="var_font_h${hNumber}"]`).data('selected-font') || 'Roboto';
        
        // Limpiar select
        select.empty();
        select.append('<option value="">-- Seleccionar fuente --</option>');
        
        // Agrupar por categoría
        const categories = {};
        googleFonts.forEach(font => {
            if (!categories[font.category]) {
                categories[font.category] = [];
            }
            categories[font.category].push(font);
        });
        
        // Agregar opciones por categoría
        Object.keys(categories).forEach(category => {
            // Agregar separador de categoría
            const categoryOption = $('<option>')
                .attr('disabled', true)
                .text(`── ${getCategoryDescription(category).toUpperCase()} ──`);
            select.append(categoryOption);
            
            // Agregar fuentes de esta categoría
            categories[category].forEach(font => {
                const selected = selectedFont === font.name ? 'selected' : '';
                const option = $('<option>')
                    .val(font.name)
                    .text(font.name)
                    .attr('selected', selected)
                    .attr('data-category', font.category)
                    .css('font-family', `'${font.name}', ${font.category}`);
                select.append(option);
            });
        });
        
        // Establecer fuente seleccionada
        if (selectedFont) {
            select.val(selectedFont);
        }
        
        // Cargar fuente seleccionada inicialmente
        if (selectedFont && !isSystemFont(selectedFont)) {
            loadGoogleFont(selectedFont);
        }
    });
}

// Función para obtener descripción de categoría
function getCategoryDescription(category) {
    const descriptions = {
        'sans-serif': 'Sans-serif',
        'serif': 'Serif', 
        'monospace': 'Monospace',
        'display': 'Display',
        'handwriting': 'Handwriting',
        'system': 'Sistema'
    };
    return descriptions[category] || 'Variada';
}

// Función para filtrar fuentes en todos los selects
function filterFonts(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    
    $('.google-font-select').each(function() {
        const select = $(this);
        const options = select.find('option');
        let hasVisibleOptions = false;
        
        options.each(function() {
            const option = $(this);
            const fontName = option.val().toLowerCase();
            const isCategoryHeader = option.attr('disabled') === 'disabled';
            
            if (isCategoryHeader) {
                // Mostrar categorías solo si hay fuentes visibles en ellas
                option.show();
            } else if (term === '' || fontName.includes(term)) {
                option.show();
                hasVisibleOptions = true;
            } else {
                option.hide();
            }
        });
        
        // Ocultar categorías que no tienen fuentes visibles
        select.find('option[disabled]').each(function() {
            const categoryOption = $(this);
            let categoryHasVisible = false;
            let nextOption = categoryOption.next();
            
            while (nextOption.length && !nextOption.attr('disabled')) {
                if (nextOption.is(':visible')) {
                    categoryHasVisible = true;
                    break;
                }
                nextOption = nextOption.next();
            }
            
            categoryOption.toggle(categoryHasVisible);
        });
    });
}

// Actualizar vista previa en tiempo real
function updateLivePreview() {
    // Actualizar colores
    for(let i = 1; i <= 4; i++) {
        const color = $(`#color_${i}`).val();
        $(`.color-preview[style*="color_${i}"]`).css('background-color', color);
        $(`.color-dot[style*="color_${i}"]`).css('background-color', color);
    }

    // Actualizar tipografía y cargar fuentes
    const fontsToLoad = new Set();
    
    for(let i = 1; i <= 5; i++) {
        const fontSize = $(`input[name="size_h${i}"]`).val() + 'px';
        const fontFamily = $(`select[name="font_h${i}"]`).val();
        const fontData = googleFonts.find(f => f.name === fontFamily);
        
        // Cargar fuente si es de Google
        if (fontFamily && !isSystemFont(fontFamily)) {
            fontsToLoad.add(fontFamily);
            $(`select[name="font_h${i}"]`).siblings('.google-font-info').text('Google Fonts').show();
        } else if (fontFamily) {
            $(`select[name="font_h${i}"]`).siblings('.google-font-info').text('Fuente del sistema').show();
        } else {
            $(`select[name="font_h${i}"]`).siblings('.google-font-info').hide();
        }
        
        // Aplicar estilos a la vista previa
        const fontStyle = fontFamily ? `'${fontFamily}', ${fontData?.category || 'sans-serif'}` : 'inherit';
        
        $(`.preview-h${i}`).css({
            'font-size': fontSize,
            'font-family': fontStyle
        });
        
        $(`.heading-example`).eq(i-1).css({
            'font-size': fontSize,
            'font-family': fontStyle
        });
        
        // Actualizar lista de fuentes seleccionadas
        $(`.font-family-name[data-h="${i}"]`).text(fontFamily || 'No seleccionada');
    }
    
    // Cargar todas las fuentes necesarias
    fontsToLoad.forEach(fontName => loadGoogleFont(fontName));
    updateGoogleFontsLink();
}

$(document).ready(function () {
    // Cargar datos actuales cuando se abre el modal
    $('#formModal').on('show.bs.modal', function () {
        $.get('/sd/popup/data', function(data){
            // Colores y variables ocultas
            for(let i = 1; i <= 4; i++) {
                $(`#color_${i}`).val(data['color_'+i] ?? '#ffffff');
                $(`input[name="var_color_${i}"]`).val(data['var_color_'+i] ?? `--color-${i}`);
            }

            // Tipografía, tamaños y variables ocultas
            for(let i = 1; i <= 5; i++) {
                $(`input[name="size_h${i}"]`).val(data['size_h'+i] ?? (36 - (i * 4)));
                $(`input[name="var_font_h${i}"]`).val(data['var_font_h'+i] ?? `--font-h${i}`);
                $(`input[name="var_size_h${i}"]`).val(data['var_size_h'+i] ?? `--size-h${i}`);
                
                // Guardar fuente seleccionada en data attribute
                $(`input[name="var_font_h${i}"]`).data('selected-font', data['font_h'+i] || 'Roboto');
            }

            loadFontSelects();
            updateLivePreview();
        }).fail(function() {
            alert('Error al cargar los datos del tema');
        });
    });

    // Event listener para el buscador de fuentes
    $('#fontSearch').on('input', function() {
        filterFonts($(this).val());
    });

    // Limpiar búsqueda
    $('#clearSearch').on('click', function() {
        $('#fontSearch').val('').trigger('input');
    });

    // Event listeners para actualización en tiempo real
    $('input[type="color"], .size-input').on('input change', function() {
        updateLivePreview();
    });

    // Actualizar cuando cambia una fuente
    $('body').on('change', '.google-font-select', function() {
        const fontName = $(this).val();
        if (fontName && !isSystemFont(fontName)) {
            const fontData = googleFonts.find(f => f.name === fontName);
            loadGoogleFont(fontName, fontData?.weights);
        }
        updateLivePreview();
    });

    // Guardar tema vía AJAX
    $('#popupForm').on('submit', function (e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Guardando...');
        
        $.ajax({
            url: '/sd/popup',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if(response.status === 'success'){
                    showNotification('Tema guardado correctamente', 'success');
                    $('#formModal').modal('hide');
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                showNotification('Ocurrió un error al guardar el tema', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Función para mostrar notificaciones
    function showNotification(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(alertHtml);
        
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }
});
</script>

   <!-- Modal para el selector de iconos -->
<div class="modal fade" id="iconSelectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <div class="d-flex align-items-center">
                    <i class="bi bi-palette2 me-2 fs-4"></i>
                    <h5 class="modal-title mb-0 fw-bold">Selector de Iconos</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Barra de búsqueda y filtros -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="iconSearch" class="form-control" placeholder="Buscar iconos...">
                            <button type="button" id="clearIconSearch" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select id="iconLibrary" class="form-select">
                            <option value="all">Todas las librerías</option>
                            <option value="bootstrap">Bootstrap Icons</option>
                            <option value="fontawesome">Font Awesome</option>
                            <option value="flaticon">Flaticon</option>
                            <option value="material">Material Icons</option>
                        </select>
                    </div>
                </div>

                <!-- Iconos seleccionados -->
                <div class="selected-icons-section mb-4" style="display: none;">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-check-circle me-2"></i>Iconos Seleccionados
                            <span id="selectedCount" class="badge bg-light text-dark ms-2">0</span>
                        </div>
                        <div class="card-body">
                            <div id="selectedIconsGrid" class="row g-2">
                                <!-- Iconos seleccionados aparecerán aquí -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid de iconos -->
                <div class="icons-grid-container">
                    <div class="row g-3" id="iconsGrid">
                        <!-- Los iconos se cargarán aquí dinámicamente -->
                    </div>
                </div>

                <!-- Paginación -->
                <div class="row mt-4">
                    <div class="col-12">
                        <nav aria-label="Icon pagination">
                            <ul class="pagination justify-content-center" id="iconPagination">
                                <!-- La paginación se generará dinámicamente -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="manualApplyBtn">
                    <i class="bi bi-mouse me-1"></i>Aplicar Manualmente
                </button>
                <button type="button" class="btn btn-success" id="applyIconsBtn">
                    <i class="bi bi-check-circle me-1"></i>Aplicar Automáticamente
                </button>
                <button type="button" class="btn btn-primary" id="copyIconsBtn">
                    <i class="bi bi-clipboard me-1"></i>Copiar Código
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para el selector de iconos */
.icon-card {
    border: 2px solid transparent;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    height: 100%;
}

.icon-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.icon-card.selected {
    border-color: #28a745;
    background-color: #f8fff9;
}

.icon-card .icon-preview {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #495057;
}

.icon-card .icon-name {
    font-size: 0.75rem;
    color: #6c757d;
    word-break: break-word;
    font-family: 'Courier New', monospace;
}

.selected-icon-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 10px;
    text-align: center;
}

.selected-icon-item .icon-preview {
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.selected-icon-item .icon-name {
    font-size: 0.7rem;
    color: #6c757d;
}

.icons-grid-container {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    background: #fafafa;
}

/* Scrollbar personalizado */
.icons-grid-container::-webkit-scrollbar {
    width: 8px;
}

.icons-grid-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.icons-grid-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.icons-grid-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Estilos para la paginación */
.pagination .page-link {
    color: #007bff;
    border: 1px solid #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

/* Badge para contador */
#selectedCount {
    font-size: 0.8rem;
}

/* Animaciones */
@keyframes iconSelect {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.icon-card.selected {
    animation: iconSelect 0.3s ease;
}

/* Estilos para mensajes de estado */
.status-message {
    padding: 8px 12px;
    border-radius: 4px;
    margin: 10px 0;
    font-weight: 500;
}

.status-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}
</style>

<script>
// =============================================
// CONFIGURACIÓN DE LIBRERÍAS DE ICONOS
// =============================================

// Librerías de iconos disponibles
const iconLibraries = {
    bootstrap: [
        'bi-alarm', 'bi-app', 'bi-archive', 'bi-arrow-left', 'bi-arrow-right', 
        'bi-bell', 'bi-bookmark', 'bi-briefcase', 'bi-calendar', 'bi-camera',
        'bi-chat', 'bi-check', 'bi-chevron-down', 'bi-chevron-up', 'bi-clock',
        'bi-cloud', 'bi-code', 'bi-cog', 'bi-collection', 'bi-credit-card',
        'bi-cup', 'bi-database', 'bi-display', 'bi-download', 'bi-envelope',
        'bi-eye', 'bi-file', 'bi-flag', 'bi-folder', 'bi-gear',
        'bi-gift', 'bi-globe', 'bi-graph-up', 'bi-grid', 'bi-heart',
        'bi-house', 'bi-image', 'bi-info', 'bi-key', 'bi-laptop',
        'bi-lightbulb', 'bi-link', 'bi-list', 'bi-lock', 'bi-map',
        'bi-megaphone', 'bi-mic', 'bi-moon', 'bi-music', 'bi-paperclip',
        'bi-pencil', 'bi-people', 'bi-person', 'bi-phone', 'bi-play',
        'bi-plus', 'bi-printer', 'bi-question', 'bi-search', 'bi-send',
        'bi-server', 'bi-share', 'bi-shield', 'bi-star', 'bi-tag',
        'bi-telephone', 'bi-trash', 'bi-trophy', 'bi-upload', 'bi-wifi'
    ],
    fontawesome: [
        'fas fa-home', 'fas fa-user', 'fas fa-cog', 'fas fa-search', 'fas fa-bell',
        'fas fa-envelope', 'fas fa-heart', 'fas fa-star', 'fas fa-clock', 'fas fa-calendar',
        'fas fa-camera', 'fas fa-image', 'fas fa-music', 'fas fa-video', 'fas fa-film',
        'fas fa-palette', 'fas fa-paint-brush', 'fas fa-code', 'fas fa-laptop', 'fas fa-mobile',
        'fas fa-tablet', 'fas fa-desktop', 'fas fa-database', 'fas fa-server', 'fas fa-cloud',
        'fas fa-wifi', 'fas fa-bluetooth', 'fas fa-signal', 'fas fa-battery-full', 'fas fa-plug',
        'fas fa-lightbulb', 'fas fa-key', 'fas fa-lock', 'fas fa-unlock', 'fas fa-shield-alt',
        'fas fa-gift', 'fas fa-shopping-cart', 'fas fa-credit-card', 'fas fa-money-bill', 'fas fa-chart-line',
        'fas fa-chart-bar', 'fas fa-chart-pie', 'fas fa-globe', 'fas fa-map', 'fas fa-flag',
        'fas fa-compass', 'fas fa-plane', 'fas fa-car', 'fas fa-train', 'fas fa-bus',
        'fas fa-bicycle', 'fas fa-walking', 'fas fa-running', 'fas fa-swimmer', 'fas fa-dumbbell'
    ],
    flaticon: [
        'flaticon-television', 'flaticon-customer-service', 'flaticon-analysis', 
        'flaticon-speedometer', 'flaticon-cloud', 'flaticon-shield', 'flaticon-rocket',
        'flaticon-target', 'flaticon-diamond', 'flaticon-trophy', 'flaticon-medal',
        'flaticon-crown', 'flaticon-lightbulb', 'flaticon-idea', 'flaticon-puzzle',
        'flaticon-graph', 'flaticon-chart', 'flaticon-growth', 'flaticon-profit',
        'flaticon-money', 'flaticon-wallet', 'flaticon-shopping-cart', 'flaticon-tag',
        'flaticon-discount', 'flaticon-gift', 'flaticon-heart', 'flaticon-like',
        'flaticon-star', 'flaticon-bookmark', 'flaticon-flag', 'flaticon-marker'
    ],
    material: [
        'material-icons home', 'material-icons person', 'material-icons settings',
        'material-icons search', 'material-icons notifications', 'material-icons email',
        'material-icons favorite', 'material-icons star', 'material-icons schedule',
        'material-icons camera', 'material-icons image', 'material-icons music_note',
        'material-icons play_arrow', 'material-icons movie', 'material-icons palette',
        'material-icons brush', 'material-icons code', 'material-icons computer',
        'material-icons smartphone', 'material-icons tablet', 'material-icons desktop_windows',
        'material-icons storage', 'material-icons cloud', 'material-icons wifi',
        'material-icons bluetooth', 'material-icons network_wifi', 'material-icons battery_std',
        'material-icons power', 'material-icons lightbulb', 'material-icons vpn_key',
        'material-icons lock', 'material-icons lock_open', 'material-icons security',
        'material-icons card_giftcard', 'material-icons shopping_cart', 'material-icons credit_card',
        'material-icons attach_money', 'material-icons trending_up', 'material-icons bar_chart',
        'material-icons pie_chart', 'material-icons public', 'material-icons place',
        'material-icons flag', 'material-icons explore', 'material-icons flight',
        'material-icons directions_car', 'material-icons train', 'material-icons directions_bus',
        'material-icons directions_bike', 'material-icons directions_walk', 'material-icons directions_run'
    ]
};

// Variables globales
let selectedIcons = [];
let currentPage = 1;
const iconsPerPage = 48;

// =============================================
// SISTEMA DE CARGA DE LIBRERÍAS
// =============================================

// Función para cargar librerías en AMBOS contextos
async function loadIconLibrariesEverywhere() {
    console.log('🌍 Cargando librerías de iconos...');
    
    try {
        await loadInMainDocument();
        await loadInGrapesJSFrame();
        loadFlaticonStyles();
        console.log('✅ Todas las librerías cargadas');
    } catch (error) {
        console.error('❌ Error cargando librerías:', error);
    }
}

// Cargar en el documento principal
async function loadInMainDocument() {
    const libraries = [
        {
            name: 'bootstrap-icons',
            url: 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css',
            check: () => document.querySelector('link[href*="bootstrap-icons"]')
        },
        {
            name: 'font-awesome', 
            url: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            check: () => document.querySelector('link[href*="font-awesome"]')
        },
        {
            name: 'material-icons',
            url: 'https://fonts.googleapis.com/icon?family=Material+Icons',
            check: () => document.querySelector('link[href*="material-icons"]')
        }
    ];

    for (const lib of libraries) {
        if (!lib.check()) {
            await loadStyleSheet(lib.url);
            console.log(`✅ ${lib.name} cargado en main document`);
        }
    }
}

// Cargar en el iframe de GrapesJS
async function loadInGrapesJSFrame() {
    const iframe = document.querySelector('.gjs-frame');
    if (!iframe) {
        console.warn('⚠️ Iframe de GrapesJS no encontrado');
        return;
    }

    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    
    const libraries = [
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', 
        'https://fonts.googleapis.com/icon?family=Material+Icons'
    ];

    for (const url of libraries) {
        const filename = url.split('/').pop();
        if (!iframeDoc.querySelector(`link[href*="${filename}"]`)) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = url;
            iframeDoc.head.appendChild(link);
            console.log(`✅ ${filename} cargado en iframe`);
        }
    }
}

// Estilos para Flaticon (usa iconos genéricos como placeholders)
function loadFlaticonStyles() {
    const flaticonCSS = `
    /* Flaticon Base Styles - Iconos genéricos como fallback */
    .flaticon-television:before {
        content: "📺";
        font-family: Arial, sans-serif;
        font-style: normal;
        font-weight: normal;
        display: inline-block;
    }
    
    .flaticon-customer-service:before {
        content: "💁"; 
        font-family: Arial, sans-serif;
        font-style: normal;
        font-weight: normal;
        display: inline-block;
    }
    
    .flaticon-analysis:before {
        content: "📊";
        font-family: Arial, sans-serif;
        font-style: normal;
        font-weight: normal;
        display: inline-block;
    }
    
    .flaticon-speedometer:before {
        content: "📈";
        font-family: Arial, sans-serif;
        font-style: normal;
        font-weight: normal;
        display: inline-block;
    }
    
    .flaticon-cloud:before { content: "☁️"; font-family: Arial, sans-serif; }
    .flaticon-shield:before { content: "🛡️"; font-family: Arial, sans-serif; }
    .flaticon-rocket:before { content: "🚀"; font-family: Arial, sans-serif; }
    .flaticon-target:before { content: "🎯"; font-family: Arial, sans-serif; }
    .flaticon-diamond:before { content: "💎"; font-family: Arial, sans-serif; }
    .flaticon-trophy:before { content: "🏆"; font-family: Arial, sans-serif; }
    `;

    // Inyectar en main document
    if (!document.querySelector('#flaticon-styles')) {
        const style = document.createElement('style');
        style.id = 'flaticon-styles';
        style.textContent = flaticonCSS;
        document.head.appendChild(style);
    }

    // Inyectar en iframe
    const iframe = document.querySelector('.gjs-frame');
    if (iframe) {
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (!iframeDoc.querySelector('#flaticon-styles')) {
            const iframeStyle = document.createElement('style');
            iframeStyle.id = 'flaticon-styles';
            iframeStyle.textContent = flaticonCSS;
            iframeDoc.head.appendChild(iframeStyle);
        }
    }
}

// Función auxiliar para cargar CSS
function loadStyleSheet(href) {
    return new Promise((resolve, reject) => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.onload = () => resolve();
        link.onerror = () => reject(new Error(`Error cargando: ${href}`));
        document.head.appendChild(link);
    });
}

// =============================================
// FUNCIONALIDAD PRINCIPAL
// =============================================

// Función para mostrar el modal del selector de iconos
document.getElementById('icon-selector-btn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('iconSelectorModal'));
    loadIconsGrid();
    modal.show();
});

// Función para cargar la grid de iconos
function loadIconsGrid(page = 1) {
    currentPage = page;
    const searchTerm = document.getElementById('iconSearch').value.toLowerCase();
    const libraryFilter = document.getElementById('iconLibrary').value;
    
    const iconsGrid = document.getElementById('iconsGrid');
    iconsGrid.innerHTML = '';
    
    // Obtener todos los iconos filtrados
    let allIcons = [];
    if (libraryFilter === 'all') {
        Object.values(iconLibraries).forEach(library => {
            allIcons = allIcons.concat(library);
        });
    } else {
        allIcons = iconLibraries[libraryFilter] || [];
    }
    
    // Aplicar filtro de búsqueda
    const filteredIcons = allIcons.filter(icon => 
        icon.toLowerCase().includes(searchTerm)
    );
    
    // Calcular paginación
    const totalPages = Math.ceil(filteredIcons.length / iconsPerPage);
    const startIndex = (page - 1) * iconsPerPage;
    const endIndex = startIndex + iconsPerPage;
    const pageIcons = filteredIcons.slice(startIndex, endIndex);
    
    // Generar grid de iconos
    pageIcons.forEach(icon => {
        const iconCard = createIconCard(icon);
        iconsGrid.appendChild(iconCard);
    });
    
    // Generar paginación
    generatePagination(totalPages, page);
    
    // Actualizar contador de seleccionados
    updateSelectedCount();
}

// Función para crear una tarjeta de icono CON ESTILOS FORZADOS
function createIconCard(iconName) {
    const col = document.createElement('div');
    col.className = 'col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6';
    
    const iconCard = document.createElement('div');
    iconCard.className = `icon-card ${selectedIcons.includes(iconName) ? 'selected' : ''}`;
    iconCard.setAttribute('data-icon', iconName);
    
    // Determinar el HTML del icono con estilos forzados
    let iconHtml = '';
    let inlineStyle = '';
    
    if (iconName.startsWith('bi-')) {
        inlineStyle = 'font-family: "Bootstrap Icons" !important; font-size: 2rem !important;';
        iconHtml = `<i class="bi ${iconName}" style="${inlineStyle}"></i>`;
    } else if (iconName.startsWith('fas ')) {
        inlineStyle = 'font-family: "Font Awesome 6 Free" !important; font-weight: 900 !important; font-size: 2rem !important;';
        iconHtml = `<i class="${iconName}" style="${inlineStyle}"></i>`;
    } else if (iconName.startsWith('flaticon-')) {
        inlineStyle = 'font-family: Arial, sans-serif !important; font-size: 2rem !important;';
        iconHtml = `<i class="${iconName}" style="${inlineStyle}"></i>`;
    } else if (iconName.startsWith('material-icons')) {
        const iconClass = iconName.split(' ')[0];
        const iconText = iconName.split(' ')[1] || 'circle';
        inlineStyle = 'font-family: "Material Icons" !important; font-size: 2rem !important;';
        iconHtml = `<span class="${iconClass}" style="${inlineStyle}">${iconText}</span>`;
    }
    
    iconCard.innerHTML = `
        <div class="icon-preview">${iconHtml}</div>
        <div class="icon-name">${iconName}</div>
    `;
    
    iconCard.addEventListener('click', function() {
        toggleIconSelection(iconName);
    });
    
    col.appendChild(iconCard);
    return col;
}

// Función para alternar selección de icono
function toggleIconSelection(iconName) {
    const index = selectedIcons.indexOf(iconName);
    
    if (index === -1) {
        selectedIcons.push(iconName);
    } else {
        selectedIcons.splice(index, 1);
    }
    
    updateIconCard(iconName);
    updateSelectedIconsGrid();
    updateSelectedCount();
}

// Función para actualizar la apariencia de la tarjeta de icono
function updateIconCard(iconName) {
    const iconCard = document.querySelector(`.icon-card[data-icon="${iconName}"]`);
    if (iconCard) {
        if (selectedIcons.includes(iconName)) {
            iconCard.classList.add('selected');
        } else {
            iconCard.classList.remove('selected');
        }
    }
}

// Función para actualizar la grid de iconos seleccionados
function updateSelectedIconsGrid() {
    const selectedIconsGrid = document.getElementById('selectedIconsGrid');
    const selectedSection = document.querySelector('.selected-icons-section');
    
    selectedIconsGrid.innerHTML = '';
    
    if (selectedIcons.length > 0) {
        selectedSection.style.display = 'block';
        
        selectedIcons.forEach(iconName => {
            const col = document.createElement('div');
            col.className = 'col-xl-3 col-lg-4 col-md-6 col-sm-12';
            
            let iconHtml = '';
            let inlineStyle = '';
            
            if (iconName.startsWith('bi-')) {
                inlineStyle = 'font-family: "Bootstrap Icons" !important;';
                iconHtml = `<i class="bi ${iconName}" style="${inlineStyle}"></i>`;
            } else if (iconName.startsWith('fas ')) {
                inlineStyle = 'font-family: "Font Awesome 6 Free" !important; font-weight: 900 !important;';
                iconHtml = `<i class="${iconName}" style="${inlineStyle}"></i>`;
            } else if (iconName.startsWith('flaticon-')) {
                inlineStyle = 'font-family: Arial, sans-serif !important;';
                iconHtml = `<i class="${iconName}" style="${inlineStyle}"></i>`;
            } else if (iconName.startsWith('material-icons')) {
                const iconClass = iconName.split(' ')[0];
                const iconText = iconName.split(' ')[1] || 'circle';
                inlineStyle = 'font-family: "Material Icons" !important;';
                iconHtml = `<span class="${iconClass}" style="${inlineStyle}">${iconText}</span>`;
            }
            
            col.innerHTML = `
                <div class="selected-icon-item">
                    <div class="icon-preview">${iconHtml}</div>
                    <div class="icon-name">${iconName}</div>
                    <button class="btn btn-sm btn-outline-danger mt-2 remove-icon" data-icon="${iconName}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            
            selectedIconsGrid.appendChild(col);
        });
        
        document.querySelectorAll('.remove-icon').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const iconToRemove = this.getAttribute('data-icon');
                toggleIconSelection(iconToRemove);
            });
        });
    } else {
        selectedSection.style.display = 'none';
    }
}

// Función para actualizar el contador de seleccionados
function updateSelectedCount() {
    const selectedCount = document.getElementById('selectedCount');
    selectedCount.textContent = selectedIcons.length;
}

// Función para generar paginación
function generatePagination(totalPages, currentPage) {
    const pagination = document.getElementById('iconPagination');
    pagination.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">Anterior</a>`;
    pagination.appendChild(prevLi);
    
    for (let i = 1; i <= totalPages; i++) {
        const pageLi = document.createElement('li');
        pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
        pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        pagination.appendChild(pageLi);
    }
    
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Siguiente</a>`;
    pagination.appendChild(nextLi);
    
    pagination.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            if (page && page !== currentPage) {
                loadIconsGrid(page);
            }
        });
    });
}

// =============================================
// APLICACIÓN DE ICONOS - CÓDIGO MEJORADO
// =============================================

// Función mejorada para reemplazar iconos SIN recargar el editor
function replaceIconSmoothly(selected, newIconClass) {
    const editor = grapesjs.editors[0];
    
    console.log('🎯 REEMPLAZANDO ICONO:', newIconClass);
    
    try {
        // Obtener el componente actual
        const component = selected;
        
        // Agregar estilos inline según el tipo de icono
        let inlineStyle = '';
        if (newIconClass.startsWith('bi ')) {
            inlineStyle = 'font-family: "Bootstrap Icons" !important;';
        } else if (newIconClass.startsWith('fas ')) {
            inlineStyle = 'font-family: "Font Awesome 6 Free" !important; font-weight: 900 !important;';
        } else if (newIconClass.startsWith('flaticon-')) {
            inlineStyle = 'font-family: Arial, sans-serif !important;';
        } else if (newIconClass.startsWith('material-icons')) {
            inlineStyle = 'font-family: "Material Icons" !important;';
        }
        
        // Actualizar el componente existente en lugar de reemplazarlo completamente
        if (newIconClass.startsWith('material-icons')) {
            // Para Material Icons usar span
            component.set({
                tagName: 'span',
                type: 'text',
                classes: newIconClass.split(' '),
                attributes: {
                    'style': inlineStyle,
                    'aria-hidden': 'true'
                },
                content: newIconClass.split(' ')[1] || 'circle'
            });
        } else {
            // Para otras librerías usar i
            component.set({
                tagName: 'i',
                type: 'text',
                classes: newIconClass.split(' '),
                attributes: {
                    'style': inlineStyle,
                    'aria-hidden': 'true'
                },
                content: ''
            });
        }
        
        console.log('✅ Componente actualizado sin recargar');
        
        // Usar métodos más suaves para actualizar
        component.emitUpdate();
        
        // Actualizar solo la vista del componente sin recargar todo
        if (component.view && component.view.render) {
            component.view.render();
        }
        
        // Forzar una actualización visual mínima
        setTimeout(() => {
            if (editor.Canvas && editor.Canvas.getWindow()) {
                const iframe = editor.Canvas.getWindow();
                if (iframe.document && iframe.document.body) {
                    // Solo disparar eventos de actualización sin recarga completa
                    editor.trigger('component:update', component);
                    editor.trigger('canvas:update');
                }
            }
        }, 50);
        
        return true;
        
    } catch (error) {
        console.error('❌ Error al actualizar icono:', error);
        return false;
    }
}

// Función principal mejorada para aplicar iconos automáticamente
document.getElementById('applyIconsBtn').addEventListener('click', function() {
    if (selectedIcons.length === 0) {
        alert('Por favor selecciona al menos un icono.');
        return;
    }
    
    const editor = grapesjs.editors[0];
    const selected = editor.getSelected();
    
    if (!selected) {
        alert('Por favor selecciona un componente en el editor primero.');
        return;
    }
    
    const iconName = selectedIcons[0];
    const newClass = getIconClass(iconName);
    
    console.log('🔍 APLICANDO ICONO AUTOMÁTICAMENTE:', iconName);
    
    // Usar la función mejorada que no recarga el editor
    if (replaceIconSmoothly(selected, newClass)) {
        showStatus(`✅ Icono aplicado: ${iconName}`, 'success');
        
        // Remover el icono aplicado de la lista
        selectedIcons.shift();
        updateSelectedIconsGrid();
        updateSelectedCount();
        
        // Si aún quedan iconos, mantener el modal abierto
        if (selectedIcons.length === 0) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('iconSelectorModal'));
            modal.hide();
        }
    } else {
        showStatus('❌ Error al aplicar el icono', 'error');
    }
});

// Modo manual garantizado (también mejorado)
document.getElementById('manualApplyBtn').addEventListener('click', function() {
    if (selectedIcons.length === 0) {
        alert('Por favor selecciona al menos un icono primero.');
        return;
    }
    
    const editor = grapesjs.editors[0];
    const modal = bootstrap.Modal.getInstance(document.getElementById('iconSelectorModal'));
    
    // Limpiar event listeners previos
    editor.off('component:selected.manualIcons');
    
    let currentIconIndex = 0;
    
    function applyIconManually(selectedElement) {
        if (currentIconIndex < selectedIcons.length) {
            const iconName = selectedIcons[currentIconIndex];
            const newClass = getIconClass(iconName);
            
            console.log(`🖱️ Aplicando manualmente: ${iconName}`);
            
            // Usar la función mejorada
            if (replaceIconSmoothly(selectedElement, newClass)) {
                currentIconIndex++;
                updateSelectedIconsGrid();
                updateSelectedCount();
                
                const remaining = selectedIcons.length - currentIconIndex;
                if (remaining > 0) {
                    showStatus(`✅ ${iconName} aplicado. Selecciona el siguiente elemento (${remaining} restantes)`, 'info');
                    // Usar namespace para evitar conflictos
                    editor.once('component:selected.manualIcons', applyIconManually);
                } else {
                    showStatus('🎉 Todos los iconos aplicados correctamente', 'success');
                    modal.hide();
                }
            } else {
                showStatus('❌ Error al aplicar icono manualmente', 'error');
            }
        }
    }
    
    showStatus(`🖱️ Modo manual: Selecciona el primer elemento para aplicar "${selectedIcons[0]}"`, 'info');
    editor.once('component:selected.manualIcons', applyIconManually);
});

// =============================================
// FUNCIONES AUXILIARES
// =============================================

// Función para obtener la clase del icono
function getIconClass(iconName) {
    if (iconName.startsWith('bi-')) {
        return `bi ${iconName}`;
    } else if (iconName.startsWith('fas ')) {
        return iconName;
    } else if (iconName.startsWith('flaticon-')) {
        return iconName;
    } else if (iconName.startsWith('material-icons')) {
        return iconName;
    }
    return iconName;
}

// Función para copiar código
document.getElementById('copyIconsBtn').addEventListener('click', function() {
    if (selectedIcons.length === 0) {
        alert('No hay iconos seleccionados para copiar.');
        return;
    }
    
    const iconCode = selectedIcons.map(iconName => {
        return `<i class="${getIconClass(iconName)}"></i>`;
    }).join('\n');
    
    navigator.clipboard.writeText(iconCode).then(() => {
        showStatus('Código de iconos copiado al portapapeles', 'success');
    }).catch(err => {
        showStatus('Error al copiar el código', 'error');
    });
});

// Event listeners para búsqueda
document.getElementById('iconSearch').addEventListener('input', function() {
    loadIconsGrid(1);
});

document.getElementById('clearIconSearch').addEventListener('click', function() {
    document.getElementById('iconSearch').value = '';
    loadIconsGrid(1);
});

document.getElementById('iconLibrary').addEventListener('change', function() {
    loadIconsGrid(1);
});

// Función para mostrar estado
function showStatus(message, type) {
    // Crear elemento de estado si no existe
    let statusEl = document.getElementById('status-message');
    if (!statusEl) {
        statusEl = document.createElement('div');
        statusEl.id = 'status-message';
        statusEl.style.position = 'fixed';
        statusEl.style.top = '20px';
        statusEl.style.right = '20px';
        statusEl.style.zIndex = '9999';
        statusEl.style.padding = '12px 16px';
        statusEl.style.borderRadius = '6px';
        statusEl.style.fontWeight = '500';
        statusEl.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        statusEl.style.maxWidth = '300px';
        statusEl.style.wordWrap = 'break-word';
        document.body.appendChild(statusEl);
    }
    
    statusEl.textContent = message;
    statusEl.className = `status-message status-${type}`;
    
    // Aplicar estilos según el tipo
    if (type === 'success') {
        statusEl.style.backgroundColor = '#d4edda';
        statusEl.style.color = '#155724';
        statusEl.style.border = '1px solid #c3e6cb';
    } else if (type === 'error') {
        statusEl.style.backgroundColor = '#f8d7da';
        statusEl.style.color = '#721c24';
        statusEl.style.border = '1px solid #f5c6cb';
    } else if (type === 'info') {
        statusEl.style.backgroundColor = '#d1ecf1';
        statusEl.style.color = '#0c5460';
        statusEl.style.border = '1px solid #bee5eb';
    }
    
    statusEl.style.display = 'block';
    
    setTimeout(() => {
        statusEl.style.display = 'none';
    }, 3000);
}

// =============================================
// INICIALIZACIÓN
// =============================================

// Inicialización del modal
document.getElementById('iconSelectorModal').addEventListener('show.bs.modal', function() {
    selectedIcons = [];
    loadIconsGrid(1);
    
    // ✅ CARGAR LIBRERÍAS AUTOMÁTICAMENTE
    loadIconLibrariesEverywhere();
});

// Limpiar al cerrar
document.getElementById('iconSelectorModal').addEventListener('hide.bs.modal', function() {
    const editor = grapesjs.editors[0];
    // Limpiar solo los event listeners de iconos
    editor.off('component:selected.manualIcons');
});
</script>

  @include($web->template . '.assets-web.assets-grapejs')
</body>
</html>

