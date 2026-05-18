VERSION 5.00
Object = "{5E9E78A0-531B-11CF-91F6-C2863C385E30}#1.0#0"; "MSFLXGRD.OCX"
Object = "{B23ACFEF-DBD7-42E2-AD46-46124EAA5DA5}#1.0#0"; "acxexcelesc.ocx"
Begin VB.Form Frm_OD_CE 
   BackColor       =   &H80000013&
   Caption         =   "OD_CE"
   ClientHeight    =   6420
   ClientLeft      =   60
   ClientTop       =   345
   ClientWidth     =   11820
   LinkTopic       =   "Form1"
   ScaleHeight     =   6420
   ScaleWidth      =   11820
   StartUpPosition =   2  'CenterScreen
   Begin AcXExcelEsc.AcXExcel AcXExcel1 
      Height          =   255
      Left            =   3720
      TabIndex        =   14
      Top             =   6000
      Visible         =   0   'False
      Width           =   1455
      Object.Visible         =   -1  'True
      AutoScroll      =   0   'False
      AutoSize        =   0   'False
      AxBorderStyle   =   1
      Caption         =   "AcXExcel"
      Color           =   -2147483633
      BeginProperty Font {0BE35203-8F91-11CE-9DE3-00AA004BB851} 
         Name            =   "MS Sans Serif"
         Size            =   8.25
         Charset         =   0
         Weight          =   400
         Underline       =   0   'False
         Italic          =   0   'False
         Strikethrough   =   0   'False
      EndProperty
      KeyPreview      =   0   'False
      PixelsPerInch   =   96
      PrintScale      =   1
      Scaled          =   -1  'True
      DropTarget      =   0   'False
      HelpFile        =   ""
      DoubleBuffered  =   0   'False
      Enabled         =   -1  'True
      Cursor          =   0
      HelpType        =   0
      HelpKeyword     =   ""
      Columna_X       =   ""
      Fila            =   0
      TotalFilas      =   0
      archivo         =   ""
      Hoja            =   ""
      Listoco         =   0   'False
   End
   Begin VB.CommandButton cmd_exportar 
      Caption         =   "Exportar a Excel"
      Enabled         =   0   'False
      Height          =   375
      Left            =   2040
      TabIndex        =   13
      Top             =   5880
      Width           =   1335
   End
   Begin VB.Frame Frame3 
      Caption         =   "Atenciones"
      Height          =   3615
      Left            =   4680
      TabIndex        =   10
      Top             =   2040
      Width           =   6855
      Begin MSFlexGridLib.MSFlexGrid MSFlexGrid2 
         Height          =   3255
         Left            =   120
         TabIndex        =   11
         Top             =   240
         Width           =   6615
         _ExtentX        =   11668
         _ExtentY        =   5741
         _Version        =   393216
         Cols            =   16
         BackColor       =   16777215
         BeginProperty Font {0BE35203-8F91-11CE-9DE3-00AA004BB851} 
            Name            =   "MS Sans Serif"
            Size            =   8.25
            Charset         =   0
            Weight          =   400
            Underline       =   0   'False
            Italic          =   0   'False
            Strikethrough   =   0   'False
         EndProperty
      End
   End
   Begin VB.CommandButton Command3 
      Caption         =   "&volver"
      Height          =   375
      Left            =   10440
      TabIndex        =   8
      Top             =   5880
      Width           =   1215
   End
   Begin VB.CommandButton cmd_grabar 
      Caption         =   "&Grabar"
      Enabled         =   0   'False
      Height          =   375
      Left            =   240
      TabIndex        =   3
      Top             =   5880
      Width           =   1215
   End
   Begin VB.Frame Frame2 
      Caption         =   "Ingreso "
      Height          =   3615
      Left            =   240
      TabIndex        =   5
      Top             =   2040
      Width           =   4335
      Begin MSFlexGridLib.MSFlexGrid MSFlexGrid1 
         Height          =   3255
         Left            =   120
         TabIndex        =   2
         Top             =   240
         Width           =   4095
         _ExtentX        =   7223
         _ExtentY        =   5741
         _Version        =   393216
         Rows            =   37
         Cols            =   3
         FixedCols       =   2
         WordWrap        =   -1  'True
      End
   End
   Begin VB.Frame Frame1 
      Height          =   975
      Left            =   240
      TabIndex        =   4
      Top             =   840
      Width           =   11295
      Begin VB.ComboBox cbo_ce 
         Height          =   315
         Left            =   240
         TabIndex        =   0
         Top             =   480
         Width           =   3615
      End
      Begin VB.ComboBox cbo_atencion 
         Height          =   315
         Left            =   6960
         TabIndex        =   1
         Top             =   480
         Width           =   3615
      End
      Begin VB.Label Label2 
         Caption         =   "Seleccione Centro de Especialidad"
         Height          =   255
         Left            =   240
         TabIndex        =   7
         Top             =   240
         Width           =   2775
      End
      Begin VB.Label Label1 
         Caption         =   "Nº de"
         Height          =   255
         Left            =   6960
         TabIndex        =   6
         Top             =   240
         Width           =   2775
      End
   End
   Begin VB.Label lbl_nombre 
      Alignment       =   2  'Center
      Caption         =   "x"
      Height          =   255
      Left            =   960
      TabIndex        =   12
      Top             =   360
      Width           =   8295
   End
   Begin VB.Label Label3 
      Alignment       =   2  'Center
      Caption         =   "Tabla Balance Demanda-Oferta Centro de Especialidad"
      BeginProperty Font 
         Name            =   "MS Sans Serif"
         Size            =   12
         Charset         =   0
         Weight          =   700
         Underline       =   0   'False
         Italic          =   0   'False
         Strikethrough   =   0   'False
      EndProperty
      Height          =   375
      Left            =   960
      TabIndex        =   9
      Top             =   0
      Width           =   8055
   End
