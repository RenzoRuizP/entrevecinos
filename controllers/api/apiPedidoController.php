<?php
// controllers/api/apiPedidoController.php

class apiPedidoController
{
    /**
     * Listar pedidos que debe ver el vecino conectado.
     * Por ahora es un STUB con datos de ejemplo.
     * Más adelante lo conectas a tu modelo / BD.
     */
    public function listarPedidos()
    {
        // Ejemplo de respuesta con 1 pedido para probar el estado "conectado con pedido"
        $pedidosDemo = [
            [
                'id_pedido'          => 1,
                'titulo_publicacion' => 'Pizza familiar entre vecinos',
                'nombre_vecino'      => 'Carlos del 304',
                'torre'              => 'Torre A',
                'departamento'       => 'Dpto. 304',
                'fecha_hora'         => date('d/m/Y H:i'),
                'monto_total'        => '35.00'
            ]
        ];

        echo json_encode([
            'ok'      => true,
            'pedidos' => $pedidosDemo
        ]);
    }
}
