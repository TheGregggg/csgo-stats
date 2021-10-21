import json

weapon = {
    '0': 'Unknown', 
    '1': 'P2000', 
    '2': 'Glock', 
    '3': 'P250', 
    '4': 'Deagle', 
    '5': 'FiveSeven', 
    '6': 'DualBerettas', 
    '7': 'Tec9', 
    '8': 'CZ', 
    '9': 'USP', 
    '10': 'Revolver', 
    '101': 'MP7', 
    '102': 'MP9', 
    '103': 'Bizon', 
    '104': 'Mac10', 
    '105': 'UMP', 
    '106': 'P90', 
    '107': 'MP5', 
    '201': 'SawedOff', 
    '202': 'Nova', 
    '203': 'Swag7', 
    '204': 'XM1014', 
    '205': 'M249', 
    '206': 'Negev', 
    '301': 'Galil', 
    '302': 'Famas', 
    '303': 'AK47',
    '304': 'M4A4', 
    '305': 'M4A1', 
    '306': 'SSG08', 
    '307': 'SG553', 
    '308': 'AUG', 
    '309': 'AWP', 
    '310': 'Scar20', 
    '311': 'G3SG1', 
    '401': 'Zeus', 
    '402': 'Kevlar', 
    '403': 'Helmet', 
    '404': 'Bomb', 
    '405': 'Knife', 
    '406': 'DefuseKit', 
    '407': 'World', 
    '501': 'Decoy', 
    '502': 'Molotov', 
    '503': 'Incendiary', 
    '504': 'Flash', 
    '505': 'Smoke', 
    '506': 'HE'
}

with open('demo.json', 'r', encoding="utf8") as f:
    data = json.loads(f.read())

ent_by_name = {}
ent_by_id = {}
for entitie in data['entities']:
    ent_by_name[entitie['name']] = entitie['id']
    ent_by_id[entitie['id']] = entitie['name']

def get_event_in_tick(tick, event):
    for e in tick['events']:
        if event == e['name']:
            return e
    return None

def get_attr_in_event(event, attr):
    for a in event['attrs']:
        if a['key'] == attr:
            return a
    return None

def get_player_in_snapshot(snapshot, player_name):
    for player in snapshot['entityUpdates']:
        if player['entityId'] == ent_by_name[player_name]:
            return player
    return None

player_name = 'Gregggg_'

round_cs = 0
for tick in data['ticks']:
    event = get_event_in_tick(tick, 'round_ended')
    if event:
        round_cs += 1
    event = get_event_in_tick(tick, 'kill')
    if event:
        killer = get_attr_in_event(event, 'killer')
        if killer and killer['numVal'] == ent_by_name[player_name]:
            victim = ent_by_id[get_attr_in_event(event, 'victim')['numVal']]
            weap = weapon[str(get_attr_in_event(event, 'weapon')['numVal'])]
            snapshot = data['snapshots'][int(tick['nr']/4)]
            playerInfo = get_player_in_snapshot(snapshot, player_name)
            pos = playerInfo['positions'][0]
            print(f"Tick event : {snapshot['tick']} - Tick snapshot : {tick['nr']} -> Round {round_cs} : Gregggg_ a tué {victim} avec {weap} en x:{pos['x']} y:{pos['y']}")


#print(data['snapshots'][1])
