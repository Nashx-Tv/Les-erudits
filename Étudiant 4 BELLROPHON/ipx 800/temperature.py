#!/usr/bin/env python3
# =============================================================
#  temperature.py — Lecture capteur Grove + enregistrement MySQL


#  Capteur    : Grove Temperature Sensor (révision 1.2)
#  Raspberry  : 192.168.0.79
#  Base MySQL : 192.168.0.11  /  auth_system
#
#  Installation dépendance :
#      pip3 install pymysql
# =============================================================

import time
import grovepi
import pymysql
import pymysql.cursors
from datetime import datetime

# ── Configuration base de données ──────────────────────────
DB_CONFIG = {
    'host':     '192.168.0.11',
    'db':       'auth_system',
    'user':     'admin',
    'password': 'pi',
    'charset':  'utf8mb4',
    'connect_timeout': 10,
}

# ── Configuration capteur ───────────────────────────────────
SENSOR_PIN     = 0                  # Port analogique A0
SENSOR_VERSION = '1.2'              # Révision de la carte Grove
SENSOR_ID      = 'raspberry_pi_01'  # Nom libre pour identifier le capteur
INTERVAL_SEC   = 300               # Intervalle d'enregistrement en secondes (30 min)

# ── Connexion MySQL ─────────────────────────────────────────
def get_connection():
    """Crée et retourne une connexion MySQL via PyMySQL."""
    return pymysql.connect(**DB_CONFIG)

def save_temperature(conn, temperature):
    """Insère un relevé de température dans la base via la procédure stockée."""
    with conn.cursor() as cursor:
        cursor.callproc('insert_temperature', (SENSOR_ID, round(temperature, 2)))
    conn.commit()

# ── Boucle principale ───────────────────────────────────────
def main():
    print("[{}]  Démarrage — capteur A{} (v{})".format(datetime.now(), SENSOR_PIN, SENSOR_VERSION))
    print("  -> Enregistrement toutes les {} s dans auth_system@{}".format(INTERVAL_SEC, DB_CONFIG['host']))
    print("  -> Ctrl+C pour arreter\n")

    conn = None

    while True:
        try:
            # 1. Lecture du capteur
            temp = grovepi.temp(SENSOR_PIN, SENSOR_VERSION)

            # 2. Connexion (ou reconnexion si nécessaire)
            if conn is None:
                conn = get_connection()
                print("  [DB] Connecté à {}".format(DB_CONFIG['host']))
            else:
                conn.ping(reconnect=True)  # Reconnexion automatique si la connexion est perdue

            # 3. Sauvegarde
            save_temperature(conn, temp)
            print("[{}]  {:.2f} °C  enregistré".format(datetime.now().strftime('%Y-%m-%d %H:%M:%S'), temp))

        except KeyboardInterrupt:
            print("\nArrêt demandé par l'utilisateur.")
            break

        except IOError:
            print("[{}]  Erreur de lecture capteur (IOError)".format(datetime.now().strftime('%H:%M:%S')))

        except pymysql.MySQLError as db_err:
            print("[{}]  Erreur MySQL : {}".format(datetime.now().strftime('%H:%M:%S'), db_err))
            # Forcer la reconnexion au prochain tour
            try:
                if conn:
                    conn.close()
            except Exception:
                pass
            conn = None

        time.sleep(INTERVAL_SEC)

    # Fermeture propre
    if conn:
        conn.close()
        print("Connexion MySQL fermée.")

if __name__ == '__main__':
    main()