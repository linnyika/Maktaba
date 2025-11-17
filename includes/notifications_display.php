<?php
// This file displays notifications in your UI
?>
<div id="notification-container">
    <div class="notification-header">
        <h3>Notifications</h3>
        <span id="notification-count" class="badge">0</span>
    </div>
    <div id="notification-list" class="notification-list">
        <!-- Notifications will be loaded here -->
    </div>
    <div class="notification-actions">
        <button id="mark-all-read" class="btn btn-sm">Mark All Read</button>
        <button id="load-more" class="btn btn-sm">Load More</button>
    </div>
</div>

<style>
.notification-list {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.notification-item.unread {
    background-color: #f0f8ff;
    font-weight: bold;
}

.notification-item.read {
    opacity: 0.7;
}

.badge {
    background: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
}
</style>