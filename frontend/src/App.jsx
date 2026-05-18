import { useEffect, useState } from "react";
import axios from "axios";

function App() {
  const [estado, setEstado] = useState(null);
  const [tablas, setTablas] = useState([]);
  const [columnas, setColumnas] = useState([]);

  useEffect(() => {
    axios.get("http://localhost:3001/").then((res) => setEstado(res.data));
    axios.get("http://localhost:3001/api/tablas").then((res) => setTablas(res.data));
    axios.get("http://localhost:3001/api/columnas").then((res) => setColumnas(res.data));
  }, []);

  return (
    <div style={{ padding: 30, fontFamily: "Arial" }}>
      <h1>SISDOC Modernizado</h1>

      <h2>Estado API</h2>
      <pre>{JSON.stringify(estado, null, 2)}</pre>

      <h2>Resumen</h2>
      <p>Total tablas: {tablas.length}</p>
      <p>Total columnas: {columnas.length}</p>

      <h2>Tablas detectadas</h2>
      <ul>
        {tablas.map((t, index) => (
          <li key={index}>{t.TABLE_NAME}</li>
        ))}
      </ul>
    </div>
  );
}

export default App;