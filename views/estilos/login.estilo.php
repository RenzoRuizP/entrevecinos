<!-- Tipografía Poppins -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.3/index.min.css">

<!-- Bootstrap 5 (CSS) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===================================================
   TOKENS EV
=================================================== */
:root {
  --ev-verde-oscuro: #0F592F;
  --ev-verde: #0E7A43;
  --ev-verde-claro: #16A34A;

  --ev-naranja: #EA7C12;
  --ev-naranja-oscuro: #C46B05;

  --ev-gris-050: #F9FAFB;
  --ev-gris-100: #F3F4F6;
  --ev-gris-200: #E5E7EB;
  --ev-gris-300: #D1D5DB;
  --ev-gris-500: #6B7280;
  --ev-gris-600: #4B5563;
  --ev-gris-900: #111827;

  --ev-borde-suave: rgba(226, 232, 240, 0.95);
  --ev-sombra-card:
    0 34px 72px rgba(15, 23, 42, 0.14),
    0 14px 32px rgba(15, 23, 42, 0.08),
    0 2px 8px rgba(15, 23, 42, 0.04);
}

/* ===================================================
   BASE / FONDO LOGIN
   Fondo neutro premium, sin halo verde
=================================================== */
html,
body {
  min-height: 100%;
  background: #F8FAFC;
}

body.login-body {
  min-height: 100vh;
  min-height: 100dvh;
  margin: 0;
  padding: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--ev-gris-900);
  background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
}

/* ===================================================
   CONTENEDOR PRINCIPAL
=================================================== */
.login-shell {
  width: min(94vw, 1080px);
  max-width: 1080px;
  min-height: 560px;
  background: #FFFFFF;
  border-radius: 28px;
  overflow: hidden;
  display: flex;
  position: relative;
  border: 1px solid var(--ev-borde-suave);
  box-shadow: var(--ev-sombra-card);
}

/* ===================================================
   HERO IZQUIERDO
=================================================== */
.login-hero {
  flex: 1.15;
  position: relative;
  overflow: hidden;
  padding: 52px 44px;
  color: #F9FAFB;
  display: flex;
  align-items: center;
  background:
    radial-gradient(circle at 75% 30%, rgba(255,255,255,0.08), transparent 60%),
    radial-gradient(circle at 20% 80%, rgba(0,0,0,0.08), transparent 70%),
    linear-gradient(145deg, #0F592F 0%, #0E7A43 45%, #16A34A 85%);
}

.login-hero::before {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 85% 85%, rgba(187,247,208,0.18) 0, transparent 60%);
  opacity: 0.9;
  pointer-events: none;
}

.login-hero-content {
  position: relative;
  z-index: 2;
  max-width: 430px;
}

.login-hero-title {
  font-size: 2.15rem;
  font-weight: 700;
  margin-bottom: 14px;
  letter-spacing: 0.01em;
  text-shadow: 0 1px 2px rgba(0,0,0,0.20);
}

.login-hero-title span {
  display: block;
  color: #FEFCE8;
}

.login-hero-text {
  font-size: 0.99rem;
  color: #E5E7EB;
  margin-bottom: 14px;
  line-height: 1.6;
  text-shadow: 0 1px 2px rgba(0,0,0,0.20);
  max-width: 42ch;
}

.login-hero-list {
  margin-top: 10px;
  padding-left: 0;
  list-style: none;
  display: grid;
  gap: 12px;
  margin-bottom: 24px;
}

.login-hero-list li {
  display: grid;
  grid-template-columns: 26px 1fr;
  column-gap: 12px;
  align-items: center;
  line-height: 1.25;
  color: #F9FAFB;
  text-shadow: 0 1px 2px rgba(0,0,0,0.20);
  font-size: 0.95rem;
}

.login-hero-list li i {
  font-size: 1.1rem;
  line-height: 1;
  opacity: .95;
  transform: translateY(-1px);
  color: #FEF3C7;
}

.login-hero-list li strong {
  font-weight: 700;
}

.badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.28);
  background: rgba(255,255,255,.10);
  color: #FFFFFF;
  backdrop-filter: blur(6px);
  user-select: none;
  cursor: default;
  box-shadow: 0 10px 22px rgba(15,23,42,0.18);
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
}

.badge-pill:hover {
  transform: translateY(-1px);
  background: rgba(255,255,255,.12);
  border-color: rgba(255,255,255,.34);
  box-shadow: 0 14px 28px rgba(15,23,42,0.22);
}

.badge-pill:focus-visible {
  outline: 3px solid rgba(187,247,208,.55);
  outline-offset: 3px;
}

/* ===================================================
   PANEL DERECHO LOGIN
   Blanco puro con separación visual interna
=================================================== */
.login-panel {
  flex: 0.95;
  position: relative;
  background: #FFFFFF;
  display: flex;
  flex-direction: column;
  padding: 34px 44px 30px;
  box-shadow: inset 1px 0 0 rgba(226, 232, 240, 0.9);
}

