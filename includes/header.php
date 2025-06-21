
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Perpustakaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            transition: all 0.3s ease;
        }
        
        .notification.success {
            background: #10b981;
            color: white;
        }
        
        .notification.error {
            background: #ef4444;
            color: white;
        }
        
        .notification.warning {
            background: #f59e0b;
            color: white;
        }
        
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .checkmark {
            animation: checkmark 0.5s ease-in-out;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">

<div id="notification-container"></div>

<script>
function showNotification(message, type = 'success', duration = 5000) {
    const container = document.getElementById('notification-container');
    const notification = document.createElement('div');
    
    let icon = '';
    let bgColor = '';
    
    switch(type) {
        case 'success':
            icon = '<i data-lucide="check-circle" class="h-5 w-5 checkmark"></i>';
            bgColor = 'bg-green-500';
            break;
        case 'error':
            icon = '<i data-lucide="x-circle" class="h-5 w-5"></i>';
            bgColor = 'bg-red-500';
            break;
        case 'warning':
            icon = '<i data-lucide="alert-triangle" class="h-5 w-5"></i>';
            bgColor = 'bg-yellow-500';
            break;
    }
    
    notification.className = `notification ${bgColor} text-white p-4 rounded-lg shadow-lg flex items-center space-x-3`;
    notification.innerHTML = `
        ${icon}
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-auto">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    `;
    
    container.appendChild(notification);
    lucide.createIcons();
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, duration);
}

function confirmDelete(message, callback) {
    if (confirm(message)) {
        callback();
        showNotification('Data berhasil dihapus', 'success');
    }
}
</script>
