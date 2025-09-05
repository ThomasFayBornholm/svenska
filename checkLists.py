import json

install_path="/var/www/html/svenska/lists/"
for word_class in ["substantiv", "adjektiv", "verb"]:
    with open(install_path + word_class + "-conj", "r", encoding="utf-8") as f:
        try:
            dict = json.load(f)
        except json.decoder.JSONDecodeError as e:
            print("ERROR: " + word_class + "-conj corrupt")
            print(e)
    with open(install_path + word_class + "-meta", "r", encoding="utf-8") as f:
        try:
            dict = json.load(f)
        except json.decoder.JSONDecodeError as e:
            print("ERROR: " + word_class + "-meta corrupt")
            print(e)

    with open(install_path + word_class + "-more", "r", encoding="utf-8") as f:
        try:
            dict = json.load(f)
        except json.decoder.JSONDecodeError as e:
            print("ERROR: " + word_class + "-more corrupt")
            print(e)

