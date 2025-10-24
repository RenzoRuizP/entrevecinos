<style>
/* =======================================================
   🌿 ENTRE VECINOS - DATOS PERSONALES (UX/UI) - BOTÓN PROFESIONAL
======================================================== */

/* Contenedor principal */
.container-datos-personales {
  animation: fadeIn 0.8s ease-in-out;
  animation-fill-mode: forwards;
  padding: 2rem 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}

/* Animación de aparición */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Card header */
.card-header.bg-success {
  background-color: #ffffff;
  border-radius: 18px;
  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease
}

/* Título principal */
.card-header h5 {
  color: #ffffff !important;
  margin: 0;
  font-weight: 600;
}

/* Inputs */
.input-premium {
  border: 1px solid #115C41;
  border-radius: .5rem;
  padding: .5rem .75rem;
  transition: all 0.25s ease;
  width: 100%;
}

.input-premium:focus {
  border-color: #115C41;
  box-shadow: 0 0 0 3px rgba(15, 89, 47, 0.15);
  outline: none;
}

/* Labels */
h6.fw-semibold.text-success {
  color: #115C41;
  font-size: 0.95rem;
  margin-bottom: .5rem;
}

/* Botón GUARDAR - estilo profesional */
.btn-guardar {
  background-color: #0F592F;        
  color: #ffffff;
  border: none;
  border-radius: 50px;              
  padding: 0.65rem 2rem;            
  font-weight: 600;
  font-size: 1rem;                  
  text-transform: uppercase;        
  letter-spacing: 0.5px;            
  box-shadow: 0 2px 6px rgba(0,0,0,0.12); /* sombra más ligera y elegante */
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); 
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-guardar:hover {
  background-color: #115C41;       
  transform: translateY(-1.5px) scale(1.015); 
  box-shadow: 0 4px 10px rgba(0,0,0,0.14); /* hover más sutil y profesional */
}

.btn-guardar:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(15, 89, 47, 0.2); /* foco accesible */
}



/* Separadores */
hr {
  border-top: 1px solid #ddd;
  margin: .5rem 0 1.5rem;
}

/* Responsividad ligera */
@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
