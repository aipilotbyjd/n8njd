# n8njd API Documentation

Base URL: `http://n8njd.test/api/v1`

All authenticated endpoints require an `Authorization` header with a Bearer token.

---

## Authentication

### Register
```bash
curl --location 'http://n8njd.test/api/v1/auth/register' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}'
```

**Response (201):**
```json
{
    "status": "success",
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "created_at": "2024-01-15T10:30:00.000000Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
    }
}
```

### Login
```bash
curl --location 'http://n8njd.test/api/v1/auth/login' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
    "email": "john@example.com",
    "password": "password123"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

### Get Current User
```bash
curl --location 'http://n8njd.test/api/v1/auth/me' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": "2024-01-15T10:30:00.000000Z",
        "created_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

### Logout
```bash
curl --location --request POST 'http://n8njd.test/api/v1/auth/logout' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Successfully logged out"
}
```

---

## Workflows

### List Workflows
```bash
curl --location 'http://n8njd.test/api/v1/workflows?per_page=15&page=1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "My First Workflow",
            "description": "A simple workflow",
            "status": "active",
            "organization_id": 1,
            "created_by": 1,
            "settings": {},
            "last_executed_at": "2024-01-15T14:30:00.000000Z",
            "execution_count": 42,
            "is_active": true,
            "created_at": "2024-01-10T10:00:00.000000Z",
            "updated_at": "2024-01-15T14:30:00.000000Z"
        }
    ],
    "first_page_url": "http://n8njd.test/api/v1/workflows?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "http://n8njd.test/api/v1/workflows?page=3",
    "next_page_url": "http://n8njd.test/api/v1/workflows?page=2",
    "path": "http://n8njd.test/api/v1/workflows",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 42
}
```

### Get Single Workflow
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "id": 1,
    "name": "My First Workflow",
    "description": "A simple workflow",
    "status": "active",
    "organization_id": 1,
    "created_by": 1,
    "settings": {
        "timeout": 300,
        "retry_on_failure": true
    },
    "last_executed_at": "2024-01-15T14:30:00.000000Z",
    "execution_count": 42,
    "is_active": true,
    "created_at": "2024-01-10T10:00:00.000000Z",
    "updated_at": "2024-01-15T14:30:00.000000Z"
}
```

### Create Workflow
```bash
curl --location 'http://n8njd.test/api/v1/workflows' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "name": "New Workflow",
    "description": "My new workflow",
    "nodes": [],
    "connections": [],
    "settings": {
        "timeout": 300
    }
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 2,
        "name": "New Workflow",
        "description": "My new workflow",
        "status": "draft",
        "organization_id": 1,
        "created_by": 1,
        "settings": {
            "timeout": 300
        },
        "last_executed_at": null,
        "execution_count": 0,
        "is_active": true,
        "created_at": "2024-01-15T15:00:00.000000Z",
        "updated_at": "2024-01-15T15:00:00.000000Z"
    }
}
```

### Update Workflow
```bash
curl --location --request PUT 'http://n8njd.test/api/v1/workflows/1' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "name": "Updated Workflow Name",
    "description": "Updated description"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "Updated Workflow Name",
        "description": "Updated description",
        "status": "active",
        "organization_id": 1,
        "created_by": 1,
        "settings": {},
        "updated_at": "2024-01-15T15:30:00.000000Z"
    }
}
```

### Delete Workflow
```bash
curl --location --request DELETE 'http://n8njd.test/api/v1/workflows/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (204):**
```
No Content
```

### Activate Workflow
```bash
curl --location --request PATCH 'http://n8njd.test/api/v1/workflows/1/activate' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "My Workflow",
        "status": "active",
        "is_active": true
    }
}
```

### Deactivate Workflow
```bash
curl --location --request PATCH 'http://n8njd.test/api/v1/workflows/1/deactivate' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "My Workflow",
        "status": "inactive",
        "is_active": false
    }
}
```

### Duplicate Workflow
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/duplicate' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 3,
        "name": "My Workflow - copy",
        "description": "A simple workflow",
        "status": "draft",
        "created_at": "2024-01-15T16:00:00.000000Z"
    }
}
```

### Execute Workflow
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/execute' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "input_data": {
        "key": "value"
    }
}'
```

