Attribute VB_Name = "RSSOFunctions"
Option Explicit

' Annual effective rate, returned as a decimal (0.1 = 10%).
' =RSSO(LoanAmount, TotalDue, Days, [Basis])
' =RSSO(CashFlows, Dates, [Basis]) or =RSSO(CashFlows, Dates, , Basis)
' Positive flows are customer receipts; negative flows are payments/fees.
' LEGAL is retained as a legacy alias for ACT/ACT (calendar-year split).
' It is a day-count convention, not a certification of statutory RRSO.

Public Function RSSO(ByVal LoanOrCashFlows As Variant, _
    ByVal TotalDueOrDates As Variant, Optional ByVal Days As Variant, _
    Optional ByVal Basis As Variant = "LEGAL") As Variant
    On Error GoTo Failed
    Dim basisValue As Variant, dayValue As Variant, mode As String
    Dim a As Variant, b As Variant, yearDays As Double, exponent As Double
    basisValue = ScalarValue(Basis)
    If IsError(basisValue) Then
        RSSO = basisValue
        Exit Function
    End If
    If Not IsMissing(Days) Then
        dayValue = ScalarValue(Days)
        If IsError(dayValue) Then
            RSSO = dayValue
            Exit Function
        End If
    End If
    ' A text third argument is a convenient basis in dated cash-flow mode.
    If IsObject(LoanOrCashFlows) And IsObject(TotalDueOrDates) Then
        If TypeOf LoanOrCashFlows Is Excel.Range And TypeOf TotalDueOrDates Is Excel.Range Then
            If IsMissing(Days) Or IsEmpty(dayValue) Then
                mode = "FLOWS"
            ElseIf VarType(dayValue) = vbString Then
                If CStr(basisValue) <> "LEGAL" Then GoTo BadValue
                basisValue = dayValue
                mode = "FLOWS"
            End If
        End If
    End If
    If IsNull(basisValue) Then GoTo BadValue
    Dim convention As String
    convention = UCase$(Trim$(CStr(basisValue)))
    Select Case convention
        Case "LEGAL", "ACT/ACT", "365", "366"
        Case Else: GoTo BadValue
    End Select
    If mode = "FLOWS" Then
        RSSO = DatedRate(LoanOrCashFlows, TotalDueOrDates, convention)
        Exit Function
    End If
    If IsMissing(Days) Then GoTo BadValue
    If Not IsNumber(dayValue) Then GoTo BadValue
    If CDbl(dayValue) <= 0# Or CDbl(dayValue) <> Fix(CDbl(dayValue)) Then GoTo BadNumber
    a = ScalarValue(LoanOrCashFlows)
    b = ScalarValue(TotalDueOrDates)
    If IsError(a) Then
        RSSO = a
        Exit Function
    End If
    If IsError(b) Then
        RSSO = b
        Exit Function
    End If
    If Not IsNumber(a) Or Not IsNumber(b) Then GoTo BadValue
    If CDbl(a) <= 0# Or CDbl(b) <= 0# Then GoTo BadNumber
    ' No dates are supplied in simple mode: LEGAL/ACT/ACT use 365 days.
    yearDays = 365#
    If convention = "366" Then yearDays = 366#
    exponent = (Log(CDbl(b)) - Log(CDbl(a))) * (yearDays / CDbl(dayValue))
    If exponent > 709# Or exponent < -36# Then GoTo BadNumber
    RSSO = ExpMinusOne(exponent)
    Exit Function
BadValue:
    RSSO = CVErr(xlErrValue)
    Exit Function
BadNumber:
    RSSO = CVErr(xlErrNum)
    Exit Function
Failed:
    RSSO = CVErr(xlErrNum)
End Function

