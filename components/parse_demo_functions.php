<?php

function get_event_in_tick($tick_obj, $event_name){
    /* Prend comme parametres : 
        un obj tick 
        le nom d'un évènement que l'on veut extraire de ce tick
       Renvoie l'évènement ou NULL 
    */
    foreach($tick_obj['events'] as $event){
        if($event['name'] == $event_name){
            return $event;
        }
    }
    return NULL;
}

function get_attr_in_event($event_obj, $attr_name){
    /* Prend comme parametres : 
        un obj évènement (de la fonction get_event_in_tick()) 
        le nom d'un attribut que l'on veut extraire
       Renvoie l'obj attribut ou NULL 
    */
    foreach($event_obj['attrs'] as $attr){
        if($attr['key'] == $attr_name){
            return $attr;
        }
    }
    return NULL;
}

function get_player_in_snapshot($snapshot_obj, $player_id){
    /* Prend comme parametres : 
        un obj snapshot
        l'id d'un joueur 
       Renvoie l'obj du joueur correspondant ou NULL 
    */
    foreach($snapshot_obj['entityUpdates'] as $entity){
        if($entity['entityId'] == $player_id){
            return $entity;
        }
    }
    return NULL;
}
function get_last_player_info($demo, $snap_tick, $player_id){
    /* fonction récursive pour récupérer la dernière info d'un joueur
    Si le joueur est mort, il n'y a plus d'info à chaque tick sur lui
    ET il est possible que le tick juste avant ne suffise pas pour récupérer ces infos.
    Cette fonction boucle en enlevant un tick à chaque fois jusqu'a avoir l'info du joueur

    Parametres :
        l'obj demo
        le tick
        l'id du joueur
    Renvoie l'obj du joueur correspondant
    */
    $player = get_player_in_snapshot($demo['snapshots'][$snap_tick], $player_id);
    if(is_null($player)){
        return get_last_player_info($demo, $snap_tick-1, $player_id);
    }
    else{
        return $player;
    }
}
function get_last_player_pos($demo, $snap_tick, $player_id){
    /*renvoie la derniere pos du joueur
    Parametre
        l'obj demo
        le tick
        l'id du joueur
    Renvoie la position du joueur sous la forme d'un dict avec comme clés x y et z
    */
    return get_last_player_info($demo, $snap_tick, $player_id)['positions'][0];
}
function get_last_player_side($demo, $snap_tick, $player_id){
    /*renvoie la derniere équipe du joueur
    Parametre
        l'obj demo
        le tick
        l'id du joueur
    Renvoie l'equipe du joueur sous la forme d'un entier
    */
    return get_last_player_info($demo, $snap_tick, $player_id)['team'];
}
?>