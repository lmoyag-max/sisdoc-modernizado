import { z } from 'zod';

export const loginSchema = z.object({
  usuario: z.string().min(1, 'El usuario es requerido').max(50),
  clave: z.string().min(1, 'La contraseña es requerida').max(100),
});

export const refreshSchema = z.object({
  refreshToken: z.string().min(1),
});

export type LoginDto = z.infer<typeof loginSchema>;
