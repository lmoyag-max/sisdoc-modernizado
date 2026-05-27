import { useQuery } from '@tanstack/react-query';
import { apiClient } from '@/lib/api/client';

export interface UploadRules {
  extensionesPermitidas: string[];
  maxFileMB:  number;
  maxTotalMB: number;
}

// Defaults seguros: se usan si el servidor no responde o aún no tiene config guardada.
export const UPLOAD_RULES_DEFAULTS: UploadRules = {
  extensionesPermitidas: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'webp'],
  maxFileMB:  20,
  maxTotalMB: 60,
};

export function useUploadRules(): UploadRules {
  const { data } = useQuery({
    queryKey: ['configuracion-upload-rules'],
    queryFn: async () => {
      const { data } = await apiClient.get<{ ok: boolean; data: { uploadRules?: UploadRules } }>('/configuracion');
      return data.data.uploadRules ?? UPLOAD_RULES_DEFAULTS;
    },
    staleTime: 5 * 60 * 1000, // cachea 5 min para no refetch en cada render
    retry: false,              // tolerante a fallos: si falla, usa defaults sin reintentar
  });
  return data ?? UPLOAD_RULES_DEFAULTS;
}
