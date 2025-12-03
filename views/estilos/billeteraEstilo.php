<?php /* billeteraEstilo.php – UX/UI Mi Billetera Entre Vecinos */ ?>

<style>
:root{
  /* Reutilizamos la paleta global de estilos.view.php */
  --ev-verde-oscuro: var(--verde-oscuro);
  --ev-verde:        var(--verde-claro);
  --ev-verde-suave:  #E6F4EC;
  --ev-gris-fondo:   var(--gris-claro);
  --ev-gris-borde:   var(--gris-borde);
  --ev-texto:        #1A1F36;
  --ev-texto-suave:  var(--gris-texto);
  --ev-rojo:         #DC2626;
}

/* Fondo suave alineado al dashboard */
.ev-wallet-wrapper{
  background-color: var(--ev-gris-fondo);
  max-width: 1200px;
  margin: 0 auto;
}

/* Card principal centrada */
.ev-wallet-card{
  border-radius: 18px;
  border: 1px solid var(--ev-gris-borde);
  background: #ffffff;
  box-shadow: 0 10px 30px rgba(15, 89, 47, 0.06);
  margin: 0 auto 2.5rem auto;
}

/* Aprovecha la base .card-body de Bootstrap */
.ev-wallet-card .card-body{
  padding: 24px 32px;
}

/* Header */
.ev-wallet-header{
  min-height: 56px;
}

/* Títulos alineados con el estándar */
.ev-wallet-title{
  font-size: 1.65rem;
  font-weight: 700;
  color: var(--ev-verde-oscuro);
}

.ev-wallet-subtitle{
  font-size: 0.92rem;
  color: var(--ev-texto-suave);
}

/* Chip de saldo */
.ev-wallet-badge{
  padding: 10px 16px;
  border-radius: 16px;
  background: var(--ev-verde-suave);
  display: flex;
  flex-direction: column;
  min-width: 210px;
  box-shadow: 0 6px 16px rgba(15, 89, 47, 0.12);
}

.ev-wallet-badge-label{
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--ev-texto-suave);
}

.ev-wallet-badge-amount{
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--ev-verde-oscuro);
}

/* Estado vacío */
.ev-wallet-empty{
  font-size: 0.95rem;
  padding: 24px 8px 6px 8px;
  border-radius: 12px;
  background-color: #ffffff;
}

.ev-wallet-empty-icon i{
  font-size: 2rem;
  color: var(--ev-verde);
}

/* Contenedor de movimientos */
.ev-wallet-movimientos{
  margin-top: 10px;
}

/* Tabla de movimientos */
.ev-wallet-table-wrapper{
  margin-top: 4px;
}

.ev-wallet-table{
  font-size: 0.9rem;
  border-color: var(--ev-gris-borde);
}

.ev-wallet-table thead th{
  border-bottom: 1px solid var(--ev-gris-borde);
  font-weight: 600;
  color: var(--ev-texto-suave);
  background-color: #ffffff;
  text-transform: uppercase;
  font-size: 0.78rem;
}

/* Filas */
.ev-wallet-table tbody tr{
  transition: background-color 0.18s ease;
}

.ev-wallet-table tbody tr:hover{
  background-color: #F9FAFB;
}

.ev-wallet-table tbody tr td{
  border-bottom: 1px solid #F3F4F6;
  padding-top: 0.7rem;
  padding-bottom: 0.7rem;
}

/* Concepto */
.ev-wallet-mov-concepto{
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.ev-wallet-mov-header{
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.ev-wallet-mov-icon{
  font-size: 1rem;
}

.ev-wallet-mov-icon--credito{
  color: var(--ev-verde);
}

.ev-wallet-mov-icon--debito{
  color: var(--ev-rojo);
}

.ev-wallet-mov-titulo{
  font-weight: 600;
  color: var(--ev-texto);
}

.ev-wallet-mov-detalle{
  color: var(--ev-texto-suave);
}

/* Monto */
.ev-wallet-mov-monto{
  font-weight: 600;
}

.ev-wallet-monto--credito{
  color: var(--ev-verde);
}

.ev-wallet-monto--debito{
  color: var(--ev-rojo);
}

/* Saldo después */
.ev-wallet-mov-saldo{
  font-size: 0.85rem;
  color: var(--ev-texto-suave);
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 991.98px){
  .ev-wallet-card .card-body{
    padding: 20px 18px;
  }

  .ev-wallet-title{
    font-size: 1.4rem;
  }

  .ev-wallet-badge{
    min-width: 0;
  }
}

@media (max-width: 575.98px){
  .ev-wallet-wrapper{
    padding-left: 0.75rem !important;
    padding-right: 0.75rem !important;
  }

  .ev-wallet-card .card-body{
    padding: 18px 14px;
  }

  .ev-wallet-badge{
    width: 100%;
  }

  /* Tabla tipo "listado" en móviles */
  .ev-wallet-table thead{
    display: none;
  }

  .ev-wallet-table tbody tr td{
    display: block;
    text-align: left !important;
  }

  .ev-wallet-table tbody tr td + td{
    margin-top: 4px;
  }

  .ev-wallet-mov-monto,
  .ev-wallet-mov-saldo{
    display: inline-block;
  }
}
</style>
