# MCP in production

## Nginx

Proxy `/mcp` to Laravel `public/index.php`, same as `/api/`. Example: [deploy/nginx.conf.example](../../deploy/nginx.conf.example).

```nginx
location ^~ /mcp {
  include fastcgi_params;
  fastcgi_param SCRIPT_FILENAME {INSTALL_DIR}/apps/api/public/index.php;
  fastcgi_param REQUEST_URI $request_uri;
  fastcgi_pass unix:/run/php/php8.3-fpm.sock;
}
```

Replace `{INSTALL_DIR}` with the absolute path on the server (e.g. `/var/www/portal`).

## Laravel env

- `APP_URL=https://YOUR_DOMAIN`
- `APP_DEBUG=false`
- HTTPS only

## Token rotation

Revoke leaked tokens in `personal_access_tokens`, then `php artisan mcp:token`.

## Health checks

```bash
curl -s https://YOUR_DOMAIN/api/v1/health
curl -s -o /dev/null -w "%{http_code}\n" -X POST https://YOUR_DOMAIN/mcp \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

## Discovery

After publishing topics, run `php artisan discovery:publish` if `DISCOVERY_PUBLIC_PATH` is set.
