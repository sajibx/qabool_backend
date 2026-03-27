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
  "lastName": "Doe",
  "phoneNumber": "+1234567890",
  "hasPastIssues": false,
  "acceptsPastIssues": true,
  "maritalStatus": "Single",
  "currentCity": "New York",
  "monthlyIncome": 5000,
  "siblings": 2,
  "familyMembers": 4,
  "lookingForAge": "25-30",
  "lookingForType": "Religious",
  "lookingForProfession": "Doctor"
}
```

**Response (201 Created):**
> [!NOTE]
> Newly registered users are assigned an `INACTIVE` status by default and require admin approval before they can log in.
```json
{
  "access_token": "eyJhbG...",
  "user": {
    "id": "cd34387d-d5c0-46f0-8bb4-435ab8411be7",
    "email": "user@example.com",
    "firstName": "John",
    "lastName": "Doe",
    "status": "INACTIVE",
    "verifiedStatus": "inactive",
    "profileImageUrl": "/uploads/random_profile_converted.jpg",
    "lastSeen": "2026-03-14T17:30:00Z",
    "isOnline": true
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
Same as Register response (but only for `ACTIVE` users).

**Error (401 Unauthorized):**
- **Invalid Credentials**: `{"message": "Invalid credentials", "statusCode": 401}`
- **Pending Approval**: `{"message": "Your account is pending admin approval", "statusCode": 401}`

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

**Request Body (Multipart/form-data or JSON):**
```json
{
  "email": "new.email@example.com",
  "firstName": "John",
  "lastName": "Doe",
  "bio": "New bio content",
  "dob": "1995-05-15",
  "gender": "FEMALE",
  "region": "London",
  "religion": "Islam (Sunni)",
  "ethnicity": "Arab",
  "height": 175,
  "weight": 70,
  "maritalStatus": "Single",
  "currentCity": "New York",
  "monthlyIncome": 6000,
  "siblings": 3,
  "familyMembers": 5,
  "lookingForAge": "20-30",
  "lookingForType": "Practising",
  "lookingForProfession": "Engineer",
  "profession": "Doctor",
  "education": "Master's Degree",
  "specialConsiderations": "None",
  "hasPastIssues": false,
  "acceptsPastIssues": true,
  "phoneNumber": "+1234567890",
  "profileImage": "(File upload allowed via multipart)"
}
```

### Get Profile Details
`GET /profiles/:id`

**Response (200 OK):**
```json
{
  "id": "uuid",
  "firstName": "...",
  "connectionStatus": "PENDING_SENT", 
  "connectionId": "match-uuid",
  "isFavorited": true,
  "isOnline": true,
  "lastSeen": "..."
}
```

### Discover Users (Authenticated)
`GET /profiles/discover`

**Description**: returns all active users in the system. This endpoint is a placeholder for future discovery algorithms.

**Response (200 OK):**
Returns a list of User objects.

> [!NOTE]
> All user profile responses (`/profiles`, `/profiles/:id`, `/favorites/my`, etc.) now include `connectionStatus` (relative to the logged-in user) and `connectionId`.

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

> [!IMPORTANT]
> **Retry Logic**: If a request is `REJECTED` (or withdrawn by the requester using the same endpoint), the backend **deletes** the connection record. This resets the `connectionStatus` to `NONE` for both users, allowing a new request to be sent in the future.

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

### Fetch Message History
`GET /chats/:id/messages`
*Supports query params: `page` (default: 1), `limit` (default: 20)*

**Response (200 OK):**
```json
{
  "messages": [
    {
      "id": "msg-uuid",
      "content": "Hi there",
      "createdAt": "...",
      "sender": { "id": "uuid", "firstName": "..." }
    }
  ],
  "meta": {
    "total": 50,
    "page": 1,
    "limit": 20,
    "totalPages": 3
  }
}
```

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

## 5. Favorites (Authenticated, Real-time)

### Add to Favorites
`POST /favorites/:id`
- Adds the specified user to your favorites.
- Triggers a `new_favorite` WebSocket event to the target user.

### Remove from Favorites
`DELETE /favorites/:id`
- Removes the specified user from your favorites.

### List My Favorites
`GET /favorites/my`
- Returns a list of User objects you have favorited.

### List Users who Favorited Me
`GET /favorites/by-whom`
- Returns a list of User objects who have added you to their favorites.

#### Real-time Favorite Notification
- **Event**: `new_favorite`
- **Data**:
  ```json
  {
    "from": {
      "id": "uuid",
      "firstName": "...",
      "lastName": "...",
      "profileImageUrl": "..."
    }
  }
  ```

---

## 6. Blocking (Authenticated)

### Block a User
`POST /api/v1/blocks/:id`
- Blocks the specified user.
- Once blocked, neither user will see each other in discovery, search, or chat lists.
- Existing chats are hidden.
- New chats cannot be created between blocked users.

### Unblock a User
`DELETE /api/v1/blocks/:id`
- Unblocks the specified user.
- User becomes visible again in discovery and search.

### List Blocked Users
`GET /api/v1/blocks`
- Returns a list of User objects you have blocked.

---

## 7. Implementation Notes (Frontend)
