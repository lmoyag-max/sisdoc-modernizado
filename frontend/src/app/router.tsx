import { createBrowserRouter, Navigate } from 'react-router-dom';
import { Layout } from '@/components/layout/Layout';
import { ProtectedRoute } from '@/components/shared/ProtectedRoute';
import { RouteError } from '@/components/shared/AppErrorBoundary';
import { ModuleGuard } from '@/components/shared/ModuleGuard';
import { LoginPage } from '@/pages/auth/LoginPage';
import { DashboardPage } from '@/pages/dashboard/DashboardPage';
import { DocumentosPage } from '@/pages/documentos/DocumentosPage';
import { NuevoDocumentoPage } from '@/pages/documentos/NuevoDocumentoPage';
import { DocumentoDetallePage } from '@/pages/documentos/DocumentoDetallePage';
import { TramitesPage } from '@/pages/tramites/TramitesPage';
import { BandejaPage } from '@/pages/bandeja/BandejaPage';
import { EnviadosPage } from '@/pages/enviados/EnviadosPage';
import { TrazabilidadPage } from '@/pages/trazabilidad/TrazabilidadPage';
import { BusquedaPage } from '@/pages/busqueda/BusquedaPage';
import { ArchivosPage } from '@/pages/archivos/ArchivosPage';
import { ConfiguracionPage } from '@/pages/configuracion/ConfiguracionPage';
import { UsuariosPage } from '@/pages/admin/UsuariosPage';
import { RolesPage } from '@/pages/admin/RolesPage';
import { ExpedientesPage } from '@/pages/expedientes/ExpedientesPage';
import { NotFoundPage } from '@/pages/NotFoundPage';
import { ReportesPage } from '@/pages/reportes/ReportesPage';

// Envoltura helper: muestra ModuleGuard con el módulo indicado
function M({ m, children }: { m: string; children: React.ReactNode }) {
  return <ModuleGuard modulo={m}>{children}</ModuleGuard>;
}

export const router = createBrowserRouter([
  { path: '/login', element: <LoginPage /> },
  {
    element: <ProtectedRoute />,
    errorElement: <RouteError />,
    children: [
      {
        element: <Layout />,
        errorElement: <RouteError />,
        children: [
          { path: '/',                    element: <Navigate to="/dashboard" replace /> },

          // ── Operativos ──────────────────────────────────────
          { path: '/dashboard',           element: <M m="dashboard"><DashboardPage /></M>,        errorElement: <RouteError /> },
          { path: '/documentos',          element: <M m="documentos"><DocumentosPage /></M>,       errorElement: <RouteError /> },
          { path: '/documentos/nuevo',    element: <M m="documentos"><NuevoDocumentoPage /></M>,  errorElement: <RouteError /> },
          { path: '/documentos/:id',      element: <M m="documentos"><DocumentoDetallePage /></M>,errorElement: <RouteError /> },
          { path: '/bandeja',             element: <M m="bandeja"><BandejaPage /></M>,             errorElement: <RouteError /> },
          { path: '/enviados',            element: <M m="enviados"><EnviadosPage /></M>,            errorElement: <RouteError /> },
          { path: '/tramites',            element: <M m="tramites"><TramitesPage /></M>,            errorElement: <RouteError /> },
          { path: '/trazabilidad',        element: <M m="trazabilidad"><TrazabilidadPage /></M>,   errorElement: <RouteError /> },
          { path: '/busqueda',            element: <M m="busqueda"><BusquedaPage /></M>,            errorElement: <RouteError /> },
          { path: '/archivos',            element: <M m="archivos"><ArchivosPage /></M>,            errorElement: <RouteError /> },

          // ── Administración ──────────────────────────────────
          { path: '/expedientes',         element: <M m="expedientes"><ExpedientesPage /></M>,     errorElement: <RouteError /> },
          { path: '/admin/usuarios',      element: <M m="usuarios"><UsuariosPage /></M>,            errorElement: <RouteError /> },
          { path: '/admin/roles',         element: <M m="roles"><RolesPage /></M>,                  errorElement: <RouteError /> },
          { path: '/admin/configuracion', element: <M m="configuracion"><ConfiguracionPage /></M>, errorElement: <RouteError /> },
          { path: '/reportes',            element: <M m="reportes"><ReportesPage /></M>,            errorElement: <RouteError /> },
        ],
      },
    ],
  },
  { path: '*', element: <NotFoundPage /> },
]);
