"""Rebuild and verify the release candidate using a dedicated desktop Excel instance."""
from pathlib import Path
import argparse
import gc
import json
import shutil
import sys

from release_tools import ROOT, HELP_FILES, sha256, source_hash, sanitize_workbook


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--temporary-vba-access", action="store_true",
                        help="Temporarily enable VBA project access, then restore its previous value")
    args = parser.parse_args()
    if sys.platform != "win32":
        parser.error("Rebuilding requires Windows and desktop Excel. Static checks run on any platform.")
    import winreg
    import win32com.client
    import win32api, win32con, win32event, win32process
    from workbook_content import examples, metadata
    from excel_checks import verify_calculations

    output = ROOT / ".build"
    output.mkdir(exist_ok=True)
    for name in HELP_FILES:
        target = output / name
        target.parent.mkdir(exist_ok=True)
        shutil.copy2(ROOT / name, target)
    raw = output / "RSSO-raw.xlsm"
    candidate = output / "RSSO.xlsm"
    if raw.exists():
        raw.unlink()
    app = process = key = None
    old = None
    try:
        if args.temporary_vba_access:
            key = winreg.CreateKey(winreg.HKEY_CURRENT_USER, r"Software\Microsoft\Office\16.0\Excel\Security")
            try:
                old = winreg.QueryValueEx(key, "AccessVBOM")
            except FileNotFoundError:
                pass
            winreg.SetValueEx(key, "AccessVBOM", 0, winreg.REG_DWORD, 1)
        app = win32com.client.DispatchEx("Excel.Application")
        process = win32api.OpenProcess(win32con.SYNCHRONIZE | win32con.PROCESS_TERMINATE |
                                      win32con.PROCESS_QUERY_INFORMATION | win32con.PROCESS_VM_READ,
                                      False, win32process.GetWindowThreadProcessId(app.Hwnd)[1])
        app.Visible = False
        app.DisplayAlerts = False
        app.EnableEvents = False
        app.AutomationSecurity = 3
        book = app.Workbooks.Add(-4167)
        book.Worksheets(1).Name = "_Build"
        try:
            project = book.VBProject
        except Exception as error:
            raise RuntimeError("Excel VBA project access is disabled. Configure it in Excel, or rerun with --temporary-vba-access.") from error
        project.VBComponents.Import(str(ROOT / "vba_source/RSSO.bas"))
        project.VBComponents("ThisWorkbook").CodeModule.AddFromString(
            (ROOT / "vba_source/ThisWorkbook.txt").read_text())
        examples(book)
        metadata(book)
        app.DisplayAlerts = False
        book.Worksheets("_Build").Delete()
        if [sheet.Name for sheet in book.Worksheets] != ["Examples"]:
            raise RuntimeError("Excel did not remove the temporary build sheet")
        book.SaveAs(str(raw), 52)
        book.Close(False)
        app.AutomationSecurity = 1
        book = app.Workbooks.Open(str(raw), 0, False)
        app.Run(f"'{book.Name}'!RegisterRSSO")
        verify_calculations(book, app, output / "calculations.json")
        if [sheet.Name for sheet in book.Worksheets] != ["Examples"]:
            raise RuntimeError("Excel did not remove the verification sheet")
        book.Worksheets("Examples").Activate()
        book.Worksheets("Examples").Range("A1").Select()
        app.ActiveWindow.ScrollRow = 1
        app.ActiveWindow.ScrollColumn = 1
        book.Save()
        book.Close(False)
        sanitize_workbook(raw, candidate)
        app.EnableEvents = True
        book = app.Workbooks.Open(str(candidate), 0, True)
        book.Worksheets("Examples").Calculate()
        value = book.Worksheets("Examples").Range("F25").Value2
        if abs(value - 0.1) > 1e-10:
            raise RuntimeError("Sanitized workbook failed the reopen calculation check")
        modules = [win32process.GetModuleFileNameEx(process, module)
                   for module in win32process.EnumProcessModules(process)]
        loaded = any("exceldna.intellisense" in path.lower() for path in modules)
        if not loaded:
            raise RuntimeError("Workbook_Open did not load the typing-help add-in")
        report = {
            "version": (ROOT / "VERSION").read_text().strip(),
            "excel_version": str(app.Version), "excel_build": str(app.Build),
            "platform": "Windows desktop Excel", "source_sha256": source_hash(),
            "workbook_sha256": sha256(candidate.read_bytes()),
            "reopened_in_excel": True, "typing_addin_loaded_on_open": loaded,
            "checks": json.loads((output / "calculations.json").read_text()),
        }
        (output / "verification.json").write_text(json.dumps(report, indent=2) + "\n")
        book.Close(False)
        print("Verified release candidate: .build/RSSO.xlsm", flush=True)
    finally:
        if app is not None:
            try:
                app.Quit()
            except Exception:
                pass
        app = None
        gc.collect()
        if process:
            if win32event.WaitForSingleObject(process, 5000) == 258:
                # Only the dedicated process created above can be terminated.
                win32api.TerminateProcess(process, 0)
                win32event.WaitForSingleObject(process, 5000)
            win32api.CloseHandle(process)
        if key is not None:
            if old is None:
                try:
                    winreg.DeleteValue(key, "AccessVBOM")
                except FileNotFoundError:
                    pass
            else:
                winreg.SetValueEx(key, "AccessVBOM", 0, old[1], old[0])
            winreg.CloseKey(key)
            print("Original VBA project access setting restored.", flush=True)


if __name__ == "__main__":
    main()
