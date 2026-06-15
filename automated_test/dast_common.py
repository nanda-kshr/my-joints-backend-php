#!/usr/bin/env python3
import base64
import json
import os
import subprocess
import time
from datetime import datetime, timezone
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple
from urllib.parse import urlencode


ROOT = Path(__file__).resolve().parents[1]
AUTOMATED_DIR = ROOT / "automated_test"
REPORT_PATH = AUTOMATED_DIR / "report.json"
SAVEPOINT_PATH = AUTOMATED_DIR / "savepoint.json"
DISCOVERY_SCRIPT = AUTOMATED_DIR / "discover_endpoints.py"
BASE_URL = os.environ.get("BASE_URL", "http://localhost:8000")
ENDPOINT_STYLE = os.environ.get("ENDPOINT_STYLE", "php")
PASSWORD = "TestPass123!"
EXPECTATION_OVERRIDES = {
    ("/api/patient/comorbidities", "GET"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/comorbidities", "POST"): {"expected_access": "role-restricted", "expected_role": "doctor"},
    ("/api/patient/disease_score", "GET"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/disease_score", "POST"): {"expected_access": "role-restricted", "expected_role": "doctor"},
    ("/api/patient/disease_score", "DELETE"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/investigation", "GET"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/investigation", "POST"): {"expected_access": "role-restricted", "expected_role": "doctor"},
    ("/api/patient/investigation", "DELETE"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/medications", "GET"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/medications", "POST"): {"expected_access": "role-restricted", "expected_role": "doctor"},
    ("/api/patient/medications", "DELETE"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/referrals", "GET"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/referrals", "POST"): {"expected_access": "role-restricted", "expected_role": "doctor"},
    ("/api/patient/referrals", "DELETE"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/treatments", "GET"): {"expected_access": "requires-auth", "expected_role": None},
    ("/api/patient/treatments", "POST"): {"expected_access": "role-restricted", "expected_role": "doctor"},
    ("/api/patient/treatments", "DELETE"): {"expected_access": "requires-auth", "expected_role": None},
}


def now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def php_url(path: str) -> str:
    clean = path.lstrip("/")
    if ENDPOINT_STYLE == "php":
        return f"{BASE_URL}/{clean}.php"
    return f"{BASE_URL}/{clean}"


def run_curl(
    method: str,
    url: str,
    headers: Optional[Dict[str, str]] = None,
    body: Optional[Dict[str, Any]] = None,
    max_time: int = 10,
) -> Dict[str, Any]:
    cmd = [
        "curl",
        "-sS",
        "-X",
        method,
        url,
        "-w",
        "\n%{http_code} %{time_total}",
        "--max-time",
        str(max_time),
    ]
    for key, value in (headers or {}).items():
        cmd.extend(["-H", f"{key}: {value}"])
    if body is not None:
        cmd.extend(["-H", "Content-Type: application/json", "-d", json.dumps(body)])
    started = time.time()
    proc = subprocess.run(cmd, capture_output=True, text=True)
    elapsed = int((time.time() - started) * 1000)
    if proc.returncode != 0:
        return {
            "status": 0,
            "response_time_ms": elapsed,
            "body": proc.stdout + proc.stderr,
            "error": proc.stderr.strip() or f"curl exit {proc.returncode}",
            "url": url,
            "method": method,
        }
    out = proc.stdout
    body_text, _, trailer = out.rpartition("\n")
    status = 0
    total = elapsed
    parts = trailer.strip().split()
    if len(parts) >= 2:
        try:
            status = int(parts[0])
            total = int(float(parts[1]) * 1000)
        except ValueError:
            pass
    return {
        "status": status,
        "response_time_ms": total,
        "body": body_text,
        "error": "",
        "url": url,
        "method": method,
    }


def load_discovery() -> List[Dict[str, Any]]:
    proc = subprocess.run(
        ["python3", str(DISCOVERY_SCRIPT)],
        cwd=ROOT,
        capture_output=True,
        text=True,
        check=True,
    )
    endpoints = json.loads(proc.stdout)["endpoints"]
    for ep in endpoints:
        override = EXPECTATION_OVERRIDES.get((ep["endpoint"], ep["method"]))
        if override:
            ep.update(override)
    return endpoints


def load_report() -> List[Dict[str, Any]]:
    if REPORT_PATH.exists():
        return json.loads(REPORT_PATH.read_text())
    return []


def save_report(records: List[Dict[str, Any]]) -> None:
    REPORT_PATH.write_text(json.dumps(records, indent=2))


def append_record(record: Dict[str, Any]) -> None:
    records = load_report()
    records.append(record)
    save_report(records)


def mask_token(token: str) -> str:
    if len(token) < 12:
        return "***"
    return f"{token[:8]}...{token[-8:]}"


def set_savepoint(data: Dict[str, Any]) -> None:
    SAVEPOINT_PATH.write_text(json.dumps(data, indent=2))


def load_savepoint() -> Dict[str, Any]:
    if SAVEPOINT_PATH.exists():
        return json.loads(SAVEPOINT_PATH.read_text())
    return {}


def severity_for_finding(category: str, finding: bool) -> str:
    if not finding:
        return "info"
    mapping = {
        "authn_bypass": "critical",
        "authz_privesc": "high",
        "idor": "high",
        "rbac_matrix": "high",
        "token_tampering": "critical",
        "injection_probe": "medium",
        "rate_limiting": "medium",
        "hardcoded_creds": "high",
    }
    return mapping.get(category, "medium")


def expected_status_text(values: List[int]) -> str:
    return "/".join(str(v) for v in values)


def make_record(
    endpoint: str,
    method: str,
    role: str,
    status: int,
    expected_statuses: List[int],
    category: str,
    note: str,
    response_time_ms: int,
    body: str,
    finding: bool,
) -> Dict[str, Any]:
    return {
        "endpoint": endpoint,
        "method": method,
        "role": role,
        "status": status,
        "expected_status": expected_status_text(expected_statuses),
        "finding": finding,
        "severity": severity_for_finding(category, finding),
        "response_time_ms": response_time_ms,
        "test_category": category,
        "note": note,
        "timestamp": now_iso(),
        "body_preview": body[:240],
    }


def auth_headers(token: Optional[str]) -> Dict[str, str]:
    if not token:
        return {}
    return {"Authorization": f"Bearer {token}"}


def jwt_payload(token: str) -> Dict[str, Any]:
    parts = token.split(".")
    payload = parts[1] + "=" * (-len(parts[1]) % 4)
    return json.loads(base64.urlsafe_b64decode(payload))


def tamper_token(token: str, mutate) -> str:
    header, payload, signature = token.split(".")
    data = jwt_payload(token)
    mutate(data)
    new_payload = base64.urlsafe_b64encode(json.dumps(data, separators=(",", ":")).encode()).decode().rstrip("=")
    return ".".join([header, new_payload, signature])


def ensure_auth_context() -> Dict[str, Any]:
    savepoint = load_savepoint()
    auth = savepoint.get("auth_context")
    if auth and auth.get("patient_token") and auth.get("doctor_token"):
        return auth

    stamp = int(time.time())
    patient_email = f"dast_patient_{stamp}@example.com"
    doctor_email = f"dast_doctor_{stamp}@example.com"
    patient_signup = run_curl(
        "POST",
        php_url("/api/patient/signup"),
        body={
            "name": "DAST Patient",
            "email": patient_email,
            "phone": "9999999999",
            "age": 30,
            "weight": 70,
            "sex": "M",
            "occupation": "QA",
            "address": "Test Lane",
            "password": PASSWORD,
        },
    )
    doctor_signup = run_curl(
        "POST",
        php_url("/api/doctor/signup"),
        body={
            "name": "DAST Doctor",
            "email": doctor_email,
            "phone": "8888888888",
            "specialization": "Rheumatology",
            "address": "Clinic Road",
            "password": PASSWORD,
        },
    )
    patient_signin = run_curl(
        "POST",
        php_url("/api/auth/signin"),
        body={"email": patient_email, "password": PASSWORD, "role": "patient"},
    )
    doctor_signin = run_curl(
        "POST",
        php_url("/api/auth/signin"),
        body={"email": doctor_email, "password": PASSWORD, "role": "doctor"},
    )
    if patient_signin["status"] != 200 or doctor_signin["status"] != 200:
        raise RuntimeError(
            f"Failed to sign in test users. patient={patient_signin['status']} doctor={doctor_signin['status']}"
        )
    patient_json = json.loads(patient_signin["body"])
    doctor_json = json.loads(doctor_signin["body"])
    auth = {
        "patient_email": patient_email,
        "doctor_email": doctor_email,
        "patient_token": patient_json["token"],
        "doctor_token": doctor_json["token"],
        "patient_id": patient_json["user"]["id"],
        "doctor_id": doctor_json["user"]["id"],
        "masked": {
            "patient_token": mask_token(patient_json["token"]),
            "doctor_token": mask_token(doctor_json["token"]),
        },
        "signup_statuses": {
            "patient": patient_signup["status"],
            "doctor": doctor_signup["status"],
        },
    }
    savepoint["auth_context"] = auth
    set_savepoint(savepoint)
    return auth


def link_doctor_patient(auth: Dict[str, Any]) -> Dict[str, Any]:
    return run_curl(
        "POST",
        php_url("/api/doctor/patient"),
        headers=auth_headers(auth["doctor_token"]),
        body={"patient_email": auth["patient_email"]},
    )


def query_url(path: str, params: Dict[str, Any]) -> str:
    return f"{php_url(path)}?{urlencode(params)}"
