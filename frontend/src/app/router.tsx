import { createBrowserRouter, Navigate } from 'react-router-dom';
import { Layout } from '@/components/layout/Layout';
import { ProtectedRoute } from '@/components/shared/ProtectedRoute';
import { LoginPage } from '@/pages/auth/LoginPage';
import { DashboardPage } from '@/pages/dashboard/DashboardPage';
import { DocumentosPage } from '@/pages/documentos/DocumentosPage';
import { TramitesPage } from '@/pages/tramites/TramitesPage';
import { NotFoundPage } from '@/pages/NotFoundPage';

export const router = createBrowserRouter([
  {
    path: '/login',
    element: <LoginPage />,
  },
  {
    element: <ProtectedRoute />,
    children: [
      {
        element: <Layout />,
        children: [
          { path: '/', element: <Navigate to="/dashboard" replace /> },
          { path: '/dashboard', element: <DashboardPage /> },
          { path: '/documentos', element: <DocumentosPage /> },
          { path: '/tramites', element: <TramitesPage /> },
          {
            path: '/expedientes',
            lazy: async () => {
              const { EmptyState } = await import('@/components/shared/EmptyState');
              const { FolderOpen } = await import('lucide-react');
              return {
                Component: () => (
                  <div className="py-16">
                    <EmptyState icon={FolderOpen} title="Expedientes" description="Módulo en construcción — próximamente." />
                  </div>
                ),
              };
            },
          },
          {
            path: '/busqueda',
            lazy: async () => {
              const { EmptyState } = await import('@/components/shared/EmptyState');
              const { Search } = await import('lucide-react');
              return {
                Component: () => (
                  <div className="py-16">
                    <EmptyState icon={Search} title="Búsqueda avanzada" description="Módulo en construcción — próximamente." />
                  </div>
                ),
              };
            },
          },
          {
            path: '/reportes',
            lazy: async () => {
              const { EmptyState } = await import('@/components/shared/EmptyState');
              const { BarChart3 } = await import('lucide-react');
              return {
                Component: () => (
                  <div className="py-16">
                    <EmptyState icon={BarChart3} title="Reportes" description="Módulo en construcción — próximamente." />
                  </div>
                ),
              };
            },
          },
        ],
      },
    ],
  },
  { path: '*', element: <NotFoundPage /> },
]);
