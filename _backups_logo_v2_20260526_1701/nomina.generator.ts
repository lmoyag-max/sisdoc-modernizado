import { jsPDF, GState } from 'jspdf';
import { BRANDING } from '@/lib/config/branding';

export interface NominaData {
  idDocumento:    number;
  numDocumento:   number | null;
  materia:        string | null;
  tipoDocumento:  string | null;
  fechaDocumento: string | null;
  fechaIngreso:   string | null;
  ingresadoPor:   string;
  origen:         string | null;
  destinos:       string[];
  observaciones?: string | null;
  /**
   * Data URL JPEG 200 px del logo (para el header).
   * Generado desde canvas con fondo blanco para eliminar transparencia RGBA.
   */
  logoBase64?:      string | null;
  /**
   * Data URL JPEG 300 px del logo (para el sello de agua, 80×80 mm al 7 % de opacidad).
   * Generado en la misma operación de canvas que logoBase64.
   */
  watermarkBase64?: string | null;
}

function fmtFecha(val: string | null | undefined): string {
  if (!val) return '—';
  try {
    return new Date(val).toLocaleDateString('es-CL', {
      year: 'numeric', month: 'long', day: 'numeric',
    });
  } catch {
    return val;
  }
}

/** Extrae la parte base64 de un data URL, o devuelve la cadena tal cual. */
function extractBase64(dataUrl: string): string {
  const idx = dataUrl.indexOf(',');
  return idx >= 0 ? dataUrl.slice(idx + 1) : dataUrl;
}

/** Detecta el formato de imagen a partir del data URL. */
function detectFmt(dataUrl: string): 'JPEG' | 'PNG' {
  return dataUrl.startsWith('data:image/jpeg') ? 'JPEG' : 'PNG';
}

