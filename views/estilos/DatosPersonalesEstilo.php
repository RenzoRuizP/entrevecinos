<style>
/* ===== 🎨 Contenedor principal ===== */
.container-datos-personales {
    max-width: 1200px;
    margin: 30px auto;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    animation: fadeIn 0.6s ease-in-out;
}

/* ===== 🧩 Card ===== */
.card {
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    transition: all 0.3s ease;
    background-color: #fff;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.18);
}

/* ===== 🏷️ Cabecera ===== */
.card-header {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    color: #fff;
    background: linear-gradient(135deg, #0F592F, #198754);
    font-weight: 600;
    letter-spacing: 0.4px;
}

.card-header h5 {
    color: #ffffff !important;
    margin: 0;
    font-weight: 600;
}

/* ===== 🧾 Labels ===== */
.form-label {
    font-weight: 600;
    font-size: 0.95rem;
    color: #1b3d2f;
}

/* ===== ✏️ Inputs y selects ===== */
.input-premium {
    border-radius: 10px;
    border: 1px solid #ced4da;
    padding: 10px 12px;
    transition: all 0.3s ease;
    background-color: #fff;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
    font-size: 0.95rem;
}

.input-premium:focus {
    border-color: #0F592F;
    box-shadow: 0 0 8px rgba(15, 89, 47, 0.25);
    outline: none;
    background-color: #fefefe;
}

/* ===== 🔽 Selects ===== */
.form-select.input-premium {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5'%3E%3Cpath fill='%230F592F' d='M2 0L0 2h4L2 0zM2 5L0 3h4l-2 2z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 10px 10px;
}

/* ===== 🧠 Animación ===== */
.fade-in {
    animation: fadeIn 0.6s ease-in-out;
}

@keyframes fadeIn {
    from {opacity: 0; transform: translateY(5px);}
    to {opacity: 1; transform: translateY(0);}
}

/* ===== 💾 Botones ===== */
.btn-outline-success, .btn-cancelar {
    border-radius: 10px;
    padding: 8px 20px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.25s ease;
}

/* ✅ Guardar */
.btn-outline-success {
    color: #0F592F;
    border-color: #0F592F;
    background-color: transparent;
}

.btn-outline-success:hover {
    background-color: #0F592F;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(15, 89, 47, 0.25);
}

/* ❌ Cancelar */
.btn-cancelar {
    color: #333;
    background-color: #e9ecef;
    border: 1px solid #d6d8d9;
}

.btn-cancelar:hover {
    background-color: #d1d1d1;
    color: #000;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* ===== 📱 Responsividad ===== */
@media (max-width: 576px) {
    .card-body {
        padding: 20px 15px;
    }

    .form-label {
        font-size: 0.9rem;
    }

    .btn-outline-success,
    .btn-cancelar {
        width: 100%;
        margin-bottom: 10px;
    }
}

/* ===== 🎯 Efecto visual de guardado ===== */
.btn-guardar {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-guardar.saving {
    pointer-events: none;
    color: transparent !important;
}

.btn-guardar.saving::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 18px;
    height: 18px;
    margin-top: -9px;
    margin-left: -9px;
    border: 2px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.btn-guardar.success::after {
    content: '✔';
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    animation: popIn 0.3s ease;
}

@keyframes popIn {
    from { transform: scale(0.5); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

</style>