End
Attribute VB_Name = "Frm_OD_CE"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Dim nfilas, Totcons, Totesp, ncol, Totintercon, TotxFila As Long
Dim sql, sql1 As String
Private Sub cbo_atencion_Click()
cod_ce = cbo_ce.ItemData(cbo_ce.ListIndex)
pp = carga_especialidad_ce(cod_ce, 0)
Totcons = 0
ncol = 0
sw_ce_hos = 0
pp = Hacer_Query(cod_ce)
Carga_Grilla2
cmd_grabar.Enabled = True
MSFlexGrid1.Row = 1
MSFlexGrid1.Col = 2
MSFlexGrid1.SetFocus
End Sub
Function Hacer_Query(cod1 As Integer) As Integer

Conexion

Set rs_derivacion = New ADODB.Recordset
rs_derivacion.ActiveConnection = DBProgramacion

sql = ""
sql = "SELECT especialidad2.id_especialidad2 as Id, especialidad2.desc_especialidad2 as Especialidad," & _
" oferta_demanda_ce.otras_derivac AS Otras_Derivaciones, oferta_demanda_ce.controles AS Controles," & _
" oferta_demanda_ce.derivacion_interna AS Derivacion_Interna, oferta_demanda_ce.lista_espera AS Lista_Espera," & _
" oferta_demanda_ce.oferta_consulta AS of_consulta,oferta_demanda_ce.oferta_procedimiento AS of_proc," & _
" oferta_demanda_ce.oferta_cir_amb AS of_ciramb" & _
" From especialidad2, oferta_demanda_ce" & _
" Where especialidad2.id_especialidad2 = oferta_demanda_ce.id_especialidad2" & _
" and oferta_demanda_ce.id_ce =" & cod1 & _
" UNION SELECT  id_especialidad2 as Id, desc_especialidad2 as Especialidad, 0 AS  Otras_Derivaciones," & _
" 0 AS Controles, 0 AS Derivacion_Interna, 0 AS Lista_Espera," & _
" 0 AS of_consulta,0 AS of_proc,0 AS of_ciramb" & _
" From especialidad2" & _
" WHERE (id_especialidad2 NOT IN " & _
                         " (SELECT    oferta_demanda_ce.id_especialidad2" & _
                         "   From oferta_demanda_ce" & _
                         "   WHERE     (oferta_demanda_ce.id_ce =" & cod1 & ")))" & _
" ORDER BY Id"


rs_derivacion.Source = sql

sql1 = ""
sql1 = "SELECT especialidad2.id_especialidad2 as Nro, especialidad2.desc_especialidad2 as Especialidad," & _
" SUM(derivacion_ap_ce.num_interconsulta) As Atenciones" & _
" From especialidad2, derivacion_ap_ce" & _
" Where especialidad2.id_especialidad2 = derivacion_ap_ce.id_especialidad2" & _
" and derivacion_ap_ce.id_ce =" & cod1 & _
" GROUP BY especialidad2.id_especialidad2, especialidad2.desc_especialidad2" & _
" UNION SELECT     id_especialidad2 as Nro, desc_especialidad2 as Especialidad, 0 AS  Atenciones" & _
" From especialidad2" & _
" WHERE     (id_especialidad2 NOT IN" & _
                          " (SELECT    derivacion_ap_ce.id_especialidad2" & _
                          " From derivacion_ap_ce" & _
                          " WHERE     (derivacion_ap_ce.id_ce =" & cod1 & ")))" & _
" ORDER BY Nro"
Set rs_deriva_ap_ce = New ADODB.Recordset
rs_deriva_ap_ce.ActiveConnection = DBProgramacion

rs_deriva_ap_ce.Source = sql1
rs_deriva_ap_ce.Open

ncol = 0
Cargar_Matriz
DBProgramacion.Close

End Function

Private Sub Cargar_Matriz()

