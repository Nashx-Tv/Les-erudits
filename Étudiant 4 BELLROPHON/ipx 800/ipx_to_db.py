import requests
import xml.etree.ElementTree as ET
import time
from datetime import datetime

IPX_URL = "http://192.168.0.131/status.xml"
ECO_URL = "http://192.168.0.21:5000/api/ecodevice"

# ── API Railway (remplace la connexion BDD locale) ──
API_URL = "https://site-web-production-77ef.up.railway.app/api_analog.php"

INTERVALLE_SECONDES = 1800

journal_eol = []

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

def lire_analog2():
    try:
        r = requests.get(ECO_URL, timeout=5)
        r.raise_for_status()
        data = r.json()
        return float(data.get("c1day_kwh", 0))
    except Exception as e:
        print("  ⚠ Eco-Device injoignable : {}".format(e))
        return 0

def envoyer_mesure(analog1, analog2, moyenne):
    try:
        r = requests.post(
            API_URL + "?action=enregistrer",
            json={"analog1": analog1, "analog2": analog2, "moyenne": moyenne},
            timeout=10
        )
        data = r.json()
        if data.get("succes"):
            print("OK | eol={} kWh | pv={} kWh | moyenne={:.4f} | {} {}".format(
                analog1, analog2, moyenne, data.get("date"), data.get("heure")))
        else:
            print("ERREUR API : {}".format(data.get("error", "inconnue")))
    except Exception as e:
        raise RuntimeError("Impossible d'envoyer a Railway : {}".format(e))

print("Demarrage - mesure toutes les {} secondes...".format(INTERVALLE_SECONDES))
print("Envoi vers : {}".format(API_URL))

while True:
    try:
        print("\nLecture IPX800...")
        analog1 = lire_analog1()
        print("   analog1 = {} kWh".format(analog1))

        print("Lecture Eco-Device...")
        analog2 = lire_analog2()
        print("   analog2 = {} kWh".format(analog2))

        journal_eol.append(analog1)
        moyenne = sum(journal_eol) / len(journal_eol)

        print("Envoi vers Railway...")
        envoyer_mesure(analog1, analog2, round(moyenne, 4))

    except RuntimeError as e:
        print("ERREUR : {}".format(e))

    print("Prochaine mesure dans {} secondes...".format(INTERVALLE_SECONDES))
    time.sleep(INTERVALLE_SECONDES)
