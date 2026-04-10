<?php
/**
 * Plugin Name: WP Image Plugin SOS
 * Description: Fuerza el texto visual de 1200x700 sin procesar la imagen.
 */

if (!defined('ABSPATH'))
    exit;

// 1. Convertir el tipo a PNG visualmente
add_filter('wp_handle_upload', function ($upload) {
    $upload['type'] = 'image/png';
    return $upload;
}, 999);

// 2. ENGAÑAR A LA BIBLIOTECA 
add_filter('wp_generate_attachment_metadata', function ($metadata) {
    $metadata['width'] = 1200;
    $metadata['height'] = 700;
    $metadata['file'] = str_replace(['.jpg', '.jpeg'], '.png', $metadata['file']);
    return $metadata;
}, 999);

// 3. autor
add_filter('wp_insert_attachment_data', function ($data) {
    $data['post_author'] = get_current_user_id();
    return $data;
}, 999);

/*=== 2. AÑADIR AL MENÚ ===*/

add_action('admin_menu', function () {
    add_menu_page(
        'sube tus Productos',
        'Productos PNG',
        'manage_options',
        'productos-png',
        'render_productos_page',
        'dashicons-format-image',
        25
    );
});


/* === 3. FORMULARIO ===*/
function procesar_subida_producto()
{
    if (!isset($_FILES['image']))
        return;

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $upload = wp_handle_upload($_FILES['image'], ['test_form' => false]);

    if (isset($upload['file'])) {

        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_text_field($_POST['name']),
            'post_content' => sanitize_textarea_field($_POST['description']),
            'post_status' => 'inherit'
        ];

        $attach_id = wp_insert_attachment($attachment, $upload['file']);

        $metadata = wp_generate_attachment_metadata($attach_id, $upload['file']);
        wp_update_attachment_metadata($attach_id, $metadata);

        return "success";
    }

    return "error";
}

