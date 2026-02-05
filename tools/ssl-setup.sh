#!/bin/bash

# SSL Certificate Setup Script for service.digitalmartmm.shop
# This script sets up Let's Encrypt SSL certificate using certbot
# Domain: service.digitalmartmm.shop
# Email: digitalmart.mag@gmail.com

set -e  # Exit on any error

# Configuration
DOMAIN="service.digitalmartmm.shop"
EMAIL="digitalmart.mag@gmail.com"

# Application Configuration - UPDATE THESE IF NEEDED
APP_PORT="8000"        # Your application runs on this port
APP_HOST="127.0.0.1"   # Your application host (localhost or Docker IP)

# Nginx & SSL Configuration
WEBROOT_PATH="/var/www/html/public"
SSL_CERT_PATH="/etc/letsencrypt/live/${DOMAIN}"
NGINX_CONFIG="/etc/nginx/sites-available/${DOMAIN}"
NGINX_ENABLED="/etc/nginx/sites-enabled/${DOMAIN}"
LOG_FILE="/var/log/letsencrypt/${DOMAIN}.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo_func() {
    echo -e "${GREEN}[SSL-SETUP]${NC} $1"
}

warn_func() {
    echo -e "${YELLOW}[SSL-SETUP]${NC} $1"
}

error_func() {
    echo -e "${RED}[SSL-SETUP]${NC} $1"
}

# Check if running as root
check_root() {
    if [ "$EUID" -ne 0 ]; then
        error_func "Please run as root (use sudo)"
        exit 1
    fi
}

# Check and install prerequisites
check_prerequisites() {
    echo_func "Checking prerequisites..."
    
    # Check if certbot is installed
    if ! command -v certbot &> /dev/null; then
        warn_func "Certbot not found. Installing..."
        apt-get update
        apt-get install -y certbot python3-certbot-nginx
    else
        echo_func "Certbot is already installed"
    fi
    
    # Check if nginx is installed
    if ! command -v nginx &> /dev/null; then
        warn_func "Nginx not found. Installing..."
        apt-get install -y nginx
    else
        echo_func "Nginx is already installed"
    fi
    
    echo_func "Prerequisites check completed"
}

# Create log directory
setup_logging() {
    echo_func "Setting up logging..."
    mkdir -p /var/log/letsencrypt
    touch $LOG_FILE
    echo_func "Log file created: $LOG_FILE"
}

# Create Nginx configuration for HTTP verification
create_nginx_config() {
    echo_func "Creating Nginx configuration for ${DOMAIN}..."
    
    cat > $NGINX_CONFIG << EOF
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};

    # Document root for Let's Encrypt verification
    location /.well-known/acme-challenge/ {
        root ${WEBROOT_PATH};
        allow all;
    }

    # Temporary redirect to application
    location / {
        return 301 https://\$server_name\$request_uri;
    }
}
EOF

    echo_func "Nginx configuration created: $NGINX_CONFIG"
    
    # Enable the site
    ln -sf $NGINX_CONFIG $NGINX_ENABLED
    
    # Test nginx configuration
    nginx -t
    
    # Reload nginx
    systemctl reload nginx
    
    echo_func "Nginx configuration enabled and reloaded"
}

# Create webroot directory if it doesn't exist
setup_webroot() {
    echo_func "Setting up webroot directory..."
    mkdir -p ${WEBROOT_PATH}/.well-known/acme-challenge
    chown -R www-data:www-data ${WEBROOT_PATH}/.well-known
    echo_func "Webroot directory created: ${WEBROOT_PATH}/.well-known/acme-challenge"
}

# Obtain SSL certificate
obtain_ssl_certificate() {
    echo_func "Obtaining SSL certificate for ${DOMAIN}..."
    echo_func "This may take a few minutes..."
    
    # Stop nginx temporarily to free port 80
    systemctl stop nginx || true
    
    # Run certbot
    certbot certonly \
        --standalone \
        --non-interactive \
        --agree-tos \
        --email ${EMAIL} \
        --domains ${DOMAIN},www.${DOMAIN} \
        --config-dir /etc/letsencrypt \
        --work-dir /var/lib/letsencrypt \
        --logs-dir /var/log/letsencrypt \
        2>&1 | tee -a $LOG_FILE
    
    # Check if certificate was obtained
    if [ -f "${SSL_CERT_PATH}/fullchain.pem" ]; then
        echo_func "SSL certificate obtained successfully!"
    else
        error_func "Failed to obtain SSL certificate. Check log: $LOG_FILE"
        exit 1
    fi
}

# Create production Nginx configuration with SSL
create_ssl_nginx_config() {
    echo_func "Creating SSL-enabled Nginx configuration..."
    
    # Detect if app is running in Docker or locally
    if docker ps --format '{{.Names}}' | grep -q 'app'; then
        APP_HOST="app"  # Docker container name
        APP_PORT="8000"
        echo_func "Detected Docker container: app:${APP_PORT}"
    else
        echo_func "Using local configuration: ${APP_HOST}:${APP_PORT}"
    fi
    
    cat > $NGINX_CONFIG << EOF
# HTTP server - redirect to HTTPS
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    
    # Let's Encrypt verification
    location /.well-known/acme-challenge/ {
        root ${WEBROOT_PATH};
        allow all;
    }
    
    # Redirect all HTTP to HTTPS
    location / {
        return 301 https://\$server_name\$request_uri;
    }
}

