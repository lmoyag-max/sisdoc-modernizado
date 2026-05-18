VERSION 5.00
Object = "{5E9E78A0-531B-11CF-91F6-C2863C385E30}#1.0#0"; "MSFLXGRD.OCX"
Object = "{B23ACFEF-DBD7-42E2-AD46-46124EAA5DA5}#1.0#0"; "acxexcelesc.ocx"
Begin VB.Form Frm_PLAN_OFERTA_DMDA_UCI_UTI 
   Caption         =   "PLAN_OFERTA_DMDA_UCI_UTI"
   ClientHeight    =   5640
   ClientLeft      =   60
   ClientTop       =   345
   ClientWidth     =   7755
   LinkTopic       =   "Form3"
   ScaleHeight     =   5640
   ScaleWidth      =   7755
   StartUpPosition =   3  'Windows Default
   Begin AcXExcelEsc.AcXExcel AcXExcel1 
      Height          =   255
      Left            =   4080
      TabIndex        =   10
      Top             =   5280
      Visible         =   0   'False
      Width           =   975
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
   Begin VB.Frame Frame1 
      Height          =   975
      Left            =   360
      TabIndex        =   5
      Top             =   840
      Width           =   6975
      Begin VB.ComboBox cbo_hospital 
         Height          =   315
         Left            =   1320
         TabIndex        =   6
         Top             =   480
         Width           =   3615
      End
      Begin VB.Label Label2 
         Caption         =   "Seleccione  Hospital"
         Height          =   255
         Left            =   1320
         TabIndex        =   7
         Top             =   240
         Width           =   2775
      End
   End
   Begin VB.CommandButton Command3 
      Caption         =   "&volver"
      Height          =   375
      Left            =   6120
      TabIndex        =   4
      Top             =   5160
      Width           =   1215
   End
   Begin VB.Frame Frame3 
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
      Height          =   3135
      Left            =   360
      TabIndex        =   2
      Top             =   1920
      Width           =   6975
      Begin MSFlexGridLib.MSFlexGrid MSFlexGrid2 
         Height          =   2535
         Left            =   120
         TabIndex        =   3
         Top             =   480
         Width           =   6735
         _ExtentX        =   11880
         _ExtentY        =   4471
         _Version        =   393216
         Cols            =   4
         BackColor       =   16777215
      End
   End
   Begin VB.CommandButton cmd_exportar 
      Caption         =   "Exportar a Excel"
      Enabled         =   0   'False
      Height          =   375
      Left            =   360
      TabIndex        =   1
      Top             =   5160
      Width           =   1935
   End
   Begin VB.CommandButton cmd_imprimir 
      Caption         =   "Imprimir"
      Enabled         =   0   'False
      Height          =   375
      Left            =   2520
      TabIndex        =   0
      Top             =   5160
      Width           =   1095
   End
   Begin VB.Label lbl_nombre 
      Alignment       =   2  'Center
      Caption         =   "x"
      Height          =   255
      Left            =   600
      TabIndex        =   9
      Top             =   480
      Width           =   6495
   End
   Begin VB.Label Label3 
      Alignment       =   2  'Center
      Caption         =   "Plan Oferta Demanda UCI - UTI"
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
      Left            =   720
      TabIndex        =   8
      Top             =   0
      Width           =   6375
   End
End
Attribute VB_Name = "Frm_PLAN_OFERTA_DMDA_UCI_UTI"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Dim cod2 As Integer
Dim sw As Integer
Dim tot_factor As Double
Dim sql1, sql2, sql3 As String
Dim Nom_Form As String
Dim resumen()


