#!/usr/bin/env python3
from dast_common import append_record, auth_headers, ensure_auth_context, make_record, php_url, run_curl, tamper_token


def main():
    auth = ensure_auth_context()
    checks = [
        ("/api/doctor/profile", "GET", tamper_token(auth["patient_token"], lambda d: d.update({"role": "doctor"})), "patient_role_flip"),
        ("/api/patient/profile", "GET", tamper_token(auth["doctor_token"], lambda d: d.update({"role": "patient"})), "doctor_role_flip"),
        ("/api/patient/profile", "GET", tamper_token(auth["patient_token"], lambda d: d.update({"id": auth["patient_id"] + 1000})), "subject_flip"),
    ]
    for endpoint, method, token, role in checks:
        result = run_curl(method, php_url(endpoint), headers=auth_headers(token))
        finding = result["status"] in {200, 201, 202, 204}
        append_record(make_record(endpoint, method, role, result["status"], [401], "token_tampering", "Tampered JWT must be rejected", result["response_time_ms"], result["body"], finding))


if __name__ == "__main__":
    main()
