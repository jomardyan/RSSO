"""Static release checks and deterministic packaging; never executes workbook macros."""
from pathlib import Path
import argparse
import hashlib
import json
import re
import zipfile
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parent.parent
SOURCE_FILES = ("vba_source/RSSO.bas", "vba_source/ThisWorkbook.txt")
HELP_FILES = ("help/ExcelDna.IntelliSense.xll", "help/ExcelDna.IntelliSense64.xll",
              "help/LICENSE.txt", "help/ExcelDna-LICENSE.txt", "help/provenance.json")
RELEASE_FILES = ("RSSO.xlsm", "README.md", "LICENSE", "VERSION", "CHANGELOG.md",
                 "THIRD_PARTY_NOTICES.md", "docs/USAGE.md", "docs/verification.json",
                 "CONTRIBUTING.md", "docs/DEVELOPMENT.md", "docs/RELEASING.md",
                 "requirements-dev.txt", "requirements-excel.txt", ".gitignore",
                 "scripts/build_workbook.py", "scripts/workbook_content.py",
                 "scripts/excel_checks.py", "scripts/release_tools.py", "tests/test_release_tools.py",
                 *SOURCE_FILES, *HELP_FILES)
PRIVATE_PATH = re.compile(r"(?i)(?:[a-z]:[\\/]+Users[\\/]|/home/|/Users/)")


def sha256(data):
    return hashlib.sha256(data).hexdigest()


def source_hash(root=ROOT):
    digest = hashlib.sha256()
    for name in SOURCE_FILES:
        digest.update(name.encode())
        digest.update((root / name).read_text(encoding="utf-8").replace("\r\n", "\n").encode())
    return digest.hexdigest()


def normalize_vba(code):
    lines = [line.rstrip() for line in code.replace("\r", "").splitlines()
             if not line.startswith("Attribute ")]
    # VBA may change identifier capitalization when compiling. Keep string literals exact.
    text = "\n".join(lines).strip()
    parts = re.split(r'("(?:[^"\n]|"")*")', text)
    return "".join(part if index % 2 else part.casefold() for index, part in enumerate(parts))


def sanitize_workbook(source, destination):
    """Remove personal Office metadata and printer settings without rewriting VBA."""
    source, destination = Path(source), Path(destination)
    if source.resolve() == destination.resolve():
        raise ValueError("Sanitization requires a separate output path")
    with zipfile.ZipFile(source) as archive:
        content = {name: archive.read(name) for name in archive.namelist()}
    content["docProps/core.xml"] = (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
        'xmlns:dc="http://purl.org/dc/elements/1.1/">'
        '<dc:title>RSSO loan rate examples</dc:title><dc:creator>RSSO contributors</dc:creator>'
        '<cp:lastModifiedBy>RSSO contributors</cp:lastModifiedBy></cp:coreProperties>'
    ).encode()
    for name in list(content):
        if name.startswith("xl/printerSettings/"):
            del content[name]
            continue
        if not name.endswith((".xml", ".rels")):
            continue
        text = content[name].decode("utf-8")
        if name == "xl/workbook.xml":
            text = re.sub(r'<mc:AlternateContent\b[^>]*>.*?</mc:AlternateContent>',
                          lambda m: "" if "absPath" in m[0] else m[0], text, flags=re.S)
        if name == "docProps/app.xml":
            text = re.sub(r'<(Company|Manager)\b[^>]*>.*?</\1>', r'<\1/>', text, flags=re.S)
        if name.startswith("xl/comments"):
            text = re.sub(r'<author>.*?</author>', '<author>RSSO contributors</author>', text, flags=re.S)
        if name.endswith(".rels"):
            for match in list(re.finditer(r'<Relationship\s[^>]*/>', text)):
                element = ET.fromstring(match[0])
                if element.attrib.get("Type", "").endswith("/printerSettings"):
                    text = text.replace(match[0], "")
                    rel_id = element.attrib["Id"]
                    part = str(Path(name).parent.parent / Path(name).name[:-5]).replace("\\", "/")
                    if part in content:
                        part_text = content[part].decode("utf-8")
                        part_text = re.sub(r'\s+r:id="' + re.escape(rel_id) + r'"', "", part_text)
                        content[part] = part_text.encode()
        if name == "[Content_Types].xml":
            text = re.sub(r'<(?:Default|Override)\b[^>]*/>',
                          lambda m: "" if "printerSettings" in m[0] else m[0], text)
        content[name] = text.encode()
    destination.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(destination, "w", zipfile.ZIP_DEFLATED) as archive:
        for name, data in content.items():
            archive.writestr(name, data)


