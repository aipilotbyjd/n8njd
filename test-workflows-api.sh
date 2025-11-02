#!/bin/bash

BASE_URL="http://n8njd.test/api/v1"
TOKEN=""

echo "=== Testing Workflow APIs with cURL ==="
echo ""

# 1. Register a user
echo "1. Register User"
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }')
echo "$REGISTER_RESPONSE" | jq '.'
echo ""

# 2. Login
echo "2. Login"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }')
echo "$LOGIN_RESPONSE" | jq '.'
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token // .access_token // empty')
echo "Token: $TOKEN"
echo ""

if [ -z "$TOKEN" ]; then
  echo "Failed to get token. Exiting."
  exit 1
fi

# 3. Create Workflow
echo "3. Create Workflow"
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/workflows" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Workflow",
    "description": "A test workflow",
    "nodes": [
      {
        "id": "node1",
        "type": "trigger",
        "position": {"x": 100, "y": 100}
      }
    ],
    "connections": []
  }')
echo "$CREATE_RESPONSE" | jq '.'
WORKFLOW_ID=$(echo "$CREATE_RESPONSE" | jq -r '.data.id // .id // empty')
echo "Workflow ID: $WORKFLOW_ID"
echo ""

if [ -z "$WORKFLOW_ID" ]; then
  echo "Failed to create workflow. Exiting."
  exit 1
fi

# 4. Get All Workflows
echo "4. Get All Workflows"
curl -s -X GET "$BASE_URL/workflows" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
echo ""

# 5. Get Single Workflow
echo "5. Get Single Workflow"
curl -s -X GET "$BASE_URL/workflows/$WORKFLOW_ID" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
echo ""

# 6. Update Workflow
echo "6. Update Workflow"
curl -s -X PUT "$BASE_URL/workflows/$WORKFLOW_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Test Workflow",
    "description": "Updated description"
  }' | jq '.'
echo ""

# 7. Activate Workflow
echo "7. Activate Workflow"
curl -s -X PATCH "$BASE_URL/workflows/$WORKFLOW_ID/activate" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
echo ""

# 8. Deactivate Workflow
echo "8. Deactivate Workflow"
curl -s -X PATCH "$BASE_URL/workflows/$WORKFLOW_ID/deactivate" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
echo ""

# 9. Duplicate Workflow
echo "9. Duplicate Workflow"
DUPLICATE_RESPONSE=$(curl -s -X POST "$BASE_URL/workflows/$WORKFLOW_ID/duplicate" \
  -H "Authorization: Bearer $TOKEN")
echo "$DUPLICATE_RESPONSE" | jq '.'
DUPLICATE_ID=$(echo "$DUPLICATE_RESPONSE" | jq -r '.data.id // .id // empty')
echo ""

# 10. Validate Workflow
echo "10. Validate Workflow"
curl -s -X POST "$BASE_URL/workflows/$WORKFLOW_ID/validate" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
echo ""

# 11. Export Workflow
echo "11. Export Workflow"
curl -s -X GET "$BASE_URL/workflows/$WORKFLOW_ID/export" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
echo ""

# 12. Delete Duplicate Workflow
if [ ! -z "$DUPLICATE_ID" ]; then
  echo "12. Delete Duplicate Workflow"
  curl -s -X DELETE "$BASE_URL/workflows/$DUPLICATE_ID" \
    -H "Authorization: Bearer $TOKEN" | jq '.'
  echo ""
fi

# 13. Delete Original Workflow
echo "13. Delete Original Workflow"
curl -s -X DELETE "$BASE_URL/workflows/$WORKFLOW_ID" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
echo ""

echo "=== Testing Complete ==="
