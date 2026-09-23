# Development

## Requirements

- Use Python 3.11+ for static validation and packaging.
- Rebuilding requires Windows, installed desktop Excel, and the packages in `requirements-excel.txt`.
- Edit `vba_source/RSSO.bas` for calculation logic and `vba_source/ThisWorkbook.txt` for the workbook-open event. The latter is inserted into Excel's existing ThisWorkbook component, not imported as a new class.

Create a virtual environment and install dependencies:

```shell
python -m venv .venv
.venv\Scripts\python -m pip install -r requirements-excel.txt
```

## Rebuild and run Excel checks

Close any candidate workbook in `.build/`, then run:

```shell
python scripts/build_workbook.py
```

Excel must permit access to the VBA project object model for a rebuild. You can configure this yourself or explicitly choose the temporary setting:

```shell
python scripts/build_workbook.py --temporary-vba-access
```

That flag changes the current user's Office 16.0 VBA access setting only for the build and restores the previous value after the dedicated Excel process closes. Normal workbook use does not need this setting. The build does not enable macros globally; it enables execution only in its own Excel instance for the workbook generated from this repository.

The builder starts from an empty workbook, imports the source, creates the examples, runs 50 calculation checks, removes the test sheet, strips personal Office metadata and printer settings, and reopens the candidate to check calculation and automatic XLL loading. Outputs are:

```text
.build/RSSO.xlsm
.build/verification.json
.build/help/
```

Inspect the candidate. When satisfied, promote both its workbook and its report:

```powershell
Copy-Item -LiteralPath .build/RSSO.xlsm -Destination RSSO.xlsm
Copy-Item -LiteralPath .build/verification.json -Destination docs/verification.json
python scripts/release_tools.py validate
```

The builder does not overwrite the tracked workbook. Binary workbook builds are not byte-for-byte reproducible because Excel writes build-dependent internal state. The verification report records the exact candidate's hash and a normalized source hash.

## Checks and limitations

```shell
python -m unittest discover -s tests -v
python scripts/release_tools.py validate
```

The desktop checks cover ordinary and extreme amounts, single and multiple drawdowns, fees, leap years, long loans, negative and zero rates, malformed input, and XIRR comparisons. XIRR comparisons use its numerical tolerance; an independent discounted-cash-flow residual is also checked.

Static CI verifies source consistency, XML metadata, the one-sheet public workbook layout, dependency hashes, and the report's binding to the binary. It does not rerun the numerical checks or visually inspect typing tooltips. The desktop build verifies that the help add-in loads on workbook open.

Keep local work, personal examples, and old exports under `.local/`. That directory is ignored by Git. Do not add raw Office documents, absolute-path logs, or private loan data to commits.
