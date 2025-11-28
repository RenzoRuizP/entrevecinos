<?php /* billeteraEstilo.php – UX/UI Mi Billetera Entre Vecinos */ ?>

<style>
:root{
  --ev-verde-oscuro:#0F592F;
  --ev-verde:#198754;
  --ev-verde-suave:#E6F4EC;
  --ev-gris-fondo:#F5F7FA;
  --ev-gris-borde:#E3E8EF;
  --ev-texto:#1A1F36;
  --ev-texto-suave:#6B7280;
  --ev-rojo:#DC2626;
}

/* Fondo suave similar a Marketplace */
.ev-wallet-wrapper{
  background-color:var(--ev-gris-fondo);
}

/* Card principal */
.ev-wallet-card{
  border-radius:20px;
  border:1px solid var(--ev-gris-borde);
  background:#ffffff;
  box-shadow:0 6px 18px rgba(0,0,0,0.05);
}

.ev-wallet-card .card-body{
  padding:24px 32px 24px 32px;
}

.ev-wallet-title{
  font-size:28px;
  font-weight:700;
  color:var(--ev-verde-oscuro);
}

.ev-wallet-subtitle{
  font-size:14px;
  color:var(--ev-texto-suave);
}

/* Chip de saldo */
.ev-wallet-badge{
  padding:10px 16px;
  border-radius:16px;
  background:var(--ev-verde-suave);
  display:flex;
  flex-direction:column;
  min-width:210px;
}

.ev-wallet-badge-label{
  font-size:11px;
  text-transform:uppercase;
  letter-spacing:0.06em;
  color:var(--ev-texto-suave);
}

.ev-wallet-badge-amount{
  font-size:22px;
  font-weight:700;
  color:var(--ev-verde-oscuro);
}

/* Movimientos */
.ev-wallet-movimientos{
  margin-top:10px;
}

/* Tabla de movimientos */
.ev-wallet-table-wrapper{
  margin-top:4px;
}

.ev-wallet-table{
  font-size:0.9rem;
  border-color:var(--ev-gris-borde);
}

.ev-wallet-table thead th{
  border-bottom:1px solid var(--ev-gris-borde);
  font-weight:600;
  color:var(--ev-texto-suave);
}

.ev-wallet-table tbody tr td{
  border-bottom:1px solid #F3F4F6;
  padding-top:0.55rem;
  padding-bottom:0.55rem;
}

/* Concepto */
.ev-wallet-mov-concepto{
  display:flex;
  flex-direction:column;
}

.ev-wallet-mov-titulo{
  font-weight:600;
  color:var(--ev-texto);
}

.ev-wallet-mov-detalle{
  color:var(--ev-texto-suave);
}

/* Monto */
.ev-wallet-mov-monto{
  font-weight:600;
}

.ev-wallet-monto--credito{
  color:var(--ev-verde);
}

.ev-wallet-monto--debito{
  color:var(--ev-rojo);
}

/* Saldo después */
.ev-wallet-mov-saldo{
  font-size:0.85rem;
}

/* Responsive */
@media (max-width:991.98px){
  .ev-wallet-card .card-body{
    padding:20px 18px;
  }

  .ev-wallet-title{
    font-size:24px;
  }
}

@media (max-width:575.98px){
  .ev-wallet-badge{
    width:100%;
  }

  .ev-wallet-table thead{
    display:none; /* tabla tipo "listado" en móviles */
  }

  .ev-wallet-table tbody tr td{
    display:block;
    text-align:left !important;
  }

  .ev-wallet-table tbody tr td + td{
    margin-top:4px;
  }

  .ev-wallet-mov-monto,
  .ev-wallet-mov-saldo{
    display:inline-block;
  }
}
</style>
