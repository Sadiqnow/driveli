# Cache Configuration Setup Guide

## Overview
This guide explains how to configure Redis or Memcached for production caching in the DriveLink application.

## Current Configuration
The application currently uses file-based caching as the default driver. Redis and Memcached configurations are already set up in `config/cache.php`.

## Redis Setup (Recommended)

### 1. Install Redis Server
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install redis-server

# CentOS/RHEL
sudo yum install redis

# macOS (using Homebrew)
brew install redis

# Windows (using Chocolatey)
choco install redis-64
```

### 2. Start Redis Service
```bash
# Linux
sudo systemctl start redis-server
sudo systemctl enable redis-server

# macOS
brew services start redis

# Windows
redis-server --service-start
```

### 3. Environment Configuration
Update your `.env` file with the following Redis settings:

```env
# Cache Configuration
CACHE_DRIVER=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Redis Cache Connection
REDIS_CACHE_DB=1
```

### 4. Test Redis Connection
```bash
# Test basic Redis connectivity
redis-cli ping
# Should return: PONG

# Test Laravel cache
php artisan tinker
Cache::put('test', 'Hello Redis', 10);
echo Cache::get('test'); // Should output: Hello Redis
```

## Memcached Setup (Alternative)

### 1. Install Memcached
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install memcached libmemcached-tools

# CentOS/RHEL
sudo yum install memcached

# macOS
brew install memcached
```

### 2. Start Memcached Service
```bash
# Linux
sudo systemctl start memcached
sudo systemctl enable memcached

# macOS
brew services start memcached
```

### 3. Environment Configuration
Update your `.env` file:

```env
# Cache Configuration
CACHE_DRIVER=memcached

# Memcached Configuration
MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211
MEMCACHED_USERNAME=null
MEMCACHED_PASSWORD=null
```

### 4. Test Memcached Connection
```bash
# Test basic Memcached connectivity
echo "stats" | nc localhost 11211

# Test Laravel cache
php artisan tinker
Cache::put('test', 'Hello Memcached', 10);
echo Cache::get('test'); // Should output: Hello Memcached
```

## Production Deployment

### Docker Setup
For containerized deployments, use the following Docker Compose configuration:

```yaml
version: '3.8'
services:
  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    restart: unless-stopped

  memcached:
    image: memcached:1.6-alpine
    ports:
      - "11211:11211"
    restart: unless-stopped

volumes:
  redis_data:
```

### Cloud Services
For cloud deployments, consider:

- **AWS ElastiCache** (Redis/Memcached)
- **Google Cloud Memorystore** (Redis)
- **Azure Cache for Redis**
- **DigitalOcean Managed Redis**

## Cache Optimization

### Cache TTL Settings
The application uses optimized TTL settings in services:

- Analytics data: 15-30 minutes
- User permissions: 60 minutes
- Driver queries: 10-20 minutes
- Static data: 24 hours

### Cache Keys
Cache keys follow a structured pattern:
```
drivelink:{service}:{method}:{parameters}:{timestamp}
```

### Monitoring Cache Performance
```bash
# Redis monitoring
redis-cli --stat

# Laravel cache statistics
php artisan tinker
$cache = app('cache');
$store = $cache->getStore();
// Monitor hit/miss ratios
```

## Troubleshooting

### Common Issues

1. **Connection Refused**
   - Check if Redis/Memcached service is running
   - Verify host and port settings
   - Check firewall settings

2. **Memory Issues**
   - Configure appropriate memory limits
   - Implement cache eviction policies
   - Monitor memory usage

3. **Performance Issues**
   - Use connection pooling
   - Implement proper serialization
   - Monitor cache hit rates

### Cache Clearing
```bash
# Clear all cache
php artisan cache:clear

# Clear specific cache store
php artisan cache:clear redis
php artisan cache:clear memcached
```

## Security Considerations

- Use authentication for Redis/Memcached in production
- Restrict access to cache servers
- Encrypt sensitive cached data
- Implement proper firewall rules
- Regular security updates for cache servers

## Performance Benchmarks

Expected performance improvements:
- **Response Time**: 40-60% faster for cached queries
- **Database Load**: 50-70% reduction in repeated queries
- **Memory Usage**: More efficient than file caching
- **Scalability**: Better horizontal scaling support

## Monitoring and Alerts

Set up monitoring for:
- Cache hit/miss ratios (>80% hit rate target)
- Memory usage (<80% of allocated memory)
- Connection pool utilization
- Response times for cached operations
