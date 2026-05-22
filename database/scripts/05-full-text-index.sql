-- Full-Text Search setup para SISDOC
-- Ejecutar una sola vez. Requiere que SQL Full-Text Search esté instalado en el servidor.
-- Verificar: SELECT FULLTEXTSERVICEPROPERTY('IsFullTextInstalled')  → debe retornar 1

USE SISDOC;
GO

-- ── Catálogo FTS ──────────────────────────────────────────────
IF NOT EXISTS (SELECT 1 FROM sys.fulltext_catalogs WHERE name = 'SisdocFTCatalog')
  CREATE FULLTEXT CATALOG SisdocFTCatalog AS DEFAULT;
GO

-- ── Índice FTS en documento.materia ──────────────────────────
-- Requiere que exista una PK única en la tabla; aquí se asume PK_documento sobre id_documento.
-- Si el nombre difiere, ajustar KEY INDEX según:
--   SELECT name FROM sys.indexes WHERE object_id = OBJECT_ID('documento') AND is_primary_key = 1
IF NOT EXISTS (
  SELECT 1 FROM sys.fulltext_indexes WHERE object_id = OBJECT_ID('documento')
)
BEGIN
  CREATE FULLTEXT INDEX ON documento(materia LANGUAGE 'Spanish')
  KEY INDEX PK_documento
  ON SisdocFTCatalog
  WITH CHANGE_TRACKING AUTO;
END
GO

-- ── Índice FTS en funcionario (nombres, apellidos) ────────────
IF NOT EXISTS (
  SELECT 1 FROM sys.fulltext_indexes WHERE object_id = OBJECT_ID('funcionario')
)
BEGIN
  CREATE FULLTEXT INDEX ON funcionario(nombres LANGUAGE 'Spanish', apellidos LANGUAGE 'Spanish')
  KEY INDEX PK_funcionario
  ON SisdocFTCatalog
  WITH CHANGE_TRACKING AUTO;
END
GO

-- Verificar que los índices se crearon:
-- SELECT OBJECT_NAME(object_id) AS tabla, * FROM sys.fulltext_indexes;
