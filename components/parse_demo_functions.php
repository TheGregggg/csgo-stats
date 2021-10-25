<?php
function get_event_in_tick($tick_obj, $event_name)
{
    foreach($tick_obj['events'] as $event){
        if($event['name'] == $event_name){
            return $event;
        }
    }
    return null;
}

function get_attr_in_event($event_obj, $attr_name)
{
    foreach($event_obj['attrs'] as $attr){
        if($attr['key'] == $attr_name){
            return $attr;
        }
    }
    return null;
}

function get_player_in_snapshot($snapshot_obj, $player_id)
{
    foreach($snapshot_obj['entityUpdates'] as $entity){
        if($entity['entityId'] == $player_id){
            return $entity;
        }
    }
    return null;
}
?>