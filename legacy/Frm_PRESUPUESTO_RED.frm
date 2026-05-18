VERSION 5.00
Object = "{5E9E78A0-531B-11CF-91F6-C2863C385E30}#1.0#0"; "MSFLXGRD.OCX"
Object = "{B23ACFEF-DBD7-42E2-AD46-46124EAA5DA5}#1.0#0"; "acxexcelesc.ocx"
Begin VB.Form Frm_PRESUPUESTO_RED 
   Caption         =   "PRESUPUESTO_RED"
   ClientHeight    =   7020
   ClientLeft      =   1860
   ClientTop       =   630
   ClientWidth     =   8040
   LinkTopic       =   "Form3"
   ScaleHeight     =   7020
   ScaleWidth      =   8040
   Begin AcXExcelEsc.AcXExcel AcXExcel1 
      Height          =   255
      Left            =   3720
      TabIndex        =   11
      Top             =   6600
      Visible         =   0   'False
      Width           =   1935
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
      HelpType        =   1
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
      Height          =   375
      Left            =   120
      TabIndex        =   10
      Top             =   6480
      UseMaskColor    =   -1  'True
      Width           =   1935
   End
   Begin VB.CommandButton cmd_imprimir 
      Caption         =   "Imprimir"
      Height          =   375
      Left            =   2280
      TabIndex        =   9
      Top             =   6480
      UseMaskColor    =   -1  'True
      Width           =   1095
   End
   Begin VB.Frame Frame3 
      BackColor       =   &H80000013&
      Caption         =   "Ingreso"
      BeginProperty Font 
         Name            =   "MS Sans Serif"
         Size            =   8.25
         Charset         =   0
         Weight          =   700
         Underline       =   0   'False
         Italic          =   0   'False
         Strikethrough   =   0   'False
      EndProperty
      ForeColor       =   &H000000C0&
      Height          =   2175
      Left            =   120
      TabIndex        =   4
      Top             =   1560
      Width           =   7695
      Begin MSFlexGridLib.MSFlexGrid MSFlexGrid2 
         Height          =   1815
         Left            =   120
         TabIndex        =   5
         Top             =   240
         Width           =   7335
         _ExtentX        =   12938
         _ExtentY        =   3201
         _Version        =   393216
         Cols            =   4
         BackColor       =   16777215
      End
   End
   Begin VB.Frame Frame1 
      Height          =   735
      Left            =   120
      TabIndex        =   3
      Top             =   720
      Width           =   7695
      Begin VB.Label Label1 
         Alignment       =   2  'Center
         Caption         =   "x"
         BeginProperty Font 
            Name            =   "MS Sans Serif"
            Size            =   8.25
            Charset         =   0
            Weight          =   700
            Underline       =   0   'False
            Italic          =   0   'False
            Strikethrough   =   0   'False
         EndProperty
         Height          =   195
         Left            =   180
         TabIndex        =   8
         Top             =   240
         Width           =   7245
      End
   End
   Begin VB.Frame Frame2 
      BackColor       =   &H80000013&
      Caption         =   "Gastos"
      BeginProperty Font 
         Name            =   "MS Sans Serif"
         Size            =   8.25
         Charset         =   0
         Weight          =   700
         Underline       =   0   'False
         Italic          =   0   'False
         Strikethrough   =   0   'False
      EndProperty
      ForeColor       =   &H00C00000&
      Height          =   2175
      Left            =   120
      TabIndex        =   1
      Top             =   4080
      Width           =   7695
      Begin MSFlexGridLib.MSFlexGrid MSFlexGrid1 
         Height          =   1815
         Left            =   120
         TabIndex        =   2
         Top             =   240
         Width           =   7335
         _ExtentX        =   12938
         _ExtentY        =   3201
         _Version        =   393216
         Cols            =   4
         BackColor       =   16777215
      End
   End
   Begin VB.CommandButton Command3 
      Caption         =   "&volver"
      Height          =   375
      Left            =   6600
      TabIndex        =   0
      Top             =   6480
      Width           =   1215
   End
   Begin VB.Label Label3 
      Alignment       =   2  'Center
      Caption         =   "Presupueto de la Red"
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
      Left            =   240
      TabIndex        =   7
      Top             =   0
      Width           =   7575
   End
   Begin VB.Label lbl_nombre 
      Alignment       =   2  'Center
      Caption         =   "x"
      Height          =   255
      Left            =   120
      TabIndex        =   6
      Top             =   480
      Width           =   7695
   End