Private Sub cbo_hospital_Click()
Dim pp As Integer
cod_hospital = cbo_hospital.ItemData(cbo_hospital.ListIndex)
limpia_servicio_grilla
cmd_exportar.Enabled = True
cmd_imprimir.Enabled = True
Totcons = 0
ncol = 0
sw_ce_hos = 0
pp = Hacer_Query(cod_hospital)
End Sub
Private Sub cbo_demanda_Click()
cod_hospital = cbo_hospital.ItemData(cbo_hospital.ListIndex)
TotServicio = cuenta_servicio_clinico(cod_hospital, cod_ss)
TotServicio = TotServicio + 1
If TotServicio > 1 Then
   Txt_dcuci.Enabled = True
   Txt_dcuti.Enabled = True
   cmd_grabar.Enabled = True
   Else
   Txt_dcuci.Enabled = False
   Txt_dcuti.Enabled = False
   cmd_grabar.Enabled = False
End If
cod_hospital = cbo_hospital.ItemData(cbo_hospital.ListIndex)
Totcons = 0
ncol = 0
sw_ce_hos = 0
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

sql = "SELECT [servicio_clinico].[id_servicio_clinico] AS Nº, [servicio_clinico].[desc_servicio_clinico] AS Servicio_Clinico," & _
" SUM([derivacion_ce_hospital].[num_atencion]) AS Centro_Esp" & _
" From servicio_clinico, servicio_clinico_especialidad, derivacion_ce_hospital" & _
" Where [servicio_clinico].[id_servicio_clinico] = [servicio_clinico_especialidad].[id_servicio_clinico]" & _
" And [servicio_clinico_especialidad].[id_especialidad2] = [derivacion_ce_hospital].[id_especialidad2]" & _
" And ([derivacion_ce_hospital].[id_hospital] = [servicio_clinico].[id_hospital])" & _
" And [servicio_clinico].[id_hospital]=" & cod1 & _
" And [servicio_clinico].[id_ssalud]=" & cod2 & _
" and servicio_clinico_especialidad.id_hospital=" & cod1 & _
" AND servicio_clinico_especialidad.id_ssalud =" & cod2 & _
" GROUP BY [servicio_clinico].[id_servicio_clinico], [servicio_clinico].[desc_servicio_clinico]" & _
" UNION SELECT id_servicio_clinico as Nº, desc_servicio_clinico as Servicio_Clinico, 0 AS  Centro_Esp" & _
" From servicio_clinico" & _
" WHERE (servicio_clinico.id_servicio_clinico NOT IN" & _
" (SELECT   servicio_clinico_especialidad.id_servicio_clinico" & _
" From derivacion_ce_hospital, servicio_clinico_especialidad" & _
" Where servicio_clinico_especialidad.id_especialidad2 = derivacion_ce_hospital.id_especialidad2" & _
" And servicio_clinico_especialidad.id_hospital =" & cod1 & _
" And servicio_clinico_especialidad.id_ssalud =" & cod2 & _
" AND derivacion_ce_hospital.id_hospital=" & cod1 & "))" & _
" AND servicio_clinico.id_hospital=" & cod1 & _
" AND servicio_clinico.id_ssalud=" & cod2 & _
" ORDER BY Nº"
rs_derivacion.Source = sql
rs_derivacion.Open


If rs_derivacion.EOF Then
     eof0 = 0
    rs_derivacion.Close
Else
    sw = 0
    Carga_Matriz
    rs_derivacion.Close
