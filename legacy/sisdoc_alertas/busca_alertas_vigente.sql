SET ANSI_NULLS ON
SET QUOTED_IDENTIFIER ON
GO

-- Procedimiento para obtener nuevos ingresos generados --
-- Desarrollado por: Camilo Sandoval Sovino --
-- Empresa: G-Talent SpA --
-- Para: SSMC --

CREATE PROCEDURE [dbo].[busca_alertas_vigente] 
AS

BEGIN
	--Si la tabla existe, se borra.
	IF OBJECT_ID('tempdb..#temporal') IS NOT NULL DROP TABLE #temporal;
	--Variables a utilizar
	DECLARE @hoy			varchar(10);
	DECLARE @contador		int;
	DECLARE @incremental	int;
	DECLARE @rut			int;
	DECLARE @email			varchar(50);
	DECLARE @fecha			datetime;
	DECLARE @diferencia		int;
	DECLARE @restantes		int;

	--Establecemos fecha de hoy
	SET @hoy = (SELECT CONVERT(VARCHAR(10), getdate(), 23));

	--Tabla para recopilación de datos
	CREATE TABLE #temporal (
		[id]					[int] IDENTITY(1,1) NOT NULL,
		[id_seguimiento]		[int],
		[rut_destino]			[int] NULL,
		[fecha_sistema]			[datetime] NULL,
		[dias_compromiso]		[int] NULL,
		[dias_transcurridos]	[int] NULL,
		[dias_restantes]		[int] NULL,
		[observaciones]			[varchar](250) NULL,
		[email]					[varchar](100) NULL
	);
	
	--Insertamos la información necesaria con los filtros respectivos
	INSERT INTO #temporal (id_seguimiento, rut_destino, fecha_sistema, dias_compromiso, observaciones)
	SELECT TOP 50 id_seguimiento, rut_destino, fecha_sistema, dias_compromiso, observaciones
	FROM tramite
	WHERE CONVERT(VARCHAR(10),fecha_sistema,23) < @hoy 
	AND rut_procedencia IS NOT NULL
	AND rut_destino IS NOT NULL
	AND id_estado_tramite = 2
	AND dias_compromiso > 0
	ORDER BY fecha_sistema DESC

	--Recorremos los datos guardados temporalmente
	SET @incremental = 1;
	SET @contador = (SELECT count(id) FROM #temporal);

	WHILE (@incremental <= @contador)
	BEGIN
		--Actualizamos la tabla temporal con los datos necesarios
		SET @rut = (SELECT rut_destino FROM #temporal WHERE id = @incremental);
		SET @fecha = (SELECT fecha_sistema FROM #temporal WHERE id = @incremental);
		SET @email = (SELECT email_fun FROM corporativo.dbo.funcionario WHERE rut_fun = @rut);
		SET @diferencia = (SELECT DATEDIFF(day, @fecha, getdate()));
		SET @restantes = (SELECT dias_compromiso - @diferencia FROM #temporal WHERE id = @incremental);

		--Si hay un Email asociado al RUT, se actualiza la tabla de registros
		IF (@email IS NOT NULL)
		BEGIN
			UPDATE #temporal
			SET email = @email, dias_transcurridos = @diferencia, dias_restantes = @restantes
			WHERE id = @incremental;
		END
		
		--Pasamos al siguiente registro
		SET @incremental = @incremental + 1;
	END

	--Mostramos la información
	SELECT * FROM #temporal
	WHERE email IS NOT NULL
	ORDER BY id ASC;

END

--EXEC busca_alertas_vigente;