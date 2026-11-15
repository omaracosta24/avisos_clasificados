<?php
require_once 'conexion.php';

// Consulta unificada para traer los avisos con sus respectivas extensiones de tablas
$sql = "SELECT a.*, cat.nombre_categoria, aut.nombre_autor, aut.email,
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
    $avisos = $stmt->fetchAll();
} catch (PDOException $e) {
    $avisos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>📢 Sistema de Avisos Clasificados</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .nav-links a { background: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-left: 10px; }
        .nav-links a:hover { background: #2980b9; }
        .aviso-card { background: white; padding: 20px; border-radius: 6px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative; border-left: 5px solid #ccc; }
        .aviso-destacado { border-left: 5px solid #f1c40f; background: #fffdf0; }
        .badge-destacado { background: #f1c40f; color: #7f6000; padding: 3px 8px; font-size: 11px; font-weight: bold; border-radius: 3px; position: absolute; top: 15px; right: 15px; }
        .categoria-tag { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #7f8c8d; }
        h3 { margin: 5px 0 10px 0; color: #2c3e50; }
        .detalles { font-size: 14px; background: #f9f9f9; padding: 10px; border-radius: 4px; margin-top: 10px; line-height: 1.5; }
        .meta-info { font-size: 12px; color: #95a5a6; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📢 Cartelera de Avisos</h1>
            <div class="nav-links">
                <a href="crear.php" style="background: #2ecc71;">➕ Publicar Aviso</a>
            </div>
        </div>

        <?php if (empty($avisos)): ?>
            <p>No hay avisos clasificados publicados en este momento.</p>
        <?php else: ?>
            <?php foreach ($avisos as $aviso): ?>
                <div class="aviso-card <?php echo $aviso['indicador_resaltado'] ? 'aviso-destacado' : ''; ?>">
                    <?php if ($aviso['indicador_resaltado']): ?>
                        <span class="badge-destacado">⭐ DESTACADO</span>
                    <?php endif; ?>
                    
                    <span class="categoria-tag"><?php echo htmlspecialchars($aviso['nombre_categoria']); ?></span>
                    <h3><?php echo htmlspecialchars($aviso['titulo']); ?></h3>
                    
                    <div class="detalles">
                        <?php if ($aviso['id_categorias'] == 1): // Laboral ?>
                            <strong>Empresa:</strong> <?php echo htmlspecialchars($aviso['empresa_contrata']); ?><br>
                            <strong>Cargo:</strong> <?php echo htmlspecialchars($aviso['descripcion_cargo']); ?><br>
                            <strong>Sueldo:</strong> <?php echo $aviso['sueldo_mensual'] ? '$' . number_format($aviso['sueldo_mensual'], 2) : 'No especificado'; ?>
                        
                        <?php elseif ($aviso['id_categorias'] == 2): // Producto ?>
                            <strong>Descripción:</strong> <?php echo htmlspecialchars($aviso['descripcion_producto']); ?><br>
                            <strong>Precio original:</strong> $<?php echo number_format($aviso['precio_producto'], 2); ?><br>
                            <?php if ($aviso['indicador_resaltado']): ?>
                                <strong style="color: #d35400;">Precio Destacado (Paga el doble): $<?php echo number_format($aviso['precio_producto'] * 2, 2); ?></strong>
                            <?php endif; ?>

                        <?php elseif ($aviso['id_categorias'] == 3): // Inmobiliario ?>
                            <strong>Tipo:</strong> <?php echo htmlspecialchars($aviso['tipo_inmueble']); ?><br>
                            <strong>Ubicación:</strong> <?php echo htmlspecialchars($aviso['ubicacion_inmueble']); ?><br>
                            <strong>Superficie:</strong> <?php echo number_format($aviso['superficie_inmueble'], 2); ?> m²<br>
                            <strong>Precio original:</strong> $<?php echo number_format($aviso['precio_inmueble'], 2); ?><br>
                            <?php if ($aviso['indicador_resaltado']): ?>
                                <strong style="color: #d35400;">Precio Destacado (Paga el doble): $<?php echo number_format($aviso['precio_inmueble'] * 2, 2); ?></strong>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="meta-info">
                        Publicado por: <strong><?php echo htmlspecialchars($aviso['nombre_autor']); ?></strong> (<?php echo htmlspecialchars($aviso['email']); ?>) el <?php echo $aviso['fecha_hora_creacion']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>