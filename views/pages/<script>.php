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
        upload: '{{ route("images.upload") }}', // Tu URL de subida de imágenes
        uploadName: 'files',
        params: {
            _token: '{{ csrf_token() }}'
        },
        assetsMimeTypes: ['image/*'],
        assets: [], // Aquí se cargarán tus imágenes existentes

        // --- Configuración clave para añadir por URL ---
        // Este texto aparecerá en el Asset Manager y GrapesJS añadirá un campo para pegar URLs.
        uploadText: 'Arrastra y suelta aquí o haz clic para subir.<br>O pega la URL de una imagen aquí.',
    },
    });

    // Listener para cuando un asset es seleccionado en el Asset Manager
editor.on('asset:active', (asset) => {
    const urlDisplayDiv = document.getElementById('selected-image-url-display');
    const urlSpan = document.getElementById('current-image-url');
    const copyButton = document.getElementById('copy-image-url');

    // Asegúrate de que sea una imagen y que tenga una URL
    if (asset && asset.get('src') && asset.get('type') === 'image') {
        const imageUrl = asset.get('src');
        urlSpan.textContent = imageUrl;
        urlDisplayDiv.style.display = 'block'; // Mostrar el div

        // Funcionalidad para copiar la URL al portapapeles
        copyButton.onclick = () => {
            navigator.clipboard.writeText(imageUrl).then(() => {
                showStatus('URL copiada al portapapeles', 'success');
            }).catch(err => {
                showStatus('Error al copiar la URL', 'error');
                console.error('Failed to copy URL: ', err);
            });
        };
    } else {
        // Ocultar el div si no hay imagen seleccionada o no es una imagen
        urlSpan.textContent = '';
        urlDisplayDiv.style.display = 'none';
        copyButton.onclick = null; // Limpiar el evento de clic
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
                    // Force a re-render of the component view
                    selectInput.view.render();
                    console.log('Re-renderizando select-input:', selectInput.get('name'));
                });
            }
        });
        // Cargar imágenes existentes cuando el editor se carga
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
        // *** CAMBIO CLAVE AQUÍ: AÑADIMOS EXPLÍCITAMENTE LOS ASSETS ***
        editor.AssetManager.add(uploadedAssets); // Añade los assets parseados al Asset Manager
        // ************************************************************

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
    // GrapesJS error object might be different, check its structure
    // from the console. If it's a server response error, it might be nested.
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
            // Nota: Con la nueva implementación, AssetManager cargará las imágenes de forma asíncrona
            // si son parte de los assets del editor. Sin embargo, para imágenes existentes en el servidor,
            // usaremos loadExistingImages(). Los assets específicos de la plantilla se pueden añadir aquí si es necesario.
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
        // Recorre recursivamente todos los componentes del wrapper
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
        // Limpiar componentes, estilos y scripts
        editor.DomComponents.clear();
        editor.CssComposer.clear();

        // Eliminar assets del AssetManager (solo del editor, no del servidor)
        const allAssets = editor.AssetManager.getAll();
        allAssets.forEach(asset => editor.AssetManager.remove(asset));

     
        showStatus('Editor limpiado completamente', 'success');
        console.log('Editor reseteado: componentes, estilos, scripts y assets eliminados.');
    }
}

