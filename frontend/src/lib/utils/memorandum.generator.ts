import { jsPDF, GState } from 'jspdf';
import { BRANDING } from '@/lib/config/branding';

// ── Tipos públicos ────────────────────────────────────────────

export interface MemorandumData {
  /** null o 'BORRADOR' para previsualización, correlativo real para definitivo */
  correlativo:     string | null;
  /** true = previsualización (muestra marca de agua BORRADOR) */
  esBorrador:      boolean;
  fechaDocumento:  string | null;
  /** Servicio origen (dependencia del usuario creador) */
  origen:          string | null;
  /** Uno o varios destinos internos/externos */
  destinos:        string[];
  materia:         string;
  referencia:      string | null;
  /** Cuerpo completo del memorándum (puede tener saltos de línea) */
  cuerpo:          string;
  /** Nombre del firmante (titular o subrogante) */
  nombreFirmante:     string;
  cargoFirmante:      string;
  /** Data URL de la imagen combinada firma+timbre en su formato original (PNG o JPEG) */
  firmaTimbreBase64:  string | null;
  /** Dimensiones naturales en píxeles — se usan para escala proporcional sin deformación */
  firmaTimbreNatW?:   number;
  firmaTimbreNatH?:   number;
  /** Logo institucional */
  logoBase64:         string | null;
  /** Servicio/dependencia del firmante — si no viene, se usa `origen` */
  servicioFirmante?:  string | null;
  /**
   * Mecanismo de firma a dibujar en el bloque final. Si se omite (caso de
   * FirmaGOB hoy, y de la vista previa/borrador), el comportamiento es
   * idéntico al actual — este campo es aditivo y no cambia ningún flujo
   * existente cuando no se especifica.
   */
  mecanismoFirma?:    'FIRMA_GOB' | 'FIRMA_SIMPLE';
  /** Requerido cuando mecanismoFirma === 'FIRMA_SIMPLE' — evidencia emitida por el backend */
  datosFirmaSimple?: {
    codigoVerificacion: string;
    hashOriginalCorto:  string;
    fechaFirma:         string; // ISO
  };
}

// ── Helpers internos ──────────────────────────────────────────

function fmtFecha(val: string | null | undefined): string {
  if (!val) return new Date().toLocaleDateString('es-CL', { year: 'numeric', month: 'long', day: 'numeric' });
  try {
    return new Date(val).toLocaleDateString('es-CL', { year: 'numeric', month: 'long', day: 'numeric' });
  } catch { return val; }
}

/** Formatea fecha/hora actual en horario de Chile: "DD-MM-AAAA HH:MM CLT". */
function fmtFechaFirma(d: Date): string {
  const formatter = new Intl.DateTimeFormat('es-CL', {
    timeZone: 'America/Santiago',
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit', hour12: false,
  });
  const parts = formatter.formatToParts(d);
  const get = (t: string) => parts.find((p) => p.type === t)?.value ?? '';
  const hora = get('hour') === '24' ? '00' : get('hour');
  return `${get('day')}-${get('month')}-${get('year')} ${hora}:${get('minute')} CLT`;
}

/**
 * Dibuja texto curvo a lo largo de un arco de circunferencia, con espaciado
 * proporcional al ancho real de cada caracter (evita que letras anchas
 * queden apretadas). `flip=true` orienta el texto para el arco inferior de
 * forma que se lea normal (no al revés) — usado para "GOBIERNO DE CHILE".
 */
function drawArcText(
  doc: jsPDF, text: string, cx: number, cy: number, radius: number,
  centerAngleDeg: number, fontSize: number, color: [number, number, number], flip: boolean,
): void {
  doc.setFontSize(fontSize);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(...color);

  const chars  = text.split('');
  const widths = chars.map((ch) => doc.getTextWidth(ch));
  const totalWidth = widths.reduce((a, b) => a + b, 0);
  const totalAngle = (totalWidth / radius) * (180 / Math.PI);

  const dir = flip ? 1 : -1;
  let angleDeg = centerAngleDeg - dir * (totalAngle / 2);

  chars.forEach((ch, i) => {
    const halfCharAngle = (widths[i] / radius) * (180 / Math.PI) / 2;
    const charAngle = angleDeg + dir * halfCharAngle;
    const angleRad  = (charAngle * Math.PI) / 180;
    const x = cx + radius * Math.cos(angleRad);
    const y = cy - radius * Math.sin(angleRad);
    const rotation = flip ? charAngle + 90 : charAngle - 90;
    doc.text(ch, x, y, { angle: rotation, align: 'center' });
    angleDeg += dir * halfCharAngle * 2;
  });
}

