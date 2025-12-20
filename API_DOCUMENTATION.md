# Apartment Booking API Documentation

## Overview
This API provides a complete backend solution for an apartment booking platform with features for user management, apartment listings, booking system, reviews, messaging, and admin controls.

## Base URL
```
http://localhost:8000/api
```

## Authentication
Most endpoints require authentication using Laravel Sanctum tokens. After login, include the token in the Authorization header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

## Language Support
The API supports both English and Arabic. To switch languages, include the Accept-Language header:
```
Accept-Language: ar
```

## API Endpoints

### Authentication

#### Register
```
POST /register
```
Fields:
- name (required)
- email (required, unique)
- password (required, min:8, confirmed)
- mobile (required, unique)
- role (required, tenant|renter)
- id_image (required, image)
- profile_image (optional, image)

#### Login
```
POST /login
```
Fields:
- mobile (required)
- password (required)

### User Management

#### Get Profile
```
GET /user
```

#### Update Profile
```
PUT /user
```
Fields:
- name (optional)
- email (optional, unique)
- mobile (optional, unique)
- profile_image (optional, image)

### Apartments

#### List Apartments
```
GET /apartments
```
Query Parameters:
- province (optional)
- city (optional)
- min_price (optional)
- max_price (optional)
- features (optional, comma separated)
- status (optional, default: available)

#### Get Apartment Details
```
GET /apartments/{id}
```

#### Create Apartment (Renter only)
```
POST /apartments
```
Fields:
- title (required)
- description (required)
- price (required)
- location (required)
- province (required)
- city (required)
- features (optional, array)
- images (optional, array of images)

#### Update Apartment (Owner only)
```
PUT /apartments/{id}
```
Fields:
- title (optional)
- description (optional)
- price (optional)
- location (optional)
- province (optional)
- city (optional)
- features (optional, array)
- status (optional, available|booked)

#### Delete Apartment (Owner or Admin only)
```
DELETE /apartments/{id}
```

#### Toggle Favorite
```
POST /apartments/{id}/favorite
```

#### Get Favorites
```
GET /favorites
```

### Bookings

#### Create Booking
```
POST /bookings
```
Fields:
- apartment_id (required)
- start_date (required, future date)
- end_date (required, after start_date)

Conflict Prevention:
- The system prevents booking conflicts by checking for overlapping dates with existing bookings (both pending and confirmed)
- Apartments in maintenance status cannot be booked
- Each booking creates a pending reservation that requires owner approval

Response:
- Returns booking details with pending status
- Total price is automatically calculated based on nightly rate and stay duration

#### List Bookings
```
GET /bookings
```
Query Parameters:
- user_id (optional)
- apartment_id (optional)
- status (optional)

#### Get Booking Details
```
GET /bookings/{id}
```

#### Update Booking Status (Owner/Admin only)
```
PUT /bookings/{id}
```
Fields:
- status (required, confirmed|rejected|cancelled)

Automatic Payment Processing:
- When status changes to "confirmed", the system automatically:
  1. Checks tenant wallet balance
  2. Deducts booking amount from tenant wallet
  3. Adds booking amount to renter (apartment owner) wallet
  4. Creates renter wallet if it doesn't exist
- If tenant has insufficient balance, the confirmation will fail
- Payment processing uses database transactions for data integrity

#### Cancel Booking (Booking user only)
```
POST /bookings/{id}/cancel
```

#### Update Booking Details (Booking user only)
```
PUT /bookings/{id}/details
```
Fields:
- start_date (optional, future date)
- end_date (optional, after start_date)

Note: Only pending bookings can be modified. The system will automatically recalculate the total price based on the new dates.

#### My Bookings
```
GET /my-bookings
```

### Reviews

#### Create/Update Review
```
POST /reviews
```
Fields:
- apartment_id (required)
- rating (required, 1-5)
- comment (optional)

#### List Reviews
```
GET /reviews
```
Query Parameters:
- user_id (optional)
- apartment_id (optional)

#### Get Review Details
```
GET /reviews/{id}
```

#### Update Review (Review owner only)
```
PUT /reviews/{id}
```
Fields:
- rating (optional, 1-5)
- comment (optional)

#### Delete Review (Review owner or Admin only)
```
DELETE /reviews/{id}
```

#### Get Apartment Reviews
```
GET /apartments/{id}/reviews
```

### Messaging

#### Send Message
```
POST /messages/send
```
Fields:
- receiver_id (required)
- message (required)

#### Get Inbox
```
GET /messages/inbox
```

#### Get Conversation
```
GET /messages/conversation/{user_id}
```

### Wallet

#### Get Wallet Balance
```
GET /wallet/balance/{user_id}
```

#### Deposit to Tenant Wallet (Admin only)
```
POST /admin/wallet/deposit/{user_id}
```
Fields:
- amount (required, numeric, minimum 0.01)

Note: Admin can deposit money to tenant wallets without needing their own wallet.

#### Request Withdrawal (Renter only)
```
POST /wallet/withdrawal-request
```
Fields:
- amount (required, numeric, minimum 0.01)

#### List Withdrawal Requests (Admin only)
```
GET /admin/wallet/withdrawal-requests
```

#### Approve Withdrawal Request (Admin only)
```
POST /admin/wallet/withdrawal-requests/{request_id}/approve
```

#### Reject Withdrawal Request (Admin only)
```
POST /admin/wallet/withdrawal-requests/{request_id}/reject
```

### Admin

#### Get Pending Users
```
GET /admin/users/pending
```

#### Approve User
```
POST /admin/users/{id}/approve
```

#### Reject User
```
POST /admin/users/{id}/reject
```

#### Get Statistics
```
GET /admin/statistics
```

## Roles
- **Admin**: Full access to all features, user management
- **Renter**: Can create apartments, manage their bookings
- **Tenant**: Can book apartments, leave reviews, send messages

## Status Values

### User Status
- pending: Waiting for admin approval
- active: Approved and active
- inactive: Deactivated
- suspended: Temporarily suspended

### Apartment Status
- available: Available for booking
- booked: Currently booked

### Booking Status
- pending: Waiting for owner approval
- confirmed: Confirmed by owner
- rejected: Rejected by owner
- cancelled: Cancelled by user
- completed: Booking period has passed

### Wallet
- balance: Current wallet balance
- deposit: Money added to tenant's wallet by admin
- withdrawal: Money withdrawn from renter's wallet by admin

## Features
- User registration with ID verification
- Multi-language support (English/Arabic)
- Apartment listing with filtering
- Advanced booking system with conflict prevention
- Overlapping date detection for all active bookings
- Review system
- Messaging between users
- Admin panel for user management
- RESTful API design
- Proper error handling
- Image upload support
- Pagination for lists