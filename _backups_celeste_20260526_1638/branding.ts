/**
 * Configuración de branding institucional HUAP/SISDOC.
 * Centraliza nombre, colores y textos para PDFs, reportes y documentos.
 */

export const BRANDING = {
  /** Nombre completo de la institución (para cabeceras de documentos oficiales) */
  nombreInstitucion: 'HOSPITAL DE URGENCIA ASISTENCIA PÚBLICA - HUAP',

  /** Abreviación institucional */
  abreviacion: 'HUAP',

  /** Nombre del sistema de gestión documental */
  nombreSistema: 'SISDOC',

  /** Subtítulo para encabezados de documentos */
  subTitulo: 'Sistema de Gestión Documental — SISDOC',

  /** Pie de página en PDFs */
  piePagina: 'SISDOC HUAP',

  /**
   * Colores institucionales HUAP (RGB)
   * Paleta roja institucional + complementos neutros
   */
  colores: {
    /** Rojo HUAP principal — encabezados, énfasis (#B00020) */
    principal:  [176,   0,  32] as [number, number, number],
    /** Rojo HUAP oscuro — líneas decorativas, bordes (#7A0015) */
    oscuro:     [122,   0,  21] as [number, number, number],
    /** Gris fondo suave — filas alternadas, fondos (#F5F5F5) */
    grisFondo:  [245, 245, 245] as [number, number, number],
    /** Gris texto secundario — etiquetas, subtítulos (#6B7280) */
    grisLabel:  [107, 114, 128] as [number, number, number],
    /** Negro suave — texto principal (#111827) */
    negro:      [ 17,  24,  39] as [number, number, number],
    /** Blanco puro (#FFFFFF) */
    blanco:     [255, 255, 255] as [number, number, number],
  },
} as const;