.login-panel-header {
  margin-bottom: 16px;
  position: relative;
  z-index: 1;
}

.login-panel-header::before {
  content: none;
}

.login-brand-mark {
  width: 78px;
  height: 78px;
  margin: 0 auto 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 22px;
  background: #FFFFFF;
  border: 1px solid rgba(226, 232, 240, 0.85);
  box-shadow:
    0 10px 24px rgba(15,23,42,0.08),
    0 2px 8px rgba(15,23,42,0.04);
  transform: translateZ(0);
}

.login-logo {
  width: 58px;
  height: 58px;
  display: block;
  object-fit: contain;
  filter: drop-shadow(0 4px 7px rgba(15,23,42,0.08));
  transform: translateZ(0);
}

.login-panel-title {
  font-size: 1.45rem;
  font-weight: 700;
  color: var(--ev-verde-oscuro);
  margin-bottom: 4px;
}

.login-panel-subtitle {
  font-size: 0.88rem;
  color: var(--ev-gris-500);
  margin-bottom: 0;
  line-height: 1.45;
}

.login-panel-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding-top: 8px;
  padding-bottom: 12px;
}

/* ===================================================
   FORMULARIO LOGIN
=================================================== */
.login-form .input-icon {
  position: absolute;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  color: #9CA3AF;
  font-size: 1rem;
}

.login-form input.form-control {
  min-height: 42px;
  padding-left: 38px;
  border-radius: 10px;
  border: 1px solid var(--ev-verde-claro);
  font-size: 0.95rem;
  transition: all 0.18s ease-out;
  box-shadow: 0 0 0 0 rgba(22,163,74,0);
}

.login-form input.form-control::placeholder {
  color: #79808C;
  font-size: 0.93rem;
}

.login-form input.form-control:focus {
  border-color: var(--ev-verde);
  box-shadow: 0 0 0 3px rgba(22,163,74,0.24);
  outline: none;
}

.login-form .mb-3 {
  margin-bottom: 1.15rem !important;
}

.login-form .mb-2 {
  margin-bottom: 1.05rem !important;
}

.login-remember-row {
  margin-top: 2px;
  margin-bottom: 1.15rem !important;
  padding-top: 2px;
}

.login-remember-row .form-check-input {
  border-radius: 4px;
}

.login-remember-row .form-check-input:checked {
  background-color: var(--ev-verde);
  border-color: var(--ev-verde);
}

.login-remember-row .form-check-label {
  font-size: 0.9rem;
  color: #111827;
}

.login-link-forgot {
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--ev-verde-oscuro);
  text-decoration: none;
}

.login-link-forgot:hover {
  color: var(--ev-verde);
  text-decoration: underline;
}

/* Botón naranja EV */
.btn-login {
  min-height: 44px;
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B);
  border: none;
  color: #FFFFFF;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 650;
  letter-spacing: -0.01em;
  box-shadow: 0 12px 26px rgba(234,124,18,0.35);
  transition: all 0.2s ease;
}

.btn-login:hover {
  background: linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C);
  color: #FFFFFF;
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(234,124,18,0.48);
}

.btn-login:active {
  transform: translateY(0);
  box-shadow: 0 6px 16px rgba(234,124,18,0.30);
}

.login-actions {
  margin-top: 16px;
}

.login-actions-text {
  margin-bottom: 10px !important;
  font-size: 0.88rem;
  color: var(--ev-gris-600);
}

.btn-outline-register {
  min-height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 22px;
  border-radius: 999px;
  border: 1.5px solid var(--ev-verde);
  background-color: #FFFFFF;
  color: var(--ev-verde-oscuro);
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.18s ease;
  box-shadow: 0 8px 18px rgba(15, 89, 47, 0.06);
}

.btn-outline-register:hover {
  transform: translateY(-1px);
  background-color: #ECFDF5;
  color: var(--ev-verde);
  box-shadow: 0 10px 22px rgba(15, 89, 47, 0.10);
}

.btn-outline-register:active {
  transform: translateY(0);
  box-shadow: 0 5px 12px rgba(15, 89, 47, 0.08);
}

.login-panel-footer {
  margin-top: 10px !important;
}

.login-panel-footer small {
  font-size: 0.78rem;
  color: #9CA3AF;
  letter-spacing: 0.01em;
}

/* ===================================================
   SPINNER
=================================================== */
.spinner-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255,255,255,0.86);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  display: none;
}

