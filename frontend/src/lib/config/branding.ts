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
   * Colores institucionales HUAP (valores RGB para jsPDF).
   * Paleta celeste claro + azul institucional + neutros.
   */
  colores: {
    /** Azul institucional medio — barras de acento, énfasis, notas (#1E5AA0) */
    principal:       [ 30,  90, 160] as [number, number, number],
    /** Azul oscuro — bordes fuertes (#0F3C78) */
    oscuro:          [ 15,  60, 120] as [number, number, number],
    /** Celeste claro — fondo del header institucional (#DCEFFD) */
    headerFondo:     [220, 239, 253] as [number, number, number],
    /** Azul suave — borde exterior header y franja decorativa inferior (#B6D7F2) */
    headerBorde:     [182, 215, 242] as [number, number, number],
    /** Gris fondo suave — filas alternadas, fondos (#F5F5F5) */
    grisFondo:       [245, 245, 245] as [number, number, number],
    /** Gris texto etiquetas — columna izquierda de tabla (#6B7280) */
    grisLabel:       [107, 114, 128] as [number, number, number],
    /** Texto principal oscuro (#0F172A) */
    negro:           [ 15,  23,  42] as [number, number, number],
    /** Texto secundario header — subtítulo (#334155) */
    textoSecundario: [ 51,  65,  85] as [number, number, number],
    /** Blanco puro (#FFFFFF) */
    blanco:          [255, 255, 255] as [number, number, number],
  },
} as const;
