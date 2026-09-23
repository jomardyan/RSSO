"""Example sheet and embedded typing-help metadata for the workbook builder."""
from pathlib import Path
from datetime import datetime
import html
ROOT = Path(__file__).resolve().parent.parent

def color(value):
    return int(value[0:2], 16) + int(value[2:4], 16)*256 + int(value[4:6], 16)*65536

def serial(text):
    return (datetime.fromisoformat(text) - datetime(1899,12,30)).days

def put(sheet, address, value):
    sheet.Range(address).Value2 = value

def merged(sheet, address, text, size=11, bold=False, fill=None, ink='243449'):
    r=sheet.Range(address)
    r.Merge()
    r.NumberFormat='@'
    r.Value2=text
    r.Font.Size=size
    r.Font.Bold=bold
    r.Font.Color=color(ink)
    r.WrapText=True
    r.VerticalAlignment=-4108
    if fill: r.Interior.Color=color(fill)

def section(sheet, row, title):
    merged(sheet,f'A{row}:H{row}',title,13,True,'193B56','FFFFFF')
    sheet.Rows(row).RowHeight=29

def result(sheet, row, formula, label='Annual rate'):
    put(sheet,f'E{row}',label)
    cell=sheet.Range(f'F{row}')
    cell.Formula=formula
    cell.NumberFormat='0.00%'
    cell.Interior.Color=color('DDF0E5')
    cell.Font.Color=color('145938')
    cell.Font.Bold=True
    cell.Font.Size=16
    merged(sheet,f'E{row+1}:H{row+2}',formula,11)
    cell.AddComment('Edit the blue input cells to recalculate. '+formula)

def flow_table(sheet, row, values):
    sheet.Range(f'A{row}:C{row}').Value2=(('Date','Cash flow','Meaning'),)
    sheet.Range(f'A{row}:C{row}').Font.Bold=True
    sheet.Range(f'A{row}:C{row}').Interior.Color=color('E4ECF2')
    rows=tuple((serial(d),a,note) for d,a,note in values)
    sheet.Range(f'A{row+1}:C{row+len(rows)}').Value2=rows
    codes=sheet.Application.International
    date_format=codes[18]*4+'-'+codes[19]*2+'-'+codes[20]*2
    sheet.Range(f'A{row+1}:A{row+len(rows)}').NumberFormat=date_format
    sheet.Range(f'B{row+1}:B{row+len(rows)}').NumberFormat='#,##0.00;[Red](#,##0.00)'
    sheet.Range(f'A{row+1}:B{row+len(rows)}').Font.Color=color('1566AD')
    sheet.Range(f'A{row+1}:B{row+len(rows)}').Interior.Color=color('EDF5FC')

