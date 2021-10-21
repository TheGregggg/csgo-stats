import csv

txt = '''EqUnknown EquipmentType = 0

	EqP2000        EquipmentType = 1
	EqGlock        EquipmentType = 2
	EqP250         EquipmentType = 3
	EqDeagle       EquipmentType = 4
	EqFiveSeven    EquipmentType = 5
	EqDualBerettas EquipmentType = 6
	EqTec9         EquipmentType = 7
	EqCZ           EquipmentType = 8
	EqUSP          EquipmentType = 9
	EqRevolver     EquipmentType = 10

	EqMP7   EquipmentType = 101
	EqMP9   EquipmentType = 102
	EqBizon EquipmentType = 103
	EqMac10 EquipmentType = 104
	EqUMP   EquipmentType = 105
	EqP90   EquipmentType = 106
	EqMP5   EquipmentType = 107

	EqSawedOff EquipmentType = 201
	EqNova     EquipmentType = 202
	EqMag7     EquipmentType = 203 // You should consider using EqSwag7 instead
	EqSwag7    EquipmentType = 203
	EqXM1014   EquipmentType = 204
	EqM249     EquipmentType = 205
	EqNegev    EquipmentType = 206

	EqGalil  EquipmentType = 301
	EqFamas  EquipmentType = 302
	EqAK47   EquipmentType = 303
	EqM4A4   EquipmentType = 304
	EqM4A1   EquipmentType = 305
	EqScout  EquipmentType = 306
	EqSSG08  EquipmentType = 306
	EqSG556  EquipmentType = 307
	EqSG553  EquipmentType = 307
	EqAUG    EquipmentType = 308
	EqAWP    EquipmentType = 309
	EqScar20 EquipmentType = 310
	EqG3SG1  EquipmentType = 311

	EqZeus      EquipmentType = 401
	EqKevlar    EquipmentType = 402
	EqHelmet    EquipmentType = 403
	EqBomb      EquipmentType = 404
	EqKnife     EquipmentType = 405
	EqDefuseKit EquipmentType = 406
	EqWorld     EquipmentType = 407

	EqDecoy      EquipmentType = 501
	EqMolotov    EquipmentType = 502
	EqIncendiary EquipmentType = 503
	EqFlash      EquipmentType = 504
	EqSmoke      EquipmentType = 505
	EqHE         EquipmentType = 506'''

csgo_weapon_data = {}

# not best implementation but fine for dev parsing
with open("CSGO Weapon Spreadsheet (Last Weapon Update_ September 21, 2021) - Raw Values.csv","r") as f:
	reader = csv.DictReader(f)
	for row in reader:
		csgo_weapon_data[row['Pistols']] = row

dico = {}
for ligne in txt.split('\n'):
	l = ligne.split()
	if l != []:
		name = l[0][2:]
		name = name.lower()

		dico[name] = {}
		
		description = None
		magazine_size = None
		Damage_per_bullet = None
		Bullet_per_Sec = None
		if name in csgo_weapon_data:
			weapon_stats = csgo_weapon_data[name]
			Damage_per_bullet = weapon_stats['Damage']
			Bullet_per_Sec = weapon_stats['CycleTime']

		dico[name]['id'] = l[3]
		dico[name]['description'] = description
		dico[name]['magazine_size'] = magazine_size
		dico[name]['Damage_per_bullet'] = Damage_per_bullet
		dico[name]['Bullet_per_Sec'] = Bullet_per_Sec

print(dico)