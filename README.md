# qevo-server – Backend Skeleton (Phase 1)

**Ziel:** Lauffähiges PHP-Backend-Grundgerüst mit Routing, ENV, Fehler-/Statusschema und Health-Endpoint.

## 1) Installation (XAMPP, Windows)

1. **Ordner entpacken** nach:  
   `C:\xampp\htdocs\qevo-server`

2. **ENV anlegen**  
   - Datei `.env.example` → **kopieren** nach `.env`  
   - In `.env` **JWT_SECRET** auf einen eigenen Wert setzen.  
     Empfohlene Befehle (eine Variante reicht):
     - PowerShell: `php -r "echo bin2hex(random_bytes(32));"`
     - CMD: `php -r "echo bin2hex(random_bytes(32));"`  
     - Ergebnis in `.env` bei `JWT_SECRET=` eintragen.

3. **Composer ausführen**  
   Öffne eine Eingabeaufforderung im Ordner `C:\xampp\htdocs\qevo-server` und führe aus:
   ```bash
   composer install
   ```
   > Falls Composer noch nicht installiert ist: https://getcomposer.org/download/

4. **Test**  
   Rufe im Browser auf:  
   `http://localhost/qevo-server/public/api/health`  
   → Erwartete JSON-Antwort:
   ```json
   {
     "code": "ok",
     "message": "healthy",
     "data": {"time": "..." , "env": "local"}
   }
   ```

## 2) Projektstruktur (Auszug)

```
qevo-server/
├─ app/
│  ├─ Core/               # Kernel, Config, Router, Error/Response
│  ├─ Http/Middleware/    # (Platzhalter) JWT, CORS
│  └─ Modules/            # Module: Auth, Client, QR, Task, Sync
├─ public/
│  ├─ .htaccess           # Weiterleitung an index.php
│  └─ index.php           # Front Controller
├─ storage/logs/          # Logs (writable)
├─ .env.example
├─ composer.json
└─ README.md
```

## 3) Nächste Schritte (Phase 1)

- DB-Migrationen füllen (Struktur aus Projektbeschreibung).
- JWT-Flow (Register/Login) implementieren.
- Middleware (CORS, Rate-Limit, JWT) aktivieren.

---

**Hinweis:** Alle Kommentare im Code sind auf Deutsch. Änderungen später bitte konsistent halten.