rs_derivacion.Open
Totesp = 0
Do While Not rs_derivacion.EOF
 Totesp = Totesp + 1
 rs_derivacion.MoveNext
Loop
Totcons = 15
nfilas = Totesp + 2

ReDim Miarray(nfilas, (Totcons + 1))
rs_derivacion.MoveFirst

'Carga el arreglo con las interconsultas
Set rs_coeficiente = New ADODB.Recordset

rs_coeficiente.ActiveConnection = DBProgramacion

If rs_derivacion.EOF Then
    For f = 2 To Totesp
        Miarray(f, ncol) = 0
        Totintercon = 0
    Next
    Miarray(f, ncol) = Totintercon
    f = 100
    ncol = ncol + 1
    rs_derivacion.Close
Else

rs_derivacion.MoveFirst
Totintercon = 0
ncol = 1

Miarray(0, 0) = "Id"
Miarray(0, 1) = "Especialidad"
Miarray(0, 2) = "Cons. Espec."
Miarray(0, 3) = "Procedimiento"
Miarray(0, 4) = "Cirug. Amb."
Miarray(0, 5) = "Derivaciones."
Miarray(0, 6) = "Otras Deriv."
Miarray(0, 7) = "Controles"
Miarray(0, 8) = "Deriv.Int."
Miarray(0, 9) = "Lista Espera"
Miarray(0, 10) = "Total Demanda"
Miarray(0, 11) = "Dmda. Proc."
Miarray(0, 12) = "Dmda. Cir. Amb."
Miarray(0, 13) = "Ofe. Cons."
Miarray(0, 14) = "Ofe. Proc."
Miarray(0, 15) = "Ofe. Cir. Amb."

Miarray(1, 2) = "Atenciones"
Miarray(1, 3) = "Cantidad"
Miarray(1, 4) = "Cantidad"
Miarray(1, 5) = "Atenciones"
Miarray(1, 6) = "Atenciones"
Miarray(1, 7) = "Atenciones"
Miarray(1, 8) = "Atenciones"
Miarray(1, 9) = "Atenciones"
Miarray(1, 10) = "Atenciones"
Miarray(1, 11) = "Cantidad"
Miarray(1, 12) = "Cantidad"
Miarray(1, 13) = "Atenciones"
Miarray(1, 14) = "Cantidad"
Miarray(1, 15) = "Cantidad"
Miarray(nfilas, 1) = "TOTAL"


For f = 2 To nfilas


 Miarray(f, 0) = rs_derivacion(0)
        Miarray(f, 1) = rs_derivacion(1)
        If Not rs_deriva_ap_ce.EOF Then
            Miarray(f, 5) = rs_deriva_ap_ce(2)
            rs_deriva_ap_ce.MoveNext
        End If
        Miarray(f, 6) = rs_derivacion(2)
        Miarray(f, 7) = rs_derivacion(3)
        Miarray(f, 8) = rs_derivacion(4)
        Miarray(f, 9) = rs_derivacion(5)
        For ncol = 5 To 9
            Totintercon = Totintercon + Miarray(f, ncol)
        Next
        Miarray(f, 10) = Totintercon
        sql = ""
        sql = "select * from coeficiente_ce" & _
        " where id_ce=" & cod_ce & _
        " and id_especialidad2=" & Miarray(f, 0)
        rs_coeficiente.Source = sql
        rs_coeficiente.Open
        If Not rs_coeficiente.EOF Then
            Miarray(f, 11) = Miarray(f, 10) * rs_coeficiente(2)
            Miarray(f, 12) = Miarray(f, 10) * rs_coeficiente(3)
        Else
            Miarray(f, 11) = 0
            Miarray(f, 12) = 0
        End If
        rs_coeficiente.Close
        Totintercon = 0
        Miarray(f, 13) = rs_derivacion(6)
        Miarray(f, 14) = rs_derivacion(7)
        Miarray(f, 15) = rs_derivacion(8)
        Miarray(f, 2) = Miarray(f, 10) - Miarray(f, 13)
        Miarray(f, 3) = Miarray(f, 11) - Miarray(f, 14)
        Miarray(f, 4) = Miarray(f, 12) - Miarray(f, 15)
        rs_derivacion.MoveNext

      
            
    'Carga Totales
    
    If rs_derivacion.EOF Then
   
    TotxFila = 0
    f = nfilas - 1
    
        For c = 2 To Totcons
            For i = 2 To f
        
                TotxFila = TotxFila + Miarray(i, c)
        
           Next
           Miarray(i, c) = TotxFila
           TotxFila = 0
        Next
    
        f = 100
        ncol = ncol + 1
    End If
Next
rs_derivacion.Close
End If

