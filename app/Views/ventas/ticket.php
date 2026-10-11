<h1>Ticket venta <?= $venta['id'] ?></h1>
<p id="ticketTotal"><?= $venta['total'] ?></p>
<p id="ticketMetodo"><?= $venta['metodo_pago'] ?></p>
<ul>
<?php foreach ($detalle as $d): ?>
<li><?= htmlspecialchars($d['producto_nombre']) ?> x<?= $d['cantidad'] ?></li>
<?php endforeach; ?>
</ul>