End
Attribute VB_Name = "Frm_PRESUPUESTO_RED"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Dim cod2 As Integer
Dim sw As Integer
Dim tot_factor As Double
Dim tot1, tot2, tot3, tot4 As Long
Dim sql1, sql2, sql3 As String
Private Sub cbo_hospital_Click()
Dim pp As Integer
cod_hospital = cbo_hospital.ItemData(cbo_hospital.ListIndex)
limpia_servicio_grilla
Totcons = 0
ncol = 0
sw_ce_hos = 0
pp = Hacer_Query(cod_hospital)
End Sub

Function Hacer_Query(cod1 As Integer) As Integer
cod2 = cod_ss
Conexion
Set rs_derivacion = New ADODB.Recordset
rs_derivacion.ActiveConnection = DBProgramacion
sw = 0
eof0 = 1
eof1 = 1
eof2 = 1
eof3 = 1

sql = ""
sql = "SELECT item_pres.id_item_pres, item_pres.item AS Item, item_pres.descripcion AS Descripcion, SUM(pres_hospital.ejecucion)+pres_ssalud.ejecucion AS Gasto," & _
" SUM(pres_hospital.presupuesto) +pres_ssalud.presupuesto AS Presupuesto From item_pres, pres_hospital, pres_ssalud WHERE item_pres.id_item_pres=pres_hospital.id_item_pres AND" & _
" item_pres.id_item_pres=pres_ssalud.id_item_pres AND pres_hospital.id_ssalud=pres_ssalud.id_ssalud AND pres_hospital.id_ssalud =" & cod2 & _
" GROUP BY item_pres.id_item_pres,  item_pres.item, item_pres.descripcion, pres_ssalud.ejecucion , pres_ssalud.Presupuesto UNION SELECT   item_pres.id_item_pres,  item_pres.item AS Item," & _
" item_pres.descripcion AS Descripcion, 0 AS Gasto, 0 AS Presupuesto From item_pres WHERE     item_pres.id_item_pres NOT IN(SELECT   pres_hospital.id_item_pres" & _
" From pres_hospital WHERE pres_hospital.id_ssalud=" & cod1 & ")" & _
" AND item_pres.id_item_pres NOT IN(SELECT   pres_ssalud.id_item_pres" & _
" From pres_ssalud WHERE   pres_ssalud.id_ssalud=" & cod1 & ")" & _
" UNION SELECT item_pres.id_item_pres, item_pres.item AS Item," & _
" item_pres.descripcion AS Descripcion, pres_ssalud.ejecucion AS" & _
" Gasto,pres_ssalud.presupuesto AS Presupuesto From item_pres, pres_ssalud WHERE item_pres.id_item_pres  NOT IN" & _
" (SELECT pres_hospital.id_item_pres From pres_hospital WHERE pres_hospital.id_ssalud=" & cod1 & ")" & _
" AND item_pres.id_item_pres=pres_ssalud.id_item_pres AND pres_ssalud.id_ssalud =" & cod1 & _
" UNION SELECT item_pres.id_item_pres, item_pres.item AS Item,item_pres.descripcion AS Descripcion, SUM(pres_hospital.ejecucion) AS Gasto,SUM(pres_hospital.Presupuesto)  As Presupuesto" & _
" From item_pres, pres_hospital WHERE item_pres.id_item_pres NOT IN (SELECT pres_ssalud.id_item_pres" & _
" From pres_ssalud WHERE pres_ssalud.id_ssalud=" & cod1 & ")" & _
" AND item_pres.id_item_pres=pres_hospital.id_item_pres AND pres_hospital.id_ssalud =" & cod1 & _
" GROUP BY item_pres.id_item_pres,  item_pres.item, item_pres.descripcion" & _
" ORDER BY item_pres.id_item_pres"