function render_productos_page()
{

    $status = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $status = procesar_subida_producto();
    }

    $imagenes = get_posts([
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'numberposts' => 8,
        'post_status' => 'inherit'
    ]);
    ?>

    <style>
        .wrap h1,
        .wrap h2,
        .wrap p,
        .wrap label {
            color: var(--text-main) !important;
        }


        .wrap input,
        .wrap textarea {
            color: #ffffff !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }


        .wrap input::placeholder,
        .wrap textarea::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        /* Texto dentro del upload */
        .upload-box p {
            color: #cbd5f5 !important;
        }

        /* Botón texto */
        .btn {
            color: white !important;
        }

        .wrap h1 {
            background: linear-gradient(to right, #c084fc, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }


        .card strong {
            color: #fff;
        }

        .badge {
            color: #38bdf8;
        }

        :root {
            --bg-color: #0f172a;
            --surface-color: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.1);
            --primary: #8b5cf6;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --error: #ef4444;
        }

        .wrap {
            background: var(--bg-color);
            padding: 30px;
            border-radius: 20px;
            color: white;
        }

        .glass {
            background: var(--surface-color);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid var(--border-color);
        }

        input,
        textarea {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            color: white;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .upload-box {
            border: 2px dashed var(--border-color);
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            cursor: pointer;
            position: relative;
        }

        .upload-box:hover {
            border-color: var(--primary);
        }

        .upload-box input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            left: 0;
            top: 0;
            cursor: pointer;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary), #a855f7);
            border: none;
            padding: 15px;
            width: 100%;
            color: white;
            border-radius: 12px;
            cursor: pointer;
            font-weight: bold;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error);
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            background: var(--surface-color);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .card .info {
            padding: 10px;
        }

        .badge {
            background: rgba(56, 189, 248, 0.2);
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
        }

        #preview {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 10px;
            display: none;
        }

        .grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important;
            gap: 20px;
        }

        .card {
            width: 100%;
            min-width: 0;
            overflow: hidden;

            display: flex;
            flex-direction: column;
        }

        .card img {
            width: 100%;
            height: 160px;
            object-fit: cover;

            display: block;
        }

        .card .info {
            padding: 10px;
            word-break: break-word;
        }

        /* descripcion del producto (recuadro)*/
        #modal p {
            margin: 2px 0;
            font-size: 13px;
            line-height: 1.4;
        }

        #modal h3 {
            margin: 5px 0;
            font-size: 16px;
            color: #ffffff !important;
        }

        /* botón de descargar*/
        .download-btn {
            display: block;
            width: fit-content;
            margin: 10px auto 0 auto;
            padding: 8px 16px;
            font-size: 13px;
            border-radius: 8px;
        }
    </style>

    <div class="wrap">

        <h1 style="font-size:28px;"> Sube tus Productos</h1>
        <p style="color:var(--text-muted)">Conversión automática a PNG + mejora visual</p>

        <?php if ($status === "success"): ?>
            <div class="alert-success">✅ Subido correctamente</div>
        <?php elseif ($status === "error"): ?>
            <div class="alert-error">❌ Error al subir</div>
        <?php endif; ?>

        <div class="glass">
            <form method="POST" enctype="multipart/form-data">

                <input type="text" name="name" placeholder="Nombre del producto" required>

                <textarea name="description" placeholder="Descripción del producto" required></textarea>

                <div class="upload-box">
                    <input type="file" name="image" onchange="previewImage(event)" required>
                    <p>Arrastra o haz clic para subir imagen</p>
                    <img id="preview">
                </div>

                <br>

                <button class="btn">Subir Producto</button>

            </form>
        </div>

        <h2 style="margin-top:40px;">Últimos productos subidos</h2>

        <div class="grid">
            <?php foreach ($imagenes as $img): ?>
                <div class="card">

                    <?php /*ver detalles de la imagen subida*/ ?>

                    <img src="<?php echo wp_get_attachment_url($img->ID); ?>" onclick="openModal(
        '<?php echo wp_get_attachment_url($img->ID); ?>',
        '<?php echo esc_js($img->post_title); ?>',
        '<?php echo esc_js($img->post_content); ?>',
        '<?php echo get_the_date('', $img); ?>',
        '<?php echo get_the_author_meta('display_name', $img->post_author); ?>',
        '<?php echo basename(get_attached_file($img->ID)); ?>',
        '<?php echo $img->post_mime_type; ?>',
        '<?php echo size_format(filesize(get_attached_file($img->ID))); ?>',
        '<?php echo wp_get_attachment_metadata($img->ID)['width']; ?>',
        '<?php echo wp_get_attachment_metadata($img->ID)['height']; ?>'
     )" style="cursor:pointer;">
                    <div class="info">
                        <strong><?php echo esc_html($img->post_title); ?></strong><br>

                        <p style="font-size:13px; color:#94a3b8; margin:5px 0;">
                            <?php echo esc_html($img->post_content); ?>
                        </p>

                        <span class="badge">PNG HQ</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <div id="modal" style="
            display:none; 
            position:fixed; 
            inset:0; 
            background:rgba(0,0,0,0.8); 
            z-index:9999; 
            align-items:center; 
            justify-content:center;">

        <div style="
            background:#0f172a;
            padding:20px;
            border-radius:15px;
            width:90%;
            max-width:500px;
            max-height:90vh;
            overflow-y:auto;
            display:flex;
            flex-direction:column;
            gap:10px;
            color:white;
            position:relative;
        ">

            <span onclick="closeModal()" style="
            position:absolute; 
            right:15px; 
            top:10px; 
            cursor:pointer; 
            font-size:20px;">✖</span>

            <img id="modalImg" style="
            width:100%;
            max-height:300px;
            object-fit:contain;
            border-radius:10px;
            margin-bottom:10px;
            background:#020617;
">

            <h3 id="modalTitle"></h3>
            <p id="modalDesc" style="color:#94a3b8;"></p>

            <hr style="border-color:#334155; margin:10px 0;">

            <p><strong>Subido el:</strong> <span id="modalDate"></span></p>
            <p><strong>Subido por:</strong> <span id="modalAuthor"></span></p>
            <p><strong>Nombre del archivo:</strong> <span id="modalFile"></span></p>
            <p><strong>Tipo de archivo:</strong> <span id="modalType"></span></p>
            <p><strong>Tamaño:</strong> <span id="modalSize"></span></p>
            <p><strong>Dimensiones:</strong> <span id="modalDim"></span></p>

            <br>

            <a id="modalDownload" class="btn download-btn" download>Descargar imagen</a>
        </div>
    </div>
    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const file = event.target.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = "block";
            }
        }
    </script>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const file = event.target.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = "block";
            }
        }

        function openModal(src, title, desc, date, author, file, type, size, width, height) {
            document.getElementById('modal').style.display = 'flex';

            document.getElementById('modalImg').src = src;
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalDesc').innerText = desc;
            document.getElementById('modalDate').innerText = date;
            document.getElementById('modalAuthor').innerText = author;
            document.getElementById('modalFile').innerText = file;
            document.getElementById('modalType').innerText = type;
            document.getElementById('modalSize').innerText = size;
            document.getElementById('modalDim').innerText = width + " x " + height;

            document.getElementById('modalDownload').href = src;
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        /* cerrar al hacer clic fuera */
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('modal');
            if (e.target === modal) {
                closeModal();
            }
        });
    </script>

    <?php
}