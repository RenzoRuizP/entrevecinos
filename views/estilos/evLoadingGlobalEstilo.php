<style id="ev-loading-global-style">
/* ============================================================
   EV — cargador global unificado
   El mismo lenguaje visual del cargador mostrado al iniciar sesión.
============================================================ */
:root{
  --ev-loader-green-dark:#0F592F;
  --ev-loader-green:#0E7A43;
  --ev-loader-green-light:#16A34A;
  --ev-loader-orange:#EA7C12;
  --ev-loader-border:#DDF4E6;
}

@keyframes evGlobalLoaderSpin{
  to{transform:rotate(360deg)}
}

.ev-global-loading-overlay{
  position:fixed;
  inset:0;
  z-index:99999;
  display:none;
  align-items:center;
  justify-content:center;
  padding:18px;
  background:rgba(248,250,252,.72);
  contain:layout paint style;
}

.ev-global-loading-compact,
.ev-loading-compact,
.ev-swal-login__loading,
.ev-shell-loading .ev-box{
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  gap:10px!important;
  min-height:48px!important;
  padding:10px 18px!important;
  border:1px solid rgba(229,231,235,.96)!important;
  border-radius:999px!important;
  background:#fff!important;
  color:var(--ev-loader-green-dark)!important;
  box-shadow:0 14px 30px rgba(15,23,42,.14)!important;
  contain:layout paint style!important;
  font-family:Poppins,system-ui,-apple-system,"Segoe UI",sans-serif!important;
  font-size:.92rem!important;
  font-weight:850!important;
  line-height:1!important;
  letter-spacing:-.01em!important;
}

.ev-global-loading-spinner,
.ev-loading-compact .spinner,
.ev-swal-login__spinner,
.ev-shell-loading .ev-spin{
  box-sizing:border-box!important;
  width:24px!important;
  height:24px!important;
  flex:0 0 24px!important;
  border:3px solid var(--ev-loader-border)!important;
  border-top-color:var(--ev-loader-green)!important;
  border-right-color:var(--ev-loader-orange)!important;
  border-bottom-color:#B7E7C8!important;
  border-left-color:#E8F6ED!important;
  border-radius:50%!important;
  background:transparent!important;
  animation:evGlobalLoaderSpin .72s linear infinite!important;
}

.ev-global-loading-text{
  color:var(--ev-loader-green-dark)!important;
  font-weight:850!important;
  line-height:1!important;
  white-space:nowrap;
}

/* Homologación de spinners internos ya existentes en los módulos. */
.spinner-border,
.ev-notification-spinner,
.ev-notificaciones-spinner,
.ev-so-spinner,
.ev-as-loading>span,
.ev-lr-loading>span,
.ev-dg-loading>span,
.ev-com-loading>span,
.ev-wallet-loading-spinner,
.ev-config-combobox-loading .spinner-border{
  box-sizing:border-box!important;
  border-color:var(--ev-loader-border)!important;
  border-top-color:var(--ev-loader-green)!important;
  border-right-color:var(--ev-loader-orange)!important;
  border-bottom-color:#B7E7C8!important;
  border-left-color:#E8F6ED!important;
  border-radius:50%!important;
  background:transparent!important;
  animation:evGlobalLoaderSpin .72s linear infinite!important;
}

@media(max-width:575.98px){
  .ev-global-loading-compact,
  .ev-loading-compact,
  .ev-swal-login__loading,
  .ev-shell-loading .ev-box{
    min-height:46px!important;
    padding:9px 15px!important;
    font-size:.86rem!important;
  }
}

@media(prefers-reduced-motion:reduce){
  .ev-global-loading-spinner,
  .ev-loading-compact .spinner,
  .ev-swal-login__spinner,
  .ev-shell-loading .ev-spin,
  .spinner-border,
  .ev-notification-spinner,
  .ev-notificaciones-spinner,
  .ev-so-spinner,
  .ev-as-loading>span,
  .ev-lr-loading>span,
  .ev-dg-loading>span,
  .ev-com-loading>span{
    animation-duration:1.25s!important;
  }
}
</style>