rs_derivacion.Source = sql
rs_derivacion.Open
If rs_derivacion.EOF Then
    MSFlexGrid2.Clear
    MsgBox "No existen Registros", vbInformation
    rs_derivacion.Close
Else
    Carga_Matriz_ing
    Totales_ing
    Carga_Grilla_Ing
    Carga_Matriz_Gasto
    Totales_Gasto
    Carga_Grilla_gasto

End If
End Function

Private Sub Carga_Matriz_Gasto()

Totesp = 0
Do While Not rs_derivacion.EOF
 If rs_derivacion(0) >= 13 Then
    Totesp = Totesp + 1
 End If
 rs_derivacion.MoveNext
Loop
Totcons = 4
nfilas = Totesp + 1

ReDim Miarray_Of(nfilas, (Totcons + 1))
rs_derivacion.MoveFirst

If rs_derivacion.EOF Then
    For f = 1 To Totesp
        Miarray_Of(f, ncol) = 0
        Totintercon = 0
    Next
    Miarray_Of(f, ncol) = Totintercon
    f = 100
    ncol = ncol + 1
    rs_derivacion.Close
Else

rs_derivacion.MoveFirst
Totintercon = 0
ncol = 1

Miarray_Of(0, 0) = "Item"
Miarray_Of(0, 1) = "Descripción"
Miarray_Of(0, 2) = "Gasto"
Miarray_Of(0, 3) = "Presupuesto"
Miarray_Of(nfilas, 1) = "TOTAL"



Do While Not rs_derivacion.EOF

If rs_derivacion(0) >= 13 Then
For f = 1 To nfilas
        Miarray_Of(f, 0) = rs_derivacion![Item]
        Miarray_Of(f, 1) = rs_derivacion![descripcion]
        Miarray_Of(f, 2) = rs_derivacion![gasto]
        Miarray_Of(f, 3) = rs_derivacion![presupuesto]
      
        rs_derivacion.MoveNext
            
    'Carga Totales

    If rs_derivacion.EOF Then

    TotxFila = 0
    f = nfilas - 1

        For c = 2 To Totcons
            For i = 1 To f

                TotxFila = TotxFila + Miarray_Of(i, c)

           Next
           TotxFila = 0
        Next

        f = 100
        ncol = ncol + 1
    End If
Next
Else
rs_derivacion.MoveNext
End If
Loop
End If

End Sub
Private Sub Totales_Gasto()
 TotxFila = 0
 nfilas = Totesp + 1
 Totcons = 4
    f = nfilas - 1

        For c = 2 To Totcons
            For i = 1 To f
                tot_factor = tot_factor + Miarray_Of(i, c)
           Next
           Miarray_Of(i, c) = tot_factor
           tot_factor = 0
        Next

        f = 100
        ncol = ncol + 1
  

End Sub

Private Sub Carga_Grilla_gasto()
    Dim i, c As Integer
    Totesp = nfilas - 1
    Totcons = 3
    MSFlexGrid1.Rows = nfilas + 1
    MSFlexGrid1.Cols = Totcons + 1
    Frm_PRESUPUESTO_RED.MSFlexGrid1.Col = 1
    Frm_PRESUPUESTO_RED.MSFlexGrid1.ColWidth(0) = 500
    Frm_PRESUPUESTO_RED.MSFlexGrid1.ColWidth(1) = 3500
    Frm_PRESUPUESTO_RED.MSFlexGrid1.ColWidth(2) = 1500
    Frm_PRESUPUESTO_RED.MSFlexGrid1.ColWidth(3) = 1500
    
    For i = 0 To nfilas
    MSFlexGrid1.Row = i

        For c = 0 To Totcons
            If i = 1 Then
                Frm_PRESUPUESTO_RED.MSFlexGrid1.CellBackColor = &H80000004
            End If
            MSFlexGrid1.Col = c
            Frm_PRESUPUESTO_RED.MSFlexGrid1.Text = Format(Miarray_Of(i, c), "#,###,###,##0")
            If Miarray_Of(i, c) < 0 Then
                Frm_PRESUPUESTO_RED.MSFlexGrid1.CellForeColor = &HC00000
                Frm_PRESUPUESTO_RED.MSFlexGrid1.CellBackColor = &HE0E0E0
            End If

            If i = Totesp + 1 Then
            Frm_PRESUPUESTO_RED.MSFlexGrid1.CellBackColor = &HE0E0E0

            End If
       Next
    Next


