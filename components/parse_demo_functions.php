<?php
function get_event_in_tick($tick_obj, $event_name)
{
    foreach($tick_obj['events'] as $event){
        if($event['name'] == $event_name){
            return $event;
        }
    }
    return NULL;
}

function get_attr_in_event($event_obj, $attr_name)
{
    foreach($event_obj['attrs'] as $attr){
        if($attr['key'] == $attr_name){
            return $attr;
        }
    }
    return NULL;
}

function get_player_in_snapshot($snapshot_obj, $player_id)
{
    foreach($snapshot_obj['entityUpdates'] as $entity){
        if($entity['entityId'] == $player_id){
            return $entity;
        }
    }
    return NULL;
}
function get_last_player_info($demo, $snap_tick, $player_id){
    $player = get_player_in_snapshot($demo['snapshots'][$snap_tick], $player_id);
    if(is_null($player)){
        return get_last_player_info($demo, $snap_tick-1, $player_id);
    }
    else{
        return $player;
    }
}
function get_last_player_pos($demo, $snap_tick, $player_id){
    return get_last_player_info($demo, $snap_tick, $player_id)['positions'][0];
}
function get_last_player_side($demo, $snap_tick, $player_id){
    return get_last_player_info($demo, $snap_tick, $player_id)['team'];
}
?>