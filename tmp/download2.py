import urllib.request
import ssl

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

req = urllib.request.Request(
    'https://adventistas.org/pt/wp-content/uploads/2013/05/logo-D1.png', 
    headers={
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'Accept': 'image/png,image/svg+xml,image/*;q=0.8,*/*;q=0.5'
    }
)

try:
    with urllib.request.urlopen(req, context=ctx) as r, open('d:\\1. Clientes\\50. DesbravaHub\\public\\assets\\images\\escudo.png', 'wb') as f:
        f.write(r.read())
    print("SUCCESS")
except Exception as e:
    print("FAIL:", e)