End Sub

Private Sub Totales_ing()
 TotxFila = 0
 nfilas = Totesp + 1
 Totcons = 4
    f = nfilas - 1

        For c = 2 To Totcons
            For i = 1 To f

                tot_factor = tot_factor + Miarray(i, c)

           Next
           'Miarray(i, c) = Format(tot_factor, "#####0.000")
           Miarray(i, c) = tot_factor
           tot_factor = 0
        Next

        f = 100
        
    

End Sub
Private Sub total_por_fila()
Miarray(0, 7) = "Total"
For f = 2 To nfilas
    TotxFila = 0
    For c = 2 To 6
        TotxFila = TotxFila + Miarray(f, c)

    Next
    Miarray(f, 7) = TotxFila
   
Next
End Sub


Private Sub Carga_Matriz_ing()

Totesp = 0
Do While Not rs_derivacion.EOF
 If rs_derivacion(0) <= 12 Then
    Totesp = Totesp + 1
 End If
 rs_derivacion.MoveNext
Loop
Totcons = 4
nfilas = Totesp + 1
If sw = 0 Then
ReDim Miarray(nfilas, (Totcons + 1))
Miarray(0, 0) = "Item"
Miarray(0, 1) = "Descripción"
Miarray(0, 2) = "Gasto"
Miarray(0, 3) = "Presupuesto"
Miarray(0, 4) = "Total"
Miarray(nfilas, 1) = "TOTAL"
End If
rs_derivacion.MoveFirst



If rs_derivacion.EOF Then
    For f = 1 To Totesp
        Miarray(f, ncol) = 0
        Totintercon = 0
    Next
    Miarray(f, ncol) = Totintercon
    f = 100
    ncol = ncol + 1
    rs_derivacion.Close
Else

rs_derivacion.MoveFirst

For f = 1 To nfilas
If rs_derivacion(0) <= 12 Then

        Miarray(f, 0) = rs_derivacion![Item]
        Miarray(f, 1) = rs_derivacion![descripcion]
        Miarray(f, 2) = rs_derivacion![gasto]
        Miarray(f, 3) = rs_derivacion![presupuesto]
        rs_derivacion.MoveNext



    If rs_derivacion.EOF Then

    TotxFila = 0
    f = nfilas - 1

        For c = 2 To Totcons
            For i = 1 To f

                TotxFila = TotxFila + Miarray(i, c)

           Next
           Miarray(i, c) = TotxFila
           TotxFila = 0
        Next

        f = 100
        ncol = ncol + 1
    End If
End If
Next
End If

End Sub

Private Sub Carga_Grilla_Ing()
    Dim i, c As Integer
    Totesp = nfilas - 1
    Totcons = 3
    MSFlexGrid2.Rows = nfilas + 1
    MSFlexGrid2.Cols = Totcons + 1
    Frm_PRESUPUESTO_RED.MSFlexGrid2.Col = 1
    Frm_PRESUPUESTO_RED.MSFlexGrid2.ColWidth(0) = 500
    Frm_PRESUPUESTO_RED.MSFlexGrid2.ColWidth(1) = 3500
    Frm_PRESUPUESTO_RED.MSFlexGrid2.ColWidth(2) = 1500
    Frm_PRESUPUESTO_RED.MSFlexGrid2.ColWidth(3) = 1500
    For i = 0 To nfilas
    MSFlexGrid2.Row = i

        For c = 0 To Totcons
            If i = 1 Then
                Frm_PRESUPUESTO_RED.MSFlexGrid2.CellBackColor = &H80000004
            End If
            MSFlexGrid2.Col = c
            Frm_PRESUPUESTO_RED.MSFlexGrid2.Text = Format(Miarray(i, c), "#,###,###,##0")
            If Miarray(i, c) < 0 Then
                Frm_PRESUPUESTO_RED.MSFlexGrid2.CellForeColor = &HC00000
                Frm_PRESUPUESTO_RED.MSFlexGrid2.CellBackColor = &HE0E0E0
            End If

            If i = Totesp + 1 Then
            Frm_PRESUPUESTO_RED.MSFlexGrid2.CellBackColor = &HE0E0E0

            End If
       Next
    Next


