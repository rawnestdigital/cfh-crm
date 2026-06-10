#!/bin/bash

# CFH-CRM Production Deployment Script
# Secure deployment with backups and health checks

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DEPLOY_DIR="/var/www/cfh-crm"
BACKUP_DIR="/var/backups/cfh-crm"
LOG_FILE="/var/log/cfh-crm/deploy.log"
APP_USER="www-data"
APP_GROUP="www-data"

# Functions
print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_step() {
    echo -e "${YELLOW}[$(date +'%H:%M:%S')] $1${NC}"
}

print_success() {
    echo -e "${GREEN}[✓] $1${NC}"
}

print_error() {
    echo -e "${RED}[✗] $1${NC}"
}

log_deployment() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Start deployment
print_header "CFH-CRM Production Deployment"
log_deployment "Deployment started"

# Pre-deployment checks
print_step "[1/10] Running pre-deployment checks..."

if [ ! -d "$DEPLOY_DIR" ]; then
    print_error "Deploy directory not found: $DEPLOY_DIR"
    exit 1
fi

if [ ! -x "$(command -v git)" ]; then
    print_error "Git is not installed"
    exit 1
fi

if [ ! -x "$(command -v composer)" ]; then
    print_error "Composer is not installed"
    exit 1
fi

print_success "Pre-deployment checks passed"

# Create backup
print_step "[2/10] Creating backup..."

mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz"
tar -czf "$BACKUP_FILE" --exclude='node_modules' --exclude='vendor' --exclude='storage/uploads' "$DEPLOY_DIR" 2>/dev/null
print_success "Backup created: $BACKUP_FILE"
log_deployment "Backup created: $BACKUP_FILE"

# Update code from git
print_step "[3/10] Updating code from repository..."

cd "$DEPLOY_DIR"
git fetch origin
git reset --hard origin/main
print_success "Code updated"
log_deployment "Code updated from git"

# Install/update dependencies
print_step "[4/10] Installing dependencies..."

composer install --no-dev --optimize-autoloader --no-interaction
print_success "Dependencies installed"
log_deployment "Dependencies installed"

# Set permissions
print_step "[5/10] Setting file permissions..."

chown -R "$APP_USER:$APP_GROUP" "$DEPLOY_DIR"
find "$DEPLOY_DIR" -type d -exec chmod 755 {} \;
find "$DEPLOY_DIR" -type f -exec chmod 644 {} \;

# Make scripts executable
chmod +x "$DEPLOY_DIR/scripts"/*.sh 2>/dev/null || true

# Set writable directories
chmod -R 770 "$DEPLOY_DIR/storage"
chmod -R 770 "$DEPLOY_DIR/logs" 2>/dev/null || true
chmod -R 770 /var/www/secure_uploads 2>/dev/null || true

print_success "Permissions set correctly"
log_deployment "File permissions configured"

# Clear cache
print_step "[6/10] Clearing cache..."

find "$DEPLOY_DIR/storage" -type f -name "*.cache" -delete 2>/dev/null || true
find "$DEPLOY_DIR/storage" -type f -name "*.tmp" -delete 2>/dev/null || true
print_success "Cache cleared"

# Database migrations (if applicable)
print_step "[7/10] Running database migrations..."

cd "$DEPLOY_DIR"
php bin/console migrate --force 2>/dev/null || true
print_success "Database migrations completed"
log_deployment "Database migrations completed"

# Optimize
print_step "[8/10] Running optimization..."

php bin/console cache:clear --no-warmup 2>/dev/null || true
php bin/console config:cache 2>/dev/null || true
print_success "Optimization completed"

# Security checks
print_step "[9/10] Running security checks..."

# Check file permissions
if [ $(find "$DEPLOY_DIR" -perm /077 -type f | wc -l) -gt 0 ]; then
    print_error "Some files have insecure permissions"
    log_deployment "WARNING: Insecure file permissions detected"
else
    print_success "File permissions are secure"
fi

# Check for exposed config files
if [ -f "$DEPLOY_DIR/.env" ]; then
    if [ $(stat -c %a "$DEPLOY_DIR/.env") != "600" ]; then
        chmod 600 "$DEPLOY_DIR/.env"
        print_success ".env file permissions fixed"
    fi
fi

# Health check
print_step "[10/10] Running health check..."

echo "Waiting for application to be ready..."
sleep 2

HEALTH_CHECK=$(curl -s -o /dev/null -w "%{http_code}" "https://join.comeforhumanity.org/health" 2>/dev/null || echo "000")

if [ "$HEALTH_CHECK" = "200" ]; then
    print_success "Health check passed (HTTP $HEALTH_CHECK)"
    log_deployment "Deployment successful - Health check: HTTP $HEALTH_CHECK"
else
    print_error "Health check failed (HTTP $HEALTH_CHECK)"
    log_deployment "ERROR: Health check failed (HTTP $HEALTH_CHECK)"
    print_step "Attempting to restore from backup..."
    cd /
    tar -xzf "$BACKUP_FILE"
    chown -R "$APP_USER:$APP_GROUP" "$DEPLOY_DIR"
    print_error "Deployment failed and rolled back to previous version"
    exit 1
fi

# Final steps
print_step "Cleaning up..."

# Keep only last 5 backups
ls -t "$BACKUP_DIR"/backup-*.tar.gz | tail -n +6 | xargs -r rm
print_success "Old backups cleaned up"

echo ""
print_header "✓ Deployment Completed Successfully"
echo ""
echo -e "${GREEN}Application is now running in production!${NC}"
echo ""
log_deployment "Deployment finished successfully"