.spinner {
  border: 4px solid #D1FAE5;
  border-top: 4px solid var(--ev-verde-oscuro);
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ===================================================
   MODALES EV BASE
=================================================== */
.modal.show .modal-dialog {
  padding-top: 3rem;
  padding-bottom: 2rem;
}

.modal-content {
  border-radius: 18px;
  border: none;
  overflow: hidden;
  background: transparent;
  box-shadow:
    0 18px 45px rgba(0,0,0,0.22),
    0 6px 12px rgba(0,0,0,0.12);
}

.modal-header.bg-success {
  background: linear-gradient(140deg, #0F592F 0%, #0E7A43 55%, #16A34A 100%);
  padding: 16px 18px;
  border-bottom: 1px solid rgba(255,255,255,0.20);
  border-radius: 18px 18px 0 0;
}

.modal-header .modal-title {
  font-weight: 700;
  font-size: 1rem;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  letter-spacing: 0.01em;
}

.modal-header .btn-close {
  filter: invert(1);
}

.modal-body {
  padding: 1.8rem 2rem 1.4rem;
  background: #FFFFFF;
  box-shadow: inset 0 1px 0 rgba(0,0,0,0.06);
}

.modal-footer {
  border-top: 1px solid #E5E7EB;
  border-radius: 0 0 18px 18px;
  padding: 0.75rem 2rem;
  background: #FFFFFF;
}

/* Wizard pasos */
.modal .progress {
  height: 32px !important;
  border-radius: 999px;
  background-color: #E5E7EB;
  overflow: hidden;
  box-shadow: inset 0 0 0 1px rgba(148,163,184,0.35);
}

.modal .progress-bar {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal .progress-bar.bg-success {
  background: linear-gradient(135deg, #0F592F 0%, #16A34A 100%) !important;
  font-weight: 700;
  color: #FFFFFF;
  letter-spacing: 0.03em;
}

.modal .progress-bar.bg-secondary {
  background-color: #D1D5DB !important;
  color: #4B5563;
}

.step h6 {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 750;
  color: #0F592F;
  font-size: 1.05rem;
  margin-bottom: 0.8rem;
}

/* Inputs de modales */
.modal-content .form-label {
  font-weight: 600;
  font-size: 0.9rem;
  color: #374151;
}

.modal-content .form-control,
.modal-content .form-select {
  border-radius: 12px;
  border: 1px solid #D1FAE5;
  font-size: 0.95rem;
  transition: all 0.18s ease-out;
  padding-left: 14px;
  padding-right: 14px;
  height: 46px;
  background: #FFFFFF;
}

.modal-content .form-control::placeholder {
  color: #A3A3A3;
}

.modal-content .form-control:focus,
.modal-content .form-select:focus {
  border-color: #16A34A;
  box-shadow: 0 0 0 4px rgba(22,163,74,0.18);
  outline: none;
}

.modal-content .row.g-3 {
  row-gap: 1.4rem;
}

.modal-content input[type="file"].form-control {
  height: auto;
  padding: 10px 12px;
}

/* Botones footer */
.modal-footer .btn {
  border-radius: 999px;
  font-size: 0.9rem;
}

.modal-footer .btn-outline-secondary {
  border-color: #D1D5DB;
  color: #4B5563;
  background-color: #FFFFFF;
}

.modal-footer .btn-outline-secondary:hover {
  background-color: #F3F4F6;
  color: #111827;
}

/* ===================================================
   CTA compacta para modales
=================================================== */
.modal-footer .btn-login.btn-modal-cta {
  height: 38px;
  padding: 0 16px;
  border-radius: 999px;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  line-height: 1;
  box-shadow: 0 10px 22px rgba(234,124,18,0.22);
}

.modal-footer .btn-login.btn-modal-cta i {
  font-size: 0.95rem;
  line-height: 1;
  display: inline-block;
  transform: translateY(0.5px);
}

/* ===================================================
   PASO 2 RESIDENCIA
=================================================== */
#formStep2 h6 {
  font-size: 1.15rem;
}

#formStep2 h6 i {
  font-size: 1.25rem;
  margin-right: 4px;
}

#formStep2 .row.g-3 {
  column-gap: 1rem;
}

#formStep2 .form-select {
  height: 46px;
  line-height: 46px;
  padding-top: 0;
  padding-bottom: 0;
}

/* ===================================================
   MODAL CREAR MI USUARIO
=================================================== */
#crear_usuario .modal-dialog {
  max-width: 980px;
}

#crear_usuario .modal-body {
  background: linear-gradient(180deg, #FFFFFF 0%, #FAFBFC 100%);
}

#crear_usuario .progress {
  height: 34px !important;
  background: rgba(15, 89, 47, 0.06);
  box-shadow:
    inset 0 0 0 1px rgba(148,163,184,0.32),
    0 10px 22px rgba(15,23,42,0.06);
}

#crear_usuario .step .row.g-3 {
  background: #FFFFFF;
  border: 1px solid rgba(229,231,235,0.95);
  border-radius: 16px;
  padding: 18px 16px;
  box-shadow:
    0 14px 30px rgba(15,23,42,0.06),
    0 2px 8px rgba(15,23,42,0.04);
}

#crear_usuario .modal-footer {
  padding-left: 18px;
  padding-right: 18px;
}