export function generarNominaPDF(data: NominaData): jsPDF {
  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
  const pW  = 210;
  const pH  = 297;  // alto A4 en mm
  const mg  = 18;
  const cW  = pW - mg * 2;  // 174 mm

  const {
    principal, headerFondo, headerBorde,
    grisFondo, grisLabel, negro, textoSecundario,
  } = BRANDING.colores;

  let y = mg;

  // ── Header institucional celeste ───────────────────────────
  const hdrH   = 26;
  const logoSz = 18;
  const logoX  = mg + 4;
  const logoY  = y + (hdrH - logoSz) / 2;

  // Fondo celeste claro
  doc.setFillColor(...headerFondo);
  doc.rect(mg, y, cW, hdrH, 'F');

  // Borde exterior suave
  doc.setDrawColor(...headerBorde);
  doc.setLineWidth(0.5);
  doc.rect(mg, y, cW, hdrH, 'S');

  // Logo institucional (izquierda, centrado verticalmente en el header)
  if (data.logoBase64) {
    try {
      doc.addImage(
        extractBase64(data.logoBase64),
        detectFmt(data.logoBase64),
        logoX, logoY, logoSz, logoSz,
      );
    } catch {
      // Logo no disponible — header continúa solo con texto
    }
  }

  // Área de texto a la derecha del logo
  const textX  = mg + 4 + logoSz + 5;
  const textCX = textX + (mg + cW - 4 - textX) / 2;

  // Nombre institución
  doc.setTextColor(...negro);
  doc.setFontSize(10.5);
  doc.setFont('helvetica', 'bold');
  doc.text(BRANDING.nombreInstitucion, textCX, y + 11, { align: 'center' });

  // Subtítulo
  doc.setTextColor(...textoSecundario);
  doc.setFontSize(8);
  doc.setFont('helvetica', 'italic');
  doc.text(BRANDING.subTitulo, textCX, y + 19, { align: 'center' });

  // Franja decorativa inferior del header
  doc.setFillColor(...headerBorde);
  doc.rect(mg, y + hdrH, cW, 1.5, 'F');

  y += hdrH + 1.5 + 4;

  // ── Bloque título nómina ───────────────────────────────────
  const tH = 14;

  doc.setFillColor(...grisFondo);
  doc.rect(mg, y, cW, tH, 'F');

  // Acento lateral azul institucional
  doc.setFillColor(...principal);
  doc.rect(mg, y, 4, tH, 'F');

  // Borde exterior
  doc.setDrawColor(229, 231, 235);
  doc.setLineWidth(0.3);
  doc.rect(mg, y, cW, tH, 'S');

  // Título
  doc.setTextColor(...negro);
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text(
    `NÓMINA N° ${data.numDocumento ?? data.idDocumento}`,
    mg + 4 + (cW - 4) / 2,
    y + tH / 2 + 2.5,
    { align: 'center' },
  );

  y += tH + 6;

  // ── Tabla de datos ─────────────────────────────────────────
  const rH     = 9.5;
  const labelW = 58;
  const valueW = cW - labelW;

  const filas: [string, string][] = [
    ['N° de Documento',    String(data.numDocumento ?? data.idDocumento)],
    ['Tipo de Documento',  data.tipoDocumento ?? '—'],
    ['Materia / Asunto',   data.materia ?? '—'],
    ['Fecha del Documento',fmtFecha(data.fechaDocumento)],
    ['Fecha de Ingreso',   fmtFecha(data.fechaIngreso)],
    ['Procedencia',        data.origen ?? '—'],
    ['Destino(s)',         data.destinos.length > 0 ? data.destinos.join(' / ') : '—'],
    ['Ingresado por',      data.ingresadoPor || '—'],
    ['Observaciones',      data.observaciones || '—'],
  ];

  filas.forEach(([etq, val], i) => {
    const ry = y + i * rH;

    // Fondo fila alternado
    doc.setFillColor(i % 2 === 0 ? 249 : 255, i % 2 === 0 ? 250 : 255, i % 2 === 0 ? 251 : 255);
    doc.rect(mg, ry, cW, rH, 'F');

    // Celda etiqueta
    doc.setFillColor(...grisFondo);
    doc.rect(mg, ry, labelW, rH, 'F');

    // Bordes
    doc.setDrawColor(226, 232, 240);
    doc.setLineWidth(0.2);
    doc.rect(mg, ry, cW, rH, 'S');
    doc.line(mg + labelW, ry, mg + labelW, ry + rH);

    // Texto etiqueta
    doc.setTextColor(...grisLabel);
    doc.setFontSize(7.5);
    doc.setFont('helvetica', 'bold');
    doc.text(etq, mg + 3, ry + 6.3);

    // Texto valor
    doc.setTextColor(...negro);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    const maxW       = valueW - 6;
    const displayVal = doc.getTextWidth(val) > maxW
      ? doc.splitTextToSize(val, maxW)[0] + '…'
      : val;
    doc.text(displayVal, mg + labelW + 3, ry + 6.3);
  });

  y += filas.length * rH + 10;

  // ── Nota de soporte físico ─────────────────────────────────
  doc.setFontSize(7.5);
  doc.setFont('helvetica', 'bolditalic');
  doc.setTextColor(...principal);
  doc.text('★  Documento físico / papel — requiere firma de recepción', mg + cW / 2, y, { align: 'center' });
  y += 14;

  // ── Líneas de firma / sello ────────────────────────────────
  doc.setDrawColor(...negro);
  doc.setLineWidth(0.4);
  const firmaX1 = mg + 10;
  const firmaX2 = mg + cW - 10 - 50;
  doc.line(firmaX1, y, firmaX1 + 50, y);
  doc.line(firmaX2, y, firmaX2 + 50, y);
  doc.setFontSize(7.5);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(...grisLabel);
  doc.text('Firma de recepción', firmaX1 + 25, y + 5, { align: 'center' });
  doc.text('Sello',              firmaX2 + 25, y + 5, { align: 'center' });
  y += 18;

  // ── Pie de página ──────────────────────────────────────────
  doc.setFontSize(7);
  doc.setFont('helvetica', 'italic');
  doc.setTextColor(...grisLabel);
  const ahora = new Date().toLocaleDateString('es-CL', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
  doc.text(`Generado el ${ahora} — ${BRANDING.piePagina}`, mg + cW / 2, y, { align: 'center' });

  // ── Sello de agua institucional ────────────────────────────
  // Se dibuja AL FINAL (sobre el contenido) con GState opacity 0.07.
  // El PDF lo compone como: 7% color logo + 93% contenido existente →
  // marca institucional apenas perceptible, sin dificultar la lectura.
  if (data.watermarkBase64) {
    try {
      const wmW = 80;
      const wmH = 80;
      const wmX = (pW - wmW) / 2;        // centrado horizontal en página
      const wmY = (pH - wmH) / 2;        // centrado vertical en página

      doc.saveGraphicsState();
      doc.setGState(new GState({ opacity: 0.07 }));
      doc.addImage(
        extractBase64(data.watermarkBase64),
        detectFmt(data.watermarkBase64),
        wmX, wmY, wmW, wmH,
      );
      doc.restoreGraphicsState();
    } catch {
      // Sello de agua falla silenciosamente — no afecta el documento
    }
  }

  return doc;
}