**Response (202):**
```json
{
    "status": "success",
    "message": "Workflow execution started",
    "data": {
        "execution_id": 123,
        "workflow_id": 1,
        "status": "running",
        "started_at": "2024-01-15T16:30:00.000000Z"
    }
}
```

---

## Executions

### List Executions
```bash
curl --location 'http://n8njd.test/api/v1/executions?per_page=20&page=1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 123,
            "workflow_id": 1,
            "status": "success",
            "mode": "manual",
            "triggered_by": 1,
            "started_at": "2024-01-15T16:30:00.000000Z",
            "finished_at": "2024-01-15T16:30:15.000000Z",
            "execution_time_ms": 15000,
            "error_message": null
        }
    ],
    "per_page": 20,
    "total": 150
}
```

### Get Execution Details
```bash
curl --location 'http://n8njd.test/api/v1/executions/123' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "id": 123,
        "workflow_id": 1,
        "status": "success",
        "mode": "manual",
        "triggered_by": 1,
        "input_data": {"key": "value"},
        "output_data": {"result": "success"},
        "started_at": "2024-01-15T16:30:00.000000Z",
        "finished_at": "2024-01-15T16:30:15.000000Z",
        "execution_time_ms": 15000,
        "node_executions_count": 5
    }
}
```

### Stop Execution
```bash
curl --location --request POST 'http://n8njd.test/api/v1/executions/123/stop' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Execution stopped successfully"
}
```

### Retry Execution
```bash
curl --location --request POST 'http://n8njd.test/api/v1/executions/123/retry' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (202):**
```json
{
    "status": "success",
    "message": "Execution retry started",
    "data": {
        "execution_id": 124,
        "original_execution_id": 123
    }
}
```

### Get Execution Logs
```bash
curl --location 'http://n8njd.test/api/v1/executions/123/logs' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "timestamp": "2024-01-15T16:30:05.000000Z",
            "level": "info",
            "message": "Node 'HTTP Request' started",
            "node_id": 1
        },
        {
            "timestamp": "2024-01-15T16:30:10.000000Z",
            "level": "info",
            "message": "Node 'HTTP Request' completed",
            "node_id": 1
        }
    ]
}
```

### Get Node Executions
```bash
curl --location 'http://n8njd.test/api/v1/executions/123/nodes' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "node_id": 10,
            "node_type": "http_request",
            "status": "success",
            "started_at": "2024-01-15T16:30:05.000000Z",
            "finished_at": "2024-01-15T16:30:10.000000Z",
            "execution_time_ms": 5000,
            "input_data": {},
            "output_data": {"statusCode": 200}
        }
    ]
}
```

---

## Workflow Versions

### List Workflow Versions
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/versions?per_page=10' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 5,
            "workflow_id": 1,
            "version": 5,
            "description": "Added error handling",
            "created_by": 1,
            "created_at": "2024-01-15T10:00:00.000000Z"
        }
    ],
    "per_page": 10,
    "total": 5
}
```

### Create Workflow Version
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/versions' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "description": "Added new nodes"
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 6,
        "workflow_id": 1,
        "version": 6,
        "description": "Added new nodes",
        "created_at": "2024-01-15T18:00:00.000000Z"
    }
}
```

### Restore Workflow Version
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/versions/3/restore' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Version restored successfully",
    "data": {
        "id": 1,
        "version": 3,
        "updated_at": "2024-01-15T18:30:00.000000Z"
    }
}
```

### Compare Versions
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/versions/3/compare/5' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "version1": {
            "nodes": [],
            "connections": [],
            "settings": {}
        },
        "version2": {
            "nodes": [],
            "connections": [],
            "settings": {}
        }
    }
}
```

---

## Workflow Sharing

### Get Workflow Shares
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/shares' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "workflow_id": 1,
            "user_id": 2,
            "permissions": "read",
            "created_at": "2024-01-10T10:00:00.000000Z"
        }
    ]
}
```

### Share Workflow
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/shares' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "user_id": 3,
    "permissions": "edit"
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 2,
        "workflow_id": 1,
        "user_id": 3,
        "permissions": "edit",
        "created_at": "2024-01-15T19:00:00.000000Z"
    }
}
```

### Remove Workflow Share
```bash
curl --location --request DELETE 'http://n8njd.test/api/v1/workflows/1/shares/3' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Share removed successfully"
}
```

---

## Workflow Import/Export

### Export Workflow
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/export' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "id": 1,
    "name": "My Workflow",
    "description": "Exported workflow",
    "nodes": [],
    "connections": [],
    "settings": {},
    "version": "1.0.0"
}
```