#crear_usuario #btnAnterior {
  padding: 10px 18px;
  border-radius: 999px;
  border: 1px solid rgba(209,213,219,0.95);
  box-shadow: 0 10px 22px rgba(15,23,42,0.05);
}

#crear_usuario #btnAnterior:disabled {
  opacity: .55;
  box-shadow: none;
}

#crear_usuario #btnSiguiente,
#crear_usuario #btnRegistrar {
  background: linear-gradient(135deg, var(--ev-naranja), #F59E0B) !important;
  border: none !important;
  color: #FFFFFF !important;
  box-shadow: 0 12px 26px rgba(234,124,18,0.35) !important;
  padding: 10px 22px;
}

#crear_usuario #btnSiguiente:hover,
#crear_usuario #btnRegistrar:hover {
  background: linear-gradient(135deg, var(--ev-naranja-oscuro), #EA580C) !important;
  transform: translateY(-1px);
  box-shadow: 0 14px 32px rgba(234,124,18,0.48) !important;
}

#crear_usuario #btnSiguiente:active,
#crear_usuario #btnRegistrar:active {
  transform: translateY(0);
  box-shadow: 0 6px 16px rgba(234,124,18,0.30) !important;
}


/* ===================================================
   PUNTO 12 - DOCUMENTOS LEGALES EN LOGIN Y REGISTRO
=================================================== */
.login-legal-links {
  margin-top: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
  gap: 5px;
  font-size: 0.72rem;
  line-height: 1.35;
}

.login-legal-links a {
  color: var(--ev-verde-oscuro);
  font-weight: 650;
  text-decoration: none;
}

.login-legal-links a:hover,
.login-legal-links a:focus-visible {
  color: var(--ev-naranja-oscuro);
  text-decoration: underline;
}

.ev-register-legal {
  display: grid;
  gap: 16px;
}