Private Function ScalarValue(ByVal value As Variant) As Variant
    If IsObject(value) Then
        If Not TypeOf value Is Excel.Range Then GoTo Invalid
        If value.Cells.CountLarge <> 1 Then GoTo Invalid
        ScalarValue = value.Value2
    ElseIf IsArray(value) Or IsNull(value) Then
        GoTo Invalid
    Else
        ScalarValue = value
    End If
    Exit Function
Invalid:
    ScalarValue = CVErr(xlErrValue)
End Function

Private Function IsNumber(ByVal value As Variant) As Boolean
    If IsError(value) Or IsEmpty(value) Or IsNull(value) Or IsObject(value) Or IsArray(value) Then Exit Function
    If VarType(value) = vbBoolean Or VarType(value) = vbDate Then Exit Function
    If VarType(value) = vbString Then Exit Function
    IsNumber = IsNumeric(value)
End Function

Private Function IsBlank(ByVal value As Variant) As Boolean
    If IsEmpty(value) Then
        IsBlank = True
    ElseIf VarType(value) = vbString Then
        IsBlank = (Len(Trim$(value)) = 0)
    End If
End Function

Private Function DatedRate(ByVal flows As Range, ByVal dates As Range, ByVal basis As String) As Variant
    Dim n As Long, used As Long, i As Long, merged As Long
    Dim amounts() As Double, serials() As Double, times() As Double
    Dim a As Variant, d As Variant, serial As Double, offset As Double
    Dim maxAmount As Double, seenNegative As Boolean, hasNegative As Boolean
    If flows.Areas.Count <> 1 Or dates.Areas.Count <> 1 Then GoTo BadRef
    If flows.Rows.Count <> dates.Rows.Count Or flows.Columns.Count <> dates.Columns.Count Then GoTo BadRef
    If flows.Rows.Count > 1 And flows.Columns.Count > 1 Then GoTo BadRef
    n = flows.Cells.CountLarge
    If n < 2 Then GoTo BadNumber
    ReDim amounts(1 To n)
    ReDim serials(1 To n)
    If dates.Parent.Parent.Date1904 Then offset = 1462#
    For i = 1 To n
        a = flows.Cells(i).Value2
        d = dates.Cells(i).Value2
        If IsError(a) Then
            DatedRate = a
            Exit Function
        End If
        If IsError(d) Then
            DatedRate = d
            Exit Function
        End If
        If IsBlank(a) And IsBlank(d) Then GoTo NextRow
        If Not IsNumber(a) Or Not IsNumber(d) Then GoTo BadValue
        serial = CDbl(d)
        If serial <> Fix(serial) Then GoTo BadValue
        If offset = 0# Then
            If serial < 1# Or serial = 60# Then GoTo BadValue
            If serial < 60# Then serial = serial + 1#
        Else
            If serial < 0# Then GoTo BadValue
            serial = serial + offset
        End If
        If serial > 2958465# Then GoTo BadValue
        ' Zero amounts do not affect the start date or sign pattern.
        If CDbl(a) = 0# Then GoTo NextRow
        used = used + 1
        amounts(used) = CDbl(a)
        serials(used) = serial
        If Abs(amounts(used)) > maxAmount Then maxAmount = Abs(amounts(used))
NextRow:
    Next i
    If used < 2 Then GoTo BadNumber
    SortFlows serials, amounts, 1, used
    ' Normalize before adding amounts, to avoid overflow and scale-dependent tests.
    For i = 1 To used
        amounts(i) = amounts(i) / maxAmount
    Next i
    For i = 1 To used
        If merged = 0 Then
            merged = 1
            serials(merged) = serials(i)
            amounts(merged) = amounts(i)
        ElseIf serials(i) = serials(merged) Then
            amounts(merged) = amounts(merged) + amounts(i)
        Else
            merged = merged + 1
            serials(merged) = serials(i)
            amounts(merged) = amounts(i)
        End If
    Next i
    used = 0
    For i = 1 To merged
        If amounts(i) <> 0# Then
            used = used + 1
            serials(used) = serials(i)
            amounts(used) = amounts(i)
        End If
    Next i
    If used < 2 Then GoTo BadNumber
    If amounts(1) <= 0# Then GoTo BadNumber
    ' Require one chronological sign change: a unique rate is then guaranteed.
    ' Later drawdowns after net repayments may have multiple roots; reject them.
    For i = 1 To used
        If amounts(i) < 0# Then
            seenNegative = True
            hasNegative = True
        ElseIf seenNegative Then
            GoTo BadNumber
        End If
    Next i
    If Not hasNegative Then GoTo BadNumber
    ReDim times(1 To used)
    ReDim Preserve amounts(1 To used)
    For i = 1 To used
        times(i) = YearFraction(CDate(serials(1)), CDate(serials(i)), basis)
    Next i
    DatedRate = SolveRate(amounts, times, used)
    Exit Function