### Import Workflow
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/import' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "name": "Imported Workflow",
    "description": "Workflow from export",
    "nodes": [],
    "connections": [],
    "settings": {}
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 10,
        "name": "Imported Workflow",
        "status": "draft",
        "created_at": "2024-01-15T20:00:00.000000Z"
    }
}
```

### Bulk Import Workflows
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/bulk-import' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "workflows": [
        {
            "name": "Workflow 1",
            "nodes": [],
            "connections": []
        },
        {
            "name": "Workflow 2",
            "nodes": [],
            "connections": []
        }
    ]
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": [
        {"id": 11, "name": "Workflow 1"},
        {"id": 12, "name": "Workflow 2"}
    ]
}
```

### Bulk Delete Workflows
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/bulk-delete' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "ids": [1, 2, 3]
}'
```

**Response (204):**
```
No Content
```

### Bulk Activate Workflows
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/bulk-activate' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "ids": [4, 5, 6]
}'
```

**Response (204):**
```
No Content
```

---

## Workflow Validation & Testing

### Validate Workflow
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/validate' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "status": "success",
        "message": "Workflow is valid.",
        "errors": []
    }
}
```

### Test Run Workflow
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/test-run' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "input_data": {"test": true}
}'
```

**Response (202):**
```json
{
    "status": "success",
    "message": "Test execution started",
    "data": {
        "execution_id": 999,
        "mode": "test"
    }
}
```

### Health Check
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/health-check' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "status": "success",
        "message": "Workflow health is good.",
        "checks": {
            "nodes_valid": true,
            "connections_valid": true,
            "credentials_valid": true
        }
    }
}
```

---

## Workflow Dependencies

### Get Sub-Workflows
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/sub-workflows' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "workflow_id": 1,
            "sub_workflow_id": 5,
            "created_at": "2024-01-10T10:00:00.000000Z"
        }
    ]
}
```

### Link Sub-Workflow
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/sub-workflows/link' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "sub_workflow_id": 7
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 2,
        "workflow_id": 1,
        "sub_workflow_id": 7
    }
}
```

### Get Dependencies
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/dependencies' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 5,
            "name": "Helper Workflow",
            "status": "active"
        }
    ]
}
```

### Get Dependents
```bash
curl --location 'http://n8njd.test/api/v1/workflows/5/dependents' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "Main Workflow",
            "status": "active"
        }
    ]
}
```

### Impact Analysis
```bash
curl --location 'http://n8njd.test/api/v1/workflows/5/impact-analysis' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "Main Workflow",
            "impact_level": "high"
        },
        {
            "id": 3,
            "name": "Secondary Workflow",
            "impact_level": "medium"
        }
    ]
}
```

---

## Workflow Comments

### Get Comments
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/comments' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "workflow_id": 1,
            "user_id": 1,
            "content": "This workflow needs optimization",
            "created_at": "2024-01-15T10:00:00.000000Z"
        }
    ]
}
```

### Create Comment
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/comments' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "content": "Great workflow!"
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 2,
        "workflow_id": 1,
        "user_id": 1,
        "content": "Great workflow!",
        "created_at": "2024-01-15T20:00:00.000000Z"
    }
}
```

### Update Comment
```bash
curl --location --request PUT 'http://n8njd.test/api/v1/workflows/1/comments/2' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "content": "Updated comment text"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "id": 2,
        "content": "Updated comment text",
        "updated_at": "2024-01-15T20:30:00.000000Z"
    }
}
```

### Delete Comment
```bash
curl --location --request DELETE 'http://n8njd.test/api/v1/workflows/1/comments/2' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (204):**
```
No Content
```

---*Response (200):**
```json
{
    "status": "success",
    "message": "Execution stopped successfully"
}
```

### Retry Execution
```bash
curl --location --request POST 'http://n8njd.test/api/v1/executions/123/retry' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (202):**
```json
{
    "status": "success",
    "message": "Execution retry started",
    "data": {
        "execution_id": 124,
        "original_execution_id": 123
    }
}
```

---

## Credentials

