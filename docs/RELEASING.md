# Publishing a release

This repository is prepared locally. Creating a GitHub repository and publishing a release are separate steps.

## Prepare an artifact

1. Update `VERSION`, the README's download filename, and `CHANGELOG.md`.
2. Rebuild in desktop Excel and promote the candidate as described in [development](DEVELOPMENT.md).
3. Run the checks and package command:

   ```shell
   python -m unittest discover -s tests -v
   python scripts/release_tools.py validate
   python scripts/release_tools.py package
   ```

4. Inspect `git diff` and `git status`. Local originals and generated files must remain ignored.
5. Commit the source, workbook, matching `docs/verification.json`, and documentation together.

The package command uses an explicit file list and produces `dist/RSSO-v<VERSION>.zip` plus a `.zip.sha256` file. The ZIP contains the workbook, both help add-ins, licenses, VBA source, usage instructions, and verification results. It includes its own `SHA256SUMS.txt` manifest. It does not include local backups, machine logs, or Word documents.

## First GitHub publication

Create an empty GitHub repository under the intended owner, then add its URL as `origin` and push the reviewed `main` branch. No owner or repository URL is assumed in this project. Review the commit author information before publishing.

The GitHub Actions workflow validates and packages on pushes, pull requests, and manual runs. It uploads a CI artifact and does not publish a GitHub Release automatically. This keeps the first public release reviewable.

Create a release tag matching the version, for example `v0.1.0`, and attach the ZIP and checksum file on GitHub's Releases page. Use the matching [changelog](../CHANGELOG.md) entry as release notes. Enable GitHub private vulnerability reporting if maintainers intend to receive private reports.

## Dependency updates

The bundled Excel-DNA IntelliSense binaries are pinned to v1.9.0 in `help/provenance.json`. To update them, obtain both binaries from the official release, review their licenses, update the recorded SHA-256 values and sizes, and repeat the Windows open-event check. Do not replace the files without changing the manifest.
