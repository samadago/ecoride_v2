#!/bin/bash

# Fix EcoRide Production Permissions Script
# Run this on your production server as sudo/root

echo "🔧 Fixing EcoRide Production Permissions..."

# Define paths
WEB_ROOT="/var/www/html"
UPLOADS_DIR="$WEB_ROOT/public/assets/uploads"

# Determine web server user (common ones)
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
else
    echo "❌ Could not determine web server user. Please run manually with your web server user."
    exit 1
fi

echo "📁 Web server user detected: $WEB_USER"

# Create uploads directory if it doesn't exist
echo "📁 Creating uploads directory..."
mkdir -p "$UPLOADS_DIR"

# Set proper ownership - web server user should own the files
echo "👤 Setting ownership to $WEB_USER..."
chown -R $WEB_USER:$WEB_USER "$UPLOADS_DIR"

# Set proper permissions
echo "🔒 Setting directory permissions..."
chmod 755 "$UPLOADS_DIR"

# Set SELinux context if SELinux is enabled
if command -v getenforce &>/dev/null && [ "$(getenforce)" != "Disabled" ]; then
    echo "🛡️ Setting SELinux context..."
    setsebool -P httpd_can_network_connect 1
    setsebool -P httpd_can_network_connect_db 1
    semanage fcontext -a -t httpd_exec_t "$UPLOADS_DIR(/.*)?"
    restorecon -R "$UPLOADS_DIR"
fi

# Verify permissions
echo "✅ Verifying setup..."
ls -la "$WEB_ROOT/public/assets/"

echo "🎉 Permissions fix complete!"
echo "📝 Summary:"
echo "   - Created: $UPLOADS_DIR"
echo "   - Owner: $WEB_USER:$WEB_USER"
echo "   - Permissions: 755"
echo ""
echo "💡 Test file upload now!"
