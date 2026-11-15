<?php
require_once 'conexion.php';

$mensaje = "";

// 1. LÓGICA DE REGISTRO: Crear un nuevo usuario publicador
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'crear_usuario') {
    $nombre = trim($_POST['nombre_autor'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    
    if (!empty($nombre) && !empty($email)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Autores (nombre_autor, email, telefono, id_roles) VALUES (:nombre, :email, :telefono, 2)");
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':telefono' => $telefono
            ]);
            $mensaje = "<div style='color: green; font-weight: bold;'>✔️ Publicador registrado con éxito.</div>";
        } catch (PDOException $e) {
            $mensaje = "<div style='color: red;'>❌ El Email ya está registrado por otro usuario.</div>";
        }
    }
}

// 2. LÓGICA DE ELIMINACIÓN: Borrar un usuario publicador
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'borrar_usuario') {
    $id_autores = intval($_POST['id_autores']);
    try {
        $stmt = $pdo->prepare("DELETE FROM Autores WHERE id_autores = :id AND id_roles = 2");
        $stmt->execute([':id' => $id_autores]);
        $mensaje = "<div style='color: green; font-weight: bold;'>✔️ Usuario eliminado del sistema.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div style='color: red;'>❌ No se pudo eliminar el usuario (tiene restricciones activas).</div>";
    }
}

// 3. LÓGICA DE MODERACIÓN: Borrar cualquier aviso de forma global
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'borrar_aviso') {
    $id_avisos = intval($_POST['id_avisos']);
    $stmt = $pdo->prepare("DELETE FROM Avisos WHERE id_avisos = :id");
    $stmt->execute([':id' => $id_avisos]);
    $mensaje = "<div style='color: green; font-weight: bold;'>✔️ El aviso fue retirado de los clasificados.</div>";
}

// OBTENER DATOS ACTUALIZADOS PARA MOSTRAR EN LAS TABLAS
$publicadores = $pdo->query("SELECT * FROM Autores WHERE id_roles = 2")->fetchAll(PDO::FETCH_ASSOC);
$avisos = $pdo->query("SELECT a.id_avisos, a.titulo, c.nombre_categoria, aut.nombre_autor 
                       FROM Avisos a 
                       INNER JOIN Categorias c ON a.id_categorias = c.id_categorias
                       INNER JOIN Autores aut ON a.id_autores = aut.id_autores")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .box { max-width: 800px; margin: 0 auto 30px auto; background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .campo { width: 100%; padding: 8px; margin-bottom: 12px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background: #34495e; color: white; }
        .btn-add { background: #2ecc71; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-del { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="box">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>⚙️ Panel de Control del Administrador</h2>
            <a href="clasificados.php" style="text-decoration: none; color: #3498db; font-weight: bold;">← Ver Clasificados</a>
        </div>
        <?php echo $mensaje; ?>
    </div>

    <!-- SECCIÓN: ALTA DE USUARIOS PUBLICADORES -->
    <div class="box">
        <h3>➕ Registrar un Nuevo Usuario Publicador</h3>
        <form action="admin.php" method="POST">
            <input type="hidden" name="action" value="crear_usuario">
            
            <label>Nombre Completo:</label>
            <input type="text" name="nombre_autor" class="campo" required placeholder="Ej: Juan Pérez">
            
            <label>Correo Electrónico:</label>
            <input type="email" name="email" class="campo" required placeholder="Ej: juan@correo.com">
            
            <label>Teléfono (Opcional):</label>
            <input type="text" name="telefono" class="campo" placeholder="Ej: 099123456">
            
            <button type="submit" class="btn-add">Registrar Usuario</button>
        </form>
    </div>

    <!-- SECCIÓN: TABLA DE PUBLICADORES REGISTRADOS -->
    <div class="box">
        <h3>👥 Usuarios Publicadores Registrados</h3>
        <?php if (empty($publicadores)): ?>
            <p>No hay publicadores registrados en el sistema.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($publicadores as $pub): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pub['nombre_autor']); ?></td>
                            <td><?php echo htmlspecialchars($pub['email']); ?></td>
                            <td><?php echo htmlspecialchars($pub['telefono'] ?? '-'); ?></td>
                            <td>
                                <form action="admin.php" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este publicador?');">
                                    <input type="hidden" name="action" value="borrar_usuario">
                                    <input type="hidden" name="id_autores" value="<?php echo $pub['id_autores']; ?>">
                                    <button type="submit" class="btn-del">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- SECCIÓN: MODERACIÓN GLOBAL DE AVISOS -->
    <div class="box">
        <h3>🛡️ Moderación de la Cartelera (Baja de Cualquier Aviso)</h3>
        <?php if (empty($avisos)): ?>
            <p>No hay avisos clasificados publicados.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Título de la Publicación</th>
                        <th>Categoría</th>
                        <th>Autor Original</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avisos as $av): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($av['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($av['nombre_categoria']); ?></td>
                            <td><?php echo htmlspecialchars($av['nombre_autor']); ?></td>
                            <td>
                                <form action="admin.php" method="POST" onsubmit="return confirm('¿Quieres dar de baja este aviso definitivamente?');">
                                    <input type="hidden" name="action" value="borrar_aviso">
                                    <input type="hidden" name="id_avisos" value="<?php echo $av['id_avisos']; ?>">
                                    <button type="submit" class="btn-del">Retirar Aviso</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>