### List Credentials
```bash
curl --location 'http://n8njd.test/api/v1/credentials' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "My API Key",
            "type": "api_key",
            "organization_id": 1,
            "created_by": 1,
            "created_at": "2024-01-10T10:00:00.000000Z"
        }
    ]
}
```

### Create Credential
```bash
curl --location 'http://n8njd.test/api/v1/credentials' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "name": "GitHub Token",
    "type": "oauth2",
    "data": {
        "access_token": "ghp_xxxxxxxxxxxx",
        "token_type": "bearer"
    }
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 2,
        "name": "GitHub Token",
        "type": "oauth2",
        "organization_id": 1,
        "created_by": 1,
        "created_at": "2024-01-15T17:00:00.000000Z"
    }
}
```

### Test Credential
```bash
curl --location --request POST 'http://n8njd.test/api/v1/credentials/1/test' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Credential test successful",
    "data": {
        "valid": true,
        "tested_at": "2024-01-15T17:30:00.000000Z"
    }
}
```

---

## Webhooks

### List Webhooks
```bash
curl --location 'http://n8njd.test/api/v1/webhooks' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "workflow_id": 1,
            "path": "my-webhook",
            "method": "POST",
            "active": true,
            "trigger_count": 42,
            "last_triggered_at": "2024-01-15T14:00:00.000000Z"
        }
    ]
}
```

### Create Webhook
```bash
curl --location 'http://n8njd.test/api/v1/webhooks' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "workflow_id": 1,
    "path": "my-webhook",
    "method": "POST",
    "auth_type": "none"
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 2,
        "workflow_id": 1,
        "path": "my-webhook",
        "method": "POST",
        "active": true,
        "url": "http://n8njd.test/api/webhook/1/my-webhook",
        "created_at": "2024-01-15T18:00:00.000000Z"
    }
}
```

### Trigger Webhook (Public)
```bash
curl --location --request POST 'http://n8njd.test/api/webhook/1/my-webhook' \
--header 'Content-Type: application/json' \
--data-raw '{
    "event": "user.created",
    "data": {
        "user_id": 123
    }
}'
```

**Response (202):**
```json
{
    "message": "Webhook received and workflow execution queued",
    "workflow_id": 1,
    "webhook_path": "my-webhook"
}
```

---

## Templates

### List Templates
```bash
curl --location 'http://n8njd.test/api/v1/templates?page=1&per_page=20' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "Slack Notification Template",
            "description": "Send notifications to Slack",
            "category": "notifications",
            "usage_count": 150,
            "rating": 4.5,
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

### Use Template
```bash
curl --location --request POST 'http://n8njd.test/api/v1/templates/1/use' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (201):**
```json
{
    "status": "success",
    "message": "Workflow created from template",
    "data": {
        "workflow_id": 5,
        "name": "Slack Notification Template",
        "status": "draft"
    }
}
```

---

## Analytics

### Get Dashboard
```bash
curl --location 'http://n8njd.test/api/v1/analytics/dashboard' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "overview": {
            "total_workflows": 25,
            "active_workflows": 18,
            "total_executions": 1250,
            "executions_today": 45,
            "success_rate": 94.5,
            "avg_execution_time_ms": 2500
        },
        "recent_executions": [],
        "top_workflows": [],
        "error_rate": 5.5
    }
}
```

### Get Workflow Metrics
```bash
curl --location 'http://n8njd.test/api/v1/analytics/workflows/1/metrics' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "workflow_id": 1,
        "name": "My Workflow",
        "total_executions": 150,
        "success_count": 142,
        "error_count": 8,
        "avg_execution_time_ms": 2300,
        "executions_last_24h": 12,
        "executions_last_7d": 85,
        "executions_last_30d": 150
    }
}
```

---

## Organizations

### List Organizations
```bash
curl --location 'http://n8njd.test/api/v1/organizations' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "My Organization",
            "slug": "my-org",
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

### Get Organization Members
```bash
curl --location 'http://n8njd.test/api/v1/organizations/1/members' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "admin",
            "joined_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

---

## Nodes

### List Available Nodes
```bash
curl --location 'http://n8njd.test/api/v1/nodes' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "type": "http_request",
            "name": "HTTP Request",
            "category": "action",
            "description": "Make HTTP requests",
            "icon": "globe"
        },
        {
            "type": "webhook",
            "name": "Webhook",
            "category": "trigger",
            "description": "Trigger workflow via webhook",
            "icon": "webhook"
        }
    ]
}
```