/**
 * Dibuja el sello "Firma Electrónica Avanzada — Gobierno de Chile" (círculo
 * con texto curvo + icono) y, a su derecha, los datos del firmante. Se usa
 * cuando el documento se firma vía FirmaGov (sin imagen de firma+timbre
 * escaneada) — la firma criptográfica real va embebida en el PDF por
 * FirmaGov; este sello es la representación visual equivalente.
 */
function drawSelloFirmaElectronica(
  doc: jsPDF, cx: number, cyTop: number, nombreFirmante: string, cargoFirmante: string,
  servicio: string | null, textX: number,
): number {
  const azulGob: [number, number, number] = [70, 110, 165];
  const r = 13;
  const cy = cyTop + r;

  doc.setDrawColor(...azulGob);
  doc.setLineWidth(0.5);
  doc.circle(cx, cy, r, 'S');
  doc.setLineWidth(0.3);
  doc.circle(cx, cy, r - 1.4, 'S');

  drawArcText(doc, 'FIRMA ELECTRÓNICA AVANZADA', cx, cy, r - 4, 90, 4.3, azulGob, false);
  drawArcText(doc, 'GOBIERNO DE CHILE', cx, cy, r - 4, -90, 4.5, azulGob, true);

  // Icono: documento con líneas
  doc.setDrawColor(...azulGob);
  doc.setLineWidth(0.4);
  doc.roundedRect(cx - 3.7, cy - 5, 7.4, 9.3, 1, 1, 'S');
  doc.setLineWidth(0.25);
  doc.line(cx - 1.9, cy - 2.3, cx + 1.9, cy - 2.3);
  doc.line(cx - 1.9, cy - 0.5, cx + 1.9, cy - 0.5);
  doc.line(cx - 1.9, cy + 1.3, cx + 0.4, cy + 1.3);
  doc.setFillColor(...azulGob);
  doc.circle(cx + 2.1, cy + 3.1, 1, 'F');

  // Datos del firmante a la derecha del sello
  let ty = cy - 9;
  doc.setTextColor(40, 40, 40);
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(8);
  doc.text('Firmado por:', textX, ty);
  ty += 4.3;
  doc.setFontSize(8.5);
  doc.text(nombreFirmante, textX, ty);
  ty += 4.3;
  doc.setFont('helvetica', 'normal');
  doc.setFontSize(7.5);
  doc.text(cargoFirmante, textX, ty);
  ty += 4.3;
  doc.text(`Fecha: ${fmtFechaFirma(new Date())}`, textX, ty);
  ty += 4.3;
  if (servicio) doc.text(servicio, textX, ty);

  return cy + r; // y final ocupado por el sello (borde inferior del círculo)
}

/**
 * Dibuja el "pie de firma" de Firma Simple DOC360: una sola tarjeta
 * centrada en la página, con el sello/imagen de firma-timbre a la
 * IZQUIERDA y el texto de la firma (rótulo, declaración, nombre, cargo,
 * fecha, código de verificación + hash) alineado a la derecha del sello,
 * dentro del mismo recuadro — igual disposición que un pie de firma
 * institucional con logo al lado del texto. La imagen es solo
 * representación visual — la evidencia real es este bloque de datos,
 * registrado en memorandum_firma_simple.
 * El alto del recuadro se calcula a partir del contenido real dibujado (no es
 * un valor fijo), para que el borde siempre encierre exactamente el contenido.
 */