End If
sql1 = ""
sql1 = ""
sql1 = "SELECT [servicio_clinico].[id_servicio_clinico] AS Nº," & _
" [servicio_clinico].[desc_servicio_clinico] AS Servicio_Clinico," & _
" SUM(derivacion_urgencia_hospital.num_atencion) As Urgencia" & _
" From servicio_clinico, servicio_clinico_especialidad, derivacion_urgencia_hospital" & _
" Where servicio_clinico.id_servicio_clinico = servicio_clinico_especialidad.id_servicio_clinico" & _
" And servicio_clinico_especialidad.id_especialidad2 = derivacion_urgencia_hospital.id_especialidad2" & _
" And derivacion_urgencia_hospital.id_hospital = servicio_clinico.id_hospital" & _
" And servicio_clinico.id_hospital=" & cod1 & _
" And servicio_clinico.id_ssalud=" & cod2 & _
" and servicio_clinico_especialidad.id_hospital=" & cod1 & _
" AND servicio_clinico_especialidad.id_ssalud=" & cod2 & _
" GROUP BY [servicio_clinico].[id_servicio_clinico], [servicio_clinico].[desc_servicio_clinico]" & _
" UNION SELECT     id_servicio_clinico as Nº, desc_servicio_clinico as Servicio_Clinico, 0 AS  Urgencia" & _
" From servicio_clinico" & _
" WHERE     (servicio_clinico.id_servicio_clinico NOT IN" & _
" (SELECT   servicio_clinico_especialidad.id_servicio_clinico" & _
" From derivacion_urgencia_hospital, servicio_clinico_especialidad" & _
" Where servicio_clinico_especialidad.id_especialidad2 = derivacion_urgencia_hospital.id_especialidad2" & _
" AND servicio_clinico_especialidad.id_hospital=" & cod1 & _
" AND servicio_clinico_especialidad.id_ssalud=" & cod2 & _
" AND derivacion_urgencia_hospital.id_hospital=" & cod1 & "))" & _
" AND servicio_clinico.id_hospital=" & cod1 & _
" AND servicio_clinico.id_ssalud=" & cod2 & _
" ORDER BY Nº"


rs_derivacion.Source = sql1
rs_derivacion.Open
If rs_derivacion.EOF Then
       eof1 = 0
    rs_derivacion.Close
Else
    sw = 1
    Carga_Matriz
    rs_derivacion.Close
End If
sql2 = "SELECT [servicio_clinico].[id_servicio_clinico] AS Nº,[servicio_clinico].[desc_servicio_clinico] AS Servicio_Clinico," & _
 " SUM(derivacion_hospital_hospital.num_paciente) As Otros_Hosp From servicio_clinico, servicio_clinico_especialidad, derivacion_hospital_hospital" & _
" Where [servicio_clinico].[id_servicio_clinico] = [servicio_clinico_especialidad].[id_servicio_clinico]" & _
" AND [servicio_clinico_especialidad].[id_especialidad2]= derivacion_hospital_hospital.[id_especialidad2]" & _
" AND  derivacion_hospital_hospital.id_hospital2=servicio_clinico.id_hospital" & _
" AND derivacion_hospital_hospital.id_hospital<>servicio_clinico.id_hospital" & _
" AND servicio_clinico.id_hospital=" & cod1 & _
" AND servicio_clinico.id_ssalud=" & cod2 & _
" and servicio_clinico_especialidad.id_hospital=" & cod1 & _
" AND servicio_clinico_especialidad.id_ssalud=" & cod2 & _
" GROUP BY [servicio_clinico].[id_servicio_clinico], [servicio_clinico].[desc_servicio_clinico]" & _
" UNION SELECT     id_servicio_clinico as Nº, desc_servicio_clinico as Servicio_Clinico, 0 AS  Otros_Hosp" & _
" From servicio_clinico" & _
" WHERE     (servicio_clinico.id_servicio_clinico NOT IN" & _
 "    (SELECT   servicio_clinico_especialidad.id_servicio_clinico" & _
 "     From derivacion_hospital_hospital, servicio_clinico_especialidad" & _
 "      Where servicio_clinico_especialidad.id_especialidad2 = derivacion_hospital_hospital.id_especialidad2" & _
" AND servicio_clinico_especialidad.id_hospital=" & cod1 & _
"  AND servicio_clinico_especialidad.id_ssalud=" & cod2 & _
"  AND  derivacion_hospital_hospital.id_hospital2=" & cod1 & _
 " AND derivacion_hospital_hospital.id_hospital<>" & cod1 & "))" & _