### Get Node Schema
```bash
curl --location 'http://n8njd.test/api/v1/nodes/http_request/schema' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "type": "http_request",
        "properties": {
            "url": {
                "type": "string",
                "required": true,
                "description": "The URL to request"
            },
            "method": {
                "type": "string",
                "enum": ["GET", "POST", "PUT", "DELETE"],
                "default": "GET"
            }
        }
    }
}
```

---

## Variables

### List Variables
```bash
curl --location 'http://n8njd.test/api/v1/variables' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "key": "API_URL",
            "value": "https://api.example.com",
            "type": "string",
            "workflow_id": null,
            "organization_id": 1
        }
    ]
}
```

### Create Variable
```bash
curl --location 'http://n8njd.test/api/v1/variables' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "key": "DATABASE_URL",
    "value": "postgresql://localhost:5432/mydb",
    "type": "string"
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 2,
        "key": "DATABASE_URL",
        "value": "postgresql://localhost:5432/mydb",
        "type": "string",
        "created_at": "2024-01-15T19:00:00.000000Z"
    }
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
    "status": "error",
    "message": "You do not have permission to perform this action."
}
```

### 404 Not Found
```json
{
    "status": "error",
    "message": "Resource not found."
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name field is required."
        ],
        "email": [
            "The email has already been taken."
        ]
    }
}
```

### 500 Internal Server Error
```json
{
    "status": "error",
    "message": "An error occurred while processing your request."
}
```

---

## Rate Limiting

API requests are rate limited to:
- **60 requests per minute** for authenticated users
- **10 requests per minute** for unauthenticated requests

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1642262400
```

---

## Pagination

List endpoints support pagination with the following query parameters:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

Paginated responses include:
```json
{
    "current_page": 1,
    "data": [...],
    "first_page_url": "...",
    "from": 1,
    "last_page": 5,
    "last_page_url": "...",
    "next_page_url": "...",
    "path": "...",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 75
}
```

## Storage & Files

### Upload File
```bash
curl --location --request POST 'http://n8njd.test/api/v1/storage/upload' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--form 'file=@"/path/to/file.pdf"' \
--form 'folder="documents"'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": "abc123",
        "filename": "file.pdf",
        "size": 1024000,
        "mime_type": "application/pdf",
        "url": "http://n8njd.test/storage/files/abc123",
        "created_at": "2024-01-15T21:00:00.000000Z"
    }
}
```

### List Files
```bash
curl --location 'http://n8njd.test/api/v1/storage/files?folder=documents' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": "abc123",
            "filename": "file.pdf",
            "size": 1024000,
            "mime_type": "application/pdf",
            "created_at": "2024-01-15T21:00:00.000000Z"
        }
    ]
}
```

### Download File
```bash
curl --location 'http://n8njd.test/api/v1/storage/files/abc123/download' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--output downloaded_file.pdf
```

### Delete File
```bash
curl --location --request DELETE 'http://n8njd.test/api/v1/storage/files/abc123' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (204):**
```
No Content
```

---

## Notifications

### List Notifications
```bash
curl --location 'http://n8njd.test/api/v1/notifications?per_page=20' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "type": "workflow_completed",
            "title": "Workflow Completed",
            "message": "Your workflow 'Data Sync' has completed successfully",
            "read_at": null,
            "created_at": "2024-01-15T22:00:00.000000Z"
        }
    ],
    "per_page": 20,
    "total": 5
}
```

### Mark as Read
```bash
curl --location --request PUT 'http://n8njd.test/api/v1/notifications/1/read' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Notification marked as read"
}
```

### Mark All as Read
```bash
curl --location --request POST 'http://n8njd.test/api/v1/notifications/mark-all-read' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "All notifications marked as read"
}
```

### Get Notification Settings
```bash
curl --location 'http://n8njd.test/api/v1/notifications/settings' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "email_notifications": true,
        "workflow_completed": true,
        "workflow_failed": true,
        "workflow_started": false
    }
}
```

### Update Notification Settings
```bash
curl --location --request PUT 'http://n8njd.test/api/v1/notifications/settings' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "email_notifications": true,
    "workflow_completed": false,
    "workflow_failed": true
}'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Settings updated successfully"
}
```

---

## AI Features

