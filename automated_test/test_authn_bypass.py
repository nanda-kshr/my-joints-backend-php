#!/usr/bin/env python3
from dast_common import append_record, auth_headers, ensure_auth_context, load_discovery, make_record, php_url, run_curl, tamper_token


def main():
    auth = ensure_auth_context()
    malformed = "Bearer definitely.not.a.jwt"
    expired = tamper_token(auth["patient_token"], lambda d: d.update({"exp": 1}))
    for ep in load_discovery():
        if ep["expected_access"] == "public":
            continue
        url = php_url(ep["endpoint"])
        variants = [
            ("none", {}, [401], "No token should be rejected"),
            ("malformed", {"Authorization": malformed}, [401], "Malformed token should be rejected"),
            ("expired", auth_headers(expired), [401], "Expired token should be rejected"),
        ]
        for role, headers, expected, note in variants:
            result = run_curl(ep["method"], url, headers=headers)
            finding = result["status"] in {200, 201, 202, 204}
            append_record(
                make_record(ep["endpoint"], ep["method"], role, result["status"], expected, "authn_bypass", note, result["response_time_ms"], result["body"], finding)
            )


if __name__ == "__main__":
    main()
