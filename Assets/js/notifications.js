// notifications.js - Simplified version
class NotificationManager {
    constructor() {
        this.lastId = 0;
        this.init();
    }

    async init() {
        await this.loadNotifications();
        // Refresh every 30 seconds
        setInterval(() => this.loadNotifications(), 30000);
    }

    async loadNotifications() {
        try {
            console.log('Loading notifications...');
            const response = await fetch(`get_notifications.php?last_id=${this.lastId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Notifications data:', data);
            
            if (data.success) {
                this.displayNotifications(data.notifications);
                this.updateBadge(data.notifications.filter(n => n.status === 'unread').length);
            } else {
                console.error('Server error:', data.message);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError('Failed to load notifications');
        }
    }

    displayNotifications(notifications) {
        const container = document.getElementById('notification-list');
        if (!container) {
            console.error('Notification container not found!');
            return;
        }

        // Clear "Loading..." message
        container.innerHTML = '';

        if (notifications.length === 0) {
            container.innerHTML = '<div class="notification-item">No new notifications</div>';
            return;
        }

        notifications.forEach(notification => {
            const element = this.createNotificationElement(notification);
            container.appendChild(element);
        });
    }

    createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `notification-item ${notification.status}`;
        div.innerHTML = `
            <div class="notification-message">${notification.message}</div>
            <div class="notification-time">${this.formatTime(notification.created_at)}</div>
        `;
        
        // Mark as read when clicked
        div.addEventListener('click', () => this.markAsRead(notification.id, div));
        return div;
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        return date.toLocaleDateString();
    }

    updateBadge(unreadCount) {
        const badge = document.querySelector('.notification-badge, #notification-count, .badge');
        if (badde) {
            badge.textContent = unreadCount;
            badge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
        }
    }

    async markAsRead(notificationId, element) {
        try {
            const response = await fetch('mark_read.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({notification_id: notificationId})
            });
            
            if (response.ok) {
                element.classList.remove('unread');
                element.classList.add('read');
                this.updateBadgeCount();
            }
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    showError(message) {
        const container = document.getElementById('notification-list');
        if (container) {
            container.innerHTML = `<div class="notification-error">${message}</div>`;
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new NotificationManager();
});