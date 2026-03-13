import urllib.request
import ssl

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

req = urllib.request.Request(
    'https://wiki.pathfindersonline.org/images/0/07/Pathfinder_Club_Emblem.png', 
    headers={'User-Agent': 'Mozilla/5.0'}
)

try:
    with urllib.request.urlopen(req, context=ctx) as r, open('d:\\1. Clientes\\50. DesbravaHub\\public\\assets\\images\\escudo-desbravador.png', 'wb') as f:
        f.write(r.read())
    print("SUCCESS")
except Exception as e:
    print("FAIL:", e)
