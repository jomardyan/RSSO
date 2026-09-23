from pathlib import Path
import json
import shutil
import sys
import tempfile
import unittest
import zipfile
import xml.etree.ElementTree as ET

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
import release_tools as release


class SanitizationTests(unittest.TestCase):
    def test_removes_private_metadata_and_printer_links_preserving_macros(self):
        with tempfile.TemporaryDirectory() as directory:
            source, result = Path(directory) / "in.xlsm", Path(directory) / "out.xlsm"
            payload = {
                "docProps/core.xml": b'<properties><creator>Private Author</creator></properties>',
                "docProps/app.xml": b'<Properties><Company>Private Company</Company><Manager>Private Person</Manager></Properties>',
                "xl/workbook.xml": b'<workbook xmlns:mc="urn:mc" xmlns:x15ac="urn:x15ac"><mc:AlternateContent><mc:Choice><x15ac:absPath url="C:\\Users\\PrivateUser\\Work\\"/></mc:Choice></mc:AlternateContent></workbook>',
                "xl/comments1.xml": b'<comments><authors><author>Private Author</author></authors></comments>',
                "xl/worksheets/sheet1.xml": b'<worksheet xmlns:r="urn:relationships"><pageSetup r:id="rId1"/></worksheet>',
                "xl/worksheets/_rels/sheet1.xml.rels": b'<Relationships><Relationship Id="rId1" Type="urn:office/printerSettings" Target="../printerSettings/printerSettings1.bin"/></Relationships>',
                "xl/printerSettings/printerSettings1.bin": b'private printer data',
                "[Content_Types].xml": b'<Types><Default Extension="bin" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.printerSettings"/><Override PartName="/xl/vbaProject.bin" ContentType="application/vnd.ms-office.vbaProject"/></Types>',
                "xl/vbaProject.bin": b'preserve VBA binary exactly',
            }
            with zipfile.ZipFile(source, "w") as archive:
                for name, data in payload.items():
                    archive.writestr(name, data)
            release.sanitize_workbook(source, result)
            with zipfile.ZipFile(result) as archive:
                self.assertEqual(archive.read("xl/vbaProject.bin"), payload["xl/vbaProject.bin"])
                self.assertNotIn("xl/printerSettings/printerSettings1.bin", archive.namelist())
                self.assertNotIn(b'r:id=', archive.read("xl/worksheets/sheet1.xml"))
                for name in archive.namelist():
                    data = archive.read(name)
                    self.assertNotIn(b'Private', data, name)
                    self.assertNotIn(b'printerSettings', data, name)
                    if name.endswith((".xml", ".rels")):
                        ET.fromstring(data)
            self.assertEqual(source.read_bytes()[:2], b'PK')

    def test_refuses_in_place_sanitization(self):
        with self.assertRaises(ValueError):
            release.sanitize_workbook("same.xlsm", "same.xlsm")

    def test_vba_comparison_ignores_identifier_case_but_preserves_strings(self):
        self.assertEqual(release.normalize_vba('Attribute VB_Name = "Example"\r\nDim Basis As String'),
                         release.normalize_vba('Dim basis As String'))
        self.assertNotEqual(release.normalize_vba('x = "LEGAL"'), release.normalize_vba('x = "legal"'))

    def test_source_hash_is_independent_of_checkout_line_endings(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            for name in release.SOURCE_FILES:
                target = root / name
                target.parent.mkdir(exist_ok=True)
                target.write_bytes(b'Option Explicit\nTest\n')
            expected = release.source_hash(root)
            for name in release.SOURCE_FILES:
                (root / name).write_bytes(b'Option Explicit\r\nTest\r\n')
            self.assertEqual(release.source_hash(root), expected)


class ReleaseTests(unittest.TestCase):
    def setUp(self):
        self.directory = tempfile.TemporaryDirectory()
        self.addCleanup(self.directory.cleanup)
        self.root = Path(self.directory.name)
        for name in release.RELEASE_FILES:
            target = self.root / name
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(release.ROOT / name, target)

    def test_rejects_modified_addin(self):
        binary = self.root / "help/ExcelDna.IntelliSense.xll"
        binary.write_bytes(binary.read_bytes() + b'tampered')
        with self.assertRaisesRegex(ValueError, "checksum mismatch"):
            release.validate(self.root)

    def test_rejects_modified_vba_source(self):
        source = self.root / "vba_source/RSSO.bas"
        source.write_text(source.read_text() + '\nPublic Sub Unexpected()\nEnd Sub\n')
        with self.assertRaisesRegex(ValueError, "differs from source"):
            release.validate(self.root)

    def test_rejects_mismatched_verification(self):
        path = self.root / "docs/verification.json"
        report = json.loads(path.read_text())
        report["workbook_sha256"] = "0" * 64
        path.write_text(json.dumps(report))
        with self.assertRaisesRegex(ValueError, "does not match"):
            release.validate(self.root)

    def test_package_is_repeatable_and_excludes_private_files(self):
        private = self.root / ".local" / "private.txt"
        private.parent.mkdir()
        private.write_text("private sample")
        output = release.package(self.root)
        first = output.read_bytes()
        self.assertEqual(release.package(self.root).read_bytes(), first)
        with zipfile.ZipFile(output) as archive:
            self.assertEqual(set(archive.namelist()), set(release.RELEASE_FILES) | {"SHA256SUMS.txt"})
            manifest = archive.read("SHA256SUMS.txt").decode()
            for line in manifest.splitlines():
                expected, name = line.split("  ", 1)
                self.assertEqual(release.sha256(archive.read(name)), expected)


if __name__ == "__main__":
    unittest.main()
