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
   * Azul institucional + complementos neutros
   */
  colores: {
    /** Azul HUAP principal — encabezados, énfasis */
    azul:       [0,   70,  127] as [number, number, number],
    /** Azul HUAP oscuro — bordes, acentos */
    azulOscuro: [0,   51,  102] as [number, number, number],
    /** Azul HUAP claro — fondos de sección */
    azulClaro:  [0,   90,  160] as [number, number, number],
    /** Gris fondo — filas alternadas, fondos suaves */
    grisFondo:  [243, 244, 246] as [number, number, number],
    /** Gris texto secundario — etiquetas, subtítulos */
    grisLabel:  [107, 114, 128] as [number, number, number],
    /** Negro suave — texto principal */
    negro:      [17,  24,  39]  as [number, number, number],
    /** Blanco puro */
    blanco:     [255, 255, 255] as [number, number, number],
  },
} as const;