" AND servicio_clinico.id_hospital=" & cod1 & _
" AND servicio_clinico.id_ssalud=" & cod2 & _
" oRDER BY Nº"
rs_derivacion.Source = sql2
rs_derivacion.Open
If rs_derivacion.EOF Then
   eof2 = 0
    rs_derivacion.Close
Else
    sw = 2
    Carga_Matriz
    rs_derivacion.Close
End If
sql3 = ""
sql3 = "SELECT  servicio_clinico.id_servicio_clinico as Nº, servicio_clinico.desc_servicio_clinico as Servicio_Clinico," & _
" SUM(demanda_hospital.derivacion_otro_ss) AS Otros_SS," & _
" SUM(demanda_hospital.Lista_Espera) As Lista_Espera" & _
" From servicio_clinico, demanda_hospital" & _
" Where servicio_clinico.id_servicio_clinico = demanda_hospital.id_servicio_clinico" & _
" AND demanda_hospital.id_hospital =" & cod1 & _
" AND  demanda_hospital.id_ssalud =" & cod2 & _
" AND  servicio_clinico.id_hospital=" & cod1 & _
" AND servicio_clinico.id_ssalud =" & cod2 & _
" GROUP BY servicio_clinico.id_servicio_clinico, servicio_clinico.desc_servicio_clinico" & _
" UNION SELECT    id_servicio_clinico AS Nº, desc_servicio_clinico AS Servicio_Clinico, 0 AS  Otros_SS, 0 AS Lista_Espera" & _
" From servicio_clinico" & _
" WHERE     id_servicio_clinico NOT IN" & _
"   (SELECT    demanda_hospital.id_servicio_clinico" & _
"    From demanda_hospital" & _
"    Where demanda_hospital.id_hospital =" & cod1 & _
" AND demanda_hospital.id_ssalud =" & cod2 & ")" & _
" AND servicio_clinico.id_hospital=" & cod1 & _
" AND servicio_clinico.id_ssalud=" & cod2 & _
" ORDER BY Nº"
rs_derivacion.Source = sql3
rs_derivacion.Open
If rs_derivacion.EOF Then
     eof3 = 0
    rs_derivacion.Close
Else
    sw = 3
    Carga_Matriz
    rs_derivacion.Close
End If
If eof0 = 1 Or eof1 = 1 Or eof2 = 1 Or eof3 = 1 Then
    Totcons = 11
    Totesp = 5
    pp = Sacar_Factores(cod1, cod2)
    nfilas = Totesp
    Carga_Factores
    Totales
Else
    MSFlexGrid2.Clear
    MsgBox "No Existen Registros", vbInformation
    Exit Function
End If

sql = "SELECT hospital.dias_cama_uci AS Dias_Cama_UCI, [Dias_Cama_UCI]*[valor_factor].[factor10] AS Hrs_Med_UCI," & _
" hospital.dias_cama_uti AS Dias_Cama_UTI, [Dias_Cama_UTI]*[valor_factor].[factor9] AS Hrs_Med_UTI" & _
" From hospital, valor_factor" & _
" WHERE valor_factor.id_especialidad2=" & 0 & _
" AND hospital.id_hospital=" & cod1 & _
" AND hospital.id_ssalud=" & cod2

Conexion
Set rs_derivacion = New ADODB.Recordset
rs_derivacion.ActiveConnection = DBProgramacion
rs_derivacion.Source = sql
rs_derivacion.Open
If rs_derivacion.EOF Then
    MsgBox "No existen registros asociados", vbInformation
Else
    Carga_Matriz_Oferta
    Sacar_Lista_Espera
    
    Carga_Grilla2
End If


End Function
Private Sub Carga_Matriz_Oferta()

Totesp = 0
Do While Not rs_derivacion.EOF
 Totesp = Totesp + 1
 rs_derivacion.MoveNext
Loop
Totcons = 4
nfilas = Totesp + 2

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
Totintercon = 0
ncol = 1

