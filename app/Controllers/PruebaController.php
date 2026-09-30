<?php
/**
 * CONTROLADOR TEMPORAL solo para probar el Router (se borra en el Paso 8).
 */
class PruebaController
{
    // Se ejecuta con: /prueba  (o con la URL vacía, por ser el controlador por defecto)
    public function index(): void
    {
        echo '<h1>Router funcionando</h1>';
        echo '<p>Controlador: PruebaController | Método: index</p>';
    }

    // Se ejecuta con: /prueba/saludo/Maria
    public function saludo(string $nombre = 'visitante'): void
    {
        // htmlspecialchars: si alguien escribe HTML en la URL, se muestra como texto
        // y no se ejecuta (protección contra XSS).
        echo '<h1>Hola, ' . htmlspecialchars($nombre) . '</h1>';
    }
    // Se ejecuta con: /prueba/db  -> comprueba la conexión y la zona horaria
    public function db(): void
    {
        $pdo = Database::getConnection();

        $fila = $pdo->query('SELECT NOW() AS ahora')->fetch();

        echo '<h1>Conexión a la base de datos: OK</h1>';
        echo '<p>Hora según MySQL: ' . htmlspecialchars($fila['ahora']) . '</p>';
        echo '<p>Hora según PHP: ' . date('Y-m-d H:i:s') . '</p>';
    }

    // Se ejecuta con: /prueba/inyeccion  -> demuestra por qué protege la sentencia preparada
    public function inyeccion(): void
    {
        $pdo = Database::getConnection();

        // Lo que escribiría un atacante en un campo de texto
        $peligroso = "' OR '1'='1";

        // FORMA INSEGURA (solo la mostramos, NUNCA la ejecutamos):
        $consultaInsegura = "SELECT * FROM usuarios WHERE usuario = '" . $peligroso . "'";

        // FORMA SEGURA: consulta con un "hueco" y el dato enviado aparte
        $stmt = $pdo->prepare('SELECT :dato AS dato');
        $stmt->execute([':dato' => $peligroso]);
        $resultado = $stmt->fetch();

        echo '<h1>Prueba de inyección SQL</h1>';
        echo '<p><strong>Consulta insegura (pegando texto):</strong><br>'
            . htmlspecialchars($consultaInsegura) . '</p>';
        echo '<p>Fíjate: el texto del atacante cambió la lógica de la consulta.</p>';
        echo '<p><strong>Consulta preparada:</strong> el dato llegó como simple texto: <code>'
            . htmlspecialchars($resultado['dato']) . '</code></p>';
    }
    // Es private: el Router NO debe permitir ejecutarlo desde la URL
    private function secreto(): void
    {
        echo 'Esto nunca debería verse desde el navegador.';
    }
}