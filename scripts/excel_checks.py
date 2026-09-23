"""Numerical and input checks executed in a dedicated desktop Excel workbook."""
from datetime import datetime
import math, json

def serial(text):
    return (datetime.fromisoformat(text)-datetime(1899,12,30)).days

def verify_calculations(book, app, report_path):
    s=book.Worksheets.Add()
    s.Name='_Verification'
    cases=[]
    def check(name,formula,expected,tolerance=1e-9):
        c=s.Range('J1');c.Formula=formula;c.Calculate()
        value=c.Value2
        ok=isinstance(value,(float,int)) and abs(value-expected)<=tolerance*max(1,abs(expected))
        cases.append(dict(test=name,formula=formula,actual=value,expected=expected,passed=ok))
        if not ok: print('FAIL',cases[-1],flush=True)
    def data(values):
        s.Range('A1:F100').ClearContents()
        s.Range(f'A1:B{len(values)}').Value2=tuple((serial(d) if isinstance(d,str) else d,a) for d,a in values)
        s.Range(f'A1:A{len(values)}').NumberFormat='yyyy-mm-dd'
    VALUE=-2146826273;NUM=-2146826252;REF=-2146826265;DIV0=-2146826281
    check('simple 30 days','=RSSO(1000,1100,30)',1.1**(365/30)-1)
    check('simple 366 basis','=RSSO(1000,1100,366,"366")',0.1)
    check('simple zero rate','=RSSO(1000,1000,30)',0)
    check('simple negative rate','=RSSO(1000,900,365)',-0.1)
    check('fractional days rejected','=RSSO(1000,1100,30.5)',NUM,0)
    check('zero days rejected','=RSSO(1000,1100,0)',NUM,0)
    check('zero loan rejected','=RSSO(0,1100,30)',NUM,0)
    check('unknown basis rejected','=RSSO(1000,1100,30,"typo")',VALUE,0)
    check('error propagation','=RSSO(1/0,1100,30)',DIV0,0)
    check('boolean rejected','=RSSO(TRUE,1100,30)',VALUE,0)
    check('numeric text rejected','=RSSO("1000",1100,30)',VALUE,0)
    check('extreme simple ratio','=RSSO(1E-200,1E200,36500)',math.expm1(math.log(1e200)*2/100))
    data([('2026-01-01',1000),('2027-01-01',-1100)])
    check('dated 365','=RSSO(B1:B2,A1:A2,,"365")',.1)
    check('dated default','=RSSO(B1:B2,A1:A2)',.1)
    check('third basis','=RSSO(B1:B2,A1:A2,"365")',.1)
    check('shape mismatch','=RSSO(B1:B2,A1:C1)',REF,0)
    check('matrix rejected','=RSSO(B1:C2,D1:E2)',REF,0)
    check('unknown dated basis','=RSSO(B1:B2,A1:A2,"typo")',VALUE,0)
    check('blank pairs ignored','=RSSO(B1:B4,A1:A4,,"365")',.1)
    s.Range('A3').Value2=serial('2027-02-01')
    check('partial pair rejected','=RSSO(B1:B3,A1:A3)',VALUE,0)
    data([('2027-01-01',-1100),('2026-01-01',-50),('2026-01-01',1000)])
    check('unsorted same-day fee','=RSSO(B1:B3,A1:A3,,"365")',1100/950-1)
    data([('2024-01-01',1000),('2025-01-01',-1100)])
    check('leap ACT ACT','=RSSO(B1:B2,A1:A2,,"ACT/ACT")',.1)
    check('leap fixed 365','=RSSO(B1:B2,A1:A2,,"365")',1.1**(365/366)-1)
    check('leap fixed 366','=RSSO(B1:B2,A1:A2,,"366")',.1)
    data([('2023-07-01',1000),('2024-07-01',-1100)])
    check('split calendar years','=RSSO(B1:B2,A1:A2)',1.1**(1/(184/365+182/366))-1)
    data([('2026-01-01',1000),('2056-01-01',-4000)])
    years=(serial('2056-01-01')-serial('2026-01-01'))/365
    check('long loan overflow regression','=RSSO(B1:B2,A1:A2,,"365")',4**(1/years)-1)
    data([('2026-01-01',1000),('2027-01-01',-1000)])
    check('dated zero','=RSSO(B1:B2,A1:A2)',0)
    data([('2026-01-01',1000),('2027-01-01',-900)])
    check('dated negative','=RSSO(B1:B2,A1:A2)',-.1)
    data([('2026-01-01',1000),('2026-01-01',-1100)])
    check('all same day','=RSSO(B1:B2,A1:A2)',NUM,0)
    data([('2026-01-01',1000),('2027-01-01',-2300),('2028-01-01',1320)])
    check('multiple-root sign pattern','=RSSO(B1:B3,A1:A3)',NUM,0)
    data([('2026-01-01',-50),('2026-01-02',1000),('2027-01-01',-1100)])
    check('cost before drawdown','=RSSO(B1:B3,A1:A3)',NUM,0)
    data([('2026-01-01',1e200),('2027-01-01',-1.1e200)])
    check('large scale','=RSSO(B1:B2,A1:A2,,"365")',.1)
    data([('2026-01-01',1e-100),('2027-01-01',-1.1e-100)])
    check('small scale','=RSSO(B1:B2,A1:A2,,"365")',.1)
    data([('2026-01-01',1000),('2027-01-01',1100)])
    check('all positive','=RSSO(B1:B2,A1:A2)',NUM,0)
    data([('2026-01-01',1000),('2027-01-01',-1100)])
    s.Range('A2').Value2=serial('2027-01-01')+0.5
    check('time rejected','=RSSO(B1:B2,A1:A2)',VALUE,0)
    s.Range('A2').NumberFormat='@';s.Range('A2').Value2='2027-01-01'
    check('date text rejected','=RSSO(B1:B2,A1:A2)',VALUE,0)
    s.Range('A2').Clear()
    s.Range('A2').Formula='=1/0'
    check('date error propagated','=RSSO(B1:B2,A1:A2)',DIV0,0)
    data([('2026-01-01',600),('2026-01-15',400),('2026-07-01',-550),('2027-01-01',-550)])
    s.Range('K1').Formula='=XIRR(B1:B4,A1:A4)';s.Calculate()
    check('multiple drawdowns vs XIRR','=RSSO(B1:B4,A1:A4,,"365")',s.Range('K1').Value2,1e-7)
    rate=s.Range('J1').Value2
    residual=sum(amount/(1+rate)**((serial(date)-serial('2026-01-01'))/365) for date,amount in [('2026-01-01',600),('2026-01-15',400),('2026-07-01',-550),('2027-01-01',-550)])
    cases.append(dict(test='independent discounted-cash-flow residual',actual=residual,expected=0,passed=abs(residual)<1e-8))
    e=book.Worksheets('Examples')
    e.Calculate()
    cases.append(dict(test='monthly instalments vs XIRR',actual=e.Range('F45').Value2,expected=e.Range('F50').Value2,passed=abs(e.Range('F45').Value2-e.Range('F50').Value2)<1e-7))
    for address in ['F15','F25','F35','F45','F50','F65','F77','F81','F89','F99']:
        value=e.Range(address).Value2
        cases.append(dict(test='example '+address,actual=value,passed=isinstance(value,float) and -1<value<10))
    app.DisplayAlerts=False
    s.Delete()
    report_path.write_text(json.dumps(cases,indent=2) + '\n')
    print(f'Tests passed: {sum(c["passed"] for c in cases)}/{len(cases)}',flush=True)
    if not all(c['passed'] for c in cases): raise RuntimeError('Validation failed; original workbook not changed')
