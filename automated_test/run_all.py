#!/usr/bin/env python3
import json
import subprocess
from collections import Counter
from pathlib import Path

from dast_common import AUTOMATED_DIR, REPORT_PATH, SAVEPOINT_PATH, ensure_auth_context, load_discovery, load_report, mask_token, set_savepoint, load_savepoint


SCRIPTS = [
    "test_authn_bypass.py",
    "test_authz_privesc.py",
    "test_idor.py",
    "test_rbac_matrix.py",
    "test_token_tampering.py",
    "test_injection_probe.py",
    "test_rate_limiting.py",
    "test_hardcoded_creds.py",
]


def main():
    REPORT_PATH.write_text("[]")
    auth = ensure_auth_context()
    save = {
        "phase": "testing",
        "server": {"host": "localhost", "port": 8000, "started": True},
        "discovery": {"completed": True, "endpoint_count": len(load_discovery())},
        "auth": {
            "patient_created": True,
            "doctor_created": True,
            "patient_token_captured": True,
            "doctor_token_captured": True,
            "patient_email": auth["patient_email"],
            "doctor_email": auth["doctor_email"],
            "patient_token_masked": mask_token(auth["patient_token"]),
            "doctor_token_masked": mask_token(auth["doctor_token"]),
        },
        "auth_context": auth,
    }
    save["auth_context"]["patient_token"] = auth["patient_token"]
    save["auth_context"]["doctor_token"] = auth["doctor_token"]
    set_savepoint(save)

    for script in SCRIPTS:
        subprocess.run(["python3", str(AUTOMATED_DIR / script)], cwd=AUTOMATED_DIR, check=True)

    sanitized = load_savepoint()
    if sanitized.get("auth_context"):
        sanitized["auth_context"] = {
            "patient_email": auth["patient_email"],
            "doctor_email": auth["doctor_email"],
            "patient_id": auth["patient_id"],
            "doctor_id": auth["doctor_id"],
            "masked": auth["masked"],
            "signup_statuses": auth["signup_statuses"],
        }
        set_savepoint(sanitized)

    report = load_report()
    counts = Counter(rec["severity"] for rec in report if rec["finding"])
    print(json.dumps({
        "endpoints_discovered": len(load_discovery()),
        "tests_run": len(report),
        "findings_by_severity": counts,
    }, indent=2, default=dict))


if __name__ == "__main__":
    main()