.ev-register-legal__heading {
  display: grid;
  grid-template-columns: 48px minmax(0, 1fr);
  gap: 13px;
  align-items: center;
  padding: 15px 16px;
  border: 1px solid #DCEFE3;
  border-radius: 16px;
  background: linear-gradient(135deg, #F0FDF4 0%, #FFFFFF 72%);
}

.ev-register-legal__heading h6 {
  margin: 0 0 4px !important;
  font-size: 1rem;
}

.ev-register-legal__heading p {
  color: var(--ev-gris-600);
  font-size: 0.82rem;
  line-height: 1.45;
}

.ev-register-legal__icon {
  width: 48px;
  height: 48px;
  display: grid;
  place-items: center;
  border-radius: 15px;
  color: #FFFFFF;
  background: linear-gradient(135deg, var(--ev-verde-oscuro), var(--ev-verde-claro));
  box-shadow: 0 10px 22px rgba(15, 89, 47, 0.18);
  font-size: 1.22rem;
}

.ev-register-legal__docs {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.ev-register-legal__doc {
  min-width: 0;
  display: grid;
  grid-template-columns: 42px minmax(0, 1fr) 18px;
  gap: 11px;
  align-items: center;
  min-height: 82px;
  padding: 13px 14px;
  border: 1px solid #E2E8F0;
  border-radius: 15px;
  color: #1F2937;
  background: #FFFFFF;
  text-decoration: none;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
  transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.ev-register-legal__doc:hover,
.ev-register-legal__doc:focus-visible {
  color: #1F2937;
  border-color: #86EFAC;
  box-shadow: 0 12px 26px rgba(15, 89, 47, 0.10);
  transform: translateY(-1px);
}

.ev-register-legal__doc-icon {
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  border-radius: 13px;
  color: var(--ev-verde-oscuro);
  background: #F0FDF4;
  border: 1px solid #BBF7D0;
  font-size: 1.05rem;
}

.ev-register-legal__doc strong,
.ev-register-legal__doc small {
  display: block;
  overflow-wrap: anywhere;
}

.ev-register-legal__doc strong {
  color: var(--ev-verde-oscuro);
  font-size: 0.84rem;
  line-height: 1.3;
}

.ev-register-legal__doc small {
  margin-top: 4px;
  color: #64748B;
  font-size: 0.7rem;
  line-height: 1.35;
}

.ev-register-legal__doc > .bi {
  color: #94A3B8;
  font-size: 0.83rem;
}

.ev-register-legal__consents {
  display: grid;
  gap: 11px;
}

.ev-register-legal__check {
  display: grid;
  grid-template-columns: 22px minmax(0, 1fr);
  gap: 11px;
  align-items: start;
  padding: 14px 15px;
  border: 1px solid #DDE4EC;
  border-radius: 14px;
  background: #FFFFFF;
  transition: border-color .16s ease, background .16s ease, box-shadow .16s ease;
}

.ev-register-legal__check:focus-within {
  border-color: #4ADE80;
  box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.12);
}

.ev-register-legal__check.is-invalid {
  border-color: #EF4444;
  background: #FEF2F2;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08);
}

.ev-register-legal__check .form-check-input {
  width: 19px;
  height: 19px;
  margin: 2px 0 0;
  cursor: pointer;
  border-color: #94A3B8;
  box-shadow: none;
}

.ev-register-legal__check .form-check-input:checked {
  background-color: var(--ev-verde);
  border-color: var(--ev-verde);
}

.ev-register-legal__check label {
  margin: 0;
  color: #334155;
  font-size: 0.8rem;
  line-height: 1.5;
  cursor: pointer;
}

.ev-register-legal__check label a {
  color: var(--ev-verde-oscuro);
  font-weight: 750;
  text-underline-offset: 2px;
}

.ev-register-legal__note {
  display: flex;
  gap: 9px;
  align-items: flex-start;
  padding: 11px 13px;
  border-radius: 12px;
  color: #7C2D12;
  background: #FFF7ED;
  border: 1px solid #FED7AA;
  font-size: 0.75rem;
  line-height: 1.45;
}

.ev-register-legal__note .bi {
  margin-top: 1px;
  color: var(--ev-naranja);
  flex: 0 0 auto;
}

#crear_usuario #btnRegistrar:disabled {
  cursor: not-allowed;
  opacity: .52;
  transform: none !important;
  box-shadow: none !important;
  filter: grayscale(.12);
}

/* ===================================================
   RESPONSIVO
=================================================== */
@media (max-width: 992px) {
  .login-hero-text {
    max-width: 52ch;
  }

  .login-hero-list {
    gap: 10px;
  }

  .login-hero-list li {
    grid-template-columns: 24px 1fr;
  }

  .login-hero-list li i {
    font-size: 1.05rem;
  }
}

@media (max-width: 768px) {
  body.login-body {
    padding: 16px;
    background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
  }

  .login-shell {
    width: 100%;
    max-width: 480px;
    min-height: 0;
    flex-direction: column;
  }

  .login-hero {
    padding: 32px 24px 24px;
    align-items: flex-start;
  }

  .login-panel {
    padding: 24px 22px 20px;
    box-shadow: inset 0 1px 0 rgba(226, 232, 240, 0.9);
  }
}

@media (max-width: 576px) {
  .login-hero {
    padding: 26px 20px 18px;
  }

  .login-hero-title {
    font-size: 1.7rem;
  }

  .login-panel-title {
    font-size: 1.25rem;
  }

  .login-shell {
    border-radius: 20px;
  }

  .login-brand-mark {
    width: 74px;
    height: 74px;
    border-radius: 20px;
    margin-bottom: 8px;
  }

  .login-logo {
    width: 54px;
    height: 54px;
  }

  .modal-body {
    padding-left: 1.25rem;
    padding-right: 1.25rem;
  }

  .modal .progress {
    height: 30px !important;
  }

  .modal .progress-bar {
    font-size: 0.6rem;
    letter-spacing: 0.01em;
    padding: 0 2px;
  }

  #step1,
  #step2,
  #step3,
  #step4 {
    width: 25% !important;
  }

  .modal-footer .btn-login.btn-modal-cta {
    height: 36px;
    padding: 0 14px;
    font-size: 0.88rem;
    line-height: 1;
  }

  #crear_usuario .step .row.g-3 {
    padding: 14px 12px;
  }

  .ev-register-legal__heading {
    grid-template-columns: 40px minmax(0, 1fr);
    gap: 10px;
    padding: 13px;
  }

  .ev-register-legal__icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    font-size: 1rem;
  }

  .ev-register-legal__heading h6 {
    font-size: .9rem;
  }

  .ev-register-legal__heading p {
    font-size: .74rem;
  }

  .ev-register-legal__docs {
    grid-template-columns: 1fr;
    gap: 9px;
  }

  .ev-register-legal__doc {
    min-height: 72px;
    padding: 11px 12px;
  }

  .ev-register-legal__check {
    padding: 12px;
  }

  .ev-register-legal__check label {
    font-size: .75rem;
  }
}

.ev-register-legal__privacy-summary{
  border:1px solid #BFDBFE;
  border-radius:14px;
  background:#F8FBFF;
  overflow:hidden;
}
.ev-register-legal__privacy-summary summary{
  list-style:none;
  cursor:pointer;
  padding:12px 14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  color:#1E3A5F;
  font-size:.8rem;
  font-weight:750;
}
.ev-register-legal__privacy-summary summary::-webkit-details-marker{display:none}
.ev-register-legal__privacy-summary summary span{display:flex;align-items:center;gap:8px}
.ev-register-legal__privacy-summary summary>i{transition:transform .18s ease}
.ev-register-legal__privacy-summary[open] summary>i{transform:rotate(180deg)}
.ev-register-legal__privacy-summary>div{
  border-top:1px solid #DBEAFE;
  padding:12px 14px 13px;
  color:#475569;
  font-size:.75rem;
  line-height:1.55;
}
.ev-register-legal__privacy-summary p{margin:0 0 7px}
.ev-register-legal__privacy-summary a{color:var(--ev-verde-oscuro);font-weight:700;overflow-wrap:anywhere}