End Sub


Private Sub carga_dias_cama()
Dim rs_dias_cama As ADODB.Recordset
Set rs_dias_cama = New ADODB.Recordset

rs_dias_cama.ActiveConnection = DBProgramacion
rs_dias_cama.Source = "select * from hospital where id_ssalud = " & cod_ss & " and id_hospital = " & cod_hospital
rs_dias_cama.Open

nTotReg = 0

rs_dias_cama.MoveFirst

Txt_dcuci.Text = rs_dias_cama![Dias_Cama_UCI]
Txt_dcuti.Text = rs_dias_cama![Dias_Cama_UTI]

rs_dias_cama.Close
End Sub
Private Sub cbo_sclinico_Click()
cbo_demanda.Enabled = True
End Sub

Private Sub cmd_grabar_Click()
Dim nfil, TotFila, nExiste, campo_sel As Integer
Dim cod_especialidad
Dim rs_oferta_hosp As ADODB.Recordset

Dim valor As Double
Dim cod_sc As Integer


ReDim Arr_Campos(4)
Arr_Campos(0) = "dias_cama_sala"
Arr_Campos(1) = "hrs_pabellon"
Arr_Campos(2) = "num_proc"
Arr_Campos(3) = "hrs_medicas_contratadas"

campo_sel = (cbo_demanda.ItemData(cbo_demanda.ListIndex) - 1)

'PREPARA EL RECORDSET PARA GRABAR NUEVO REG
Set rs_oferta_hosp = New ADODB.Recordset
rs_oferta_hosp.CursorType = adOpenKeyset
rs_oferta_hosp.LockType = adLockOptimistic
rs_oferta_hosp.Open "oferta_hospital", DBProgramacion, , , adCmdTable

'ACTUALIZA LOS COEFICIENTES EN LA TABLA HOSPITAL
Sql_update = "update hospital set dias_cama_uci = " & Val(Txt_dcuci.Text) & ", dias_cama_uti = " & Val(Txt_dcuti.Text) & " where id_hospital = " & cod_hospital & " AND id_ssalud = " & cod_ss
DBProgramacion.Execute Sql_update
        
For i = 1 To TotServicio - 1
MSFlexGrid1.Row = i
MSFlexGrid1.Col = 2
    MSFlexGrid1.Col = 0
    cod_especialidad = MSFlexGrid1.Text
    nExiste = consulta_sc(MSFlexGrid1.Text, Val(cbo_hospital.ItemData(cbo_hospital.ListIndex)), Val(cod_ss))
   If nExiste = 0 Then
      rs_oferta_hosp.AddNew
      rs_oferta_hosp!id_hospital = cbo_hospital.ItemData(cbo_hospital.ListIndex)
      rs_oferta_hosp!id_ssalud = cod_ss
      MSFlexGrid1.Col = 0
      rs_oferta_hosp!id_servicio_clinico = Val(MSFlexGrid1.Text)
      MSFlexGrid1.Col = 2
      rs_oferta_hosp!dias_cama_sala = IIf(cbo_demanda.ItemData(cbo_demanda.ListIndex) = 1, Val(MSFlexGrid1.Text), 0)
      rs_oferta_hosp!Hrs_Pabellon = IIf(cbo_demanda.ItemData(cbo_demanda.ListIndex) = 2, Val(MSFlexGrid1.Text), 0)
      rs_oferta_hosp!num_proc = IIf(cbo_demanda.ItemData(cbo_demanda.ListIndex) = 3, Val(MSFlexGrid1.Text), 0)
      rs_oferta_hosp!hrs_medicas_contratadas = IIf(cbo_demanda.ItemData(cbo_demanda.ListIndex) = 4, Val(MSFlexGrid1.Text), 0)
      rs_oferta_hosp.Update
    Else
        MSFlexGrid1.Col = 0
        cod_sc = MSFlexGrid1.Text
        MSFlexGrid1.Col = 2
        valor = MSFlexGrid1.Text ' Format(MSFlexGrid1.Text, "###0.00")
        
        Sql_update = "update oferta_hospital set " & Arr_Campos(campo_sel) & " = " & Val(valor) & " where id_servicio_clinico = " & Val(cod_sc) & " and id_hospital = " & cod_hospital & " AND id_ssalud = " & cod_ss
        DBProgramacion.Execute Sql_update

