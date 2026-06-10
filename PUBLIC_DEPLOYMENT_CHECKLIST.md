# CFH-CRM Production Deployment Checklist

## 📋 Pre-Deployment Requirements

### Server Requirements
- [ ] Server OS: Linux (Ubuntu 20.04 LTS or higher recommended)
- [ ] PHP Version: 8.1 or higher
- [ ] Web Server: Nginx or Apache with mod_rewrite
- [ ] Database: MySQL 8.0+ or MariaDB 10.5+
- [ ] Memory: Minimum 2GB RAM
- [ ] Disk Space: Minimum 20GB
- [ ] SSL Certificate: Valid HTTPS certificate (Let's Encrypt recommended)
- [ ] Firewall: Configured to allow HTTP(80) and HTTPS(443)

### Software Requirements
- [ ] Git installed and configured
- [ ] Composer installed globally
- [ ] OpenSSL extension enabled
- [ ] PDO MySQL extension enabled
- [ ] JSON extension enabled
- [ ] OpenSSL PHP extension enabled
- [ ] Mbstring PHP extension enabled

## 🔒 Security Configuration

### Database Security
- [ ] Database user created with limited privileges
- [ ] Database password generated (min 16 characters, mixed case + numbers + symbols)
- [ ] SSL connection enabled between app and database
- [ ] Database backups configured (daily automated)
- [ ] SQL credentials stored in `.env` file (600 permissions)
- [ ] `.env` file excluded from git repository

### Application Security
- [ ] `.env` file created from `.env.example`
- [ ] `APP_DEBUG` set to `false`
- [ ] `APP_ENV` set to `production`
- [ ] CSRF protection enabled
- [ ] Session security headers configured
- [ ] Input validation implemented globally
- [ ] Output encoding implemented in all templates
- [ ] File upload restrictions configured
- [ ] Rate limiting enabled

### Server Security
- [ ] SSL/TLS 1.2+ only (no SSLv3, TLSv1.0, TLSv1.1)
- [ ] Strong SSL ciphers configured
- [ ] HSTS header enabled
- [ ] Security headers configured (CSP, X-Frame-Options, etc.)
- [ ] Root login disabled
- [ ] SSH key-based authentication only
- [ ] Firewall rules configured
- [ ] Fail2Ban or similar installed and configured
- [ ] Server updated with latest security patches

## 📁 File System Setup

### Directory Structure
- [ ] `/var/www/cfh-crm/` - Application root (755 permissions)
- [ ] `/var/www/cfh-crm/public/` - Web root (755 permissions)
- [ ] `/var/www/secure_uploads/` - File uploads outside web root (770 permissions)
- [ ] `/var/log/cfh-crm/` - Logs directory (770 permissions)
- [ ] `/var/backups/cfh-crm/` - Backups directory (750 permissions)

### File Permissions
- [ ] Application files: 644 (rw-r--r--)
- [ ] Application directories: 755 (rwxr-xr-x)
- [ ] Storage directory: 770 (rwxrwx---)
- [ ] Logs directory: 770 (rwxrwx---)
- [ ] `.env` file: 600 (rw-------)
- [ ] Executable scripts: 755 (rwxr-xr-x)

### Ownership
- [ ] Web server user: `www-data` (or equivalent)
- [ ] Application owner: Verified correct
- [ ] Secure uploads directory: Correct ownership and permissions

## 🚀 Deployment Steps

### Pre-Deployment
- [ ] Full database backup created
- [ ] Application backup created
- [ ] Maintenance page ready
- [ ] Rollback plan documented

### Deployment
- [ ] Run deployment script: `bash scripts/production-deploy.sh`
- [ ] Verify all dependencies installed
- [ ] Database migrations executed (if applicable)
- [ ] Cache cleared
- [ ] Permissions verified

### Post-Deployment
- [ ] Application health check: `curl https://join.comeforhumanity.org/health`
- [ ] Homepage loads correctly
- [ ] Login page accessible
- [ ] HTTPS working correctly
- [ ] Security headers present
- [ ] Error logs checked
- [ ] Access logs reviewed

## 🔍 Testing

### Functionality Testing
- [ ] User registration works
- [ ] User login works
- [ ] User logout works
- [ ] Database queries return correct data
- [ ] File uploads working
- [ ] File downloads working
- [ ] Email notifications working
- [ ] All forms submit correctly

### Security Testing
- [ ] SQL injection protection verified
- [ ] XSS protection verified
- [ ] CSRF token present on all forms
- [ ] Rate limiting working
- [ ] Session management working
- [ ] HTTPS redirects working
- [ ] Security headers present
- [ ] No sensitive data in logs

### Performance Testing
- [ ] Page load time acceptable (<2 seconds)
- [ ] Database queries optimized
- [ ] Cache working correctly
- [ ] No memory leaks
- [ ] CPU usage normal

## 📊 Monitoring Setup

### Logging
- [ ] Application logs enabled
- [ ] Error logs monitored
- [ ] Security logs monitored
- [ ] Nginx logs monitored
- [ ] PHP-FPM logs monitored
- [ ] MySQL logs monitored
- [ ] Log rotation configured

### Alerts
- [ ] High error rate alert configured
- [ ] High CPU usage alert configured
- [ ] High memory usage alert configured
- [ ] Disk space alert configured
- [ ] SSL certificate expiry alert configured
- [ ] Failed login attempts alert configured

### Backups
- [ ] Daily automated backups configured
- [ ] Backups stored outside web root
- [ ] Backup retention policy set (90 days)
- [ ] Backup restoration tested
- [ ] Backup encryption enabled

## 📝 Documentation

- [ ] Deployment documentation created
- [ ] Emergency procedures documented
- [ ] Rollback procedures documented
- [ ] Security policies documented
- [ ] Password rotation policy documented
- [ ] Team access procedures documented
- [ ] On-call procedures documented

## 👥 Team Communication

- [ ] All team members notified
- [ ] Stakeholders informed of go-live
- [ ] Support team trained
- [ ] Status page updated
- [ ] Incident response plan reviewed
- [ ] Escalation procedures in place

## 🎯 Final Verification

- [ ] All items checked
- [ ] Security audit completed
- [ ] Performance testing passed
- [ ] Load testing completed
- [ ] Rollback testing successful
- [ ] Go/No-Go decision made
- [ ] Deployment authorized
- [ ] Deployment completed successfully
- [ ] Post-deployment monitoring active
- [ ] Team standing by for issues

## 📞 Post-Deployment Support

- [ ] Monitoring team notified
- [ ] Support team on standby
- [ ] Issue tracking system ready
- [ ] Communication channels open
- [ ] Status updates scheduled
- [ ] Rollback team ready (if needed)

---

**Deployment Date:** _____________
**Deployed By:** _____________
**Reviewed By:** _____________
**Approved By:** _____________

**Notes:**
```


```
