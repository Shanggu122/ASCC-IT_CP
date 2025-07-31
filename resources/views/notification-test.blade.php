<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        button {
            background: #12372a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .notification {
            background: #f0f8ff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
        }
        .notification.unread {
            border-left: 4px solid #12372a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Notification System Test</h1>
        
        <div>
            <button onclick="testNotifications()">Test Load Notifications</button>
            <button onclick="testUnreadCount()">Test Unread Count</button>
            <button onclick="testCreateNotification()">Create Test Notification</button>
        </div>
        
        <div id="results"></div>
    </div>

    <script>
        function testNotifications() {
            fetch('/api/notifications')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('results').innerHTML = 
                        '<h3>Notifications:</h3>' + 
                        data.notifications.map(n => 
                            `<div class="notification ${n.is_read ? '' : 'unread'}">
                                <strong>${n.title}</strong><br>
                                ${n.message}<br>
                                <small>${n.created_at} - ${n.type}</small>
                            </div>`
                        ).join('');
                })
                .catch(error => {
                    document.getElementById('results').innerHTML = '<p>Error: ' + error + '</p>';
                });
        }

        function testUnreadCount() {
            fetch('/api/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('results').innerHTML = 
                        '<h3>Unread Count: ' + data.count + '</h3>';
                })
                .catch(error => {
                    document.getElementById('results').innerHTML = '<p>Error: ' + error + '</p>';
                });
        }

        function testCreateNotification() {
            // This would normally be called from your backend when status changes
            document.getElementById('results').innerHTML = 
                '<p>Test notification creation - check your dashboard!</p>';
        }
    </script>
</body>
</html>
