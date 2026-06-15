#!/usr/bin/env python3
from dast_common import append_record, auth_headers, ensure_auth_context, link_doctor_patient, make_record, query_url, run_curl


def main():
    auth = ensure_auth_context()
    link_doctor_patient(auth)
    outsider = auth["patient_id"] + 999999

    checks = [
        ("/api/patient/doctors", "GET", "patient", auth["patient_token"], {"uid": outsider}, [400, 403], "Patient should not enumerate another patient's doctors"),
        ("/api/patient/investigation", "GET", "patient", auth["patient_token"], {"uid": outsider}, [400, 403], "Patient should not read another patient's investigations"),
        ("/api/patient/referrals", "GET", "patient", auth["patient_token"], {"uid": outsider}, [400, 403], "Patient should not read another patient's referrals"),
        ("/api/patient/treatments", "GET", "patient", auth["patient_token"], {"uid": outsider}, [400, 403], "Patient should not read another patient's treatments"),
        ("/api/patient/comorbidities", "GET", "patient", auth["patient_token"], {"uid": outsider}, [400, 403], "Patient should not read another patient's comorbidities"),
        ("/api/patient/disease_score", "GET", "patient", auth["patient_token"], {"uid": outsider}, [400, 403], "Patient should not read another patient's disease score"),
        ("/api/patient/medications", "GET", "patient", auth["patient_token"], {"uid": outsider}, [400, 403], "Patient should not read another patient's medications"),
        ("/api/patient/complaints", "GET", "patient", auth["patient_token"], {"uid": outsider}, [200, 403], "Patient route should not disclose another patient's complaint data"),
        ("/api/patient/doctors", "GET", "doctor", auth["doctor_token"], {"uid": outsider}, [403], "Doctor should be blocked from unassigned patient"),
        ("/api/patient/investigation", "GET", "doctor", auth["doctor_token"], {"uid": outsider}, [403], "Doctor should be blocked from unassigned patient"),
    ]

    for endpoint, method, role, token, params, expected, note in checks:
        result = run_curl(method, query_url(endpoint, params), headers=auth_headers(token))
        body = result["body"].lower()
        finding = result["status"] in {200, 201, 202, 204} and ("[]" not in body or role == "doctor")
        append_record(make_record(endpoint, method, role, result["status"], expected, "idor", note, result["response_time_ms"], result["body"], finding))


if __name__ == "__main__":
    main()
