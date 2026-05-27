import { jsPDF } from 'jspdf';
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
   * Data URL JPEG del logo institucional (generado desde canvas en el modal).
   * Si se omite o falla, el header muestra solo texto.
   */
  logoBase64?:    string | null;
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

export function generarNominaPDF(data: NominaData): jsPDF {
  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
  const pW  = 210;
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

  // Logo institucional (izquierda, centrado verticalmente)
  if (data.logoBase64) {
    try {
      const fmt = data.logoBase64.startsWith('data:image/jpeg') ? 'JPEG' : 'PNG';
      doc.addImage(data.logoBase64, fmt, logoX, logoY, logoSz, logoSz);
    } catch {
      // Logo no disponible — continuar solo con texto
    }
  }

  // Área de texto a la derecha del logo
  const textX  = mg + 4 + logoSz + 5;
  const textCX = textX + (mg + cW - 4 - textX) / 2;

  // Nombre institución — texto principal oscuro
  doc.setTextColor(...negro);
  doc.setFontSize(10.5);
  doc.setFont('helvetica', 'bold');
  doc.text(BRANDING.nombreInstitucion, textCX, y + 11, { align: 'center' });

  // Subtítulo — texto secundario
  doc.setTextColor(...textoSecundario);
  doc.setFontSize(8);
  doc.setFont('helvetica', 'italic');
  doc.text(BRANDING.subTitulo, textCX, y + 19, { align: 'center' });

  // Franja decorativa inferior — azul suave
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

  return doc;
}