function drawSelloFirmaSimple(
  doc: jsPDF, cW: number, mg: number, yTop: number,
  nombreFirmante: string, cargoFirmante: string, servicio: string | null,
  firmaTimbreBase64: string | null, natW: number, natH: number,
  codigoVerificacion: string, hashOriginalCorto: string, fechaFirma: string,
  colores: { principal: [number, number, number]; grisLabel: [number, number, number]; negro: [number, number, number] },
): number {
  const cardW   = Math.min(130, cW);
  const cardX   = mg + (cW - cardW) / 2;
  const padX    = 5;
  const padTop  = 3.5;
  const imgZoneW = 30;
  const gap      = 4;

  const textX    = cardX + padX + imgZoneW + gap;
  const textMaxW = cardW - padX * 2 - imgZoneW - gap;

  // ── Columna de texto (izquierda a derecha del sello) ──────────
  let ty = yTop + padTop;

  doc.setTextColor(...colores.principal);
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(8);
  doc.text('FIRMA SIMPLE DOC360', textX, ty);
  ty += 3.4;

  doc.setFont('helvetica', 'normal');
  doc.setFontSize(5.8);
  doc.setTextColor(...colores.grisLabel);
  const declaracion = doc.splitTextToSize(
    'Firmado electrónicamente mediante credenciales institucionales DOC360.', textMaxW,
  ) as string[];
  declaracion.forEach((linea) => { doc.text(linea, textX, ty); ty += 2.6; });

  ty += 0.6;
  doc.setTextColor(...colores.negro);
  doc.setFont('helvetica', 'bold');
  doc.setFontSize(7.5);
  doc.text(nombreFirmante, textX, ty);
  ty += 3.2;

  doc.setFont('helvetica', 'normal');
  doc.setFontSize(6.5);
  doc.setTextColor(...colores.negro);
  const cargoServicio = servicio ? `${cargoFirmante} · ${servicio}` : cargoFirmante;
  const cargoLines = doc.splitTextToSize(cargoServicio, textMaxW) as string[];
  cargoLines.forEach((linea) => { doc.text(linea, textX, ty); ty += 2.8; });

  doc.setTextColor(...colores.grisLabel);
  doc.text(fmtFechaFirma(new Date(fechaFirma)), textX, ty);
  ty += 3.2;

  // Código de verificación y hash en una sola línea.
  doc.setFontSize(5.8);
  doc.text(`Código: ${codigoVerificacion}  ·  Hash SHA-256: ${hashOriginalCorto}…`, textX, ty);
  ty += padTop; // padding inferior simétrico al superior

  // ── Sello/imagen — a la izquierda, dentro del mismo recuadro ──
  // Se dibuja después de calcular el bloque de texto para poder centrarlo
  // verticalmente respecto a él (o al menos ocupar el mismo alto disponible).
  const textBlockH = ty - (yTop + padTop);
  let imgBottom = yTop + padTop;
  if (firmaTimbreBase64) {
    const MAX_FT_W = imgZoneW - 2, MAX_FT_H = Math.max(18, textBlockH);
    let ftW = MAX_FT_W, ftH = MAX_FT_H;
    if (natW > 0 && natH > 0) {
      const ratio = natH / natW;
      ftW = MAX_FT_W; ftH = ftW * ratio;
      if (ftH > MAX_FT_H) { ftH = MAX_FT_H; ftW = ftH / ratio; }
    }
    const imgZoneCx = cardX + padX + imgZoneW / 2;
    const ftX = imgZoneCx - ftW / 2;
    const ftY = yTop + padTop + Math.max(0, (textBlockH - ftH) / 2);
    try { doc.addImage(firmaTimbreBase64, ftX, ftY, ftW, ftH); imgBottom = ftY + ftH; } catch (e) {
      console.warn('[memoPDF] addImage firma+timbre (Firma Simple) falló:', e);
    }
  }

  const contentBottom = Math.max(ty, imgBottom + padTop);

  // Recuadro dibujado al final, ajustado exactamente al contenido real
  // (evita descuadres si algún texto envuelve a 2 líneas o la imagen es alta).
  doc.setDrawColor(...colores.principal);
  doc.setLineWidth(0.5);
  doc.rect(cardX, yTop, cardW, contentBottom - yTop, 'S');

  return contentBottom;
}

/** Carga una URL de imagen (pública o con credenciales) como data URL JPEG.
 *  Mismo patrón que NominaModal: fetch → blob → canvas (quita alfa).
 *  Registra un aviso en consola si la imagen no se puede cargar,
 *  pero nunca lanza excepción para no interrumpir la generación del PDF. */
