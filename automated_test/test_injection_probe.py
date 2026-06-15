#!/usr/bin/env python3
from dast_common import append_record, auth_headers, ensure_auth_context, make_record, php_url, query_url, run_curl


PAYLOADS = [
    "' OR '1'='1",
    "\" OR \"1\"=\"1",
    "';SELECT SLEEP(2)--",
]


def main():
    auth = ensure_auth_context()
    checks = []
    for payload in PAYLOADS:
        checks.extend(
            [
                ("/api/auth/signin", "POST", "public", None, {"email": payload, "password": "x", "role": "patient"}),
                ("/api/patient/doctors", "GET", "patient", auth["patient_token"], {"uid": payload}),
                ("/api/patient/pain-assessment", "GET", "public", None, {"uid": payload}),
            ]
        )
    for endpoint, method, role, token, data in checks:
        if method == "POST":
            result = run_curl(method, php_url(endpoint), headers=auth_headers(token), body=data)
        else:
            result = run_curl(method, query_url(endpoint, data), headers=auth_headers(token))
        body_lower = result["body"].lower()
        suspicious = result["response_time_ms"] > 1800 or "sql" in body_lower or "syntax" in body_lower or result["status"] >= 500
        append_record(make_record(endpoint, method, role, result["status"], [400, 401], "injection_probe", f"Detection payload: {next(iter(data.values()))}", result["response_time_ms"], result["body"], suspicious))


if __name__ == "__main__":
    main()