End Sub
Private Sub Carga_Grilla2()
    Dim i, c As Integer
    Totesp = nfilas - 1
    MSFlexGrid2.Rows = nfilas + 1
    MSFlexGrid2.Cols = Totcons + 1
    Frm_OD_CE.MSFlexGrid2.Col = 1
    Frm_OD_CE.MSFlexGrid2.ColWidth(0) = 500
    Frm_OD_CE.MSFlexGrid2.ColWidth(1) = 2300
    Frm_OD_CE.MSFlexGrid2.ColWidth(2) = 1000
    Frm_OD_CE.MSFlexGrid2.ColWidth(3) = 1100
    Frm_OD_CE.MSFlexGrid2.ColWidth(5) = 1100
    Frm_OD_CE.MSFlexGrid2.ColWidth(6) = 1000
    Frm_OD_CE.MSFlexGrid2.ColWidth(7) = 800
    Frm_OD_CE.MSFlexGrid2.ColWidth(8) = 800
    Frm_OD_CE.MSFlexGrid2.ColWidth(10) = 1200
    Frm_OD_CE.MSFlexGrid2.ColWidth(11) = 1000
    Frm_OD_CE.MSFlexGrid2.ColWidth(12) = 1300
    Frm_OD_CE.MSFlexGrid2.ColWidth(13) = 900
    Frm_OD_CE.MSFlexGrid2.ColWidth(14) = 900
    Frm_OD_CE.MSFlexGrid2.ColWidth(15) = 1100
    
    For i = 0 To nfilas
    MSFlexGrid2.Row = i

        For c = 0 To Totcons

            MSFlexGrid2.Col = c
            Frm_OD_CE.MSFlexGrid2.Text = Miarray(i, c)
            If Miarray(i, c) < 0 Then
                Frm_OD_CE.MSFlexGrid2.CellForeColor = &HC00000
                Frm_OD_CE.MSFlexGrid2.CellBackColor = &HE0E0E0
            End If
            If c = 10 Then
                Frm_OD_CE.MSFlexGrid2.CellBackColor = 12648447
            End If
            If i = nfilas Then
            Frm_OD_CE.MSFlexGrid2.CellBackColor = &HE0E0E0

            End If
       Next
    Next


End Sub




Private Sub cbo_ce_Click()
cod_ce = cbo_ce.ItemData(cbo_ce.ListIndex)
Totcons = 0
ncol = 0
sw_ce_hos = 0
pp = Hacer_Query(cod_ce)
Carga_Grilla2
cmd_exportar.Enabled = True
End Sub

Private Sub cmd_exportar_Click()
Dim j, i, sub_col As Integer
Dim FilaExcelGrilla As String


  cmd_exportar.Enabled = False
  Nom_Form = RTrim(Frm_OD_CE.Caption) & ".xls"
  AcXExcel1.TotalFilas = MSFlexGrid2.Rows
  AcXExcel1.Hoja = "Hoja1"
  AcXExcel1.Listoco = False
  AcXExcel1.archivo = "c:\desa\programacion\excel\" & Nom_Form
  AcXExcel1.Inicializa
  nfilas = MSFlexGrid2.Rows - 1
  Totcons = MSFlexGrid2.Cols - 1
  For j = 0 To nfilas - 1
  MSFlexGrid2.Row = j

       For i = 1 To (Totcons)
       MSFlexGrid2.Col = i
           If j = 0 Then
              FilaExcelGrilla = FilaExcelGrilla & "·c" & MSFlexGrid2.Text 'Miarray(j, i)
           Else
              If i = 1 Then
                 FilaExcelGrilla = FilaExcelGrilla & "·c" & MSFlexGrid2.Text 'Miarray(j, i)
              Else
                 FilaExcelGrilla = FilaExcelGrilla & "·n" & Val(Replace(MSFlexGrid2.Text, ".", "")) 'Miarray(j, i)
              End If
           End If
        Next
   AcXExcel1.fila = j
   AcXExcel1.Columna_X = FilaExcelGrilla & "·"
   FilaExcelGrilla = ""
   AcXExcel1.MeteColumna
  Next
  FilaExcelGrilla = ""
  sub_col = 66
  For i = 0 To (Totcons - 1)
  FilaExcelGrilla = FilaExcelGrilla & "·fsum(" & Chr(sub_col) & "2:" & Chr(sub_col) & Trim(Str(nfilas)) & ")"
  sub_col = sub_col + 1
  Next
  AcXExcel1.fila = nfilas
  AcXExcel1.Columna_X = "·cTotal" & FilaExcelGrilla & "·"
  AcXExcel1.MeteColumna
  
  AcXExcel1.GrabaPlanilla
  Do While Not AcXExcel1.Listoco
    DoEvents
  Loop
  cmd_exportar.Enabled = True
   MsgBox "Se han exportado los datos al siguiente archivo " & "c:\desa\programacion\excel\" & Nom_Form, vbInformation
End Sub