/* ===================================================
   CREAR MI USUARIO · AJUSTE PREMIUM UX/UI 2026
   Footer persistente + validación visual + Legal limpio
=================================================== */
#crear_usuario.show .modal-dialog,
#crear_usuario .modal-dialog {
  width: min(980px, calc(100% - 24px));
  max-width: 980px;
  max-height: calc(100dvh - 24px);
  margin: 12px auto;
  padding-top: 0;
  padding-bottom: 0;
}

#crear_usuario .modal-content {
  display: flex;
  flex-direction: column;
  min-height: 0;
  max-height: calc(100dvh - 24px);
  overflow: hidden;
  border-radius: 18px !important;
  background: #FFFFFF;
  background-clip: padding-box;
}

/* El radio lo controla el contenedor: el encabezado llena por completo
   las esquinas y evita el halo/mancha blanca superior. */
#crear_usuario .modal-header.bg-success {
  border-radius: 0 !important;
  background-clip: border-box;
}

#crear_usuario #formCrearUsuario {
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}

#crear_usuario .modal-header,
#crear_usuario .modal-footer {
  flex: 0 0 auto;
}

#crear_usuario .modal-body {
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto !important;
  overscroll-behavior: contain;
  scrollbar-gutter: stable;
  padding: 24px 26px 22px;
  background: #FFFFFF;
}

#crear_usuario .modal-body::-webkit-scrollbar {
  width: 9px;
}

#crear_usuario .modal-body::-webkit-scrollbar-track {
  background: #F8FAFC;
}

#crear_usuario .modal-body::-webkit-scrollbar-thumb {
  background: #CBD5E1;
  border: 2px solid #F8FAFC;
  border-radius: 999px;
}

#crear_usuario .modal-footer {
  position: relative;
  z-index: 5;
  min-height: 68px;
  padding: 12px 20px;
  background: rgba(255,255,255,.98);
  border-top: 1px solid #E5E7EB;
  box-shadow: 0 -10px 28px rgba(15, 23, 42, 0.06);
  backdrop-filter: blur(10px);
}

#crear_usuario .progress {
  margin-bottom: 20px !important;
}

#crear_usuario .step > h6 {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 24px;
  margin: 0 0 12px !important;
  padding: 0;
  font-size: 1.05rem !important;
  line-height: 1.25;
}

#crear_usuario .step > h6 > i {
  display: inline-grid;
  flex: 0 0 20px;
  width: 20px;
  height: 20px;
  margin: 0 !important;
  place-items: center;
  font-size: 1.05rem !important;
  line-height: 1;
}

#crear_usuario .step .row.g-3 {
  margin-bottom: 0 !important;
}

#crear_usuario .form-text {
  margin-top: 5px;
  color: #6B7280;
  font-size: .75rem;
  line-height: 1.4;
}

#crear_usuario .invalid-feedback {
  margin-top: 6px;
  color: #B42318;
  font-size: .75rem;
  font-weight: 600;
  line-height: 1.35;
}

#crear_usuario .form-control.is-invalid,
#crear_usuario .form-select.is-invalid {
  border-color: #E5484D;
  background-color: #FFFDFD;
  box-shadow: 0 0 0 3px rgba(229, 72, 77, .10);
}

#crear_usuario .form-control.is-valid,
#crear_usuario .form-select.is-valid {
  border-color: #86EFAC;
  background-image: none;
}

/* Estado activo EV: naranja para interacción; rojo para error y verde para éxito. */
#crear_usuario .form-control:focus,
#crear_usuario .form-select:focus {
  border-color: #EA7C12;
  outline: 0;
  box-shadow: 0 0 0 4px rgba(234, 124, 18, .16);
}

#crear_usuario .form-control.is-valid:focus,
#crear_usuario .form-select.is-valid:focus {
  border-color: #EA7C12;
  box-shadow: 0 0 0 4px rgba(234, 124, 18, .16);
}

#crear_usuario .form-control.is-invalid:focus,
#crear_usuario .form-select.is-invalid:focus {
  border-color: #E5484D;
  box-shadow: 0 0 0 4px rgba(229, 72, 77, .13);
}

/* Neutraliza el azul de autocompletado del navegador dentro del modal. */
#crear_usuario input.form-control:-webkit-autofill,
#crear_usuario input.form-control:-webkit-autofill:hover {
  -webkit-text-fill-color: #111827 !important;
  -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset !important;
  caret-color: #111827;
}

#crear_usuario input.form-control:-webkit-autofill:focus {
  -webkit-text-fill-color: #111827 !important;
  -webkit-box-shadow:
    0 0 0 1000px #FFFFFF inset,
    0 0 0 4px rgba(234, 124, 18, .16) !important;
}

/* Paso Legal: jerarquía clara y menor carga visual */
#crear_usuario .ev-register-legal {
  display: grid;
  gap: 12px;
}

