<?php
if (empty($esAdministradorGeneralConsulta)) { return; }
include_once __DIR__ . '/../estilos/adminCommunitySelectorEstilo.php';
$evScopeModule = preg_replace('/[^a-z0-9_-]/i', '', (string)($evAdminScopeModule ?? 'general')) ?: 'general';
$evScopeDescription = (string)($evAdminScopeDescription ?? 'Selecciona la comunidad que deseas consultar sin cambiar tu sesión administrativa.');
$evScopeCommunities = is_array($comunidadesConsultaAdmin ?? null) ? $comunidadesConsultaAdmin : [];
?>
<section class="ev-admin-scope" data-ev-admin-scope-card="<?= htmlspecialchars($evScopeModule, ENT_QUOTES, 'UTF-8') ?>" aria-label="Comunidad a consultar">
  <div class="ev-admin-scope__copy">
    <span class="ev-admin-scope__icon"><i class="bi bi-buildings"></i></span>
    <div><h3>Comunidad a consultar</h3><p><?= htmlspecialchars($evScopeDescription, ENT_QUOTES, 'UTF-8') ?></p></div>
  </div>
  <div class="ev-admin-scope__field">
    <label for="evAdminScope_<?= htmlspecialchars($evScopeModule, ENT_QUOTES, 'UTF-8') ?>">Condominio o urbanización</label>
    <select id="evAdminScope_<?= htmlspecialchars($evScopeModule, ENT_QUOTES, 'UTF-8') ?>" class="ev-searchable-select" data-ev-admin-scope="<?= htmlspecialchars($evScopeModule, ENT_QUOTES, 'UTF-8') ?>" data-search-placeholder="Buscar condominio o urbanización">
      <option value="">---- Seleccionar ----</option>
      <?php foreach ($evScopeCommunities as $comunidad):
        $tipo = strtolower(trim((string)($comunidad['tipo_conjunto'] ?? '')));
        $codigo = (int)($comunidad['codigo_comunidad'] ?? 0);
        if (!in_array($tipo, ['condominio','urbanizacion'], true) || $codigo <= 0) continue;
        $tipoNombre = $tipo === 'urbanizacion' ? 'Urbanización' : 'Condominio';
        $label = $tipoNombre . ' · ' . (string)($comunidad['nombre'] ?? 'Comunidad');
        $distrito = trim((string)($comunidad['nombre_distrito'] ?? ''));
        if ($distrito !== '') $label .= ' · ' . $distrito;
      ?>
        <option value="<?= htmlspecialchars($tipo . '|' . $codigo, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
      <?php endforeach; ?>
    </select>
    <div class="ev-admin-scope__status"><i class="bi bi-info-circle"></i><span data-ev-admin-scope-status>Sin filtro: se muestran todas las comunidades. Selecciona una para acotar la consulta.</span></div>
  </div>
</section>