Private Sub cmd_grabar_Click()
Dim nfil, TotFila, nExiste, campo_sel, ch As Integer
Dim cod_especialidad
Dim rs_oferta_demanda_ce As ADODB.Recordset
Conexion
ReDim Arr_Campos(7)
Arr_Campos(0) = "otras_derivac"
Arr_Campos(1) = "controles"
Arr_Campos(2) = "derivacion_interna"
Arr_Campos(3) = "lista_espera"
Arr_Campos(4) = "oferta_consulta"
Arr_Campos(5) = "oferta_procedimiento"
Arr_Campos(6) = "oferta_cir_amb"

campo_sel = (cbo_atencion.ItemData(cbo_atencion.ListIndex) - 1)

'PREPARA EL RECORDSET PARA GRABAR
Set rs_oferta_demanda_ce = New ADODB.Recordset
rs_oferta_demanda_ce.CursorType = adOpenKeyset
rs_oferta_demanda_ce.LockType = adLockOptimistic
rs_oferta_demanda_ce.Open "oferta_demanda_ce", DBProgramacion, , , adCmdTable

ch = consulta_hospital(Val(cbo_ce.ItemData(cbo_ce.ListIndex)), Val(cod_ss))

For i = 1 To TotFilaEsp - 1
MSFlexGrid1.Row = i
MSFlexGrid1.Col = 2
'If Val(MSFlexGrid1.Text) > 0 Then
    MSFlexGrid1.Col = 0
    cod_especialidad = MSFlexGrid1.Text
    
   nExiste = consulta_especialidad(MSFlexGrid1.Text, Val(cbo_ce.ItemData(cbo_ce.ListIndex)), Val(cod_ss))
    If nExiste = 0 Then
     rs_oferta_demanda_ce.AddNew
      rs_oferta_demanda_ce!id_ce = cbo_ce.ItemData(cbo_ce.ListIndex)
      rs_oferta_demanda_ce!id_hospital = ch
      MSFlexGrid1.Col = 0
      rs_oferta_demanda_ce!id_especialidad2 = Val(MSFlexGrid1.Text)
      MSFlexGrid1.Col = 2
      rs_oferta_demanda_ce!otras_derivac = IIf(cbo_atencion.ItemData(cbo_atencion.ListIndex) = 1, Val(MSFlexGrid1.Text), 0)
      rs_oferta_demanda_ce!controles = IIf(cbo_atencion.ItemData(cbo_atencion.ListIndex) = 2, Val(MSFlexGrid1.Text), 0)
      rs_oferta_demanda_ce!derivacion_interna = IIf(cbo_atencion.ItemData(cbo_atencion.ListIndex) = 3, Val(MSFlexGrid1.Text), 0)
      rs_oferta_demanda_ce!lista_espera = IIf(cbo_atencion.ItemData(cbo_atencion.ListIndex) = 4, Val(MSFlexGrid1.Text), 0)
      rs_oferta_demanda_ce!oferta_consulta = IIf(cbo_atencion.ItemData(cbo_atencion.ListIndex) = 5, Val(MSFlexGrid1.Text), 0)
      rs_oferta_demanda_ce!oferta_procedimiento = IIf(cbo_atencion.ItemData(cbo_atencion.ListIndex) = 6, Val(MSFlexGrid1.Text), 0)
      rs_oferta_demanda_ce!oferta_cir_amb = IIf(cbo_atencion.ItemData(cbo_atencion.ListIndex) = 7, Val(MSFlexGrid1.Text), 0)
       rs_oferta_demanda_ce!id_ssalud = cod_ss
    rs_oferta_demanda_ce.Update
    Else
        MSFlexGrid1.Col = 2
        Sql_update = "update oferta_demanda_ce set " & Arr_Campos(campo_sel) & " = " & Val(MSFlexGrid1.Text) & " where id_ce = " & cbo_ce.ItemData(cbo_ce.ListIndex) & " AND id_especialidad2 = " & Val(cod_especialidad)
        DBProgramacion.Execute Sql_update

End If
'End If
Next
rs_oferta_demanda_ce.Close
MsgBox "Registro Guardado"
Totcons = 0
ncol = 0
sw_ce_hos = 0
pp = Hacer_Query(cod_ce)
Carga_Grilla2
'DBProgramacion.Close
End Sub
Function consulta_especialidad(grilla_valor As String, cod2 As Integer, cod3 As Integer) As Integer
Dim rs_especialidad2 As ADODB.Recordset
On Error GoTo error_control
Conexion
Set rs_especialidad2 = New ADODB.Recordset
rs_especialidad2.ActiveConnection = DBProgramacion
rs_especialidad2.Source = "select * from oferta_demanda_ce where id_especialidad2 = " & Val(grilla_valor) & " and id_ce = " & Val(cod2) & " and id_ssalud = " & Val(cod3)
rs_especialidad2.Open

nTotReg = 0

rs_especialidad2.MoveFirst
Do While Not rs_especialidad2.EOF
   nTotReg = TotReg + 1
   rs_especialidad2.MoveNext
Loop

