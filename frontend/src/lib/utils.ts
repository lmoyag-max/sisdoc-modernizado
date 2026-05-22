import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { format, formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatFecha(date: string | Date | null | undefined): string {
  if (!date) return '—';
  return format(new Date(date), 'dd/MM/yyyy', { locale: es });
}

export function formatFechaHora(date: string | Date | null | undefined): string {
  if (!date) return '—';
  return format(new Date(date), 'dd/MM/yyyy HH:mm', { locale: es });
}

export function formatRelativo(date: string | Date | null | undefined): string {
  if (!date) return '—';
  return formatDistanceToNow(new Date(date), { addSuffix: true, locale: es });
}

export function iniciales(nombre: string | null | undefined): string {
  if (!nombre) return '?';
  return nombre
    .split(' ')
    .slice(0, 2)
    .map((n) => n[0])
    .join('')
    .toUpperCase();
}

export function truncate(str: string | null | undefined, max = 60): string {
  if (!str) return '';
  return str.length > max ? str.slice(0, max) + '…' : str;
}

/**
 * Convierte una ruta relativa de /uploads a una URL completa.
 *
 * En desarrollo (Vite, import.meta.env.DEV):
 *   El backend corre en puerto 3001 pero las imágenes estáticas no pasan
 *   por el proxy de Vite. Construimos la URL usando el mismo hostname
 *   del navegador pero con el puerto del backend (3001).
 *   → Funciona tanto desde localhost como desde la red local (10.x.x.x).
 *
 * En producción (nginx):
 *   Nginx sirve tanto el frontend como /uploads desde el mismo origen.
 *   La URL relativa funciona directamente.
 */
export function uploadUrl(path: string | null | undefined): string | null {
  if (!path) return null;
  if (import.meta.env.PROD) return path;
  // En dev: mismo host que el browser pero puerto del backend
  const backendPort = 3001;
  const base = `${window.location.protocol}//${window.location.hostname}:${backendPort}`;
  return `${base}${path}`;
}
