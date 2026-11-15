<?php
session_start();
require_once 'conexion.php';

// Forzamos el autor al ID 1 para que use el usuario 'Carlos Gómez' que ya creaste en tu base de datos
$_SESSION['id_autores'] = 1; 

$mensaje = "";

try {
    $stmt_cat = $pdo->query("SELECT * FROM Categorias");
    $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias = [];
}

// Toda la lógica de guardado solo se ejecuta si el usuario presiona el botón "Guardar"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo'] ?? '');
    
    // Corregido: Ahora coincide exactamente con el name="id_categorias" de tu formulario
    $id_categorias = $_POST['id_categorias'] ?? ''; 
    
    // Corregido: Ahora coincide exactamente con el name="resaltado" de tu formulario
    $indicador_resaltado = isset($_POST['resaltado']) ? 1 : 0;
    $id_autores = $_SESSION['id_autores'];

    if (!empty($titulo) && !empty($id_categorias)) {
        try {
            $pdo->beginTransaction();

            $sqlAviso = "INSERT INTO Avisos (titulo, indicador_resaltado, id_categorias, id_autores) 
                         VALUES (:titulo, :indicador_resaltado, :id_categorias, :id_autores)";
            $stmtAviso = $pdo->prepare($sqlAviso);
            $stmtAviso->bindParam(':titulo', $titulo);
            $stmtAviso->bindParam(':indicador_resaltado', $indicador_resaltado);
            $stmtAviso->bindParam(':id_categorias', $id_categorias);
            $stmtAviso->bindParam(':id_autores', $id_autores);
            $stmtAviso->execute();
            
            $id_avisos = $pdo->lastInsertId();

            if ($id_categorias == 1) { // Laboral
                $empresa = trim($_POST['empresa'] ?? '');
                $cargo = trim($_POST['cargo'] ?? '');
                $sueldo = !empty($_POST['sueldo_mensual']) ? $_POST['sueldo_mensual'] : null;

                $sqlLab = "INSERT INTO Avisos_Laborales (id_avisos, empresa_contrata, descripcion_cargo, sueldo_mensual) 
                           VALUES (:id_avisos, :empresa, :cargo, :sueldo)";
                $stmtLab = $pdo->prepare($sqlLab);
                $stmtLab->bindParam(':id_avisos', $id_avisos);
                $stmtLab->bindParam(':empresa', $empresa);
                $stmtLab->bindParam(':cargo', $cargo);
                $stmtLab->bindParam(':sueldo', $sueldo);
                $stmtLab->execute();

            } elseif ($id_categorias == 2) { // Producto
                $desc_prod = trim($_POST['descripcion_producto'] ?? '');
                $precio_prod = $_POST['precio_producto'] ?? 0;

                $sqlProd = "INSERT INTO Avisos_Productos (id_avisos, descripcion_producto, precio_producto) 
                            VALUES (:id_avisos, :desc_prod, :precio_prod)";
                $stmtProd = $pdo->prepare($sqlProd);
                $stmtProd->bindParam(':id_avisos', $id_avisos);
                $stmtProd->bindParam(':desc_prod', $desc_prod);
                $stmtProd->bindParam(':precio_prod', $precio_prod);
                $stmtProd->execute();

            } elseif ($id_categorias == 3) { // Inmobiliario
                $tipo_inm = trim($_POST['tipo_inmueble'] ?? '');
                $ubicacion = trim($_POST['ubicacion_inmueble'] ?? '');
                $superficie = $_POST['superficie_inmueble'] ?? 0;
                $precio_inm = $_POST['precio_inmueble'] ?? 0;

                $sqlInm = "INSERT INTO Avisos_Inmobiliarios (id_avisos, tipo_inmueble, ubicacion_inmueble, superficie_inmueble, precio_inmueble) 
                           VALUES (:id_avisos, :tipo_inm, :ubicacion, :superficie, :precio_inm)";
                $stmtInm = $pdo->prepare($sqlInm);
                $stmtInm->bindParam(':id_avisos', $id_avisos);
                $stmtInm->bindParam(':tipo_inm', $tipo_inm);
                $stmtInm->bindParam(':ubicacion', $ubicacion);
                $stmtInm->bindParam(':superficie', $superficie);
                $stmtInm->bindParam(':precio_inm', $precio_inm);
                $stmtInm->execute();
            }

            $pdo->commit();
            $mensaje = "<div style='color: green; margin-bottom: 20px; font-weight: bold;'>¡Aviso guardado con éxito!</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje = "<div style='color: red; margin-bottom: 20px;'>Error al guardar: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensaje = "<div style='color: orange; margin-bottom: 20px;'>Por favor, complete todos los campos obligatorios.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicar Aviso</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .form-container { max-width: 500px; margin: 0 auto; background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .campo { width: 100%; padding: 8px; margin-bottom: 12px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .seccion-dinamica { display: none; background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 12px; border: 1px solid #ddd; }
        .btn-enviar { background: #2ecc71; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
    </style>
    <script>
        function actualizarCampos() {
            var seleccion = document.getElementById("id_categorias").value;
            document.getElementById("form_laboral").style.display = (seleccion == "1") ? "block" : "none";
            document.getElementById("form_producto").style.display = (seleccion == "2") ? "block" : "none";
            document.getElementById("form_inmobiliario").style.display = (seleccion == "3") ? "block" : "none";
        }
    </script>
</head>
<body>

    <div class="form-container">
        <h2>📢 Publicar Nuevo Aviso</h2>
        
        <?php echo $mensaje; ?>

        <form action="crear.php" method="POST">
            
            <label>Título del Aviso:</label>
            <input type="text" name="titulo" class="campo" required placeholder="Ej: Vendo Guitarra Eléctrica">

            <label>Categoría de Clasificado:</label>
            <select name="id_categorias" id="id_categorias" class="campo" onchange="actualizarCampos()" required>
                <option value="">-- Elige una opción --</option>
                <option value="1">Laboral</option>
                <option value="2">Producto</option>
                <option value="3">Inmobiliario</option>
            </select>

            <label>
                <input type="checkbox" name="resaltado" value="1"> <strong>¡Destacar publicación! (Paga el doble)</strong>
            </label>
            <br><br>

            <!-- Atributos específicos: Laboral -->
            <div id="form_laboral" class="seccion-dinamica">
                <strong>Campos Laborales:</strong><br><br>
                <label>Empresa:</label><input type="text" name="empresa" class="campo">
                <label>Cargo:</label><input type="text" name="cargo" class="campo">
                <label>Sueldo mensual (Opcional):</label><input type="number" step="0.01" name="sueldo_mensual" class="campo">
            </div>

            <!-- Atributos específicos: Producto -->
            <div id="form_producto" class="seccion-dinamica">
                <strong>Campos de Producto:</strong><br><br>
                <label>Descripción del artículo:</label><input type="text" name="descripcion_producto" class="campo">
                <label>Precio ($):</label><input type="number" step="0.01" name="precio_producto" class="campo">
            </div>

            <!-- Atributos específicos: Inmobiliario -->
            <div id="form_inmobiliario" class="seccion-dinamica">
                <strong>Campos Inmobiliarios:</strong><br><br>
                <label>Tipo Inmueble (Casa/Apto):</label><input type="text" name="tipo_inmueble" class="campo">
                <label>Ubicación:</label><input type="text" name="ubicacion_inmueble" class="campo">
                <label>Superficie (m²):</label><input type="number" step="0.01" name="superficie_inmueble" class="campo">
                <label>Precio ($):</label><input type="number" step="0.01" name="precio_inmueble" class="campo">
            </div>

            <button type="submit" class="btn-enviar">Guardar y Publicar</button>
        </form>
    </div>

</body>
</html>