error_control:
If (Err.Number = 3021) Then
    consulta_especialidad = 0
    Else
    consulta_especialidad = nTotReg
End If
End Function

Function consulta_hospital(cod1 As Integer, cod2 As Integer) As Integer
Dim rs_especialidad2 As ADODB.Recordset
Dim codigo As Integer


Conexion
Set rs_especialidad2 = New ADODB.Recordset
rs_especialidad2.ActiveConnection = DBProgramacion
rs_especialidad2.Source = "select * from centro_especialidad where id_ce = " & Val(cod1) & " and id_ssalud = " & Val(cod2)
rs_especialidad2.Open

nTotReg = 0

rs_especialidad2.MoveFirst
Do While Not rs_especialidad2.EOF
   nTotReg = TotReg + 1
   codigo = rs_especialidad2![id_hospital]
   rs_especialidad2.MoveNext
Loop
rs_especialidad2.Close

consulta_hospital = codigo
End Function

Private Sub Command1_Click()
MsgBox "Registro Grabado", vbInformation, "Oferta Demanda"
End Sub

Private Sub Command3_Click()
Unload Me
End Sub

Private Sub Form_Load()
Dim pp
lbl_nombre.Caption = Trim(nom_establecimiento)
carga_combos
pp = carga_grilla_demanda(0, 0)


End Sub
Function carga_combos()


Dim DBProgramacion As ADODB.Connection
Dim nfil, TotFila, posi As Integer
Set DBProgramacion = New ADODB.Connection
DBProgramacion.ConnectionString = "DSN=programa_odbc"
DBProgramacion.Open


On Error GoTo error_control

Dim rs_od_ce As ADODB.Recordset

Set rs_od_ce = New ADODB.Recordset


rs_od_ce.ActiveConnection = DBProgramacion

If Val(tipo_establecimiento) > 1 Then
    rs_od_ce.Source = "select * from centro_especialidad Where Trim(clave) ='" & Trim(password) & "'"
Else
    rs_od_ce.Source = "select * from centro_especialidad where id_ssalud=" & cod_ss
End If
rs_od_ce.Open

nfil = -1

rs_od_ce.MoveFirst
Do While Not rs_od_ce.EOF
    nfil = nfil + 1
    If cod_establecimiento = rs_od_ce![id_ce] Then
       posi = nfil
    End If
   cbo_ce.AddItem rs_od_ce![desc_ce]
   cbo_ce.ItemData(cbo_ce.NewIndex) = rs_od_ce![id_ce]
   rs_od_ce.MoveNext
   
Loop

If tipo_establecimiento > 1 Then
   cbo_ce.ListIndex = posi
End If

cbo_atencion.AddItem "OTRAS DERIVACIONES"
cbo_atencion.ItemData(cbo_atencion.NewIndex) = 1
cbo_atencion.AddItem "CONTROLES"
cbo_atencion.ItemData(cbo_atencion.NewIndex) = 2
cbo_atencion.AddItem "DERIVACIONES INTERNAS"
cbo_atencion.ItemData(cbo_atencion.NewIndex) = 3
cbo_atencion.AddItem "LISTA ESPERA"
cbo_atencion.ItemData(cbo_atencion.NewIndex) = 4
cbo_atencion.AddItem "OFERTA CONSULTAS"
cbo_atencion.ItemData(cbo_atencion.NewIndex) = 5
cbo_atencion.AddItem "OFERTA PROCEDIMIENTO"
cbo_atencion.ItemData(cbo_atencion.NewIndex) = 6
cbo_atencion.AddItem "OFERTA CIRUGIA AMB."
cbo_atencion.ItemData(cbo_atencion.NewIndex) = 7


rs_od_ce.Close
DBProgramacion.Close


error_control:
If Err.Number = 3021 Then
   MsgBox "No existen Centros de Especialidades asociados al Servicio de Salud", vbInformation, "Alerta"
   cmd_grabar.Enabled = False
End If
End Function

Function carga_especialidad_ce(cod1 As Integer, cod2 As Integer) As Integer
Dim DBProgramacion As ADODB.Connection
Dim rs_especial As ADODB.Recordset

Dim nfil, TotFila, campo_sel As Integer

ReDim Arr_Campos(7)
Arr_Campos(0) = "otras_derivac"
Arr_Campos(1) = "controles"
Arr_Campos(2) = "derivacion_interna"
Arr_Campos(3) = "lista_espera"
Arr_Campos(4) = "oferta_consulta"
Arr_Campos(5) = "oferta_procedimiento"
Arr_Campos(6) = "oferta_cir_amb"

campo_sel = (cbo_atencion.ItemData(cbo_atencion.ListIndex) - 1)

Set DBProgramacion = New ADODB.Connection
DBProgramacion.ConnectionString = "DSN=programa_odbc"
DBProgramacion.Open

Set rs_especial = New ADODB.Recordset
rs_especial.ActiveConnection = DBProgramacion