document.getElementById('clear-btn').addEventListener('click', clearEditorContent);


    // Función para cargar scripts guardados
    function loadScripts(scripts) {
        if (!scripts || !scripts.length) return;

        scripts.forEach(scriptData => {
            try {
                if (scriptData.type === 'inline') {
                    // Crear elemento script en el DOM
                    const script = document.createElement('script');
                    if (scriptData.attributes) {
                        Object.entries(scriptData.attributes).forEach(([name, value]) => {
                            script.setAttribute(name, value);
                        });
                    }
                    script.textContent = scriptData.content;
                    document.body.appendChild(script);
                } else if (scriptData.type === 'component') {
                    // Buscar componentes del mismo tipo y asignar el script reconstruido
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

        // UI Feedback
        const saveBtn = document.getElementById('save-btn');
        const originalBtnText = saveBtn.textContent;
        saveBtn.textContent = 'Guardando...';
        saveBtn.disabled = true;
        showStatus('Guardando cambios...', 'saving');

        try {
            // Obtener nombre de la plantilla
            const name = prompt('Nombre de la plantilla:', `Plantilla_${new Date().toLocaleDateString('es')}`);
            if (!name) throw new Error('Nombre requerido');

            // Recopilar todos los datos del editor
            const templateData = {
                name: name,
                content: editor.getComponents(),
                styles: editor.getStyle(),
                scripts: getEditorScripts(), // Función unificada
                assets: editor.AssetManager.getAll().map(asset => ({ src: asset.get('src'), type: asset.get('type') })), // Guardar solo src y type de los assets
                id: currentTemplateId || null
            };

            // Enviar datos al servidor
            const response = await fetch('{{ route("templates.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // 🚨 FALTA EN TU CÓDIGO ACTUAL
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
        // Actualizar ID si es nueva plantilla
        if (!currentTemplateId) {
            currentTemplateId = data.id;
            window.history.pushState({}, '', `?load=${data.id}`);
            document.querySelector('.template-info').textContent = `Editando Plantilla ID: ${data.id}`;
            document.getElementById('save-btn').textContent = 'Actualizar';
        }

        // Configurar vista previa
        const previewBtn = document.getElementById('preview-btn');
        previewBtn.onclick = () => window.open(data.preview_url, '_blank');
        previewBtn.disabled = false;

        showStatus('Cambios guardados correctamente', 'success');
        console.log('Plantilla guardada:', data);

        // Opcional: Actualizar lista de plantillas si existe
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
            // Asegúrate de que las imágenes se añadan al Asset Manager con el formato correcto
            // GrapesJS espera un array de objetos con al menos la propiedad `src`
            const formattedImages = images.map(img => ({
                src: img.url, // Asume que tu backend devuelve una URL completa
                // Puedes añadir otras propiedades si tu backend las proporciona,
                // como `width`, `height`, `name`, `id` para futuras operaciones
                name: img.name || img.url.split('/').pop(),
                // Si quieres que las imágenes sean eliminables desde el gestor de assets de GrapesJS,
                // necesitas el ID y configurar el parámetro `openAssetsOnCreate` a `false` en GrapesJS
                // para que GrapesJS no maneje el ID de forma automática, y lo gestiones tú.
                // Para simplificar, asumiremos que la eliminación se hará por URL.
                // Si deseas eliminar por ID, tendrías que enviar el ID en la petición de eliminación
                // y asegurarte que tu backend lo utiliza.
            }));

            editor.AssetManager.add(formattedImages);
            console.log('Imágenes existentes cargadas:', formattedImages);

            // Una vez que las imágenes están en el AssetManager, puedes habilitar la eliminación.
            // GrapesJS Asset Manager tiene un evento 'asset:remove' que puedes escuchar.
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
                            body: JSON.stringify({ url: imageUrl }) // Envía la URL para identificar la imagen a eliminar
                        });

                        if (!deleteResponse.ok) {
                            const errorData = await deleteResponse.json();
                            throw new Error(errorData.message || 'Error al eliminar la imagen en el servidor.');
                        }

                        const result = await deleteResponse.json();
                        if (result.success) {
                            showStatus('Imagen eliminada correctamente', 'success');
                            // Si se eliminó del servidor, no necesitas hacer nada más con el AssetManager,
                            // ya que GrapesJS ya la habrá eliminado de su lista al disparar el evento 'asset:remove'.
                        } else {
                            // Si el servidor indica que no se eliminó, puedes añadirla de nuevo al asset manager
                            // o mostrar un error y no eliminarla visualmente.
                            editor.AssetManager.add(asset); // Re-añadir si la eliminación falla en el servidor
                            showStatus(result.message || 'Error al eliminar la imagen.', 'error');
                        }
                    } catch (error) {
                        console.error('Error al eliminar imagen:', error);
                        showStatus('Error al eliminar imagen: ' + error.message, 'error');
                        editor.AssetManager.add(asset); // Re-añadir si hay un error en la comunicación
                    }
                } else {
                    // Si el usuario cancela la eliminación, re-añade la imagen al AssetManager
                    editor.AssetManager.add(asset);
                }
            });

        } catch (error) {
            console.error('Error al cargar imágenes existentes:', error);
            showStatus('Error al cargar imágenes existentes', 'error');
        }
    }


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


    editor.on('load', () => {
        loadBladeComponents();

    });


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
    } catch (err) {
        console.error('Error cargando componentes guardados:', err);
    }
}

async function loadBladeComponents() {
    try {
        const response = await fetch('/sd/grape-components');
        const components = await response.json();
        components.forEach(comp => {
            editor.BlockManager.add(comp.id, {
                label: comp.label,
                content: comp.content,
                category: "Componentes Laravel",
                attributes: { class: 'fa fa-cube' },
            });
        });
    } catch (error) {
        console.error('Error al cargar componentes Blade:', error);
    }
}

// Guardar componente personalizado




// Listeners generales

document.getElementById('save-btn').addEventListener('click', saveTemplate);
document.getElementById('clear-btn').addEventListener('click', clearEditorContent);



</script>