export async function cargarImagenDataUrl(url: string): Promise<string | null> {
  try {
    const res = await fetch(url, { credentials: 'include' });
    if (!res.ok) {
      console.warn(`[memoPDF] No se pudo cargar imagen (HTTP ${res.status}): ${url}`);
      return null;
    }
    const blob = await res.blob();
    if (!blob.size) {
      console.warn(`[memoPDF] Imagen vacía (0 bytes): ${url}`);
      return null;
    }

    // JPEG: leer directo sin canvas
    if (blob.type.includes('jpeg') || /\.(jpg|jpeg)$/i.test(url)) {
      return new Promise((resolve, reject) => {
        const reader    = new FileReader();
        reader.onload   = () => resolve(reader.result as string);
        reader.onerror  = () => {
          console.warn(`[memoPDF] FileReader falló para: ${url}`);
          reject(new Error('FileReader error'));
        };
        reader.readAsDataURL(blob);
      });
    }

    // PNG u otros: canvas con fondo blanco → JPEG (elimina canal alfa)
    const blobUrl = URL.createObjectURL(blob);
    return new Promise<string | null>((resolve) => {
      const img = new Image();
      img.onload = () => {
        if (!img.naturalWidth || !img.naturalHeight) {
          console.warn(`[memoPDF] Imagen cargada pero sin dimensiones: ${url}`);
          URL.revokeObjectURL(blobUrl); resolve(null); return;
        }
        try {
          const canvas = document.createElement('canvas');
          canvas.width  = img.naturalWidth;
          canvas.height = img.naturalHeight;
          const ctx = canvas.getContext('2d');
          if (!ctx) { URL.revokeObjectURL(blobUrl); resolve(null); return; }
          ctx.fillStyle = '#ffffff';
          ctx.fillRect(0, 0, canvas.width, canvas.height);
          ctx.drawImage(img, 0, 0);
          URL.revokeObjectURL(blobUrl);
          resolve(canvas.toDataURL('image/jpeg', 0.92));
        } catch (canvasErr) {
          console.warn(`[memoPDF] Canvas falló para ${url}:`, canvasErr);
          URL.revokeObjectURL(blobUrl); resolve(null);
        }
      };
      img.onerror = () => {
        console.warn(`[memoPDF] Image.onerror para: ${url}`);
        URL.revokeObjectURL(blobUrl); resolve(null);
      };
      img.src = blobUrl;
    });
  } catch (err) {
    console.warn(`[memoPDF] fetch falló para ${url}:`, err);
    return null;
  }
}

// ── Generador principal ───────────────────────────────────────