BadRef:
    DatedRate = CVErr(xlErrRef)
    Exit Function
BadValue:
    DatedRate = CVErr(xlErrValue)
    Exit Function
BadNumber:
    DatedRate = CVErr(xlErrNum)
End Function

Private Sub SortFlows(ByRef dates() As Double, ByRef amounts() As Double, ByVal first As Long, ByVal last As Long)
    Dim i As Long, j As Long, pivot As Double, temp As Double
    i = first
    j = last
    pivot = dates((first + last) \ 2)
    Do While i <= j
        Do While dates(i) < pivot: i = i + 1: Loop
        Do While dates(j) > pivot: j = j - 1: Loop
        If i <= j Then
            temp = dates(i): dates(i) = dates(j): dates(j) = temp
            temp = amounts(i): amounts(i) = amounts(j): amounts(j) = temp
            i = i + 1
            j = j - 1
        End If
    Loop
    If first < j Then SortFlows dates, amounts, first, j
    If i < last Then SortFlows dates, amounts, i, last
End Sub

Private Function YearFraction(ByVal startDate As Date, ByVal endDate As Date, ByVal basis As String) As Double
    Dim currentDate As Date, periodEnd As Date, daysInYear As Double, y As Long
    If basis = "365" Or basis = "366" Then
        YearFraction = (CDbl(endDate) - CDbl(startDate)) / CDbl(basis)
        Exit Function
    End If
    currentDate = startDate
    Do While currentDate < endDate
        y = Year(currentDate)
        daysInYear = 365#
        If (y Mod 4 = 0 And y Mod 100 <> 0) Or y Mod 400 = 0 Then daysInYear = 366#
        ' Avoid DateSerial(10000,1,1) at the upper supported date.
        periodEnd = endDate
        If Year(endDate) > y Then periodEnd = DateSerial(y + 1, 1, 1)
        YearFraction = YearFraction + (CDbl(periodEnd) - CDbl(currentDate)) / daysInYear
        currentDate = periodEnd
    Loop
End Function