#crear_usuario .ev-register-legal__heading {
  display: grid;
  grid-template-columns: 44px minmax(0, 1fr);
  gap: 12px;
  align-items: center;
  padding: 2px 2px 8px;
  border: 0;
  border-radius: 0;
  background: transparent;
}

#crear_usuario .ev-register-legal__icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  color: var(--ev-verde-oscuro);
  background: #ECFDF3;
  border: 1px solid #BBF7D0;
  box-shadow: none;
  font-size: 1.12rem;
}

#crear_usuario .ev-register-legal__eyebrow,
#crear_usuario .ev-register-legal__section-kicker {
  display: block;
  margin-bottom: 2px;
  color: var(--ev-verde);
  font-size: .66rem;
  font-weight: 800;
  letter-spacing: .09em;
  line-height: 1.2;
  text-transform: uppercase;
}

#crear_usuario .ev-register-legal__heading h6 {
  margin: 0 0 2px !important;
  color: var(--ev-verde-oscuro) !important;
  font-size: 1rem;
  line-height: 1.25;
}

#crear_usuario .ev-register-legal__heading p {
  color: #64748B;
  font-size: .79rem;
  line-height: 1.4;
}

#crear_usuario .ev-register-legal__review,
#crear_usuario .ev-register-legal__consent-panel {
  border: 1px solid #E2E8F0;
  border-radius: 17px;
  background: #FFFFFF;
  overflow: hidden;
}

#crear_usuario .ev-register-legal__review {
  padding: 14px;
  box-shadow: 0 8px 22px rgba(15, 23, 42, .035);
}

#crear_usuario .ev-register-legal__section-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 10px;
}

#crear_usuario .ev-register-legal__section-title h6,
#crear_usuario .ev-register-legal__consent-heading h6 {
  margin: 0 !important;
  color: #1F2937 !important;
  font-size: .88rem;
  line-height: 1.3;
}

#crear_usuario .ev-register-legal__section-badge {
  flex: 0 0 auto;
  padding: 5px 9px;
  border-radius: 999px;
  color: #166534;
  background: #F0FDF4;
  border: 1px solid #DCFCE7;
  font-size: .65rem;
  font-weight: 750;
  white-space: nowrap;
}

#crear_usuario .ev-register-legal__docs {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 9px;
}

#crear_usuario .ev-register-legal__doc {
  min-height: 66px;
  padding: 10px 11px;
  grid-template-columns: 36px minmax(0, 1fr) auto;
  gap: 9px;
  border: 1px solid #E5E7EB;
  border-radius: 13px;
  background: #F8FAFC;
  box-shadow: none;
}

#crear_usuario .ev-register-legal__doc:hover,
#crear_usuario .ev-register-legal__doc:focus-visible {
  border-color: #86EFAC;
  background: #F7FEF9;
  box-shadow: 0 8px 18px rgba(15, 89, 47, .07);
  transform: translateY(-1px);
}

#crear_usuario .ev-register-legal__doc-icon {
  width: 36px;
  height: 36px;
  border-radius: 11px;
  color: var(--ev-verde-oscuro);
  background: #FFFFFF;
  border: 1px solid #D1FAE5;
  font-size: .94rem;
}

#crear_usuario .ev-register-legal__doc strong {
  color: #1F5135;
  font-size: .78rem;
  line-height: 1.28;
}

#crear_usuario .ev-register-legal__doc small {
  margin-top: 3px;
  color: #7C8798;
  font-size: .66rem;
}

#crear_usuario .ev-register-legal__doc-action {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--ev-verde);
  font-size: .67rem;
  font-weight: 800;
  white-space: nowrap;
}

#crear_usuario .ev-register-legal__privacy-summary {
  margin-top: 10px;
  border: 0;
  border-top: 1px solid #E8EDF3;
  border-radius: 0;
  background: transparent;
}

#crear_usuario .ev-register-legal__privacy-summary summary {
  padding: 10px 2px 0;
  color: #475569;
  font-size: .74rem;
  font-weight: 700;
}

#crear_usuario .ev-register-legal__privacy-summary summary span i {
  color: var(--ev-verde);
}

#crear_usuario .ev-register-legal__privacy-summary > div {
  margin-top: 9px;
  padding: 11px 12px;
  border: 1px solid #E2E8F0;
  border-radius: 11px;
  background: #F8FAFC;
  color: #526173;
  font-size: .71rem;
  line-height: 1.5;
}

#crear_usuario .ev-register-legal__consent-panel {
  padding: 14px;
  border-color: #D7EADF;
  background: #FBFEFC;
}

#crear_usuario .ev-register-legal__consent-heading {
  display: grid;
  grid-template-columns: 34px minmax(0, 1fr);
  gap: 9px;
  align-items: center;
  margin-bottom: 10px;
}

#crear_usuario .ev-register-legal__consent-icon {
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  border-radius: 10px;
  color: #166534;
  background: #DCFCE7;
  font-size: .9rem;
}

