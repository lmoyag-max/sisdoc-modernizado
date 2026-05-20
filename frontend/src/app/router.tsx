import { createBrowserRouter, Navigate } from 'react-router-dom';
import { Layout } from '@/components/layout/Layout';
import { ProtectedRoute } from '@/components/shared/ProtectedRoute';
import { LoginPage } from '@/pages/auth/LoginPage';
import { DashboardPage } from '@/pages/dashboard/DashboardPage';
import { DocumentosPage } from '@/pages/documentos/DocumentosPage';
import { NuevoDocumentoPage } from '@/pages/documentos/NuevoDocumentoPage';
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
    children: [
      {
        element: <Layout />,
        children: [
          { path: '/',                    element: <Navigate to="/dashboard" replace /> },
          { path: '/dashboard',           element: <DashboardPage /> },
          { path: '/documentos',          element: <DocumentosPage /> },
          { path: '/documentos/nuevo',    element: <NuevoDocumentoPage /> },
          { path: '/documentos/:id',      element: <DocumentosPage /> },
          { path: '/bandeja',             element: <BandejaPage /> },
          { path: '/enviados',            element: <EnviadosPage /> },
          { path: '/tramites',            element: <TramitesPage /> },
          { path: '/trazabilidad',        element: <TrazabilidadPage /> },
          { path: '/busqueda',            element: <BusquedaPage /> },
          { path: '/archivos',            element: <ArchivosPage /> },
          { path: '/expedientes',         element: <ExpedientesPage /> },
          { path: '/admin/usuarios',      element: <UsuariosPage /> },
          { path: '/admin/configuracion', element: <ConfiguracionPage /> },
          { path: '/reportes',            element: <ReportesPage /> },
        ],
      },
    ],
  },
  { path: '*', element: <NotFoundPage /> },
]);