End If

Next
rs_oferta_hosp.Close

MsgBox "Registro Guardado"
Totcons = 0

End Sub

Function consulta_sc(grilla_valor As String, cod2 As Integer, cod3 As Integer) As Integer
Dim rs_of As ADODB.Recordset
On Error GoTo error_control
Conexion
Set rs_of = New ADODB.Recordset
rs_of.ActiveConnection = DBProgramacion
rs_of.Source = "select * from oferta_hospital where id_servicio_clinico = " & Val(grilla_valor) & " and id_ssalud = " & cod_ss & " and id_hospital = " & cod_hospital
rs_of.Open

nTotReg = 0

rs_of.MoveFirst
Do While Not rs_of.EOF
   nTotReg = TotReg + 1
   rs_of.MoveNext
Loop

error_control:
If (Err.Number = 3021) Then
    consulta_sc = 0
    Else
    consulta_sc = nTotReg
End If
End Function



Private Sub Command3_Click()
Unload Me
End Sub

Private Sub Form_Load()
Dim pp
lbl_nombre.Caption = Trim(nom_establecimiento)
carga_combos

End Sub
Function carga_combos()

Conexion
Dim pp As Integer
Dim rs_hospital As ADODB.Recordset

On Error GoTo error_control

Set rs_hospital = New ADODB.Recordset


rs_hospital.ActiveConnection = DBProgramacion


If Val(tipo_establecimiento) < 2 Then
   rs_hospital.Source = "select * from ssalud where id_ssalud=" & cod_ss
End If
 
rs_hospital.Open


nfil = -1

rs_hospital.MoveFirst
Label1.Caption = rs_hospital![desc_ssalud]

'cod_hospital = cbo_hospital.ItemData(cbo_hospital.ListIndex)
limpia_servicio_grilla
Totcons = 0
ncol = 0
sw_ce_hos = 0
pp = Hacer_Query(cod_ss)
'Do While Not rs_hospital.EOF
'    nfil = nfil + 1
'    If cod_establecimiento = rs_hospital![id_ssalud] Then
'       posi = nfil
'    End If
'   cbo_hospital.AddItem rs_hospital![desc_ssalud]
'   cbo_hospital.ItemData(cbo_hospital.NewIndex) = rs_hospital![id_ssalud]
'   rs_hospital.MoveNext
'
'Loop

'If tipo_establecimiento > 0 Then
'    cbo_hospital.ListIndex = posi
'End If
rs_hospital.Close


error_control:

If Err.Number = 3021 Then
MsgBox "No existen datos", vbInformation, "sin datos"
End If

End Function
Private Sub carga_sc()
Dim rs_sc As ADODB.Recordset
Set rs_sc = New ADODB.Recordset

Conexion
On Error GoTo error_control

rs_sc.ActiveConnection = DBProgramacion

rs_sc.Source = "select * from servicio_clinico Where id_hospital =" & cod_hospital
rs_sc.Open
cbo_sclinico.Clear
rs_sc.MoveFirst
Do While Not rs_sc.EOF
    cbo_sclinico.AddItem rs_sc![desc_servicio_clinico]
    cbo_sclinico.ItemData(cbo_sclinico.NewIndex) = rs_sc![id_servicio_clinico]
    rs_sc.MoveNext
   