def examples(book):
    s=book.Worksheets.Add(After=book.Worksheets(book.Worksheets.Count))
    s.Name='Examples'
    s.Cells.Font.Name='Calibri'
    s.Cells.Font.Size=11
    s.Cells.Font.Color=color('243449')
    s.Rows.RowHeight=21
    for col,width in [('A',18),('B',18),('C',29),('D',3),('E',21),('F',18),('G',17),('H',17)]:
        s.Columns(col).ColumnWidth=width
    merged(s,'A1:H2','RSSO | loan rate examples',24,True,'193B56','FFFFFF')
    merged(s,'A3:H3','Edit blue inputs. Green cells calculate the annual effective rate.',12)
    merged(s,'A4:H5','Simple: =RSSO(LoanAmount, TotalDue, Days, [Basis])\nDated: =RSSO(CashFlows, Dates, , [Basis])',12,True,'EDF5FC')
    merged(s,'A6:H7','Type =RSSO( for argument tooltips when the bundled help add-in is loaded. Ctrl+A or the fx button opens standard argument help. Enable workbook macros for calculations.',11)
    merged(s,'A8:H9','Use positive amounts for money received and negative amounts for repayments and fees. Dates must be real Excel dates. Results are decimals formatted as percentages. Some Excel locales use semicolons instead of commas.',11)
    button=s.Shapes.AddShape(5,s.Range('F10').Left,s.Range('F10').Top,220,28)
    button.TextFrame.Characters().Text='RSSO function help'
    button.OnAction='ShowRSSOHelp'
    button.Fill.ForeColor.RGB=color('2379A0')
    button.TextFrame.Characters().Font.Color=color('FFFFFF')
    button.Line.Visible=0

    section(s,13,'1  |  One repayment after 30 days')
    s.Range('A15:B17').Value2=(('Loan received',1000),('Total repaid',1100),('Days',30))
    s.Range('B15:B17').Font.Color=color('1566AD')
    s.Range('B15:B17').Interior.Color=color('EDF5FC')
    result(s,15,'=RSSO(B15,B16,B17)')
    merged(s,'A19:H20','The total repayment includes every cost paid at maturity. This example uses a 365-day year. A 10% cost over 30 days produces a much larger annual rate.',11)

    section(s,23,'2  |  Dated loan with one repayment')
    flow_table(s,24,[('2026-01-01',1000,'Loan received'),('2027-01-01',-1100,'Repayment')])
    result(s,25,'=RSSO(B25:B26,A25:A26,,"365")')
    merged(s,'A29:H30','Expected result: 10.00%. Leave Days empty when using ranges. The fixed 365 basis uses the same day-count convention as Excel XIRR.',11)

    section(s,33,'3  |  Upfront fee paid on the drawdown date')
    flow_table(s,34,[('2026-01-01',1000,'Gross loan received'),('2026-01-01',-50,'Fee paid upfront'),('2027-01-01',-1100,'Final repayment')])
    result(s,35,'=RSSO(B35:B37,A35:A37,,"365")')
    merged(s,'A39:H40','Expected result: 15.79%. The customer receives a net 950 on day one. Enter the fee separately or use the net receipt; do not count the same fee twice.',11)

    section(s,43,'4  |  Twelve monthly repayments')
    monthly=[('2026-01-01',1000,'Loan received')]
    monthly += [(f'2026-{m:02d}-01',-100,f'Instalment {m-1}') for m in range(2,13)]
    monthly += [('2027-01-01',-100,'Instalment 12')]
    flow_table(s,44,monthly)
    result(s,45,'=RSSO(B45:B57,A45:A57,,"365")')
    result(s,50,'=XIRR(B45:B57,A45:A57)','Excel XIRR check')
    merged(s,'E54:H57','The two rates should agree. Payment dates matter, so enter the actual schedule rather than replacing it with the total amount paid.',11)
    merged(s,'A59:H60','The 365 basis is demonstrated here. Use an explicitly agreed day-count convention for your calculation; see the basis notes below.',11)

    section(s,63,'5  |  Two drawdowns before repayments begin')
    flow_table(s,64,[('2026-01-01',600,'First drawdown'),('2026-01-15',400,'Second drawdown'),('2026-07-01',-550,'First repayment'),('2027-01-01',-550,'Final repayment')])
    result(s,65,'=RSSO(B65:B68,A65:A68,"365")')
    merged(s,'A70:H71','A text basis may also be supplied as the third argument in dated mode. Net receipts must precede net repayments; alternating borrowing and repayment schedules return #NUM! because a unique rate is not assured.',11)

    section(s,74,'6  |  Leap year: compare ACT/ACT and fixed 365')
    flow_table(s,76,[('2024-01-01',1000,'Loan received'),('2025-01-01',-1100,'366 days later')])
    result(s,77,'=RSSO(B77:B78,A77:A78,,"ACT/ACT")','ACT/ACT')
    result(s,81,'=RSSO(B77:B78,A77:A78,,"365")','Fixed 365')
    merged(s,'A81:C84','ACT/ACT gives exactly 10.00% for this calendar year. Fixed 365 gives approximately 9.97%.',11)

    section(s,87,'7  |  Zero-cost loan')
    flow_table(s,88,[('2026-01-01',1000,'Loan received'),('2027-01-01',-1000,'Equal repayment')])
    result(s,89,'=RSSO(B89:B90,A89:A90)')
    merged(s,'A93:H94','Expected result: 0.00%. The default in dated mode is the legacy LEGAL basis, which is equivalent to ACT/ACT.',11)

    section(s,97,'8  |  Repayment below the amount received')
    flow_table(s,98,[('2026-01-01',1000,'Loan received'),('2027-01-01',-900,'Lower repayment')])
    result(s,99,'=RSSO(B99:B100,A99:A100,,"365")')
    merged(s,'A103:H104','Expected result: -10.00%. Negative annual rates are supported down to the numerical precision limit above -100%.',11)

    section(s,107,'Basis and input rules')
    notes=[
        ('365 / 366','Fixed year length. Dated time fraction = elapsed calendar days / the selected basis.'),
        ('ACT/ACT / LEGAL','Split elapsed days at each 1 January and divide each segment by 365 or 366. LEGAL is a legacy name, not a guarantee of statutory RRSO compliance.'),
        ('Simple-mode default','Without dates, ACT/ACT and LEGAL use 365. Select 366 explicitly if needed. Days must be a positive whole number.'),
        ('Dated inputs','Use matching single-column or single-row ranges. Real Excel dates only; no date text or time-of-day. Completely blank pairs are ignored; partial pairs are invalid.'),
        ('Cash-flow order','Rows may be unsorted. Same-date entries are combined. Non-zero net receipts must precede net repayments. At least two different dates are required.'),
        ('Errors','#VALUE!: invalid input or basis. #REF!: incompatible ranges. #NUM!: invalid amounts/days, unsupported sign pattern, or no representable rate. Source cell errors are preserved.'),
        ('Typing help','Keep the help folder beside RSSO.xlsm. The Windows add-in loads for this Excel session when workbook macros run. If blocked, use Ctrl+A / fx; calculations do not depend on the add-in.'),
    ]
    for i,(title,text) in enumerate(notes):
        row=109+3*i
        merged(s,f'A{row}:B{row+1}',title,11,True,'EDF5FC')
        merged(s,f'C{row}:H{row+1}',text,11)
    s.Activate()
    s.Application.ActiveWindow.DisplayGridlines=False
    s.Application.ActiveWindow.Zoom=85
    s.Range('A13').Select()
    s.Application.ActiveWindow.FreezePanes=True
    s.Range('A1').Select()
    s.PageSetup.PrintArea='$A$1:$H$130'
    s.PageSetup.Orientation=2
    s.PageSetup.Zoom=False
    s.PageSetup.FitToPagesWide=1
    s.PageSetup.FitToPagesTall=False
    return s

def metadata(book):
    args=[
      ('LoanOrCashFlows','Positive loan amount, or cash-flow range. Customer receipts +; payments and fees -.'),
      ('TotalDueOrDates','Total repayment, or matching range of real Excel dates.'),
      ('[Days]','Positive whole days for a simple loan. Omit for dated cash flows; a text basis is also accepted.'),
      ('[Basis]','365, 366, ACT/ACT or LEGAL. Default LEGAL = calendar ACT/ACT; simple default = 365.')]
    xml='<IntelliSense xmlns="http://schemas.excel-dna.net/intellisense/1.0"><FunctionInfo><Function Name="RSSO" Description="Annual effective loan rate. Simple: RSSO(loan,total,days). Dated: RSSO(flows,dates,,basis). Format result as %." >'
    xml+=''.join(f'<Argument Name="{html.escape(n)}" Description="{html.escape(d)}" />' for n,d in args)
    xml+='</Function></FunctionInfo></IntelliSense>'
    book.CustomXMLParts.Add(xml)
