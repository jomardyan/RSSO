# RSSO for Excel

Calculate an effective annual loan rate from a single repayment or a dated cash-flow schedule. The macro-enabled workbook includes eight editable examples and optional help while typing `=RSSO(`.

**Status:** initial public release, v0.1.0. Tested in Windows desktop Excel 16.0. The calculation is implemented in VBA; Excel for the web cannot run it.

## Get started

1. Download the `RSSO-v0.1.0.zip` asset from this repository's **Releases** page, or clone/download the repository.
2. Extract the files. Keep `RSSO.xlsm` and the `help/` folder together.
3. Open `RSSO.xlsm` in desktop Excel. Review the VBA source and enable macros if you trust it.
4. Open **Examples**, edit the blue inputs, and read the green percentage results.

```excel
=RSSO(1000,1100,30)
=RSSO(B2:B10,A2:A10,,"365")
```

The first example is a 1,000 loan repaid as 1,100 after 30 days. It returns approximately **218.87% annually**. In dated mode, customer receipts are positive and repayments or fees are negative. Format the result as a percentage. Regional Excel settings may use semicolons instead of commas.

See [usage, supported cash flows, and day-count conventions](docs/USAGE.md).

## Help while typing

On Windows, `Workbook_Open` loads the bundled [Excel-DNA IntelliSense 1.9.0](https://github.com/Excel-DNA/IntelliSense/releases/tag/v1.9.0) add-in for the current Excel session. Its function descriptions are embedded in the workbook. This provides argument help while entering `=RSSO(`.

If the XLL add-in cannot load, use **Ctrl+A** after typing `=RSSO(`, the **fx** button, or the workbook's help button. Calculations do not depend on the add-in. Automatic typing help requires Windows; other desktop Excel platforms have not been tested. The bundled XLLs are not signed by this project; organizational macro and add-in policies still apply.

## Calculation scope

RSSO supports fixed 365/366-day years and calendar-year ACT/ACT. The original `LEGAL` name remains an alias for ACT/ACT. **That name does not establish compliance with statutory RRSO/APR rules.** Applicable fees, statutory assumptions, and day-count conventions must be determined separately.

Net receipts must precede net repayments after combining entries on the same date. Schedules that alternate back to borrowing return `#NUM!` because a unique rate is not guaranteed. See [the input rules](docs/USAGE.md#input-rules).

## Project layout

```text
RSSO.xlsm           Ready-to-use, sanitized workbook
vba_source/         VBA calculation and workbook-open code
help/               Pinned typing-help add-ins and third-party licenses
scripts/            Excel rebuild, validation, and release packaging
tests/             Tests for release tooling
docs/              Usage, development, release instructions, and verification
.github/            CI workflow and contribution templates
```

Local originals and old exports are excluded through `.gitignore`. Generated release archives go into `dist/`.

## Verify or contribute

Python 3.11 or newer is required for tooling. It is not required to use the workbook.

```shell
python -m pip install -r requirements-dev.txt
python -m unittest discover -s tests -v
python scripts/release_tools.py validate
python scripts/release_tools.py package
```

Validation checks the embedded VBA against the source, add-in checksums, workbook metadata, and the recorded Excel results. CI performs these checks and packages the verified workbook; it does **not** execute Excel or VBA. The [verification report](docs/verification.json) records 50 desktop Excel checks and is bound to the workbook and source hashes.

See [development](docs/DEVELOPMENT.md), [contributing](CONTRIBUTING.md), and [release instructions](docs/RELEASING.md).

## License

RSSO code and documentation are available under the [MIT license](LICENSE). Bundled Excel-DNA components retain their own MIT copyright notices; see [third-party notices](THIRD_PARTY_NOTICES.md).
