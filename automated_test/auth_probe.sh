#!/bin/zsh
set -euo pipefail

BASE_URL="${1:-http://localhost:8000}"
STAMP="$(date +%s)"
PATIENT_EMAIL="dast_patient_${STAMP}@example.com"
DOCTOR_EMAIL="dast_doctor_${STAMP}@example.com"
PASSWORD="TestPass123!"

echo "Creating patient account at ${BASE_URL}"
curl -sS -X POST "${BASE_URL}/api/patient/signup.php" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"DAST Patient\",\"email\":\"${PATIENT_EMAIL}\",\"phone\":\"9999999999\",\"age\":30,\"weight\":70,\"sex\":\"M\",\"occupation\":\"QA\",\"address\":\"Test Lane\",\"password\":\"${PASSWORD}\"}" \
  -w "\n%{http_code} %{time_total}\n"

echo "Creating doctor account at ${BASE_URL}"
curl -sS -X POST "${BASE_URL}/api/doctor/signup.php" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"DAST Doctor\",\"email\":\"${DOCTOR_EMAIL}\",\"phone\":\"8888888888\",\"specialization\":\"Rheumatology\",\"address\":\"Clinic Road\",\"password\":\"${PASSWORD}\"}" \
  -w "\n%{http_code} %{time_total}\n"

echo "Signing in patient"
curl -sS -X POST "${BASE_URL}/api/auth/signin.php" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"${PATIENT_EMAIL}\",\"password\":\"${PASSWORD}\",\"role\":\"patient\"}" \
  -w "\n%{http_code} %{time_total}\n"

echo "Signing in doctor"
curl -sS -X POST "${BASE_URL}/api/auth/signin.php" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"${DOCTOR_EMAIL}\",\"password\":\"${PASSWORD}\",\"role\":\"doctor\"}" \
  -w "\n%{http_code} %{time_total}\n"
