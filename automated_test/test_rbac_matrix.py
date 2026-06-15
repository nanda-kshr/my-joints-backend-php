#!/usr/bin/env python3
from dast_common import append_record, auth_headers, ensure_auth_context, link_doctor_patient, load_discovery, make_record, php_url, query_url, run_curl


def choose_url(endpoint: str, method: str, auth: dict, role: str) -> str:
    patient_endpoints = {
        "/api/patient/doctors",
        "/api/patient/investigation",
        "/api/patient/referrals",
        "/api/patient/treatments",
        "/api/patient/comorbidities",
        "/api/patient/disease_score",
        "/api/patient/medications",
        "/api/patient/complaints",
    }
    if method == "GET" and endpoint in patient_endpoints:
        uid = auth["patient_id"]
        return query_url(endpoint, {"uid": uid})
    return php_url(endpoint)


def main():
    auth = ensure_auth_context()
    link_doctor_patient(auth)
    roles = [
        ("patient", auth["patient_token"]),
        ("doctor", auth["doctor_token"]),
    ]
    for ep in load_discovery():
        if ep["expected_access"] == "public":
            continue
        for role, token in roles:
            expected = [200, 201, 400, 404] if (
                ep["expected_access"] == "requires-auth"
                or ep["expected_role"] == role
                or (role == "doctor" and ep["endpoint"].startswith("/api/patient/") and ep["method"] == "GET")
            ) else [401, 403]
            result = run_curl(ep["method"], choose_url(ep["endpoint"], ep["method"], auth, role), headers=auth_headers(token))
            finding = False
            if expected == [401, 403] and result["status"] in {200, 201, 202, 204}:
                finding = True
            append_record(make_record(ep["endpoint"], ep["method"], role, result["status"], expected, "rbac_matrix", "Role/method matrix check", result["response_time_ms"], result["body"], finding))


if __name__ == "__main__":
    main()
