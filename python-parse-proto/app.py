import json
from os import spawnl

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

def get_player_in_snapshot(snapshot, player_id):
    for player in snapshot['entityUpdates']:
        if player['entityId'] == player_id:
            return player
    return None

def get_last_player_pos(snap_tick, player_id):
    player = get_player_in_snapshot(data['snapshots'][snap_tick], player_id)
    if player is None:
        return get_last_player_pos(snap_tick-1, player_id)
    else:
        return player['positions'][0]

for tick in data['ticks']:
    event = get_event_in_tick(tick, 'kill')
    if event:
        killer = get_attr_in_event(event, 'killer')
        if killer:
            killer = killer['numVal']
            victim = get_attr_in_event(event, 'victim')['numVal']
            weap = get_attr_in_event(event, 'weapon')['numVal']
            k_pos = get_last_player_pos(int(tick['nr']/8), killer)
            v_pos = get_last_player_pos(int(tick['nr']/8), victim)
            print(f"{ent_by_id[killer]} a tué {ent_by_id[victim]} avec {weapon[str(weap)]} ; kpos: {k_pos} vpos: {v_pos}")
    
        

#print(data['snapshots'][1])