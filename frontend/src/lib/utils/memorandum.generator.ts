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
}

// ── Helpers internos ──────────────────────────────────────────

function fmtFecha(val: string | null | undefined): string {
  if (!val) return new Date().toLocaleDateString('es-CL', { year: 'numeric', month: 'long', day: 'numeric' });
  try {
    return new Date(val).toLocaleDateString('es-CL', { year: 'numeric', month: 'long', day: 'numeric' });
  } catch { return val; }
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

  // ── 1. Header institucional (igual que nómina) ────────────────
  const hdrH   = 26;
  const logoSz = 18;
  const logoX  = mg + 4;
  const logoY  = y + (hdrH - logoSz) / 2;

  doc.setFillColor(...headerFondo);
  doc.rect(mg, y, cW, hdrH, 'F');
  doc.setDrawColor(...headerBorde);
  doc.setLineWidth(0.5);
  doc.rect(mg, y, cW, hdrH, 'S');

  if (data.logoBase64) {
    try { doc.addImage(data.logoBase64, logoX, logoY, logoSz, logoSz); } catch (e) { console.warn('[memoPDF] addImage logo falló:', e); }
  } else {
    console.warn('[memoPDF] logoBase64 es null — el logo no se insertará en el encabezado.');
  }

  const textX  = mg + 4 + logoSz + 5;
  const textCX = textX + (mg + cW - 4 - textX) / 2;

  doc.setTextColor(...negro);
  doc.setFontSize(10.5);
  doc.setFont('helvetica', 'bold');
  doc.text(BRANDING.nombreInstitucion, textCX, y + 11, { align: 'center' });
  doc.setTextColor(...textoSecundario);
  doc.setFontSize(8);
  doc.setFont('helvetica', 'italic');
  doc.text(BRANDING.subTitulo, textCX, y + 19, { align: 'center' });

  doc.setFillColor(...headerBorde);
  doc.rect(mg, y + hdrH, cW, 1.5, 'F');
  y += hdrH + 1.5 + 5;

  // ── 2. Bloque título MEMORÁNDUM ───────────────────────────────
  const tH = 14;
  doc.setFillColor(...principal);
  doc.rect(mg, y, cW, tH, 'F');
  doc.setTextColor(...blanco);
  doc.setFontSize(13);
  doc.setFont('helvetica', 'bold');
  doc.text('MEMORÁNDUM INTERNO', mg + cW / 2, y + tH / 2 + 2.5, { align: 'center' });
  y += tH + 4;

  // ── 3. Correlativo + Fecha ────────────────────────────────────
  const corrH = 9;
  doc.setFillColor(...grisFondo);
  doc.rect(mg, y, cW, corrH, 'F');
  doc.setDrawColor(226, 232, 240);
  doc.setLineWidth(0.3);
  doc.rect(mg, y, cW, corrH, 'S');

  doc.setTextColor(...negro);
  doc.setFontSize(9);
  doc.setFont('helvetica', 'bold');
  const corrText = data.esBorrador
    ? 'N° [Por asignar — Borrador]'
    : `N° ${data.correlativo ?? '—'}`;
  doc.text(corrText, mg + 4, y + corrH / 2 + 1.5);

  const fechaText = fmtFecha(data.fechaDocumento);
  doc.setFont('helvetica', 'normal');
  doc.setFontSize(8.5);
  doc.text(fechaText, mg + cW - 4, y + corrH / 2 + 1.5, { align: 'right' });
  y += corrH + 5;

  // ── 4. Tabla PARA / DE / MATERIA / REFERENCIA ─────────────────
  const rH     = 9;
  const labelW = 30;
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
    doc.setFontSize(7.5);
    doc.setFont('helvetica', 'bold');
    doc.text(etq, mg + 3, ry + 6.3);

    // Valor (con truncado si excede ancho)
    doc.setTextColor(...negro);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    const maxW = valW - 6;
    const lines = doc.splitTextToSize(val, maxW) as string[];
    const display = lines[0] + (lines.length > 1 ? '…' : '');
    doc.text(display, mg + labelW + 3, ry + 6.3);
  });

  y += filasMeta.length * rH + 7;

  // ── 5. Línea separadora ───────────────────────────────────────
  doc.setDrawColor(...principal);
  doc.setLineWidth(0.6);
  doc.line(mg, y, mg + cW, y);
  y += 6;

  // ── 6. Cuerpo del memorándum ──────────────────────────────────
  doc.setTextColor(...negro);
  doc.setFontSize(9.5);
  doc.setFont('helvetica', 'normal');

  const lineaAltura = 5.5;
  // Reservar ~70mm al final para firma (img+línea+texto) + pie de página
  const reservaFirma = 70;

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
  y += 4;

  // ── 7. Bloque firma ───────────────────────────────────────────
  // Imagen combinada firma+timbre, escala proporcional, centrada.
  const firmaBlockH = 60;
  if (y + firmaBlockH > pH - mg) {
    doc.addPage();
    y = mg + 8;
  }

  // Zona de firma: mitad derecha de la página
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

  if (data.firmaTimbreBase64) {
    try {
      // jsPDF auto-detecta el formato desde el prefijo data:image/png o data:image/jpeg
      doc.addImage(data.firmaTimbreBase64, ftX, y, ftW, ftH);
      y += ftH + 2;
    } catch (e) {
      console.warn('[memoPDF] addImage firma+timbre falló:', e);
      y += 8;
    }
  } else {
    console.warn('[memoPDF] firmaTimbreBase64 es null — imagen no disponible.');
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

  // ── 8. Línea separadora inferior ─────────────────────────────
  y += 4;
  doc.setDrawColor(226, 232, 240);
  doc.setLineWidth(0.3);
  doc.line(mg, y, mg + cW, y);
  y += 5;

  // ── 9. Pie de página ──────────────────────────────────────────
  const ahora = new Date().toLocaleDateString('es-CL', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
  doc.setFontSize(7);
  doc.setFont('helvetica', 'italic');
  doc.setTextColor(...grisLabel);

  const pieText = data.esBorrador
    ? `DOCUMENTO EN PREPARACIÓN — Generado el ${ahora} — ${BRANDING.piePagina}`
    : `Generado el ${ahora} — ${BRANDING.piePagina}`;
  doc.text(pieText, mg + cW / 2, y, { align: 'center' });

  return doc;
}