resumen(0, 2) = "OFERTA"
        resumen(1, 2) = rs_derivacion![Dias_Cama_UCI]
        resumen(2, 2) = rs_derivacion![Hrs_Med_UCI]
        resumen(3, 2) = rs_derivacion![Dias_Cama_UTI]
        resumen(4, 2) = rs_derivacion![Hrs_Med_UTI]
               
        rs_derivacion.MoveNext
            
    'Carga Totales

    If rs_derivacion.EOF Then
      f = 100
      ncol = ncol + 1
    End If

End If

End Sub
Private Sub Sacar_Lista_Espera()
resumen(0, 3) = "LISTA DE ESPERA"
For f = 1 To 4

        resumen(f, 3) = resumen(f, 1) - resumen(f, 2)
        resumen(f, 3) = resumen(f, 1) - resumen(f, 2)
        resumen(f, 3) = resumen(f, 1) - resumen(f, 2)
        resumen(f, 3) = resumen(f, 1) - resumen(f, 2)
      
       
Next

End Sub

Private Sub Totales()
 TotxFila = 0
 
 nfilas = Totesp + 2
 Totcons = 7
 x = 1
 
  ReDim resumen(nfilas, 4)
 resumen(1, 0) = "Días Cama UCI"
 resumen(2, 0) = "Hrs. Médicas UCI"
 resumen(3, 0) = "Días Cama UTI"
 resumen(4, 0) = "Hrs. Médicas UTI"
 resumen(0, 1) = "DEMANDA"
    f = nfilas - 1

        For c = 3 To Totcons
            For i = 1 To f

                tot_factor = tot_factor + Miarray(i, c)

           Next
           'Miarray(i, c) = Format(tot_factor, "#####0.000")
           Miarray(i, c) = tot_factor
          
           resumen(x, 1) = tot_factor
           tot_factor = 0
           x = x + 1
          
        Next

        f = 100
        ncol = ncol + 1
    

End Sub
Private Sub total_por_fila()
Miarray(0, 7) = "Total"
For f = 2 To nfilas
    TotxFila = 0
    For c = 2 To 5
        TotxFila = TotxFila + Miarray(f, c)

    Next
    Miarray(f, 6) = TotxFila
   
Next
End Sub
Function Sacar_Factores(c1 As Integer, c2 As Integer) As Integer
Conexion
Set rs_factor = New ADODB.Recordset
rs_factor.ActiveConnection = DBProgramacion
56
sql = ""
sql = "SELECT servicio_clinico.id_servicio_clinico AS Nº, servicio_clinico.desc_servicio_clinico AS Servicio_Clinico," & _
" [servicio_clinico].[uci]*hospital.coef_uci AS Factor_Dias_Cama_UCI, Factor_Dias_Cama_UCI*valor_factor.factor10 AS Hrs_Med_UCI," & _
" [servicio_clinico].[uti]*hospital.coef_uti AS Factor_Dias_Cama_UTI, Factor_Dias_Cama_UTI*valor_factor.factor9 AS Hrs_Med_UTI" & _
" From servicio_clinico, hospital, valor_factor" & _
" WHERE (((valor_factor.id_especialidad2)=0) AND ((servicio_clinico.id_hospital)=[hospital].[id_hospital])" & _
" AND ((servicio_clinico.id_ssalud)=[hospital].[id_ssalud])" & _
" AND ((hospital.id_hospital)=" & c1 & ")" & _
" AND ((hospital.id_ssalud)=" & c2 & "))" & _
" ORDER BY servicio_clinico.id_servicio_clinico"


rs_factor.Source = sql
rs_factor.Open
Totesp = 0
If Not rs_factor.EOF Then
    Do While Not rs_factor.EOF
    Totesp = Totesp + 1
    rs_factor.MoveNext
    Loop
End If

If Totesp > 0 Then
    ReDim facarreglo(Totesp, 6)
    rs_factor.MoveFirst
    For x = 1 To Totesp
        facarreglo(x, 2) = rs_factor(2)
        facarreglo(x, 3) = rs_factor(3)
        facarreglo(x, 4) = rs_factor(4)
        facarreglo(x, 5) = rs_factor(5)
        rs_factor.MoveNext
    Next