export function generarMemorandumPDF(data: MemorandumData): jsPDF {
  // Carta (US Letter): 8.5 × 11 pulgadas = 215.9 × 279.4 mm
  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'letter' });

  const pW  = 215.9;
  const pH  = 279.4;
  const mg  = 18;
  const cW  = pW - mg * 2;   // ~180 mm

  const {
    principal, headerFondo, headerBorde,
    grisFondo, grisLabel, negro, textoSecundario, blanco,
  } = BRANDING.colores;

  let y = mg;

  // ── 0. Marca de agua BORRADOR (se dibuja primero, queda debajo) ──
  if (data.esBorrador) {
    doc.saveGraphicsState();
    doc.setGState(new GState({ opacity: 0.12 }));
    doc.setFontSize(72);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(220, 30, 30);
    // Texto diagonal centrado en la página Carta
    const cx = pW / 2;
    const cy = pH / 2;
    doc.text('BORRADOR', cx, cy, { align: 'center', angle: 45 });
    doc.restoreGraphicsState();
  }

  // ── 1. Header institucional (compacto) ────────────────────────
  const hdrH   = 15;
  const logoSz = 11;
  const logoX  = mg + 3;
  const logoY  = y + (hdrH - logoSz) / 2;

  doc.setFillColor(...headerFondo);
  doc.rect(mg, y, cW, hdrH, 'F');
  doc.setDrawColor(...headerBorde);
  doc.setLineWidth(0.4);
  doc.rect(mg, y, cW, hdrH, 'S');

  if (data.logoBase64) {
    try { doc.addImage(data.logoBase64, logoX, logoY, logoSz, logoSz); } catch (e) { console.warn('[memoPDF] addImage logo falló:', e); }
  } else {
    console.warn('[memoPDF] logoBase64 es null — el logo no se insertará en el encabezado.');
  }

  const textX  = mg + 3 + logoSz + 4;
  const textCX = textX + (mg + cW - 4 - textX) / 2;

  doc.setTextColor(...negro);
  doc.setFontSize(9.5);
  doc.setFont('helvetica', 'bold');
  doc.text(BRANDING.nombreInstitucion, textCX, y + 6.5, { align: 'center' });
  doc.setTextColor(...textoSecundario);
  doc.setFontSize(7);
  doc.setFont('helvetica', 'italic');
  doc.text(BRANDING.subTitulo, textCX, y + 11.5, { align: 'center' });

  doc.setFillColor(...headerBorde);
  doc.rect(mg, y + hdrH, cW, 1, 'F');
  y += hdrH + 1 + 3;

  // ── 2. Bloque título MEMORÁNDUM ───────────────────────────────
  const tH = 7.5;
  doc.setFillColor(...principal);
  doc.rect(mg, y, cW, tH, 'F');
  doc.setTextColor(...blanco);
  doc.setFontSize(10.5);
  doc.setFont('helvetica', 'bold');
  doc.text('MEMORÁNDUM INTERNO', mg + cW / 2, y + tH / 2 + 1.8, { align: 'center' });
  y += tH + 3;

  // ── 3. Correlativo + Fecha ────────────────────────────────────
  const corrH = 6.5;
  doc.setFillColor(...grisFondo);
  doc.rect(mg, y, cW, corrH, 'F');
  doc.setDrawColor(226, 232, 240);
  doc.setLineWidth(0.3);
  doc.rect(mg, y, cW, corrH, 'S');

  doc.setTextColor(...negro);
  doc.setFontSize(8.5);
  doc.setFont('helvetica', 'bold');
  const corrText = data.esBorrador
    ? 'N° [Por asignar — Borrador]'
    : `N° ${data.correlativo ?? '—'}`;
  doc.text(corrText, mg + 3.5, y + corrH / 2 + 1.3);

  const fechaText = fmtFecha(data.fechaDocumento);
  doc.setFont('helvetica', 'normal');
  doc.setFontSize(8);
  doc.text(fechaText, mg + cW - 3.5, y + corrH / 2 + 1.3, { align: 'right' });
  y += corrH + 3;

  // ── 4. Tabla PARA / DE / MATERIA / REFERENCIA ─────────────────
  const rH     = 6.5;
  const labelW = 28;
  const valW   = cW - labelW;

  const destinosStr = data.destinos.length > 0
    ? data.destinos.join(' / ')
    : '—';

  const filasMeta: [string, string][] = [
    ['PARA:',    destinosStr],
    ['DE:',      data.origen ?? '—'],
    ['MATERIA:', data.materia],
  ];
  if (data.referencia?.trim()) {
    filasMeta.push(['REF.:', data.referencia.trim()]);
  }

  filasMeta.forEach(([etq, val], i) => {
    const ry = y + i * rH;
    doc.setFillColor(i % 2 === 0 ? 249 : 255, i % 2 === 0 ? 250 : 255, i % 2 === 0 ? 251 : 255);
    doc.rect(mg, ry, cW, rH, 'F');

    doc.setFillColor(...grisFondo);
    doc.rect(mg, ry, labelW, rH, 'F');

    doc.setDrawColor(226, 232, 240);
    doc.setLineWidth(0.2);
    doc.rect(mg, ry, cW, rH, 'S');
    doc.line(mg + labelW, ry, mg + labelW, ry + rH);

    // Etiqueta
    doc.setTextColor(...principal);
    doc.setFontSize(7);
    doc.setFont('helvetica', 'bold');
    doc.text(etq, mg + 3, ry + rH / 2 + 1.3);

    // Valor (con truncado si excede ancho)
    doc.setTextColor(...negro);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(7.5);
    const maxW = valW - 6;
    const lines = doc.splitTextToSize(val, maxW) as string[];
    const display = lines[0] + (lines.length > 1 ? '…' : '');
    doc.text(display, mg + labelW + 3, ry + rH / 2 + 1.3);
  });

  y += filasMeta.length * rH + 4;

  // ── 5. Línea separadora ───────────────────────────────────────
  doc.setDrawColor(...principal);
  doc.setLineWidth(0.6);
  doc.line(mg, y, mg + cW, y);
  y += 4;

  // ── 6. Cuerpo del memorándum ──────────────────────────────────
  doc.setTextColor(...negro);
  doc.setFontSize(8.75);
  doc.setFont('helvetica', 'normal');

  const lineaAltura = 4.5;
  // Reservar espacio para el pie de firma compacto (tarjeta ~40mm + separador
  // + pie de página). El bloque de FirmaGOB (drawSelloFirmaElectronica, ~26mm)
  // y el resto de variantes caben holgadamente dentro de esta reserva.
  const reservaFirma = 44;

  const cuerpoLines = doc.splitTextToSize(data.cuerpo || '—', cW) as string[];

  for (const linea of cuerpoLines) {
    // Nueva página cuando no queda espacio para la firma completa
    if (y + lineaAltura > pH - mg - reservaFirma) {
      doc.addPage();
      y = mg;
    }
    doc.text(linea, mg, y);
    y += lineaAltura;
  }

  // Espacio moderado entre cuerpo y firma (reduce hueco visual excesivo)
  y += 3;

  // ── 7. Bloque firma ───────────────────────────────────────────
  const firmaBlockH = 40;
  if (y + firmaBlockH > pH - mg) {
    doc.addPage();
    y = mg + 6;
  }

  // Zona de firma: mitad derecha de la página — usada solo por las variantes
  // legacy (FirmaGOB / imagen escaneada); Firma Simple centra su propia
  // tarjeta en el ancho completo (ver drawSelloFirmaSimple).
  const firmaX = mg + cW / 2;  // x inicio
  const firmaW = cW / 2;       // ancho disponible

  // ── Calcular dimensiones respetando la relación de aspecto original ──
  // Máximo: 74mm ancho / 42mm alto — moderado y proporcional.
  // Si no llegan las dimensiones naturales, usar un tamaño razonable de fallback.
  const MAX_FT_W = 74;
  const MAX_FT_H = 42;

  let ftW: number;
  let ftH: number;

  const natW = data.firmaTimbreNatW ?? 0;
  const natH = data.firmaTimbreNatH ?? 0;

  if (natW > 0 && natH > 0) {
    // Escalar desde ancho máximo; si el alto resultante excede el límite, escalar desde alto
    const ratio = natH / natW;
    ftW = MAX_FT_W;
    ftH = ftW * ratio;
    if (ftH > MAX_FT_H) {
      ftH = MAX_FT_H;
      ftW = ftH / ratio;
    }
  } else {
    // Fallback sin dimensiones: 70 × 35 mm (aspecto 2:1 típico firma+timbre)
    ftW = 70;
    ftH = 35;
  }

  // Centrar horizontalmente dentro de la zona de firma
  const ftX = firmaX + (firmaW - ftW) / 2;

  if (data.mecanismoFirma === 'FIRMA_SIMPLE' && !data.esBorrador && data.datosFirmaSimple) {
    // Firma Simple DOC360: reemplaza el sello de FirmaGOB. La imagen de
    // firma/timbre (si existe) es solo representación visual — la evidencia
    // real es el código de verificación + hash, emitidos por el backend en
    // POST /memorandum/:id/firmar-simple (Fase A).
    y = drawSelloFirmaSimple(
      doc, cW, mg, y,
      data.nombreFirmante, data.cargoFirmante, data.servicioFirmante ?? data.origen ?? null,
      data.firmaTimbreBase64, natW, natH,
      data.datosFirmaSimple.codigoVerificacion, data.datosFirmaSimple.hashOriginalCorto, data.datosFirmaSimple.fechaFirma,
      { principal, grisLabel, negro },
    );
  } else if (data.mecanismoFirma === 'FIRMA_SIMPLE' && !data.esBorrador) {
    // PDF pre-firma de Firma Simple (antes de la Fase A): todavía no hay
    // código/hash que mostrar. Sin este branch explícito, caería en el
    // sello de FirmaGOB por defecto (rama siguiente) — texto incorrecto
    // para este mecanismo. Tarjeta compacta centrada, consistente con el
    // pie de firma final (drawSelloFirmaSimple) pero con borde tenue —
    // señala visualmente que aún está pendiente.
    const cardW = Math.min(115, cW);
    const cardX = mg + (cW - cardW) / 2;
    const cx    = cardX + cardW / 2;
    const cardYStart = y;
    let ty = y + 5;
    doc.setTextColor(...negro);
    doc.setFontSize(7.5);
    doc.setFont('helvetica', 'bold');
    doc.text(data.nombreFirmante, cx, ty, { align: 'center' });
    ty += 3.3;
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(6.5);
    doc.setTextColor(...grisLabel);
    doc.text(data.cargoFirmante, cx, ty, { align: 'center' });
    ty += 3.3;
    doc.setFontSize(6);
    doc.setFont('helvetica', 'italic');
    doc.text('(Pendiente de Firma Simple DOC360 — se completará al confirmar)', cx, ty, { align: 'center' });
    ty += 4;
    doc.setDrawColor(...grisLabel);
    doc.setLineWidth(0.3);
    doc.rect(cardX, cardYStart, cardW, ty - cardYStart, 'S');
    y = ty;
  } else if (data.firmaTimbreBase64) {
    try {
      // jsPDF auto-detecta el formato desde el prefijo data:image/png o data:image/jpeg
      doc.addImage(data.firmaTimbreBase64, ftX, y, ftW, ftH);
      y += ftH + 2;
    } catch (e) {
      console.warn('[memoPDF] addImage firma+timbre falló:', e);
      y += 8;
    }

    // Línea de firma
    doc.setDrawColor(...negro);
    doc.setLineWidth(0.4);
    doc.line(firmaX + 5, y, firmaX + firmaW - 5, y);
    y += 4;

    // Nombre y cargo centrados en la zona de firma
    doc.setTextColor(...negro);
    doc.setFontSize(8.5);
    doc.setFont('helvetica', 'bold');
    doc.text(data.nombreFirmante, firmaX + firmaW / 2, y, { align: 'center' });
    y += 5;

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(...grisLabel);
    doc.text(data.cargoFirmante, firmaX + firmaW / 2, y, { align: 'center' });
    y += 5;
  } else if (!data.esBorrador) {
    // Definitivo, sin imagen escaneada: firma vía FirmaGov — sello "Firma
    // Electrónica Avanzada" + datos del firmante. Este PDF es el que se
    // envía a FirmaGov para firmar; la firma criptográfica real (PAdES/
    // PKCS#7) queda embebida por FirmaGov al momento de procesarlo.
    // IMPORTANTE: nunca se dibuja en la vista previa (esBorrador=true) —
    // mostrar el sello antes de tener una respuesta real de FirmaGov
    // implicaría visualmente que el documento ya está firmado sin estarlo.
    const selloCx   = firmaX + 17;
    const selloTextX = selloCx + 13 + 6;
    const yFinSello = drawSelloFirmaElectronica(
      doc, selloCx, y, data.nombreFirmante, data.cargoFirmante,
      data.servicioFirmante ?? data.origen ?? null, selloTextX,
    );
    y = yFinSello + 4;
  } else {
    // Vista previa (borrador): solo nombre y cargo, sin sello — la firma
    // todavía no existe en esta etapa. El texto de "pendiente" depende del
    // mecanismo (Firma Simple no debe mencionar FirmaGov y viceversa).
    y += 6;

    doc.setDrawColor(...negro);
    doc.setLineWidth(0.4);
    doc.line(firmaX + 5, y, firmaX + firmaW - 5, y);
    y += 4;

    doc.setTextColor(...negro);
    doc.setFontSize(8.5);
    doc.setFont('helvetica', 'bold');
    doc.text(data.nombreFirmante, firmaX + firmaW / 2, y, { align: 'center' });
    y += 4.5;

    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(...grisLabel);
    doc.text(data.cargoFirmante, firmaX + firmaW / 2, y, { align: 'center' });
    y += 4.5;

    doc.setFontSize(7);
    doc.setFont('helvetica', 'italic');
    doc.setTextColor(...grisLabel);
    const pendienteTexto = data.mecanismoFirma === 'FIRMA_SIMPLE'
      ? '(Pendiente de Firma Simple DOC360 — se asignará al confirmar la emisión)'
      : '(Pendiente de firma electrónica — se aplicará al confirmar con FirmaGov)';
    doc.text(pendienteTexto, firmaX + firmaW / 2, y, { align: 'center' });
    y += 5;
  }

  // ── 8. Línea separadora inferior ─────────────────────────────
  y += 3;
  doc.setDrawColor(226, 232, 240);
  doc.setLineWidth(0.3);
  doc.line(mg, y, mg + cW, y);
  y += 3.5;

  // ── 9. Pie de página ──────────────────────────────────────────
  const ahora = new Date().toLocaleDateString('es-CL', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
  doc.setFontSize(6.5);
  doc.setFont('helvetica', 'italic');
  doc.setTextColor(...grisLabel);

  const pieText = data.esBorrador
    ? `DOCUMENTO EN PREPARACIÓN — Generado el ${ahora} — ${BRANDING.piePagina}`
    : `Generado el ${ahora} — ${BRANDING.piePagina}`;
  doc.text(pieText, mg + cW / 2, y, { align: 'center' });

  return doc;
}
