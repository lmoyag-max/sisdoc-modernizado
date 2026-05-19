// Declaración de tipos para mssql v12 (sin @types/mssql disponible)
declare module 'mssql' {
  export interface config {
    user?: string;
    password?: string;
    server: string;
    port?: number;
    database?: string;
    options?: {
      encrypt?: boolean;
      trustServerCertificate?: boolean;
      enableArithAbort?: boolean;
      instanceName?: string;
    };
    pool?: {
      max?: number;
      min?: number;
      idleTimeoutMillis?: number;
    };
    connectionTimeout?: number;
    requestTimeout?: number;
    authentication?: {
      type: string;
      options?: { userName?: string; password?: string };
    };
  }

  export class ConnectionPool {
    connected: boolean;
    constructor(config: config);
    connect(): Promise<ConnectionPool>;
    close(): Promise<void>;
    request(): Request;
  }

  export class Request {
    input(name: string, type: DataType, value: unknown): this;
    query<T = Record<string, unknown>>(sql: string): Promise<{ recordset: T[]; returnValue: number }>;
    execute<T = Record<string, unknown>>(procedure: string): Promise<{ recordset: T[]; returnValue: number }>;
  }

  export type DataType = unknown;

  export const Int: DataType;
  export const VarChar: ((length?: number) => DataType) & DataType;
  export const NVarChar: ((length?: number) => DataType) & DataType;
  export const Char: ((length?: number) => DataType) & DataType;
  export const Bit: DataType;
  export const DateTime: DataType;
  export const Date: DataType;
  export const Float: DataType;

  export function connect(config: config): Promise<ConnectionPool>;
  export function close(): Promise<void>;
}
