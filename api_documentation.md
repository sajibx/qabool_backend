# Qabool API Documentation (v1)

This document provides all the necessary details to integrate the Qabool backend with the Flutter frontend.

## Base Configuration
- **Base URL**: `http://localhost:3000`
- **Swagger UI**: `http://localhost:3000/api`
- **Authentication**: Bearer Token (JWT) in Authorization Header.
  ```text
  Authorization: Bearer <your_jwt_token>
  ```

---

## 1. Authentication

### Register User
`POST /auth/register`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "firstName": "John",
  "lastName": "Doe"
}
```

**Response (201 Created):**
```json
{
  "access_token": "eyJhbG...",
  "user": {
    "id": "uuid-string",
    "email": "user@example.com",
    "firstName": "John",
    "lastName": "Doe"
  }
}
```

### Login
`POST /auth/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200 OK):**
Same as Register response.

---

## 2. Profiles (Authenticated)

### Get Discovery List
`GET /profiles`
*Supports query params: `religion`, `region`*

**Response (200 OK):**
```json
[
  {
    "id": "uuid",
    "firstName": "Amara",
    "lastName": "Khan",
    "religion": "Islam (Sunni)",
    "region": "London",
    "profileImageUrl": "https://...",
    "bio": "Searching for...",
    "isVerified": true
  }
]
```

### Get My Profile
`GET /profiles/me`

**Response (200 OK):**
Returns the full User object of the authenticated user.

### Update My Profile
`PUT /profiles/me`

**Request Body:**
```json
{
  "bio": "New bio content",
  "religion": "Islam (Shia)",
  "education": "Master's in Engineering",
  "dob": "1995-05-15",
  "gender": "FEMALE"
}
```

---

## 3. Connections (Matches)

### Send Connection Request
`POST /connections`

**Request Body:**
```json
{
  "recipientId": "other-user-uuid"
}
```

### List My Connections
`GET /connections`

**Response (200 OK):**
```json
[
  {
    "id": "match-uuid",
    "status": "PENDING",
    "requester": { "id": "...", "firstName": "..." },
    "recipient": { "id": "...", "firstName": "..." }
  }
]
```

### Respond to Request
`PUT /connections/:id`

**Request Body:**
```json
{
  "status": "ACCEPTED" 
}
```
*Status options: `ACCEPTED`, `REJECTED`*

---

## 4. Messaging & Real-time (WebSockets)

### List Chats
`GET /chats`

**Response (200 OK):**
```json
[
  {
    "id": "chat-uuid",
    "participants": [ { "id": "...", "firstName": "..." } ],
    "messages": [ { "content": "Last message...", "createdAt": "..." } ]
  }
]
```

### Get Message History
`GET /chats/:id/messages`

**Response (200 OK):**
List of message objects.

### WebSocket Integration (Socket.io)
**URL**: `ws://localhost:3000`
**Auth**: Pass token in `auth` object or `Authorization` header.

#### Events (Client to Server)
- `send_message`: `{ "chatId": "uuid", "recipientId": "uuid", "content": "Hi", "type": "TEXT" }`
- `typing_status`: `{ "chatId": "uuid", "recipientId": "uuid", "isTyping": true }`

#### Events (Server to Client)
- `new_message`: Triggered when a new message arrives.
- `typing_status`: Triggered when the other participant starts/stops typing.

---

## Guide for Frontend Implementation

1. **State Persistence**: Store the `access_token` securely (e.g., `flutter_secure_storage`).
2. **Global Headers**: Attach the token to every request using an Interceptor (e.g., Dio Interceptor).
3. **Real-time**: Use `socket_io_client` in Flutter. Connect when the app starts/user logs in and join the user room automatically handled by backend.
4. **Validation**: The backend uses `class-validator`. If you send a bad request, it returns a 400 with a descriptive error array.
