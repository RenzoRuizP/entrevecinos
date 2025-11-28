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
}
</style>
