import { createBrowserRouter, Navigate } from 'react-router-dom';
import { Layout } from '@/components/layout/Layout';
import { ProtectedRoute } from '@/components/shared/ProtectedRoute';
import { LoginPage } from '@/pages/auth/LoginPage';
import { DashboardPage } from '@/pages/dashboard/DashboardPage';
import { DocumentosPage } from '@/pages/documentos/DocumentosPage';
import { TramitesPage } from '@/pages/tramites/TramitesPage';
import { BandejaPage } from '@/pages/bandeja/BandejaPage';
import { EnviadosPage } from '@/pages/enviados/EnviadosPage';
import { TrazabilidadPage } from '@/pages/trazabilidad/TrazabilidadPage';
import { BusquedaPage } from '@/pages/busqueda/BusquedaPage';
import { ArchivosPage } from '@/pages/archivos/ArchivosPage';
import { NotFoundPage } from '@/pages/NotFoundPage';
import { EmptyState } from '@/components/shared/EmptyState';
import { FolderOpen, Users, BarChart3, Settings } from 'lucide-react';

const Stub = ({ title, desc, icon: Icon }: { title: string; desc: string; icon: React.ComponentType<{ className?: string }> }) => (
  <div className="py-20">
    <EmptyState icon={Icon} title={title} description={desc} />
  </div>
);

export const router = createBrowserRouter([
  { path: '/login', element: <LoginPage /> },
  {
    element: <ProtectedRoute />,
    children: [
      {
        element: <Layout />,
        children: [
          { path: '/',               element: <Navigate to="/dashboard" replace /> },
          { path: '/dashboard',      element: <DashboardPage /> },
          { path: '/documentos',     element: <DocumentosPage /> },
          { path: '/documentos/:id', element: <DocumentosPage /> },
          { path: '/bandeja',        element: <BandejaPage /> },
          { path: '/enviados',       element: <EnviadosPage /> },
          { path: '/tramites',       element: <TramitesPage /> },
          { path: '/trazabilidad',   element: <TrazabilidadPage /> },
          { path: '/busqueda',       element: <BusquedaPage /> },
          { path: '/archivos',       element: <ArchivosPage /> },
          {
            path: '/expedientes',
            element: <Stub icon={FolderOpen} title="Expedientes" desc="Gestión de expedientes — próximamente" />,
          },
          {
            path: '/admin/usuarios',
            element: <Stub icon={Users} title="Usuarios" desc="Administración de usuarios — próximamente" />,
          },
          {
            path: '/reportes',
            element: <Stub icon={BarChart3} title="Reportes" desc="Módulo de reportes avanzados — próximamente" />,
          },
          {
            path: '/admin/configuracion',
            element: <Stub icon={Settings} title="Configuración" desc="Configuración del sistema — próximamente" />,
          },
        ],
      },
    ],
  },
  { path: '*', element: <NotFoundPage /> },
]);
