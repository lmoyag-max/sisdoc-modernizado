import { createBrowserRouter, Navigate } from 'react-router-dom';
import { Layout } from '@/components/layout/Layout';
import { ProtectedRoute } from '@/components/shared/ProtectedRoute';
import { RouteError } from '@/components/shared/AppErrorBoundary';
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
import { ExpedientesPage } from '@/pages/expedientes/ExpedientesPage';
import { NotFoundPage } from '@/pages/NotFoundPage';
import { ReportesPage } from '@/pages/reportes/ReportesPage';

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
          { path: '/dashboard',           element: <DashboardPage />,        errorElement: <RouteError /> },
          { path: '/documentos',          element: <DocumentosPage />,       errorElement: <RouteError /> },
          { path: '/documentos/nuevo',    element: <NuevoDocumentoPage />,   errorElement: <RouteError /> },
          { path: '/documentos/:id',      element: <DocumentoDetallePage />, errorElement: <RouteError /> },
          { path: '/bandeja',             element: <BandejaPage />,          errorElement: <RouteError /> },
          { path: '/enviados',            element: <EnviadosPage />,         errorElement: <RouteError /> },
          { path: '/tramites',            element: <TramitesPage />,         errorElement: <RouteError /> },
          { path: '/trazabilidad',        element: <TrazabilidadPage />,     errorElement: <RouteError /> },
          { path: '/busqueda',            element: <BusquedaPage />,         errorElement: <RouteError /> },
          { path: '/archivos',            element: <ArchivosPage />,         errorElement: <RouteError /> },
          { path: '/expedientes',         element: <ExpedientesPage />,      errorElement: <RouteError /> },
          { path: '/admin/usuarios',      element: <UsuariosPage />,         errorElement: <RouteError /> },
          { path: '/admin/configuracion', element: <ConfiguracionPage />,    errorElement: <RouteError /> },
          { path: '/reportes',            element: <ReportesPage />,         errorElement: <RouteError /> },
        ],
      },
    ],
  },
  { path: '*', element: <NotFoundPage /> },
]);