rs_factor.Close
End If
End Function
Private Sub Carga_Factores()

Totcons = 7
nfilas = Totesp + 1

'rs_derivacion.MoveFirst

x = 0
For f = 1 To nfilas - 1


   x = x + 1
        Miarray(f, 3) = Round(Miarray(f, 2) * facarreglo(x, 2))
        Miarray(f, 4) = Round(Miarray(f, 2) * facarreglo(x, 3))
        Miarray(f, 5) = Round(Miarray(f, 2) * facarreglo(x, 4))
        Miarray(f, 6) = Round(Miarray(f, 2) * facarreglo(x, 5))
       ' Miarray(f, 7) = Round(Miarray(f, 2) * facarreglo(x, 6))
        
    Next

 End Sub

Private Sub Carga_Matriz()

Totesp = 0
Do While Not rs_derivacion.EOF
 Totesp = Totesp + 1
 rs_derivacion.MoveNext
Loop
Totcons = 7
nfilas = Totesp + 2
If sw = 0 Then
ReDim Miarray(nfilas, (Totcons + 1))
'Miarray(0, 0) = "Id"
Miarray(0, 1) = "Servicio Clínico"
Miarray(0, 2) = "Días Cama UCI"
Miarray(0, 3) = "Hrs. Méd. UCI"
Miarray(0, 4) = "Días Cama UTI"
Miarray(0, 5) = "Hrs. Méd. UTI"
Miarray(0, 6) = "Total"
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


        'Miarray(f, 0) = rs_derivacion(0)
        Miarray(f, 1) = rs_derivacion(1)
        Miarray(f, 2) = Miarray(f, 2) + rs_derivacion(2)
        If sw = 3 Then
            Miarray(f, 2) = Miarray(f, 2) + rs_derivacion(3)
            
        End If
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
Next
End If

End Sub

Private Sub Carga_Grilla2()
    Dim i, c As Integer
    Totesp = 4
    Totcons = 3
    MSFlexGrid2.Rows = Totesp + 1
    MSFlexGrid2.Cols = Totcons + 1
    Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.Col = 1
    'Frm_PLAN_DMDA_UCI_UTI.MSFlexGrid2.ColWidth(0) = 500
    Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.ColWidth(0) = 1500
    Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.ColWidth(1) = 1500
    Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.ColWidth(2) = 1500
    Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.ColWidth(3) = 2000
'    Frm_PLAN_DMDA_UCI_UTI.MSFlexGrid2.ColWidth(4) = 1200
'    Frm_PLAN_DMDA_UCI_UTI.MSFlexGrid2.ColWidth(5) = 1200
    'Frm_PLAN_DMDA_UCI_UTI.MSFlexGrid2.ColWidth(6) = 1200

    For i = 0 To Totesp
    MSFlexGrid2.Row = i

        For c = 0 To Totcons
            If i = 0 Then
                Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.CellBackColor = &H80000004
            End If
            MSFlexGrid2.Col = c
            Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.Text = Format(resumen(i, c), "###,###,##0")
            If c = 1 Then
            Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.CellBackColor = 648447
            End If
            If c = 3 Then
            Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.CellBackColor = &HE0E0E0
            End If
            
            If resumen(i, c) < 0 Then
                Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.CellForeColor = &HC00000
                Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.CellBackColor = &HE0E0E0
            End If

            If i = Totesp + 1 Then
            Frm_PLAN_OFERTA_DMDA_UCI_UTI.MSFlexGrid2.CellBackColor = &HE0E0E0

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



