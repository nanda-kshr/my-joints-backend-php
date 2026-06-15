#!/usr/bin/env python3
from dast_common import append_record, make_record, php_url, run_curl


def main():
    throttled = False
    for idx in range(30):
        result = run_curl(
            "POST",
            php_url("/api/auth/signin"),
            body={"email": f"nobody{idx}@example.com", "password": "wrong", "role": "patient"},
            max_time=5,
        )
        if result["status"] == 429:
            throttled = True
        append_record(make_record("/api/auth/signin", "POST", "public", result["status"], [401, 429], "rate_limiting", f"Burst signin attempt {idx + 1}/30", result["response_time_ms"], result["body"], False))
    if not throttled:
        append_record(make_record("/api/auth/signin", "POST", "public", 401, [429], "rate_limiting", "No rate limiting observed during 30-request burst", 0, "", True))


if __name__ == "__main__":
    main()
