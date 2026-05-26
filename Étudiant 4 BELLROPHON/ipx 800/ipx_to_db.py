import requests
import xml.etree.ElementTree as ET
import pymysql
import time
from datetime import datetime

IPX_URL = "http://192.168.0.131/status.xml"

DB_CONFIG = {
    "host":     "192.168.0.11",
    "port":     3306,
    "user":     "admin",
    "password": "pi",
    "db":       "auth_system",
}

INTERVALLE_SECONDES = 10

def lire_analog1():
    try:
        r = requests.get(IPX_URL, timeout=5)
        r.raise_for_status()
        root = ET.fromstring(r.text)
        return float(root.find("analog1").text)
    except requests.RequestException as e:
        raise RuntimeError("Impossible de joindre l'IPX800 : {}".format(e))
    except (AttributeError, ValueError) as e:
        raise RuntimeError("Erreur de lecture analog1 : {}".format(e))

def inserer_mesure(analog1, date_mesure, heure_mesure):
    conn = pymysql.connect(**DB_CONFIG)
    cursor = conn.cursor()
    try:
        cursor.execute(
            "SELECT AVG(analog1), COUNT(*) FROM historique_energie WHERE date_mesure = %s",
            (date_mesure,)
        )
        row = cursor.fetchone()
        nb = row[1] or 0
        moyenne = ((row[0] or 0) * nb + analog1) / (nb + 1)

        cursor.execute(
            "INSERT INTO historique_energie (analog1, moyenne_jour, date_mesure, heure_mesure) VALUES (%s, %s, %s, %s)",
            (analog1, moyenne, date_mesure, heure_mesure)
        )
        conn.commit()
        print("OK | analog1={} kWh | moyenne_jour={:.4f} | {} {}".format(analog1, moyenne, date_mesure, heure_mesure))
    except pymysql.Error as e:
        conn.rollback()
        raise RuntimeError("Erreur base de donnees : {}".format(e))
    finally:
        cursor.close()
        conn.close()

print("Demarrage - mesure toutes les {} secondes...".format(INTERVALLE_SECONDES))

while True:
    try:
        now = datetime.now()
        date_mesure  = now.strftime("%Y-%m-%d")
        heure_mesure = now.strftime("%H:%M:%S")

        print("Lecture IPX800...")
        analog1 = lire_analog1()
        print("   analog1 = {} kWh".format(analog1))

        print("Insertion en base de donnees...")
        inserer_mesure(analog1, date_mesure, heure_mesure)

    except RuntimeError as e:
        print("ERREUR : {}".format(e))

    print("Prochaine mesure dans {} secondes...\n".format(INTERVALLE_SECONDES))
    time.sleep(INTERVALLE_SECONDES)
