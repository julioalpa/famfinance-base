#!/usr/bin/env bash
# Genera certificados TLS locales para la PWA de FamFinance usando openssl
# No requiere mkcert ni ninguna dependencia extra.
set -euo pipefail

CERT_DIR="$(cd "$(dirname "$0")/.." && pwd)/docker-compose/certs"
mkdir -p "$CERT_DIR"

# ── Detectar IP de red local ──────────────────────────────────────────────────
LOCAL_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
if [[ -z "$LOCAL_IP" ]]; then
    LOCAL_IP=$(ip route get 1.1.1.1 2>/dev/null | grep -oP 'src \K\S+' || true)
fi
if [[ -z "$LOCAL_IP" ]]; then
    echo ""
    read -rp "  No se detectó la IP automáticamente."$'\n'"  Ingresá la IP local del servidor (ej: 192.168.1.100): " LOCAL_IP
fi
echo "→ IP detectada: $LOCAL_IP"

# ── 1. Crear la CA raíz ───────────────────────────────────────────────────────
echo "→ Generando CA raíz..."
openssl genrsa -out "$CERT_DIR/CA.key" 4096 2>/dev/null
openssl req -x509 -new -nodes \
    -key    "$CERT_DIR/CA.key" \
    -sha256 -days 730 \
    -subj   "/CN=FamFinance Local CA/O=FamFinance" \
    -out    "$CERT_DIR/CA.crt"

# ── 2. Crear la clave y CSR del servidor ──────────────────────────────────────
echo "→ Generando clave del servidor..."
openssl genrsa -out "$CERT_DIR/local.key" 2048 2>/dev/null
openssl req -new \
    -key  "$CERT_DIR/local.key" \
    -subj "/CN=$LOCAL_IP/O=FamFinance" \
    -out  "$CERT_DIR/local.csr"

# ── 3. Archivo de extensiones (SAN: IPs y dominios válidos) ──────────────────
cat > "$CERT_DIR/ext.cnf" << EOF
[req]
distinguished_name = req
[SAN]
subjectAltName = IP:${LOCAL_IP}, IP:127.0.0.1, DNS:localhost
EOF

# ── 4. Firmar el cert del servidor con nuestra CA ─────────────────────────────
echo "→ Firmando certificado..."
openssl x509 -req \
    -in    "$CERT_DIR/local.csr" \
    -CA    "$CERT_DIR/CA.crt" \
    -CAkey "$CERT_DIR/CA.key" \
    -CAcreateserial \
    -out   "$CERT_DIR/local.crt" \
    -days 730 -sha256 \
    -extensions SAN \
    -extfile "$CERT_DIR/ext.cnf"

# ── 5. CA para instalar en Android ───────────────────────────────────────────
cp "$CERT_DIR/CA.crt" "$CERT_DIR/famfinance-CA.crt"

# Limpiar temporales
rm -f "$CERT_DIR/local.csr" "$CERT_DIR/ext.cnf" "$CERT_DIR/CA.srl" "$CERT_DIR/CA.key"

# ── Resultado ─────────────────────────────────────────────────────────────────
echo ""
echo "  ✓ Certificados generados en: $CERT_DIR"
echo ""
echo "  Archivos:"
echo "    local.crt          — certificado del servidor (nginx lo usa)"
echo "    local.key          — clave privada (nginx la usa)"
echo "    famfinance-CA.crt  — CA raíz para instalar en Android"
echo ""
echo "  ─────────────────────────────────────────────────────"
echo "  PRÓXIMOS PASOS:"
echo "  ─────────────────────────────────────────────────────"
echo "  1. Levantá nginx:   docker compose up -d nginx"
echo "     (usar 'up', no 'restart', para que monte el volumen)"
echo ""
echo "  2. Instalá el CA en cada celular:"
echo "     a. Mandá famfinance-CA.crt por WhatsApp/Telegram/Drive"
echo "     b. Abrilo desde Descargas en el celu"
echo "     c. Android pregunta para qué → 'Certificado de CA'"
echo "     d. Nombre: FamFinance → Aceptar"
echo "     e. Repetir en el segundo celu"
echo ""
echo "  3. Abrí Chrome en el celu:"
echo "     https://${LOCAL_IP}:8443"
echo ""
echo "  4. Chrome muestra el banner 'Instalar app' → tap → listo"
echo "  ─────────────────────────────────────────────────────"
echo ""
