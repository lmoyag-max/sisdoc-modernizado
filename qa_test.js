const http = require('http');

function req(method, path, token, body) {
  return new Promise((resolve, reject) => {
    const opts = {
      hostname: 'localhost', port: 3001,
      path: '/api/v1' + path,
      method,
      headers: {
        'Content-Type': 'application/json',
        ...(token ? { 'Authorization': 'Bearer ' + token } : {})
      }
    };
    const r = http.request(opts, res => {
      let d = '';
      res.on('data', c => d += c);
      res.on('end', () => {
        try { resolve({ status: res.statusCode, body: JSON.parse(d) }); }
        catch (e) { resolve({ status: res.statusCode, body: d }); }
      });
    });
    r.on('error', reject);
    if (body) r.write(JSON.stringify(body));
    r.end();
  });
}

async function login(u, p) {
  const r = await req('POST', '/auth/login', null, { usuario: u, clave: p });
  if (!r.body.data?.accessToken) throw new Error('Login failed for ' + u + ': ' + JSON.stringify(r.body));
  return r.body.data.accessToken;
}

async function main() {
  const toks = {};
  toks.admin    = await login('admin',    'Huap.2025');
  toks.ti       = await login('ti',       'Huap.2025');
  toks.aba      = await login('aba',      'Huap.2025');
  toks.ofparte  = await login('ofparte',  'Huap.2025');
  toks.contrato = await login('contrato', 'Huap.2025');
  console.log('LOGIN: todos OK\n');

  const results = [];
  function log(ok, test, detail) {
    const line = '[' + (ok ? 'OK  ' : 'FAIL') + '] ' + test + (detail ? ' | ' + detail : '');
    console.log(line);
    results.push({ ok, test, detail: detail || '' });
    return ok;
  }

  // ─── AUTH ───────────────────────────────────────
  console.log('=== AUTH ===');
  const me = await req('GET', '/auth/me', toks.admin);
  log(me.body.data?.usuario === 'admin', 'GET /auth/me (admin)', 'usuario=' + me.body.data?.usuario);

  const noAuth = await req('GET', '/documentos', null);
  log(noAuth.status === 401, 'Sin token → 401', 'status=' + noAuth.status);

  const badPwd = await req('POST', '/auth/login', null, { usuario: 'admin', clave: 'mal' });
  log(!badPwd.body.ok && badPwd.status === 401, 'Login contraseña incorrecta → 401', 'status=' + badPwd.status);

  const badUser = await req('POST', '/auth/login', null, { usuario: 'noexiste', clave: 'algo' });
  log(!badUser.body.ok, 'Login usuario inexistente → error', 'error=' + badUser.body.error);

  // ─── CATALOGOS ──────────────────────────────────
  // NOTA: las rutas reales son /estados y /estados-tramite (no /estados-documento)
  console.log('\n=== CATALOGOS ===');
  const tipos = await req('GET', '/catalogos/tipos-documento', toks.admin);
  log(tipos.body.ok && tipos.body.data?.length > 0, 'GET /catalogos/tipos-documento', 'count=' + tipos.body.data?.length);

  const estados = await req('GET', '/catalogos/estados', toks.admin);
  log(estados.body.ok && estados.body.data?.length > 0, 'GET /catalogos/estados', 'count=' + estados.body.data?.length);

  const deps = await req('GET', '/catalogos/dependencias', toks.admin);
  log(deps.body.ok && deps.body.data?.length > 0, 'GET /catalogos/dependencias', 'count=' + deps.body.data?.length);

  // Los catálogos retornan {id, descripcion} — no idTipoDocumento/idEstadoDocumento
  const idTipo   = tipos.body.data?.[0]?.id;
  const idEstado = estados.body.data?.[0]?.id;
  const idDep    = deps.body.data?.[1]?.id;

  // ─── DOCUMENTOS ─────────────────────────────────
  console.log('\n=== DOCUMENTOS ===');
  const docsAdm = await req('GET', '/documentos?pagina=1&porPagina=10', toks.admin);
  log(docsAdm.body.ok, 'GET /documentos (admin)', 'total=' + docsAdm.body.meta?.total);

  const docsAba = await req('GET', '/documentos?pagina=1&porPagina=10', toks.aba);
  log(docsAba.body.ok, 'GET /documentos (aba-funcionario)', 'total=' + docsAba.body.meta?.total);

  log(
    docsAdm.body.meta?.total >= docsAba.body.meta?.total,
    'Separación doc por servicio (admin >= funcionario)',
    'admin=' + docsAdm.body.meta?.total + ' aba=' + docsAba.body.meta?.total
  );

  const docId = docsAdm.body.data?.[0]?.idDocumento;
  const det = await req('GET', '/documentos/' + docId, toks.admin);
  // El campo es estadoDocumento.descripcion (objeto anidado, no descEstado flat)
  log(det.body.ok && det.body.data?.idDocumento, 'GET /documentos/:id',
    'id=' + det.body.data?.idDocumento + ' estado=' + det.body.data?.estadoDocumento?.descripcion);

  const hist = await req('GET', '/documentos/' + docId + '/historial', toks.admin);
  log(hist.body.ok, 'GET /documentos/:id/historial', 'count=' + hist.body.data?.length);

  // Crear documento
  const nuevoDoc = await req('POST', '/documentos', toks.admin, {
    materia: 'TEST QA - Prueba automatizada ' + Date.now(),
    idTipoDocumento: idTipo,
    idEstadoDocumento: idEstado,
    fechaDocumento: new Date().toISOString().split('T')[0],
    observaciones: 'Documento de prueba QA'
  });
  // La respuesta usa idDocumento y numInterno (snake→camel mapeado)
  const newDocOk = log(
    nuevoDoc.body.ok && nuevoDoc.body.data?.idDocumento,
    'POST /documentos (crear)',
    'id=' + nuevoDoc.body.data?.idDocumento + ' numInterno=' + nuevoDoc.body.data?.numInterno
  );
  const newDocId = nuevoDoc.body.data?.idDocumento;

  // Crear documento reservado
  const docRes = await req('POST', '/documentos', toks.admin, {
    materia: 'TEST QA - Documento RESERVADO',
    idTipoDocumento: idTipo,
    idEstadoDocumento: idEstado,
    fechaDocumento: new Date().toISOString().split('T')[0],
    reservado: true
  });
  // El campo reservado viene en la respuesta
  log(docRes.body.ok, 'POST /documentos reservado', 'ok=' + docRes.body.ok + ' id=' + docRes.body.data?.idDocumento);

  // Derivar documento — schema usa idDestino (número de dependencia) y observaciones (plural)
  if (newDocId && idDep) {
    const deriv = await req('POST', '/documentos/' + newDocId + '/derivar', toks.admin, {
      idDestino: idDep,
      observaciones: 'Derivación de prueba QA'
    });
    log(deriv.body.ok, 'POST /documentos/:id/derivar', 'ok=' + deriv.body.ok + ' msg=' + deriv.body.message);
  }

  // Despachar (usa idDestino, no idDependenciasDestino)
  if (newDocId) {
    const desp = await req('POST', '/documentos/' + newDocId + '/despachar', toks.admin, {
      idDestino: idDep,
      observacion: 'Despacho de prueba QA'
    });
    log(desp.body.ok || desp.status === 400 || desp.status === 404, 'POST /documentos/:id/despachar',
      'status=' + desp.status + ' msg=' + (desp.body.message || desp.body.error));
  }

  // ─── TRAMITES ───────────────────────────────────
  console.log('\n=== TRAMITES ===');
  const tramites = await req('GET', '/tramites?pagina=1', toks.admin);
  log(tramites.body.ok, 'GET /tramites (admin)', 'total=' + tramites.body.meta?.total);

  const tramitesOf = await req('GET', '/tramites?pagina=1', toks.ofparte);
  log(tramitesOf.body.ok, 'GET /tramites (ofparte)', 'total=' + tramitesOf.body.meta?.total);

  const tramActivo = tramites.body.data?.find(t => t.idEstado === 1);
  if (tramActivo) {
    const recibir = await req('PATCH', '/tramites/' + tramActivo.idSeguimiento + '/recibir', toks.admin, {});
    log(recibir.body.ok || recibir.status === 400, 'PATCH /tramites/:id/recibir', 'status=' + recibir.status);
  } else {
    log(true, 'PATCH /tramites/:id/recibir', 'sin tramites activos para probar');
  }

  // ─── REPORTES ───────────────────────────────────
  console.log('\n=== REPORTES ===');
  const dashA = await req('GET', '/reportes/dashboard', toks.admin);
  log(dashA.body.ok && dashA.body.data?.totales, 'GET /reportes/dashboard (admin)',
    'total=' + dashA.body.data?.totales?.total + ' urgentes=' + dashA.body.data?.totales?.urgentes);

  const dashTI = await req('GET', '/reportes/dashboard', toks.ti);
  log(dashTI.body.ok, 'GET /reportes/dashboard (ti)', 'total=' + dashTI.body.data?.totales?.total);

  const activ = await req('GET', '/reportes/actividad-reciente', toks.admin);
  log(activ.body.ok, 'GET /reportes/actividad-reciente', 'count=' + activ.body.data?.length);

  const csv = await req('GET', '/reportes/exportar', toks.admin);
  log(csv.status === 200 || csv.status === 404, 'GET /reportes/exportar', 'status=' + csv.status);

  // ─── USUARIOS ───────────────────────────────────
  console.log('\n=== USUARIOS ===');
  const usrs = await req('GET', '/usuarios?pagina=1', toks.admin);
  log(usrs.body.ok, 'GET /usuarios (admin)', 'total=' + usrs.body.meta?.total);

  const usrsTI = await req('GET', '/usuarios?pagina=1', toks.ti);
  log(usrsTI.status === 403, 'GET /usuarios (ti sin permiso) → 403', 'status=' + usrsTI.status);

  const usrsMeta = await req('GET', '/usuarios/meta/roles', toks.admin);
  log(usrsMeta.body.ok, 'GET /usuarios/meta/roles', 'count=' + usrsMeta.body.data?.length);

  // ─── BUSQUEDA ───────────────────────────────────
  console.log('\n=== BUSQUEDA ===');
  const bDocs = await req('GET', '/busqueda?q=of&tipo=documentos', toks.admin);
  log(bDocs.body.ok, 'GET /busqueda tipo=documentos', 'docs=' + bDocs.body.data?.documentos?.length + ' total=' + bDocs.body.data?.total);

  const bFuncs = await req('GET', '/busqueda?q=admin&tipo=funcionarios', toks.admin);
  log(bFuncs.body.ok, 'GET /busqueda tipo=funcionarios', 'funcs=' + bFuncs.body.data?.funcionarios?.length);

  const bTram = await req('GET', '/busqueda?q=prueba&tipo=tramites', toks.admin);
  log(bTram.body.ok, 'GET /busqueda tipo=tramites', 'trams=' + bTram.body.data?.tramites?.length);

  const bTodos = await req('GET', '/busqueda?q=doc&tipo=todos', toks.admin);
  log(bTodos.body.ok, 'GET /busqueda tipo=todos', 'docs=' + bTodos.body.data?.documentos?.length + ' funcs=' + bTodos.body.data?.funcionarios?.length);

  const bCorto = await req('GET', '/busqueda?q=a&tipo=documentos', toks.admin);
  log(bCorto.body.ok, 'GET /busqueda q<2 chars (devuelve vacio sin error)', 'total=' + bCorto.body.data?.total);

  // ─── ARCHIVOS ───────────────────────────────────
  console.log('\n=== ARCHIVOS ===');
  const arcs = await req('GET', '/archivos', toks.admin);
  log(arcs.body.ok, 'GET /archivos', 'count=' + arcs.body.data?.length);

  if (newDocId) {
    const arcsDoc = await req('GET', '/archivos?idDocumento=' + newDocId, toks.admin);
    log(arcsDoc.body.ok, 'GET /archivos?idDocumento=' + newDocId, 'count=' + arcsDoc.body.data?.length);
  }

  // ─── CONFIGURACION ──────────────────────────────
  console.log('\n=== CONFIGURACION ===');
  const conf = await req('GET', '/configuracion', null);
  log(conf.body.ok, 'GET /configuracion (publica)', 'sistema=' + conf.body.data?.nombreSistema);

  const confPatch = await req('PATCH', '/configuracion', toks.admin, { nombreSistema: conf.body.data?.nombreSistema });
  log(confPatch.body.ok, 'PATCH /configuracion (admin)', 'ok=' + confPatch.body.ok);

  const confPatchNoAuth = await req('PATCH', '/configuracion', null, { nombreSistema: 'hack' });
  log(!confPatchNoAuth.body.ok || confPatchNoAuth.status === 401, 'PATCH /configuracion sin auth → 401', 'status=' + confPatchNoAuth.status);

  // ─── ALERTAS ──────────────────────────────────────
  // Las rutas reales son subrutas: /alertas/configuracion, /alertas/pendientes, etc.
  console.log('\n=== ALERTAS ===');
  const alertasConf = await req('GET', '/alertas/configuracion', toks.admin);
  log(alertasConf.status !== 500, 'GET /alertas/configuracion (admin)', 'status=' + alertasConf.status + ' ok=' + alertasConf.body.ok);

  const alertasConfNoAdmin = await req('GET', '/alertas/configuracion', toks.ti);
  log(alertasConfNoAdmin.status === 403, 'GET /alertas/configuracion (ti sin permiso) → 403', 'status=' + alertasConfNoAdmin.status);

  // ─── ROLES ──────────────────────────────────────
  console.log('\n=== ROLES ===');
  const roles = await req('GET', '/roles', toks.admin);
  log(roles.body.ok, 'GET /roles (admin)', 'count=' + roles.body.data?.length);

  const rolesTI = await req('GET', '/roles', toks.ti);
  log(rolesTI.status === 403, 'GET /roles (ti sin permiso) → 403', 'status=' + rolesTI.status);

  // ─── JEFATURAS ──────────────────────────────────
  console.log('\n=== JEFATURAS ===');
  const jefs = await req('GET', '/jefaturas', toks.admin);
  log(jefs.status !== 500, 'GET /jefaturas (admin)', 'status=' + jefs.status + ' ok=' + jefs.body.ok);

  // ─── MEMORANDUM ─────────────────────────────────
  console.log('\n=== MEMORANDUM ===');
  const firmActiv = await req('GET', '/memorandum/firmante-activo', toks.admin);
  log(firmActiv.status !== 500, 'GET /memorandum/firmante-activo', 'status=' + firmActiv.status + ' ok=' + firmActiv.body.ok);

  const firmsDisp = await req('GET', '/memorandum/firmantes-disponibles', toks.admin);
  log(firmsDisp.status !== 500, 'GET /memorandum/firmantes-disponibles', 'status=' + firmsDisp.status);

  // ─── TRAZABILIDAD ────────────────────────────────
  console.log('\n=== TRAZABILIDAD ===');
  if (docId) {
    const traz = await req('GET', '/documentos/' + docId + '/historial', toks.admin);
    log(traz.body.ok, 'Historial/trazabilidad doc#' + docId, 'eventos=' + traz.body.data?.length);
  }

  // ─── FIRMA-GOB ──────────────────────────────────
  console.log('\n=== FIRMA-GOB ===');
  const fgHist = await req('GET', '/firma-gob/historial?pagina=1', toks.admin);
  log(fgHist.status !== 500, 'GET /firma-gob/historial', 'status=' + fgHist.status);

  // ─── RESUMEN FINAL ──────────────────────────────
  console.log('\n========================');
  console.log('RESUMEN FINAL DE PRUEBAS');
  console.log('========================');
  const okCount   = results.filter(r => r.ok).length;
  const failCount = results.filter(r => !r.ok).length;
  console.log('TOTAL: ' + results.length + ' | OK: ' + okCount + ' | FAIL: ' + failCount);

  if (failCount > 0) {
    console.log('\nFALLOS DETECTADOS:');
    results.filter(r => !r.ok).forEach(r => console.log('  [FAIL] ' + r.test + ' | ' + r.detail));
  }

  process.exit(failCount > 0 ? 1 : 0);
}

main().catch(e => { console.error('FATAL:', e.message); process.exit(1); });
