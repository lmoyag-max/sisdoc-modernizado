import { Router } from 'express';
import { validate } from '../../middleware/validate.middleware';
import { requireAuth } from '../../middleware/auth.middleware';
import { loginSchema } from './auth.schema';
import * as authController from './auth.controller';

const router = Router();

/**
 * @swagger
 * /auth/login:
 *   post:
 *     tags: [Autenticación]
 *     summary: Iniciar sesión
 *     security: []
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             required: [usuario, clave]
 *             properties:
 *               usuario: { type: string }
 *               clave: { type: string }
 *     responses:
 *       200:
 *         description: Login exitoso con tokens JWT
 *       401:
 *         description: Credenciales inválidas
 */
router.post('/login', validate(loginSchema), authController.login);

/**
 * @swagger
 * /auth/refresh:
 *   post:
 *     tags: [Autenticación]
 *     summary: Renovar access token usando refresh token
 *     security: []
 *     responses:
 *       200:
 *         description: Nuevo access token
 */
router.post('/refresh', authController.refresh);

/**
 * @swagger
 * /auth/logout:
 *   post:
 *     tags: [Autenticación]
 *     summary: Cerrar sesión
 *     responses:
 *       200:
 *         description: Sesión cerrada
 */
router.post('/logout', requireAuth, authController.logout);

/**
 * @swagger
 * /auth/me:
 *   get:
 *     tags: [Autenticación]
 *     summary: Obtener usuario autenticado
 *     responses:
 *       200:
 *         description: Datos del usuario actual
 */
router.get('/me', requireAuth, authController.me);

export default router;
