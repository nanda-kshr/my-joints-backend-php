#!/usr/bin/env python3
import json
import subprocess
from pathlib import Path

from dast_common import ROOT, append_record, make_record


def main():
    tracked = subprocess.run(["git", "ls-files", ".env", ".env.example", "postman.json"], cwd=ROOT, capture_output=True, text=True, check=True).stdout.splitlines()
    findings = []
    env_path = ROOT / ".env"
    if ".env" in tracked and env_path.exists():
        keys = []
        for line in env_path.read_text().splitlines():
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            key, value = line.split("=", 1)
            if value.strip():
                keys.append(f"{key.strip()}(set)")
        if keys:
            findings.append(f".env tracked with populated keys: {', '.join(keys[:8])}")
    append_record(
        make_record(
            "/codebase",
            "SCAN",
            "n/a",
            200,
            [0],
            "hardcoded_creds",
            f"Tracked secret exposure candidates: {json.dumps(findings[:10])}",
            0,
            "\n".join(findings),
            bool(findings),
        )
    )


if __name__ == "__main__":
    main()
