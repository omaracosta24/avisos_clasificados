<?php
require_once 'conexion.php';

// Consulta unificada para traer los avisos y sus extensiones dinámicas
$sql = "SELECT a.*, cat.nombre_categoria, aut.nombre_autor,
               p.descripcion_producto, p.precio_producto,
               l.empresa_contrata, l.descripcion_cargo, l.sueldo_mensual,
               i.tipo_inmueble, i.ubicacion_inmueble, i.superficie_inmueble, i.precio_inmueble
        FROM Avisos a
        INNER JOIN Categorias cat ON a.id_categorias = cat.id_categorias
        INNER JOIN Autores aut ON a.id_autores = aut.id_autores
        LEFT JOIN Avisos_Productos p ON a.id_avisos = p.id_avisos
        LEFT JOIN Avisos_Laborales l ON a.id_avisos = l.id_avisos
        LEFT JOIN Avisos_Inmobiliarios i ON a.id_avisos = i.id_avisos
        ORDER BY a.indicador_resaltado DESC, a.fecha_hora_creacion DESC";

try {
    $stmt = $pdo->query($sql);
    $avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $avisos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cartelera de Clasificados</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { background: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .card { background: white; padding: 20px; margin-bottom: 15px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #ccc; position: relative; }
        .destacado { border-left-color: #f1c40f; background: #fffdf0; }
        .badge { background: #f1c40f; color: #7f6000; font-size: 11px; font-weight: bold; padding: 3px 6px; border-radius: 3px; position: absolute; top: 15px; right: 15px; }
        .cat { font-size: 11px; font-weight: bold; color: #7f8c8d; text-transform: uppercase; }
        .detalles { background: #f9f9f9; padding: 12px; border-radius: 4px; margin: 10px 0; font-size: 14px; line-height: 1.5; }
        .autor { font-size: 12px; color: #95a5a6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📢 Avisos Clasificados Publicados</h1>
            <div>
                <a href="crear.php" class="btn" style="background: #2ecc71;">➕ Crear Aviso</a>
                <a href="admin.php" class="btn" style="background: #7f8c8d;">⚙️ Panel Admin</a>
            </div>
        </div>
        <hr><br>

        <?php if (empty($avisos)): ?>
            <p>No hay avisos clasificados publicados en este momento.</p>
        <?php else: ?>
            <?php foreach ($avisos as $av): ?>
                <div class="card <?php echo $av['indicador_resaltado'] ? 'destacado' : ''; ?>">
                    <?php if ($av['indicador_resaltado']): ?>
                        <span class="badge">⭐ DESTACADO</span>
                    <?php endif; ?>
                    <span class="cat"><?php echo htmlspecialchars($av['nombre_categoria']); ?></span>
                    <h3><?php echo htmlspecialchars($av['titulo']); ?></h3>
                    
                    <div class="detalles">
                        <?php if ($av['id_categorias'] == 1): ?>
                            <strong>Empresa:</strong> <?php echo htmlspecialchars($av['empresa_contrata']); ?><br>
                            <strong>Cargo:</strong> <?php echo htmlspecialchars($av['descripcion_cargo']); ?><br>
                            <strong>Sueldo:</strong> <?php echo $av['sueldo_mensual'] ? '$' . number_format($av['sueldo_mensual'], 2) : 'A convenir'; ?>
                        <?php elseif ($av['id_categorias'] == 2): ?>
                            <strong>Descripción del artículo:</strong> <?php echo htmlspecialchars($av['descripcion_producto']); ?><br>
                            <strong>Precio unitario:</strong> $<?php echo number_format($av['precio_producto'], 2); ?>
                        <?php elseif ($av['id_categorias'] == 3): ?>
                            <strong>Tipo de Inmueble:</strong> <?php echo htmlspecialchars($av['tipo_inmueble']); ?><br>
                            <strong>Ubicación:</strong> <?php echo htmlspecialchars($av['ubicacion_inmueble']); ?><br>
                            <strong>Superficie:</strong> <?php echo number_format($av['superficie_inmueble'], 2); ?> m²<br>
                            <strong>Precio:</strong> $<?php echo number_format($av['precio_inmueble'], 2); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="autor">Publicado por: <strong><?php echo htmlspecialchars($av['nombre_autor']); ?></strong></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>