Loop
rs_sc.Close
error_control:
If Err.Number = 3021 Then
   MsgBox "No existen servicios clínicos asociados", vbInformation, "Sin Datos"
   Else
   cbo_sclinico.Enabled = True
End If
End Sub



Public Sub limpia_servicio_grilla()
MSFlexGrid2.Clear

End Sub

Private Sub Txt_dcuci_KeyPress(KeyAscii As Integer)
Select Case KeyAscii
Case 48 To 57
     If Len(Txt_dcuci.Text) > 6 Then
    MsgBox "El Máximo es 999.999", vbInformation, "Ingreso"
    End If
Case Else
     MsgBox "Debe ingresar sólo número enteros", vbInformation, "Números"
     Txt_dcuci.SetFocus
   
  End Select

End Sub

Private Sub Txt_dcuti_KeyPress(KeyAscii As Integer)
Dim nint As Integer
nint = 0
Select Case KeyAscii
Case 48 To 57
     If Len(Txt_dcuti.Text) > 6 Then
    MsgBox "El Máximo es 999.999", vbInformation, "Ingreso"
    End If
Case Else
     nint = 1
     MsgBox "Debe ingresar sólo número enteros", vbInformation, "Números"
      Txt_dcuti.SetFocus
End Select

End Sub

Private Sub Txt_dcuti_LostFocus()
If nint = 1 Then
   Txt_dcuti.Text = ""
End If
End Sub

Private Sub cmd_exportar_Click()
exportar_ing
exportar_gasto
End Sub
Private Sub exportar_ing()
Dim j, i, sub_col As Integer
Dim FilaExcelGrilla As String
 
  Nom_Form = RTrim(Frm_PRESUPUESTO_RED.Caption) & "_INGRESOS" & ".xls"
  AcXExcel1.TotalFilas = MSFlexGrid2.Rows
  AcXExcel1.Hoja = "Hoja1"
  AcXExcel1.Listoco = False
  AcXExcel1.archivo = "c:\desa\programacion\excel\" & Nom_Form
  AcXExcel1.Inicializa
  nfilas = MSFlexGrid2.Rows - 1
  Totcons = MSFlexGrid2.Cols - 1
  For j = 0 To nfilas - 1
  MSFlexGrid2.Row = j

       For i = 0 To (Totcons)
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
  MsgBox "Se han exportados los datos al siguiente archivo : " & vbNewLine & _
         AcXExcel1.archivo, vbExclamation
  
End Sub

Private Sub exportar_gasto()
Dim j, i, sub_col As Integer
Dim FilaExcelGrilla As String


  
  Nom_Form = RTrim(Frm_PRESUPUESTO_RED.Caption) & "_GASTO" & ".xls"
  AcXExcel1.TotalFilas = MSFlexGrid1.Rows
  AcXExcel1.Hoja = "Hoja1"
  AcXExcel1.Listoco = False
  AcXExcel1.archivo = "c:\desa\programacion\excel\" & Nom_Form
  AcXExcel1.Inicializa
  nfilas = MSFlexGrid1.Rows - 1
  Totcons = MSFlexGrid1.Cols - 1
  For j = 0 To nfilas - 1
  MSFlexGrid1.Row = j

       For i = 0 To (Totcons)
       MSFlexGrid1.Col = i
           If j = 0 Then
              FilaExcelGrilla = FilaExcelGrilla & "·c" & MSFlexGrid1.Text
           Else
              If i = 1 Then
                 FilaExcelGrilla = FilaExcelGrilla & "·c" & MSFlexGrid1.Text
              Else
                 FilaExcelGrilla = FilaExcelGrilla & "·n" & Val(Replace(MSFlexGrid1.Text, ".", ""))
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
  MsgBox "Se han exportados los datos al siguiente archivo : " & vbNewLine & _
         AcXExcel1.archivo, vbExclamation
  