Kueri = "SELECT  especialidad2.id_especialidad2, "
Kueri = Kueri + "especialidad2.desc_especialidad2, "
Kueri = Kueri + "oferta_demanda_ce." & Arr_Campos(campo_sel) & " AS ninter "
Kueri = Kueri + "FROM especialidad2, oferta_demanda_ce "
Kueri = Kueri + "WHERE "
Kueri = Kueri + "(especialidad2.id_especialidad2 =  oferta_demanda_ce.id_especialidad2) "
Kueri = Kueri + "AND ( oferta_demanda_ce.id_ce = " & cod1 & ") "
'Kueri = Kueri + "AND ( oferta_demanda_ce.id_ssalud = " & cod2 & ") "
Kueri = Kueri + "UNION "
Kueri = Kueri + "SELECT "
Kueri = Kueri + "id_especialidad2, desc_especialidad2, 0 AS ninter "
Kueri = Kueri + "FROM especialidad2 "
Kueri = Kueri + "WHERE (id_especialidad2 NOT IN "
Kueri = Kueri + "(SELECT oferta_demanda_ce.id_especialidad2 "
Kueri = Kueri + "FROM oferta_demanda_ce "
Kueri = Kueri + "WHERE "
Kueri = Kueri + "(oferta_demanda_ce.id_ce = " & cod1 & "))) "
'Kueri = Kueri + "AND ( derivacion_urgencia_hospital.id_hospital = " & cod2 & "))); "
rs_especial.Source = Kueri
rs_especial.Open

rs_especial.MoveFirst
TotFila = rs_especial.RecordCount

Frm_OD_CE.MSFlexGrid1.Rows = TotFilaEsp
Frm_OD_CE.MSFlexGrid1.ColWidth(0) = 500
Frm_OD_CE.MSFlexGrid1.ColWidth(1) = 2300
Frm_OD_CE.MSFlexGrid1.ColWidth(2) = 1000
Frm_OD_CE.MSFlexGrid1.Col = 0
Frm_OD_CE.MSFlexGrid1.Row = 0
Frm_OD_CE.MSFlexGrid1.Text = "ID"
Frm_OD_CE.MSFlexGrid1.Col = 1
Frm_OD_CE.MSFlexGrid1.Row = 0
Frm_OD_CE.MSFlexGrid1.Text = "Especialidad"
Frm_OD_CE.MSFlexGrid1.Col = 2
Frm_OD_CE.MSFlexGrid1.Row = 0
Frm_OD_CE.MSFlexGrid1.Text = "Derivaciones"

Frm_OD_CE.MSFlexGrid1.Col = 0
rs_especial.MoveFirst
nfil = 1
Do While Not rs_especial.EOF
  Frm_OD_CE.MSFlexGrid1.Col = 0
  Frm_OD_CE.MSFlexGrid1.Row = nfil
  Frm_OD_CE.MSFlexGrid1.Text = rs_especial![id_especialidad2]
  Frm_OD_CE.MSFlexGrid1.Col = 1
  Frm_OD_CE.MSFlexGrid1.Row = nfil
  Frm_OD_CE.MSFlexGrid1.Text = rs_especial![desc_especialidad2]
  Frm_OD_CE.MSFlexGrid1.Col = 2
  Frm_OD_CE.MSFlexGrid1.Row = nfil
  Frm_OD_CE.MSFlexGrid1.Text = IIf(IsNull(rs_especial![ninter]), "0", rs_especial![ninter])
  nfil = nfil + 1
  rs_especial.MoveNext
Loop
rs_especial.Close
End Function


Private Sub MSFlexGrid1_KeyPress(KeyAscii As Integer)
Select Case KeyAscii
Case 48 To 57
    If Val(MSFlexGrid1.Text) = 0 Then
    MSFlexGrid1.Text = ""
    End If
    If Len(MSFlexGrid1.Text) < 6 Then
    MSFlexGrid1.Text = MSFlexGrid1.Text & Chr(KeyAscii)
    Else
    MsgBox "El Máximo es 999999", vbInformation, "Ingreso"
    End If
Case 13
        
    If MSFlexGrid1.Col < 2 Then
        
        MSFlexGrid1.Col = MSFlexGrid1.Col + 1
    Else
        MSFlexGrid1.Rows = MSFlexGrid1.Rows + 1
        MSFlexGrid1.Row = MSFlexGrid1.Row + 1
        MSFlexGrid1.Col = 0 'MSFlexGrid1.Col + 1
    End If
Case 8
    If MSFlexGrid1.Text <> "" Then MSFlexGrid1.Text = Mid(MSFlexGrid1.Text, 1, Len(MSFlexGrid1.Text) - 1)
End Select
End Sub
Function carga_grilla_demanda(cod1 As Integer, cod2 As Integer) As Integer
Dim DBProgramacion As ADODB.Connection
Dim rs_especial As ADODB.Recordset

Dim nfil, TotFila As Integer