Private Sub cmd_exportar_Click()
Dim j, i, sub_col As Integer
Dim FilaExcelGrilla As String


  cmd_exportar.Enabled = False
  Nom_Form = RTrim(Frm_PLAN_OFERTA_DMDA_UCI_UTI.Caption) & ".xls"
  AcXExcel1.TotalFilas = MSFlexGrid2.Rows
  AcXExcel1.Hoja = "Hoja1"
  AcXExcel1.Listoco = False
  AcXExcel1.archivo = "c:\desa\programacion\excel\" & Nom_Form
  AcXExcel1.Inicializa
  nfilas = MSFlexGrid2.Rows
  Totcons = MSFlexGrid2.Cols - 1
  For j = 0 To nfilas - 1
  MSFlexGrid2.Row = j

       For i = 0 To (Totcons)
       MSFlexGrid2.Col = i
           If j = 0 Then
              FilaExcelGrilla = FilaExcelGrilla & "·c" & MSFlexGrid2.Text 'Miarray(j, i)
           Else
              If i = 0 Then
                 FilaExcelGrilla = FilaExcelGrilla & "·c" & MSFlexGrid2.Text 'Miarray(j, i)
              Else
                 FilaExcelGrilla = FilaExcelGrilla & "·n" & Val(Replace(MSFlexGrid2.Text, ".", ""))  'Miarray(j, i)
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

Dim rs_hospital As ADODB.Recordset

On Error GoTo error_control

Set rs_hospital = New ADODB.Recordset


rs_hospital.ActiveConnection = DBProgramacion


If Val(tipo_establecimiento) > 1 Then
    rs_hospital.Source = "select * from hospital Where Trim(clave) ='" & Trim(password) & "'"
   Else
    rs_hospital.Source = "select * from hospital where id_ssalud=" & cod_ss
End If
 
rs_hospital.Open


nfil = -1

rs_hospital.MoveFirst
Do While Not rs_hospital.EOF
    nfil = nfil + 1
    If cod_establecimiento = rs_hospital![id_hospital] Then
       posi = nfil
    End If
   cbo_hospital.AddItem rs_hospital![desc_hospital]
   cbo_hospital.ItemData(cbo_hospital.NewIndex) = rs_hospital![id_hospital]
   rs_hospital.MoveNext
   
Loop

If tipo_establecimiento > 1 Then
    cbo_hospital.ListIndex = posi
End If
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
        
    If MSFlexGrid1.Col < 4 Then
        
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
Dim rs_especial As ADODB.Recordset

Dim nfil, TotFila As Integer

Conexion
Set rs_especial = New ADODB.Recordset
rs_especial.ActiveConnection = DBProgramacion
rs_especial.Source = "Select * from servicio_clinico where id_hospital = " & cod1 & " and id_ssalud = " & cod2 & " order by id_servicio_clinico "
rs_especial.Open

rs_especial.MoveFirst
TotFila = rs_especial.RecordCount

Frm_OFERTA_HOSP.MSFlexGrid2.Rows = TotServicio
Frm_OFERTA_HOSP.MSFlexGrid2.ColWidth(0) = 500
Frm_OFERTA_HOSP.MSFlexGrid2.ColWidth(1) = 2000
Frm_OFERTA_HOSP.MSFlexGrid2.ColWidth(2) = 1000
Frm_OFERTA_HOSP.MSFlexGrid2.ColWidth(3) = 1000


Frm_OFERTA_HOSP.MSFlexGrid2.Col = 0
Frm_OFERTA_HOSP.MSFlexGrid2.Row = 0
Frm_OFERTA_HOSP.MSFlexGrid2.Text = "ID"
Frm_OFERTA_HOSP.MSFlexGrid2.Col = 1
Frm_OFERTA_HOSP.MSFlexGrid2.Row = 0
Frm_OFERTA_HOSP.MSFlexGrid2.Text = "Especialidad"
Frm_OFERTA_HOSP.MSFlexGrid2.Col = 2
Frm_OFERTA_HOSP.MSFlexGrid2.Row = 0
Frm_OFERTA_HOSP.MSFlexGrid2.Text = "Sala"
Frm_OFERTA_HOSP.MSFlexGrid2.Col = 3
Frm_OFERTA_HOSP.MSFlexGrid2.Row = 0
Frm_OFERTA_HOSP.MSFlexGrid2.Text = "Pabellon"
Frm_OFERTA_HOSP.MSFlexGrid2.Col = 4
Frm_OFERTA_HOSP.MSFlexGrid2.Row = 0
Frm_OFERTA_HOSP.MSFlexGrid2.Text = "UCI"
Frm_OFERTA_HOSP.MSFlexGrid2.Col = 5
Frm_OFERTA_HOSP.MSFlexGrid2.Row = 0
Frm_OFERTA_HOSP.MSFlexGrid2.Text = "UTI"



