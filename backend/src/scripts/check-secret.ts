import { getPool, sql, closePool } from '../config/database';

async function main() {
  const candidato = process.argv[2] ?? '';
  const ambiente   = (process.argv[3] ?? 'TEST').toUpperCase();

  const pool = await getPool();
  const res = await pool.request()
    .input('amb', sql.VarChar(20), ambiente)
    .query<{ jwt_secret: string | null; api_token_key: string | null; entity: string | null }>(
      'SELECT jwt_secret, api_token_key, entity FROM firma_gob_config WHERE ambiente = @amb'
    );

  const row = res.recordset[0];
  const actual = row?.jwt_secret ?? '';
  const coincide = actual === candidato;

  console.log(`[check-secret] Ambiente: ${ambiente}`);
  console.log(`[check-secret] jwt_secret almacenado (largo=${actual.length}): ${actual.slice(0, 3)}...${actual.slice(-3)}`);
  console.log(`[check-secret] jwt_secret candidato  (largo=${candidato.length}): ${candidato.slice(0, 3)}...${candidato.slice(-3)}`);
  console.log(`[check-secret] ¿COINCIDEN?: ${coincide ? 'SÍ' : 'NO'}`);
  console.log(`[check-secret] api_token_key almacenado: ${row?.api_token_key}`);
  console.log(`[check-secret] entity almacenado: ${row?.entity}`);

  await closePool();
}

main().catch((e) => { console.error(e); process.exit(1); });
