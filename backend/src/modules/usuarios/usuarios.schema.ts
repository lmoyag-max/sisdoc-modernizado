import { z } from 'zod';

// TODO: ampliar max a 72 cuando se elimine la columna legacy `clave` VARCHAR(10)
const claveSchema = z
  .string()
  .min(8, 'La contraseña debe tener al menos 8 caracteres')
  .max(10, 'Máximo 10 caracteres (límite del campo legacy)')
  .regex(/^(?=.*[A-Z])(?=.*\d)/, 'La contraseña debe tener al menos una mayúscula y un número');

export const crearUsuarioSchema = z.object({
  usuario:         z.string().min(1).max(10),
  clave:           claveSchema,
  nombres:         z.string().min(1).max(30),
  apellidos:       z.string().min(1).max(30),
  idDependencia:   z.number().int().positive().optional(),
  todos_servicios: z.boolean().optional(),
  roles:           z.array(z.string().min(1)).optional(),
  email:           z.string().email('Formato de correo electrónico inválido').optional(),
});

export const actualizarUsuarioSchema = z.object({
  nombres:         z.string().min(1).max(30).optional(),
  apellidos:       z.string().min(1).max(30).optional(),
  clave:           claveSchema.optional(),
  roles:           z.array(z.string().min(1)).optional(),
  idDependencia:   z.number().int().positive().optional(),
  todos_servicios: z.boolean().optional(),
  email:           z.string().email('Formato de correo electrónico inválido').nullable().optional(),
});
