<?php

require_once 'conexion.php';

session_start();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_autores'])) {
    $_SESSION['id_autores'] = 1; 
}
if (!isset($_SESSION['nombre_autor'])) {
    $_SESSION['nombre_autor'] = 'Carlos Gómez';
}

$id_publicador_actual = $_SESSION['id_autores'];
$nombre_publicador_actual = $_SESSION['nombre_autor'];
$mensaje = "";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'borrar_propio_aviso') {
    $id_avisos = intval($_POST['id_avisos']);
    
    $stmt_verificar = $pdo->prepare("SELECT id_avisos FROM Avisos WHERE id_avisos = :id_aviso AND id_autores = :id_autor");
    $stmt_verificar->execute([
        ':id_aviso' => $id_avisos,
        ':id_autor' => $id_publicador_actual
    ]);
    
    if ($stmt_verificar->fetch()) {
        $stmt_borrar = $pdo->prepare("DELETE FROM Avisos WHERE id_avisos = :id_aviso");
        $stmt_borrar->execute([':id_aviso' => $id_avisos]);
        $mensaje = "<div style='color: green; font-weight: bold;'>✔️ Tu aviso fue eliminado correctamente.</div>";
    } else {
        $mensaje = "<div style='color: red; font-weight: bold;'>❌ Error: No tenés permisos para borrar este aviso.</div>";
    }
}

$stmt_mis_avisos = $pdo->prepare("
    SELECT a.id_avisos, a.titulo, c.nombre_categoria 
    FROM Avisos a 
    INNER JOIN Categorias c ON a.id_categorias = c.id_categorias
    WHERE a.id_autores = :id_autor
");
$stmt_mis_avisos->execute([':id_autor' => $id_publicador_actual]);
$mis_avisos = $stmt_mis_avisos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Publicador</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .box { max-width: 800px; margin: 0 auto 30px auto; background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background: #34495e; color: white; }
        .btn-add { background: #2ecc71; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-mod { background: #3498db; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-right: 5px; }
        .btn-del { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="box">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>📝 Panel de Control: <?php echo htmlspecialchars($nombre_publicador_actual); ?></h2>
            <a href="crear.php" class="btn-add">+ Crear Nuevo Aviso</a>
        </div>
        <?php echo $mensaje; ?>
    </div>

    <div class="box">
        <h3>📋 Mis Avisos Publicados</h3>
        <?php if (empty($mis_avisos)): ?>
            <p style="color: #777;">No tenés avisos publicados todavía en el sistema.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Título de la Publicación</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mis_avisos as $av): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($av['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($av['nombre_categoria']); ?></td>
                            <td style="display: flex; align-items: center;">
                                <!-- Botón para modificar (enlazará al archivo de edición) -->
                                <a href="modificar.php?id=<?php echo $av['id_avisos']; ?>" class="btn-mod">Modificar</a>
                                
                                <!-- Formulario de borrado seguro mediante POST -->
                                <form action="publicador.php" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este aviso?');" style="margin: 0;">
                                    <input type="hidden" name="action" value="borrar_propio_aviso">
                                    <input type="hidden" name="id_avisos" value="<?php echo $av['id_avisos']; ?>">
                                    <button type="submit" class="btn-del">Borrar</button>
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