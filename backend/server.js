const express = require("express");
const cors = require("cors");
const sql = require("mssql");
require("dotenv").config();

const app = express();

app.use(cors());
app.use(express.json());

const dbConfig = {
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  server: process.env.DB_SERVER,
  port: parseInt(process.env.DB_PORT),
  database: process.env.DB_DATABASE,
  options: {
    encrypt: false,
    trustServerCertificate: true,
  },
};

app.get("/", (req, res) => {
  res.json({
    sistema: "SISDOC Modernizado",
    estado: "Backend operativo",
  });
});

app.get("/api/health-db", async (req, res) => {
  try {
    const pool = await sql.connect(dbConfig);
    const result = await pool.request().query("SELECT GETDATE() AS fecha");
    res.json({ ok: true, data: result.recordset });
  } catch (error) {
    res.status(500).json({ ok: false, error: error.message });
  }
});

app.get("/api/tablas", async (req, res) => {
  try {
    const pool = await sql.connect(dbConfig);

    const result = await pool.request().query(`
      SELECT TABLE_NAME
      FROM INFORMATION_SCHEMA.TABLES
      WHERE TABLE_TYPE='BASE TABLE'
      ORDER BY TABLE_NAME
    `);

    res.json(result.recordset);
  } catch (error) {
    res.status(500).json({ ok: false, error: error.message });
  }
});



app.get("/api/procedimientos", async (req, res) => {
  try {
    const pool = await sql.connect(dbConfig);

    const result = await pool.request().query(`
      SELECT 
        name AS procedimiento,
        create_date,
        modify_date
      FROM sys.procedures
      ORDER BY name
    `);

    res.json(result.recordset);
  } catch (error) {
    res.status(500).json({ ok: false, error: error.message });
  }
});

app.get("/api/buscar/:texto", async (req, res) => {
  try {
    const texto = req.params.texto;
    const pool = await sql.connect(dbConfig);

    const result = await pool.request()
      .input("texto", sql.VarChar, `%${texto}%`)
      .query(`
        SELECT 
          TABLE_NAME,
          COLUMN_NAME,
          DATA_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME LIKE @texto
           OR COLUMN_NAME LIKE @texto
        ORDER BY TABLE_NAME, COLUMN_NAME
      `);

    res.json(result.recordset);
  } catch (error) {
    res.status(500).json({ ok: false, error: error.message });
  }
});


app.listen(process.env.PORT, () => {
  console.log(`SISDOC API corriendo en puerto ${process.env.PORT}`);
});