Private Function SolveRate(ByRef amounts() As Double, ByRef times() As Double, ByVal n As Long) As Variant
    ' Solve in log(1 + rate); scaled exponentials keep long loans finite.
    Dim low As Double, high As Double, mid As Double, f As Double, i As Long
    f = ScaledNPV(0#, amounts, times, n)
    If f = 0# Then
        SolveRate = 0#
        Exit Function
    End If
    low = -1#: high = 1#
    Do While ScaledNPV(low, amounts, times, n) > 0#
        low = low * 2#
        If low < -36# Then low = -36#
        If low = -36# Then Exit Do
    Loop
    Do While ScaledNPV(high, amounts, times, n) < 0#
        high = high * 2#
        If high > 709# Then high = 709#
        If high = 709# Then Exit Do
    Loop
    If ScaledNPV(low, amounts, times, n) > 0# Or ScaledNPV(high, amounts, times, n) < 0# Then
        SolveRate = CVErr(xlErrNum)
        Exit Function
    End If
    For i = 1 To 256
        mid = low + (high - low) / 2#
        f = ScaledNPV(mid, amounts, times, n)
        If f = 0# Or high - low <= 0.0000000000002 * (1# + Abs(mid)) Then
            SolveRate = ExpMinusOne(mid)
            Exit Function
        End If
        If f < 0# Then low = mid Else high = mid
    Next i
    SolveRate = CVErr(xlErrNum)
End Function

Private Function ScaledNPV(ByVal logRate As Double, ByRef amounts() As Double, ByRef times() As Double, ByVal n As Long) As Double
    Dim i As Long, largest As Double, exponent As Double, term As Double
    Dim total As Double, correction As Double, adjusted As Double, nextTotal As Double
    largest = -1E+308
    For i = 1 To n
        exponent = Log(Abs(amounts(i))) - times(i) * logRate
        If exponent > largest Then largest = exponent
    Next i
    For i = 1 To n
        exponent = Log(Abs(amounts(i))) - times(i) * logRate - largest
        term = 0#
        If exponent > -745# Then term = Sgn(amounts(i)) * Exp(exponent)
        adjusted = term - correction
        nextTotal = total + adjusted
        correction = (nextTotal - total) - adjusted
        total = nextTotal
    Next i
    ScaledNPV = total
End Function

Private Function ExpMinusOne(ByVal value As Double) As Double
    ' Preserve precision for rates close to zero.
    If Abs(value) < 0.00001 Then
        ExpMinusOne = value * (1# + value * (0.5 + value * (1# / 6# + value / 24#)))
    Else
        ExpMinusOne = Exp(value) - 1#
    End If
End Function

Public Sub RegisterRSSO()
    Application.MacroOptions Macro:="'" & Replace(ThisWorkbook.Name, "'", "''") & "'!RSSO", _
        Description:="Annual effective loan rate. Simple: RSSO(loan,total,days,[basis]). Dated: RSSO(flows,dates,,[basis]). Format as percentage.", _
        Category:=1, _
        ArgumentDescriptions:=Array( _
            "Positive loan amount, or a row/column of cash flows: receipts positive; payments and fees negative.", _
            "Positive total repayment in simple mode, or matching Excel dates for cash-flow mode.", _
            "Positive whole days for simple mode. Leave blank for dated cash flows; a text basis is also accepted here.", _
            "365 or 366 for fixed years; ACT/ACT or legacy LEGAL for calendar years. Default LEGAL; simple mode defaults to 365.")
End Sub

Public Sub ShowRSSOHelp()
    MsgBox "Single repayment:" & vbCrLf & _
        "=RSSO(1000,1100,30)" & vbCrLf & vbCrLf & _
        "Dated cash flows:" & vbCrLf & _
        "=RSSO(B17:B18,A17:A18,,""365"")" & vbCrLf & vbCrLf & _
        "Receipts positive; payments/fees negative. Format the result as %." & vbCrLf & _
        "After typing =RSSO( press Ctrl+A, or use the fx button, for argument help." & vbCrLf & _
        "See the Examples sheet for working formulas and basis details.", vbInformation, "RSSO function help"
End Sub

Public Sub LoadRSSOTypingHelp()
    ' The portable, official Excel-DNA add-in supplies real in-cell tooltips.
    ' Register for this Excel session only; no global add-in settings are changed.
    On Error GoTo Unavailable
    Dim addinPath As String, loaded As Boolean
#If Mac Then
    Exit Sub
#ElseIf Win64 Then
    addinPath = ThisWorkbook.Path & "\help\ExcelDna.IntelliSense64.xll"
#Else
    addinPath = ThisWorkbook.Path & "\help\ExcelDna.IntelliSense.xll"
#End If
    If Len(Dir$(addinPath)) = 0 Then Exit Sub
    loaded = Application.RegisterXLL(addinPath)
Unavailable:
    ' Standard fx / Ctrl+A help remains available if the optional XLL cannot load.
End Sub
