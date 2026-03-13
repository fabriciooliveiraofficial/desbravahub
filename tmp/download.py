import urllib.request
import ssl

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Emblema_Desbravadores.svg/512px-Emblema_Desbravadores.svg.png'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})

try:
    with urllib.request.urlopen(req, context=ctx) as response, open('d:\\1. Clientes\\50. DesbravaHub\\public\\assets\\images\\escudo-desbravador.png', 'wb') as out_file:
        out_file.write(response.read())
    print("Downloaded successfully!")
except Exception as e:
    print("Error:", e)