On Error GoTo error_control

Set DBProgramacion = New ADODB.Connection
DBProgramacion.ConnectionString = "DSN=programa_odbc"
DBProgramacion.Open

Set rs_especial = New ADODB.Recordset
rs_especial.ActiveConnection = DBProgramacion

rs_especial.Source = "Select * from especialidad2 order by id_especialidad2"
rs_especial.Open

rs_especial.MoveFirst
TotFila = rs_especial.RecordCount

Frm_OD_CE.MSFlexGrid2.Rows = TotFilaEsp
Frm_OD_CE.MSFlexGrid2.ColWidth(0) = 500
Frm_OD_CE.MSFlexGrid2.ColWidth(1) = 2000
Frm_OD_CE.MSFlexGrid2.ColWidth(2) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(3) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(4) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(5) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(6) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(7) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(8) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(9) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(10) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(11) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(12) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(13) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(14) = 1000
Frm_OD_CE.MSFlexGrid2.ColWidth(15) = 1000


Frm_OD_CE.MSFlexGrid2.Col = 0
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "ID"
Frm_OD_CE.MSFlexGrid2.Col = 1
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Especialidad"
Frm_OD_CE.MSFlexGrid2.Col = 2
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Cons. Espec."
Frm_OD_CE.MSFlexGrid2.Col = 3
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Procedimiento"
Frm_OD_CE.MSFlexGrid2.Col = 4
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Cirug. Amb."
Frm_OD_CE.MSFlexGrid2.Col = 5
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Derivaciones"
Frm_OD_CE.MSFlexGrid2.Col = 6
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Otras Deriv."
Frm_OD_CE.MSFlexGrid2.Col = 7
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Controles"
Frm_OD_CE.MSFlexGrid2.Col = 8
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Deriv.Int."
Frm_OD_CE.MSFlexGrid2.Col = 9
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Lista Espera"
Frm_OD_CE.MSFlexGrid2.Col = 10
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Total Demanda"
Frm_OD_CE.MSFlexGrid2.Col = 11
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Dmda. Proc."
Frm_OD_CE.MSFlexGrid2.Col = 12
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Dmda. Cir. Amb."
Frm_OD_CE.MSFlexGrid2.Col = 13
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Ofe. Cons."
Frm_OD_CE.MSFlexGrid2.Col = 14
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Ofe. Proc."
Frm_OD_CE.MSFlexGrid2.Col = 15
Frm_OD_CE.MSFlexGrid2.Row = 0
Frm_OD_CE.MSFlexGrid2.Text = "Ofe. Cir. Amb."



Frm_OD_CE.MSFlexGrid2.Col = 0
rs_especial.MoveFirst
nfil = 1
Do While Not rs_especial.EOF
  Frm_OD_CE.MSFlexGrid2.Col = 0
  Frm_OD_CE.MSFlexGrid2.Row = nfil
  Frm_OD_CE.MSFlexGrid2.Text = rs_especial![id_especialidad2]
  Frm_OD_CE.MSFlexGrid2.Col = 1
  Frm_OD_CE.MSFlexGrid2.Row = nfil
  Frm_OD_CE.MSFlexGrid2.Text = rs_especial![desc_especialidad2]
  Frm_OD_CE.MSFlexGrid2.Col = 2
  Frm_OD_CE.MSFlexGrid2.Row = nfil
  Frm_OD_CE.MSFlexGrid2.Text = "0"
  Frm_OD_CE.MSFlexGrid2.Col = 3
  Frm_OD_CE.MSFlexGrid2.Row = nfil
  Frm_OD_CE.MSFlexGrid2.Text = "0"
  Frm_OD_CE.MSFlexGrid2.Col = 6
  Frm_OD_CE.MSFlexGrid2.Row = nfil
  Frm_OD_CE.MSFlexGrid2.Text = "0"
  Frm_OD_CE.MSFlexGrid2.Col = 7
  Frm_OD_CE.MSFlexGrid2.Row = nfil
  Frm_OD_CE.MSFlexGrid2.Text = "0"
  Frm_OD_CE.MSFlexGrid2.Col = 8
  Frm_OD_CE.MSFlexGrid2.Row = nfil
  Frm_OD_CE.MSFlexGrid2.Text = "0"
  Frm_OD_CE.MSFlexGrid2.Col = 9
  Frm_OD_CE.MSFlexGrid2.Row = nfil
  Frm_OD_CE.MSFlexGrid2.Text = "0"
  Frm_OD_CE.MSFlexGrid2.Col = 10
  Frm_OD_CE.MSFlexGrid2.Row = nfil
  Frm_OD_CE.MSFlexGrid2.Text = "0"
  nfil = nfil + 1
  rs_especial.MoveNext
Loop
rs_especial.Close

error_control:
If Err.Number = 3021 Then
    MsgBox "No se han activado Especialidades", vbInformation
End If
End Function
