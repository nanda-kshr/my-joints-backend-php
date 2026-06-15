#!/usr/bin/env python3
from dast_common import append_record, auth_headers, ensure_auth_context, load_discovery, make_record, php_url, run_curl


def main():
    auth = ensure_auth_context()
    for ep in load_discovery():
        if ep["expected_role"] != "doctor":
            continue
        result = run_curl(ep["method"], php_url(ep["endpoint"]), headers=auth_headers(auth["patient_token"]))
        finding = result["status"] in {200, 201, 202, 204}
        append_record(
            make_record(
                ep["endpoint"],
                ep["method"],
                "patient",
                result["status"],
                [401, 403],
                "authz_privesc",
                "Patient token against doctor-only endpoint",
                result["response_time_ms"],
                result["body"],
                finding,
            )
        )


if __name__ == "__main__":
    main()
