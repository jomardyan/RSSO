# Contributing

Small, focused pull requests are welcome. Describe the behavior being changed and include a minimal example of any calculation discrepancy. Use fictional amounts and dates in issues.

Read [development](docs/DEVELOPMENT.md) before changing VBA or workbook-generation code. Keep the readable source and embedded workbook VBA synchronized. A calculation change needs desktop Excel verification and an updated report; static CI alone does not execute VBA.

For documentation-only changes, run the static checks. For tooling changes, add tests that cover an actual failure mode. Include your Excel version, operating system, and regional formula separator when reporting workbook issues.

Contributions are provided under this project's MIT license. Preserve third-party notices when changing bundled dependencies.
