#!/bin/bash

# Test script for the Apartment Booking API

echo "Testing Apartment Booking API"

# Base URL
BASE_URL="http://localhost:8000/api"

# Test 1: Get apartments
echo "Test 1: Get apartments"
curl -X GET "$BASE_URL/apartments" -H "Accept: application/json"

echo -e "\n----------------------------------------\n"

# Test 2: Register a new user
echo "Test 2: Register a new user"
curl -X POST "$BASE_URL/register" -H "Accept: application/json" \
  -H "Content-Type: multipart/form-data" \
  -F "name=John Doe" \
  -F "email=john.doe@example.com" \
  -F "password=password123" \
  -F "password_confirmation=password123" \
  -F "mobile=1234567890" \
  -F "role=renter" \
  -F "id_image=@C:\Users\USER\install-laravel\first-app\my_first_app\sample_id.jpg"

echo -e "\n----------------------------------------\n"

# Test 3: Login
echo "Test 3: Login"
curl -X POST "$BASE_URL/login" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"mobile":"1234567890","password":"password123"}'

echo -e "\n----------------------------------------\n"

echo "API tests completed"