End Sub

Private Sub cmd_imprimir_Click()
Dim aux As String
Dim rs_imprimir As ADODB.Recordset
Dim nfil, TotFila As Integer
Conexion


'ELIMINA LOS REGISTROS ANTES DE GRABAR
DBProgramacion.Execute "delete from imprime_datos"

'PREPARA EL RECORDSET PARA GRABAR
Set rs_imprimir = New ADODB.Recordset
rs_imprimir.CursorType = adOpenKeyset
rs_imprimir.LockType = adLockOptimistic
rs_imprimir.Open "imprime_datos", DBProgramacion, , , adCmdTable
nfilas = MSFlexGrid2.Rows
Totcons = MSFlexGrid2.Cols

' Imprime tabla de valorización
For i = 1 To nfilas - 1
MSFlexGrid2.Row = i
MSFlexGrid2.Col = 0
   rs_imprimir.AddNew
   aux = ""
   aux = MSFlexGrid2.Text

   MSFlexGrid2.Col = 1
   If aux <> "" Then
   rs_imprimir!descripcion = aux & "   " & MSFlexGrid2.Text
   Else
   rs_imprimir!descripcion = "       " & MSFlexGrid2.Text
   End If
   
   MSFlexGrid2.Col = 2
   rs_imprimir!c1 = Val(Replace(MSFlexGrid2.Text, ".", ""))
   MSFlexGrid2.Col = 3
   rs_imprimir!c2 = Val(Replace(MSFlexGrid2.Text, ".", ""))
   rs_imprimir.Update

Next
rs_imprimir.Close
Set rs_imprimir = New ADODB.Recordset
rs_imprimir.ActiveConnection = DBProgramacion
sql = "select * from imprime_datos"
rs_imprimir.Source = sql
rs_imprimir.Open

DataEnvironment1.plan
DataReport4.Sections("Sección4").Controls("etiqueta1").Caption = "PRESUPUESTO DE LA RED INGRESOS"
DataReport4.Show 1
DataEnvironment1.Connection1.Close
DataEnvironment1.Connection1.Open
rs_imprimir.Close

'Imprime Presupuesto Hospital

'ELIMINA LOS REGISTROS ANTES DE GRABAR
DBProgramacion.Execute "delete from imprime_datos"

'PREPARA EL RECORDSET PARA GRABAR
Set rs_imprimir = New ADODB.Recordset
rs_imprimir.CursorType = adOpenKeyset
rs_imprimir.LockType = adLockOptimistic
rs_imprimir.Open "imprime_datos", DBProgramacion, , , adCmdTable
nfilas = MSFlexGrid1.Rows
Totcons = MSFlexGrid1.Cols

For i = 1 To nfilas - 1
MSFlexGrid1.Row = i
MSFlexGrid1.Col = 0
   rs_imprimir.AddNew
   aux = ""
   aux = MSFlexGrid1.Text

   MSFlexGrid1.Col = 1
   If aux <> "" Then
    rs_imprimir!descripcion = aux & "   " & MSFlexGrid1.Text
   Else
    rs_imprimir!descripcion = "       " & MSFlexGrid1.Text
   End If
   MSFlexGrid1.Col = 2
   rs_imprimir!c1 = Val(Replace(MSFlexGrid1.Text, ".", ""))
   MSFlexGrid1.Col = 3
   rs_imprimir!c2 = Val(Replace(MSFlexGrid1.Text, ".", ""))
   rs_imprimir.Update

Next
rs_imprimir.Close
Set rs_imprimir = New ADODB.Recordset
rs_imprimir.ActiveConnection = DBProgramacion
sql = "select * from imprime_datos"
rs_imprimir.Source = sql
rs_imprimir.Open

DataEnvironment1.plan
DataReport5.Sections("Sección4").Controls("etiqueta1").Caption = "PRESUPUESTO DE LA RED GASTOS"
DataReport5.Show 1
DataEnvironment1.Connection1.Close
DataEnvironment1.Connection1.Open
rs_imprimir.Close
End Sub