def validate(root=ROOT):
    from oletools.olevba import VBA_Parser

    root = Path(root)
    for name in RELEASE_FILES:
        if not (root / name).is_file():
            raise ValueError(f"Missing release file: {name}")
    version = (root / "VERSION").read_text().strip()
    if not re.fullmatch(r"\d+\.\d+\.\d+", version):
        raise ValueError("VERSION must be a numeric major.minor.patch version")
    manifest = json.loads((root / "help/provenance.json").read_text())
    if {item["file"] for item in manifest} != {Path(p).name for p in HELP_FILES if p.endswith(".xll")}:
        raise ValueError("Unexpected add-in manifest entries")
    for item in manifest:
        data = (root / "help" / item["file"]).read_bytes()
        if sha256(data) != item["sha256"] or len(data) != item["bytes"]:
            raise ValueError(f"Add-in checksum mismatch: {item['file']}")
        if not item["source"].startswith("https://github.com/Excel-DNA/IntelliSense/releases/download/v1.9.0/"):
            raise ValueError("Unexpected add-in origin")
    workbook = root / "RSSO.xlsm"
    with zipfile.ZipFile(workbook) as archive:
        names = archive.namelist()
        for name in names:
            data = archive.read(name)
            if any(PRIVATE_PATH.search(data.decode(encoding, errors="ignore"))
                   for encoding in ("utf-8", "utf-16-le")):
                raise ValueError(f"Local machine path in workbook part: {name}")
            if name.endswith((".xml", ".rels")):
                parsed = ET.fromstring(data)
                if name.endswith(".rels") and any(child.attrib.get("TargetMode") == "External" for child in parsed):
                    raise ValueError(f"External workbook relationship: {name}")
            if name.startswith(("xl/externalLinks/", "xl/printerSettings/")) or name == "xl/connections.xml":
                raise ValueError(f"Unexpected external connection or printer setting: {name}")
        ns = {"s": "http://schemas.openxmlformats.org/spreadsheetml/2006/main"}
        document = ET.fromstring(archive.read("xl/workbook.xml"))
        sheets = [s.attrib["name"] for s in document.findall("s:sheets/s:sheet", ns)]
        if sheets != ["Examples"]:
            raise ValueError(f"Unexpected public workbook sheets: {sheets}")
        if not any(b'http://schemas.excel-dna.net/intellisense/1.0' in archive.read(n)
                   for n in names if n.startswith("customXml/item") and n.endswith(".xml")):
            raise ValueError("Embedded IntelliSense descriptions missing")
        core = ET.fromstring(archive.read("docProps/core.xml"))
        for child in core:
            if child.tag.rsplit("}", 1)[-1] in ("creator", "lastModifiedBy") and child.text != "RSSO contributors":
                raise ValueError("Personal workbook author metadata found")
    expected = {"RSSOFunctions.bas": "vba_source/RSSO.bas", "ThisWorkbook.cls": "vba_source/ThisWorkbook.txt"}
    parser = VBA_Parser(str(workbook))
    found = set()
    try:
        for _, _, name, code in parser.extract_macros():
            if PRIVATE_PATH.search(code):
                raise ValueError(f"Local path in VBA module: {name}")
            if name in expected:
                found.add(name)
                if normalize_vba(code) != normalize_vba((root / expected[name]).read_text(encoding="utf-8")):
                    raise ValueError(f"Workbook VBA differs from source: {name}")
            elif normalize_vba(code):
                raise ValueError(f"Unexpected executable VBA module: {name}")
    finally:
        parser.close()
    if found != set(expected):
        raise ValueError("Required VBA modules missing")
    report = json.loads((root / "docs/verification.json").read_text())
    if report["version"] != version:
        raise ValueError("Verification report version does not match VERSION")
    if report["source_sha256"] != source_hash(root) or report["workbook_sha256"] != sha256(workbook.read_bytes()):
        raise ValueError("Verification report does not match the current source and workbook")
    if not report["checks"] or not all(case["passed"] for case in report["checks"]):
        raise ValueError("Excel verification contains failures")
    if not report.get("reopened_in_excel") or not report.get("typing_addin_loaded_on_open"):
        raise ValueError("Workbook-open verification is missing")
    for name in RELEASE_FILES:
        if Path(name).suffix in (".md", ".txt", ".json", ".bas"):
            if PRIVATE_PATH.search((root / name).read_text(encoding="utf-8")):
                raise ValueError(f"Local path in public file: {name}")
    return {"version": version, "excel_checks": len(report["checks"]), "sheets": sheets}


def package(root=ROOT):
    root = Path(root)
    result = validate(root)
    output = root / "dist" / f"RSSO-v{result['version']}.zip"
    output.parent.mkdir(exist_ok=True)
    manifest = "".join(f"{sha256((root / name).read_bytes())}  {name}\n" for name in sorted(RELEASE_FILES))
    with zipfile.ZipFile(output, "w", zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for name in sorted(RELEASE_FILES):
            info = zipfile.ZipInfo(name, (2026, 1, 1, 0, 0, 0))
            info.compress_type = zipfile.ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            archive.writestr(info, (root / name).read_bytes())
        info = zipfile.ZipInfo("SHA256SUMS.txt", (2026, 1, 1, 0, 0, 0))
        info.compress_type = zipfile.ZIP_DEFLATED
        info.external_attr = 0o100644 << 16
        archive.writestr(info, manifest)
    checksum = output.with_suffix(".zip.sha256")
    checksum.write_text(f"{sha256(output.read_bytes())}  {output.name}\n", encoding="utf-8")
    return output


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("command", choices=("validate", "package"))
    args = parser.parse_args()
    if args.command == "package":
        print(package())
    else:
        print(json.dumps(validate(), indent=2))


if __name__ == "__main__":
    main()