### Suggest Nodes
```bash
curl --location --request POST 'http://n8njd.test/api/v1/ai/suggest-nodes' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "description": "I want to send data to Slack when a webhook is triggered"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "suggestions": [
            {
                "type": "webhook",
                "name": "Webhook Trigger",
                "confidence": 0.95
            },
            {
                "type": "slack",
                "name": "Slack",
                "confidence": 0.92
            }
        ]
    }
}
```

### Generate Workflow
```bash
curl --location --request POST 'http://n8njd.test/api/v1/ai/generate-workflow' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "description": "Create a workflow that monitors GitHub issues and sends notifications to Slack"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "name": "GitHub to Slack Notifications",
        "nodes": [
            {
                "type": "github_trigger",
                "config": {}
            },
            {
                "type": "slack",
                "config": {}
            }
        ],
        "connections": []
    }
}
```

### Explain Error
```bash
curl --location --request POST 'http://n8njd.test/api/v1/ai/explain-error' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "error_message": "Connection timeout after 30000ms",
    "node_type": "http_request"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "explanation": "The HTTP request timed out because the server did not respond within 30 seconds.",
        "suggestions": [
            "Increase the timeout value in node settings",
            "Check if the target server is accessible",
            "Verify network connectivity"
        ]
    }
}
```

### Generate Expression
```bash
curl --location --request POST 'http://n8njd.test/api/v1/ai/generate-expression' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "description": "Get the first 10 characters of the email field"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "expression": "{{ $json.email.substring(0, 10) }}",
        "explanation": "This expression extracts the first 10 characters from the email field"
    }
}
```

---

## Admin Endpoints

### Get System Health
```bash
curl --location 'http://n8njd.test/api/v1/admin/system/health' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ADMIN_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "status": "healthy",
        "database": "connected",
        "redis": "connected",
        "queue": "running",
        "disk_space": "85% available",
        "memory": "60% used"
    }
}
```

### Get System Metrics
```bash
curl --location 'http://n8njd.test/api/v1/admin/system/metrics' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ADMIN_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "total_users": 150,
        "total_workflows": 450,
        "total_executions": 12500,
        "active_executions": 5,
        "queue_size": 12,
        "avg_response_time_ms": 250
    }
}
```

### List All Users (Admin)
```bash
curl --location 'http://n8njd.test/api/v1/admin/users?per_page=50' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ADMIN_TOKEN_HERE'
```

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "status": "active",
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "per_page": 50,
    "total": 150
}
```

### Suspend User
```bash
curl --location --request POST 'http://n8njd.test/api/v1/admin/users/5/suspend' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ADMIN_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "User suspended successfully"
}
```

### Get Audit Logs
```bash
curl --location 'http://n8njd.test/api/v1/admin/audit-logs?per_page=100&page=1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ADMIN_TOKEN_HERE'
```

**Response (200):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "action": "workflow.created",
            "resource_type": "workflow",
            "resource_id": 5,
            "ip_address": "192.168.1.1",
            "user_agent": "Mozilla/5.0...",
            "created_at": "2024-01-15T23:00:00.000000Z"
        }
    ],
    "per_page": 100,
    "total": 5000
}
```

---

## Collaboration

### Get Presence
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/presence' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "user_id": 2,
            "user_name": "Jane Smith",
            "cursor_position": {"x": 100, "y": 200},
            "last_seen": "2024-01-15T23:30:00.000000Z"
        }
    ]
}
```

### Join Presence
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/presence/join' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Joined workflow editing session"
}
```

### Submit Operation
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/operations' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "type": "node_moved",
    "node_id": 5,
    "position": {"x": 150, "y": 250}
}'
```

**Response (200):**
```json
{
    "status": "success",
    "operation_id": "op_123"
}
```

---

## Schedules

### Create Schedule
```bash
curl --location --request POST 'http://n8njd.test/api/v1/workflows/1/schedules' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "cron_expression": "0 9 * * *",
    "timezone": "UTC",
    "active": true
}'
```

**Response (201):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "workflow_id": 1,
        "cron_expression": "0 9 * * *",
        "timezone": "UTC",
        "active": true,
        "next_run_at": "2024-01-16T09:00:00.000000Z"
    }
}
```

