# Consultation Notification System

## Overview

This system provides real-time notifications for consultation status updates in the dashboard. Students will receive notifications when their consultations are accepted, completed, rescheduled, or cancelled.

## Features

-   **Real-time Notifications**: Inbox box beside the calendar showing notification updates
-   **Unread Count**: Badge showing the number of unread notifications
-   **Mark as Read**: Click notifications to mark them as read
-   **Mark All as Read**: Button to mark all notifications as read at once
-   **Auto-refresh**: Notifications are automatically refreshed every 30 seconds
-   **Responsive Design**: Works on both desktop and mobile devices

## Components Added

### 1. Database

-   **Table**: `notifications`
-   **Columns**:
    -   `id`: Primary key
    -   `user_id`: Student ID (references t_student.Stud_ID)
    -   `booking_id`: Consultation booking ID
    -   `type`: Notification type (accepted, completed, rescheduled, cancelled)
    -   `title`: Notification title
    -   `message`: Notification message
    -   `is_read`: Boolean for read status
    -   `created_at`, `updated_at`: Timestamps

### 2. Backend Components

-   **Model**: `App\Models\Notification`
-   **Controller**: `App\Http\Controllers\NotificationController`
-   **Routes**: API endpoints for notification management
-   **Auto-notification**: When consultation status is updated, notifications are automatically created

### 3. Frontend Components

-   **Inbox Box**: Located beside the calendar in the dashboard
-   **Styling**: Custom CSS for notification appearance
-   **JavaScript**: Handles loading, displaying, and managing notifications

## API Endpoints

### GET /api/notifications

Returns all notifications for the current user

```json
{
    "notifications": [
        {
            "id": 1,
            "title": "Consultation Accepted",
            "message": "Your consultation with Prof. Smith has been accepted...",
            "type": "accepted",
            "is_read": false,
            "created_at": "2025-01-18T10:30:00Z"
        }
    ]
}
```

### GET /api/notifications/unread-count

Returns the count of unread notifications

```json
{
    "count": 3
}
```

### POST /api/notifications/mark-read

Mark a specific notification as read

```json
{
    "notification_id": 1
}
```

### POST /api/notifications/mark-all-read

Mark all notifications as read

## How It Works

1. **Professor Updates Status**: When a professor updates a consultation status through their interface
2. **Notification Created**: The system automatically creates a notification for the student
3. **Real-time Display**: The notification appears in the student's inbox box
4. **User Interaction**: Student can click to mark as read or mark all as read

## Notification Types

-   **Accepted** (Green badge): Consultation has been approved by professor
-   **Completed** (Blue badge): Consultation has been completed
-   **Rescheduled** (Yellow badge): Consultation has been rescheduled to a new date
-   **Cancelled** (Red badge): Consultation has been cancelled

## Testing

Visit `/test-notifications` to test the notification system functionality.

## Mobile Responsiveness

The notification inbox is fully responsive and will stack below the calendar on mobile devices.

## Customization

You can customize:

-   **Colors**: Modify the CSS variables in dashboard.blade.php
-   **Refresh Interval**: Change the 30-second interval in the JavaScript
-   **Notification Types**: Add new types in the Notification model
-   **Messages**: Customize notification messages in the createConsultationNotification method
