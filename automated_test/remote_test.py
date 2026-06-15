#!/usr/bin/env python3
import json
import urllib.request
import urllib.error
import urllib.parse
import time
import os
import sys

BASE_URL = os.environ.get("BASE_URL", "http://180.235.121.253:8087/my_joints/api").rstrip("/")
TIMESTAMP = int(time.time())

PATIENT_EMAIL = f"patient_{TIMESTAMP}@test.com"
DOCTOR_EMAIL = f"doctor_{TIMESTAMP}@test.com"
PASSWORD = "Password123!"

results = []

def run_test(name, endpoint, method="GET", headers=None, body=None, expected_status=(200, 201)):
    url = f"{BASE_URL}/{endpoint.lstrip('/')}"
    headers = headers or {}
    req_headers = {"Content-Type": "application/json"}
    req_headers.update(headers)
    
    data = None
    if body is not None:
        data = json.dumps(body).encode("utf-8")
        
    req = urllib.request.Request(url, data=data, headers=req_headers, method=method)
    
    start_time = time.time()
    try:
        with urllib.request.urlopen(req, timeout=10) as response:
            status = response.status
            resp_body = response.read().decode("utf-8")
    except urllib.error.HTTPError as e:
        status = e.code
        resp_body = e.read().decode("utf-8")
    except Exception as e:
        status = 0
        resp_body = str(e)
        
    duration = int((time.time() - start_time) * 1000)
    
    parsed_body = None
    try:
        parsed_body = json.loads(resp_body)
    except Exception:
        parsed_body = resp_body
        
    passed = status in expected_status
    results.append({
        "name": name,
        "endpoint": endpoint,
        "method": method,
        "status": status,
        "expected": expected_status,
        "passed": passed,
        "duration_ms": duration,
        "body": parsed_body
    })
    return passed, status, parsed_body

def main():
    print(f"Starting API validation tests against target: {BASE_URL}\n")
    
    # 1. Patient signup
    p_signup_body = {
        "name": "Test Patient",
        "email": PATIENT_EMAIL,
        "phone": "9999999999",
        "age": 30,
        "weight": 70,
        "sex": "M",
        "occupation": "Tester",
        "address": "Test St 1",
        "password": PASSWORD
    }
    passed, _, _ = run_test("Patient Signup", "patient/signup.php", "POST", body=p_signup_body, expected_status=[201])
    
    # 2. Doctor signup
    d_signup_body = {
        "name": "Test Doctor",
        "email": DOCTOR_EMAIL,
        "phone": "8888888888",
        "specialization": "Rheumatology",
        "address": "Test Clinic 1",
        "password": PASSWORD
    }
    passed, _, _ = run_test("Doctor Signup", "doctor/signup.php", "POST", body=d_signup_body, expected_status=[201])
    
    # 3. Patient signin
    signin_p_body = {
        "email": PATIENT_EMAIL,
        "password": PASSWORD,
        "role": "patient"
    }
    passed, _, p_resp = run_test("Patient Signin", "auth/signin.php", "POST", body=signin_p_body, expected_status=[200])
    patient_token = p_resp.get("token") if passed and isinstance(p_resp, dict) else None
    patient_id = p_resp.get("user", {}).get("id") if passed and isinstance(p_resp, dict) else None
    
    # 4. Doctor signin
    signin_d_body = {
        "email": DOCTOR_EMAIL,
        "password": PASSWORD,
        "role": "doctor"
    }
    passed, _, d_resp = run_test("Doctor Signin", "auth/signin.php", "POST", body=signin_d_body, expected_status=[200])
    doctor_token = d_resp.get("token") if passed and isinstance(d_resp, dict) else None
    doctor_id = d_resp.get("user", {}).get("id") if passed and isinstance(d_resp, dict) else None
    
    # Headers for auth
    p_headers = {"Authorization": f"Bearer {patient_token}"} if patient_token else {}
    d_headers = {"Authorization": f"Bearer {doctor_token}"} if doctor_token else {}
    
    # 5. Patient Profile
    run_test("Get Patient Profile", "patient/profile.php", "GET", headers=p_headers, expected_status=[200])
    
    # 6. Doctor Profile
    run_test("Get Doctor Profile", "doctor/profile.php", "GET", headers=d_headers, expected_status=[200])
    
    # 7. Link Doctor & Patient
    link_body = {
        "patient_email": PATIENT_EMAIL
    }
    run_test("Link Doctor to Patient", "doctor/patient.php", "POST", headers=d_headers, body=link_body, expected_status=[200, 201])
    
    # 8. Doctor's Patients
    run_test("Get Doctor's Patient List", "doctor/patients.php", "GET", headers=d_headers, expected_status=[200])
    
    # 9. Send Consultation Request
    consult_body = {
        "patient_id": patient_id,
        "doctor_id": doctor_id,
        "message": "Hello, I need consultation."
    }
    run_test("Send Consult Request", "doctor/consult-request.php", "POST", headers=d_headers, body=consult_body, expected_status=[200])
    
    # 10. Patient's Assigned Doctors (should return the linked doctor)
    run_test("Get Patient's Assigned Doctors", f"patient/doctors.php?uid={patient_id}", "GET", headers=p_headers, expected_status=[200])
    
    # Print nice table/results summary
    print(f"{'Test Name':<30} | {'Method':<6} | {'Endpoint':<30} | {'Status':<6} | {'Result':<6} | {'Time (ms)'}")
    print("-" * 95)
    all_passed = True
    for res in results:
        res_text = "PASSED" if res["passed"] else "FAILED"
        if not res["passed"]:
            all_passed = False
        print(f"{res['name']:<30} | {res['method']:<6} | {res['endpoint']:<30} | {res['status']:<6} | {res_text:<6} | {res['duration_ms']}")
        
    print("\nSummary:")
    total = len(results)
    passed_cnt = sum(1 for r in results if r["passed"])
    print(f"Passed: {passed_cnt}/{total} ({(passed_cnt/total)*100:.2f}%)")
    
    if not all_passed:
        print("\nSome tests failed. Exiting with status code 1.")
        sys.exit(1)
    else:
        print("\nAll tests passed successfully.")
        sys.exit(0)

if __name__ == "__main__":
    main()
