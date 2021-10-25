import json

file = "weapon.json"

with open(file, "r") as f:
    data = json.loads(f.read())

for keys, obj in data.items():
    if obj["magazine_size"] == "":
        obj["magazine_size"] = 0
    else:
        obj["magazine_size"] = int(obj["magazine_size"])

    if obj["Damage_per_bullet"] == "":
        obj["Damage_per_bullet"] = 0
    else:
        obj["Damage_per_bullet"] = int(obj["Damage_per_bullet"])

    if obj["Bullet_per_Sec"] == "":
        obj["Bullet_per_Sec"] = 0
    else:
        obj["Bullet_per_Sec"] = float(obj["Bullet_per_Sec"])

with open("weapon_cleared.json", "w") as f:
    f.write(json.dumps(data))