Frm_OFERTA_HOSP.MSFlexGrid2.Col = 0
rs_especial.MoveFirst
nfil = 1
Do While Not rs_especial.EOF
  Frm_OFERTA_HOSP.MSFlexGrid2.Col = 0
  Frm_OFERTA_HOSP.MSFlexGrid2.Row = nfil
  Frm_OFERTA_HOSP.MSFlexGrid2.Text = rs_especial![id_servicio_clinico]
  Frm_OFERTA_HOSP.MSFlexGrid2.Col = 1
  Frm_OFERTA_HOSP.MSFlexGrid2.Row = nfil
  Frm_OFERTA_HOSP.MSFlexGrid2.Text = rs_especial![desc_servicio_clinico]
  Frm_OFERTA_HOSP.MSFlexGrid2.Col = 2
  Frm_OFERTA_HOSP.MSFlexGrid2.Row = nfil
  Frm_OFERTA_HOSP.MSFlexGrid2.Text = "0"
  Frm_OFERTA_HOSP.MSFlexGrid2.Col = 3
  Frm_OFERTA_HOSP.MSFlexGrid2.Row = nfil
  Frm_OFERTA_HOSP.MSFlexGrid2.Text = "0"
  Frm_OFERTA_HOSP.MSFlexGrid2.Col = 4
  Frm_OFERTA_HOSP.MSFlexGrid2.Row = nfil
  Frm_OFERTA_HOSP.MSFlexGrid2.Text = "0"
  Frm_OFERTA_HOSP.MSFlexGrid2.Col = 5
  Frm_OFERTA_HOSP.MSFlexGrid2.Row = nfil
  Frm_OFERTA_HOSP.MSFlexGrid2.Text = "0"
  nfil = nfil + 1
  rs_especial.MoveNext
Loop
rs_especial.Close
End Function

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

Private Sub cmd_imprimir_Click()
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

For i = 1 To nfilas - 1
MSFlexGrid2.Row = i
MSFlexGrid2.Col = 0
If (MSFlexGrid2.Text) <> "" Then
   rs_imprimir.AddNew
   rs_imprimir!descripcion = MSFlexGrid2.Text
   MSFlexGrid2.Col = 1
   rs_imprimir!c1 = Val(Replace(MSFlexGrid2.Text, ".", ""))
   MSFlexGrid2.Col = 2
   rs_imprimir!c2 = Val(Replace(MSFlexGrid2.Text, ".", ""))
   MSFlexGrid2.Col = 3
   rs_imprimir!c3 = Val(Replace(MSFlexGrid2.Text, ".", ""))
   rs_imprimir.Update
End If
Next
rs_imprimir.Close
Set rs_imprimir = New ADODB.Recordset
rs_imprimir.ActiveConnection = DBProgramacion
sql = "select * from imprime_datos"
rs_imprimir.Source = sql
rs_imprimir.Open

DataEnvironment1.plan
DataReport7.Sections("Sección4").Controls("etiqueta1").Caption = "PLAN OFERTA DEMANDA UCI - UTI"
DataReport7.Show 1
DataEnvironment1.Connection1.Close
DataEnvironment1.Connection1.Open
rs_imprimir.Close
MsgBox "Impresión terminada"
End Sub