### List Schedules
```bash
curl --location 'http://n8njd.test/api/v1/workflows/1/schedules' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "workflow_id": 1,
            "cron_expression": "0 9 * * *",
            "timezone": "UTC",
            "active": true,
            "last_run_at": "2024-01-15T09:00:00.000000Z",
            "next_run_at": "2024-01-16T09:00:00.000000Z"
        }
    ]
}
```

---

## Queue Management

### Get Queue Status
```bash
curl --location 'http://n8njd.test/api/v1/executions/queue/status' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "pending": 15,
        "processing": 3,
        "failed": 2,
        "completed": 1250
    }
}
```

### Get Queue Metrics
```bash
curl --location 'http://n8njd.test/api/v1/executions/queue/metrics' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "avg_wait_time_ms": 500,
        "avg_processing_time_ms": 2500,
        "throughput_per_minute": 24,
        "oldest_job_age_seconds": 120
    }
}
```

### Clear Queue
```bash
curl --location --request POST 'http://n8njd.test/api/v1/executions/queue/clear' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ADMIN_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Queue cleared successfully",
    "jobs_removed": 15
}
```

---

## Authentication Advanced

### Refresh Token
```bash
curl --location --request POST 'http://n8njd.test/api/v1/auth/refresh' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

### Change Password
```bash
curl --location --request POST 'http://n8njd.test/api/v1/auth/change-password' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "current_password": "oldpassword123",
    "new_password": "newpassword456",
    "new_password_confirmation": "newpassword456"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Password changed successfully"
}
```

### Forgot Password
```bash
curl --location --request POST 'http://n8njd.test/api/v1/auth/forgot-password' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
    "email": "john@example.com"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Password reset link sent to your email"
}
```

### Reset Password
```bash
curl --location --request POST 'http://n8njd.test/api/v1/auth/reset-password' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
    "email": "john@example.com",
    "token": "reset_token_here",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "Password reset successfully"
}
```

### Enable MFA
```bash
curl --location --request POST 'http://n8njd.test/api/v1/auth/mfa/enable' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE'
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "qr_code": "data:image/png;base64,...",
        "secret": "JBSWY3DPEHPK3PXP",
        "backup_codes": ["12345678", "87654321"]
    }
}
```

### Verify MFA
```bash
curl --location --request POST 'http://n8njd.test/api/v1/auth/mfa/verify' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_TOKEN_HERE' \
--data-raw '{
    "code": "123456"
}'
```

**Response (200):**
```json
{
    "status": "success",
    "message": "MFA enabled successfully"
}
```

### OAuth Login
```bash
curl --location 'http://n8njd.test/api/v1/auth/oauth/github' \
--header 'Accept: application/json'
```

**Response (302):**
```
Redirect to GitHub OAuth page
```

---

## WebSocket Events

For real-time updates, connect to WebSocket endpoint:

```javascript
const ws = new WebSocket('ws://n8njd.test/ws');

ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    console.log('Event:', data);
};
```

**Event Types:**
- `workflow.execution.started`
- `workflow.execution.completed`
- `workflow.execution.failed`
- `workflow.execution.progress`
- `presence.user.joined`
- `presence.user.left`
- `presence.cursor.moved`

**Example Event:**
```json
{
    "event": "workflow.execution.completed",
    "data": {
        "execution_id": 123,
        "workflow_id": 1,
        "status": "success",
        "execution_time_ms": 5000
    }
}
```

---

## Best Practices

### Authentication
- Always include the `Authorization: Bearer TOKEN` header
- Store tokens securely (never in localStorage for sensitive apps)
- Refresh tokens before they expire
- Use HTTPS in production

### Error Handling
- Check HTTP status codes
- Parse error messages from response body
- Implement retry logic for 5xx errors
- Handle rate limiting (429) with exponential backoff

### Pagination
- Use `per_page` parameter to control page size
- Default is 15 items per page
- Maximum is 100 items per page
- Always check `total` to know how many pages exist

### Performance
- Use pagination for large datasets
- Cache responses when appropriate
- Use webhooks instead of polling
- Batch operations when possible (bulk endpoints)

### Security
- Never expose API tokens in client-side code
- Use environment variables for sensitive data
- Implement proper CORS policies
- Validate all input data
- Use HTTPS only in production

---

## Support

For API support and questions:
- Documentation: http://n8njd.test/docs
- GitHub Issues: https://github.com/your-repo/issues
- Email: support@n8njd.com

---

**Last Updated:** January 2024  
**API Version:** v1
