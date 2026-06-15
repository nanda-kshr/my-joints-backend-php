#!/usr/bin/env python3
import json
import re
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
API_ROOT = ROOT / "api"

METHOD_GUARD_PATTERN = re.compile(r"getRequestMethod\(\)\s*!==\s*'([A-Z]+)'")
METHOD_BRANCH_PATTERN = re.compile(r"\$method\s*===\s*'([A-Z]+)'")
INLINE_METHOD_PATTERN = re.compile(r"getRequestMethod\(\)\s*===\s*'([A-Z]+)'")
AUTH_PATTERNS = [
    ("doctor", re.compile(r"JWT::requireDoctorAuth\(")),
    ("patient", re.compile(r"JWT::requirePatientAuth\(")),
    ("auth", re.compile(r"JWT::requireAuth\(")),
]


def infer_methods(text: str):
    methods = set(METHOD_GUARD_PATTERN.findall(text))
    methods.update(METHOD_BRANCH_PATTERN.findall(text))
    methods.update(INLINE_METHOD_PATTERN.findall(text))
    return sorted(methods)


def infer_auth(text: str):
    if re.search(r"JWT::requireDoctorAuth\(", text):
        return {"access": "role-restricted", "role": "doctor"}
    if re.search(r"JWT::requirePatientAuth\(", text):
        return {"access": "role-restricted", "role": "patient"}
    if re.search(r"JWT::requireAuth\(", text):
        return {"access": "requires-auth", "role": None}
    return {"access": "public", "role": None}


def main():
    endpoints = []
    for path in sorted(API_ROOT.rglob("*.php")):
        rel = path.relative_to(API_ROOT)
        if rel.name == "index.php":
            route = "/api/" + "/".join(rel.parts[:-1])
        else:
            route = "/api/" + "/".join(rel.with_suffix("").parts)

        if any(part in {"health", "actuator", "metrics"} for part in rel.parts):
            continue

        text = path.read_text(encoding="utf-8")
        methods = infer_methods(text)
        auth = infer_auth(text)

        for method in methods:
            endpoints.append(
                {
                    "endpoint": route,
                    "method": method,
                    "source": str(rel),
                    "expected_access": auth["access"],
                    "expected_role": auth["role"],
                }
            )

    output = {
        "count": len(endpoints),
        "endpoints": endpoints,
    }
    print(json.dumps(output, indent=2))


if __name__ == "__main__":
    main()