# HTTPS server
server {
    listen 443 ssl http2;
    server_name ${DOMAIN} www.${DOMAIN};
    
    # SSL Certificate configuration
    ssl_certificate ${SSL_CERT_PATH}/fullchain.pem;
    ssl_certificate_key ${SSL_CERT_PATH}/privkey.pem;
    
    # SSL Security settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Proxy to backend application
    location / {
        proxy_pass http://${APP_HOST}:${APP_PORT};
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_cache_bypass \$http_upgrade;
        
        # Timeout settings
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
    
    # Let's Encrypt verification
    location /.well-known/ {
        root ${WEBROOT_PATH};
        allow all;
    }
}
EOF

    echo_func "SSL-enabled Nginx configuration created"
    
    # Test and reload nginx
    nginx -t
    systemctl reload nginx
    
    echo_func "Nginx reloaded with SSL configuration"
}

# Setup auto-renewal
setup_auto_renewal() {
    echo_func "Setting up automatic certificate renewal..."
    
    # Create renewal script
    cat > /etc/letsencrypt/renewal-hooks/post/renew-ssl.sh << EOF
#!/bin/bash
# Post-renewal hook for SSL certificate renewal
# This script reloads nginx after certificate renewal

# Reload nginx to pick up new certificates
systemctl reload nginx

# Restart Docker containers if needed (optional)
# systemctl restart phoneservice

echo "SSL certificate renewed successfully"
EOF

    chmod +x /etc/letsencrypt/renewal-hooks/post/renew-ssl.sh
    
    # Test renewal
    certbot renew --dry-run 2>&1 | tee -a $LOG_FILE
    
    echo_func "Auto-renewal configured"
    echo_func "Certificates will automatically renew 30 days before expiration"
}

# Display certificate information
display_cert_info() {
    echo_func "SSL Certificate Information:"
    echo "================================"
    certbot certificates --config-dir /etc/letsencrypt 2>/dev/null | grep -A 5 "${DOMAIN}" || true
    echo "================================"
    
    # Show expiry date
    echo_func "Certificate expiry date:"
    openssl x509 -enddate -noout -in ${SSL_CERT_PATH}/cert.pem 2>/dev/null || echo "Unable to get expiry date"
    
    # Show days remaining
    if [ -f "${SSL_CERT_PATH}/cert.pem" ]; then
        DAYS=$(openssl x509 -enddate -noout -in ${SSL_CERT_PATH}/cert.pem 2>/dev/null | cut -d= -f2)
        echo_func "Certificate expires on: $DAYS"
    fi
}

# Verify SSL installation
verify_ssl() {
    echo_func "Verifying SSL installation..."
    
    # Check if certificate files exist
    if [ -f "${SSL_CERT_PATH}/fullchain.pem" ] && [ -f "${SSL_CERT_PATH}/privkey.pem" ]; then
        echo_func "✓ Certificate files exist"
    else
        error_func "✗ Certificate files missing"
        return 1
    fi
    
    # Check nginx configuration
    if [ -f $NGINX_CONFIG ]; then
        echo_func "✓ Nginx configuration exists"
    else
        error_func "✗ Nginx configuration missing"
        return 1
    fi
    
    # Test HTTPS connection
    echo_func "Testing HTTPS connection..."
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -L "https://${DOMAIN}" --connect-timeout 10 || echo "000")
    
    if [ "$HTTP_CODE" != "000" ]; then
        echo_func "✓ HTTPS is working (HTTP response code: $HTTP_CODE)"
    else
        warn_func "⚠ Could not connect to HTTPS. Please verify your DNS and server configuration."
    fi
    
    echo_func "SSL verification completed"
}

# Print summary
print_summary() {
    echo ""
    echo "=============================================="
    echo -e "${GREEN}SSL Setup Complete!${NC}"
    echo "=============================================="
    echo ""
    echo "Domain: ${DOMAIN}"
    echo "Email: ${EMAIL}"
    echo ""
    echo "Certificate Location:"
    echo "  - Full Chain: ${SSL_CERT_PATH}/fullchain.pem"
    echo "  - Private Key: ${SSL_CERT_PATH}/privkey.pem"
    echo ""
    echo "Nginx Configuration:"
    echo "  - Config: $NGINX_CONFIG"
    echo "  - Enabled: $NGINX_ENABLED"
    echo ""
    echo "Log File: $LOG_FILE"
    echo ""
    echo "Useful Commands:"
    echo "  - View certificate: certbot certificates"
    echo "  - Test renewal: certbot renew --dry-run"
    echo "  - Manual renew: certbot renew"
    echo "  - Reload nginx: systemctl reload nginx"
    echo ""
    echo "=============================================="
}

# Main execution
main() {
    echo_func "Starting SSL setup for ${DOMAIN}"
    echo_func "Email: ${EMAIL}"
    echo ""
    
    check_root
    check_prerequisites
    setup_logging
    setup_webroot
    create_nginx_config
    obtain_ssl_certificate
    create_ssl_nginx_config
    setup_auto_renewal
    display_cert_info
    verify_ssl
    print_summary
    
    echo_func "SSL setup completed successfully!"
}

# Run main function
main "$@"