#crear_usuario .ev-register-legal__consent-heading p {
  margin: 2px 0 0;
  color: #64748B;
  font-size: .72rem;
  line-height: 1.35;
}

#crear_usuario .ev-register-legal__consents {
  gap: 0;
  overflow: hidden;
  border: 1px solid #DDE7E1;
  border-radius: 13px;
  background: #FFFFFF;
}

#crear_usuario .ev-register-legal__check {
  padding: 12px 13px;
  border: 0;
  border-radius: 0;
  background: #FFFFFF;
  box-shadow: none;
}

#crear_usuario .ev-register-legal__check + .ev-register-legal__check {
  border-top: 1px solid #E7ECE9;
}

#crear_usuario .ev-register-legal__check:hover {
  background: #FCFEFD;
}

#crear_usuario .ev-register-legal__check:focus-within {
  border-color: transparent;
  background: #F7FEF9;
  box-shadow: inset 3px 0 0 var(--ev-verde);
}

#crear_usuario .ev-register-legal__check.is-invalid {
  border-color: transparent;
  background: #FFF7F7;
  box-shadow: inset 3px 0 0 #E5484D;
}

#crear_usuario .ev-register-legal__check label {
  color: #3E4C5F;
  font-size: .75rem;
  line-height: 1.45;
}

#crear_usuario .ev-register-legal__note {
  margin-top: 9px;
  padding: 0 2px;
  gap: 7px;
  align-items: center;
  color: #64748B;
  background: transparent;
  border: 0;
  border-radius: 0;
  font-size: .69rem;
  line-height: 1.35;
}

#crear_usuario .ev-register-legal__note .bi {
  margin: 0;
  color: var(--ev-verde);
}

@media (max-width: 767.98px) {
  #crear_usuario.show .modal-dialog,
  #crear_usuario .modal-dialog {
    width: 100%;
    max-width: none;
    max-height: 100dvh;
    min-height: 100dvh;
    margin: 0;
  }

  #crear_usuario .modal-content {
    min-height: 100dvh;
    max-height: 100dvh;
    border-radius: 0;
  }

  #crear_usuario .modal-header.bg-success,
  #crear_usuario .modal-footer {
    border-radius: 0;
  }

  #crear_usuario .modal-body {
    padding: 18px 15px 16px;
  }

  #crear_usuario .modal-footer {
    min-height: 64px;
    padding: 10px 14px;
  }

  #crear_usuario .progress {
    height: 30px !important;
    margin-bottom: 16px !important;
  }

  #crear_usuario .step .row.g-3 {
    padding: 14px 11px;
  }

  #crear_usuario .ev-register-legal__heading {
    grid-template-columns: 40px minmax(0, 1fr);
    padding-bottom: 5px;
  }

  #crear_usuario .ev-register-legal__icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
  }

  #crear_usuario .ev-register-legal__docs {
    grid-template-columns: 1fr;
  }

  #crear_usuario .ev-register-legal__section-badge {
    display: none;
  }

  #crear_usuario .ev-register-legal__doc-action {
    font-size: 0;
  }

  #crear_usuario .ev-register-legal__doc-action i {
    font-size: .78rem;
  }

  #crear_usuario .ev-register-legal__review,
  #crear_usuario .ev-register-legal__consent-panel {
    padding: 12px;
    border-radius: 15px;
  }
}

/* Confirmación final de registro · SweetAlert con identidad EV */
.ev-swal-registro {
  width: min(460px, calc(100% - 30px)) !important;
  padding: 28px 28px 24px !important;
  border: 1px solid rgba(15, 89, 47, .10) !important;
  border-radius: 22px !important;
  box-shadow: 0 30px 70px rgba(15, 23, 42, .22) !important;
}

.ev-swal-registro .swal2-title {
  color: #1F2937 !important;
  font-size: 1.55rem !important;
  font-weight: 750 !important;
}

.ev-swal-registro .swal2-html-container {
  color: #5B6472 !important;
  font-size: .98rem !important;
}

.ev-swal-registro__confirm {
  min-width: 126px;
  min-height: 44px;
  padding: 10px 22px;
  border: 0;
  border-radius: 999px;
  color: #FFFFFF;
  background: #0E7A43;
  box-shadow: 0 12px 24px rgba(14, 122, 67, .20);
  font-family: inherit;
  font-size: .92rem;
  font-weight: 700;
  transition: transform .18s ease, background-color .18s ease, box-shadow .18s ease;
}

.ev-swal-registro__confirm:hover {
  background: #0F592F;
  box-shadow: 0 14px 28px rgba(15, 89, 47, .24);
  transform: translateY(-1px);
}

.ev-swal-registro__confirm:focus-visible {
  outline: 0;
  box-shadow: 0 0 0 4px rgba(234, 124, 18, .22), 0 14px 28px rgba(15, 89, 47, .20);
}

</style>