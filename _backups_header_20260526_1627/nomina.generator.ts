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
  /** Base64 del logo institucional (data URL). Si se omite, solo texto en la cabecera. */
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
  const doc  = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
  const pW   = 210;
  const mg   = 18;
  const cW   = pW - mg * 2;

  // ── Colores institucionales HUAP ────────────────────────────
  const { azul, azulOscuro, grisFondo, grisLabel, negro, blanco } = BRANDING.colores;

  let y = mg;

  // ── Franja de cabecera ─────────────────────────────────────
  doc.setFillColor(...azul);
  doc.rect(mg, y, cW, 22, 'F');

  if (data.logoBase64) {
    // Logo a la izquierda dentro de la franja
    try {
      doc.addImage(data.logoBase64, 'PNG', mg + 3, y + 2, 18, 18);
    } catch {
      // Logo inválido — continuar sin él
    }
    // Texto centrado sobre el espacio restante (desplazado por el logo)
    doc.setTextColor(...blanco);
    doc.setFontSize(10.5);
    doc.setFont('helvetica', 'bold');
    doc.text(BRANDING.nombreInstitucion, mg + cW / 2 + 5, y + 9, { align: 'center' });
    doc.setFontSize(8);
    doc.setFont('helvetica', 'normal');
    doc.text(BRANDING.subTitulo, mg + cW / 2 + 5, y + 16, { align: 'center' });
  } else {
    doc.setTextColor(...blanco);
    doc.setFontSize(10.5);
    doc.setFont('helvetica', 'bold');
    doc.text(BRANDING.nombreInstitucion, mg + cW / 2, y + 9, { align: 'center' });
    doc.setFontSize(8);
    doc.setFont('helvetica', 'normal');
    doc.text(BRANDING.subTitulo, mg + cW / 2, y + 16, { align: 'center' });
  }
  y += 26;

  // ── Título nómina ──────────────────────────────────────────
  doc.setFillColor(...grisFondo);
  doc.rect(mg, y, cW, 13, 'F');
  doc.setDrawColor(...azulOscuro);
  doc.setLineWidth(0.6);
  doc.line(mg, y, mg, y + 13);
  doc.line(mg + cW, y, mg + cW, y + 13);
  doc.setTextColor(...negro);
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.text(
    `NÓMINA N° ${data.numDocumento ?? data.idDocumento}`,
    mg + cW / 2, y + 9,
    { align: 'center' },
  );
  y += 17;

  // ── Tabla de datos ─────────────────────────────────────────
  const rH      = 9.5;
  const labelW  = 58;
  const valueW  = cW - labelW;

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

    // Fondo fila (alternado)
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

    // Texto valor (truncar si es muy largo)
    doc.setTextColor(...negro);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    const maxW = valueW - 6;
    const displayVal = doc.getTextWidth(val) > maxW
      ? doc.splitTextToSize(val, maxW)[0] + '…'
      : val;
    doc.text(displayVal, mg + labelW + 3, ry + 6.3);
  });

  y += filas.length * rH + 10;

  // ── Nota de soporte ────────────────────────────────────────
  doc.setFontSize(7.5);
  doc.setFont('helvetica', 'bolditalic');
  doc.setTextColor(...azul);
  doc.text('★  Documento físico / papel — requiere firma de recepción', mg + cW / 2, y, { align: 'center' });
  y += 14;

  // ── Firma / sello ──────────────────────────────────────────
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
  doc.text('Sello', firmaX2 + 25, y + 5, { align: 'center' });
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
