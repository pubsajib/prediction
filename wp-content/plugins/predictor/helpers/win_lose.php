<?php 
function winLoseHtml($prediction, $type='', $limit=false) {
    $data = '';
    if (!empty($prediction['wl'])) {
        $winLose = winLoseDataBy($type, $prediction['wl']);
        $winLoseCount = count($winLose);
        $data .= '<ul class="list-unstyled winLoseContainer" total='. $winLoseCount .'>';
        if ($limit) {
            if ($limit > $winLoseCount) $limit = $winLoseCount;
            for ($i=0; $i < $limit; $i++) {
                $data .= WLStatusItem($winLose[$i]);
            }
        } else {
            foreach ($winLose as $wl) {
                $data .= WLStatusItem($wl);
            }
        }
        $data .= '</ul>';
    }
    return $data;
}
function winLoseDataBy($type='', $predictions) {
    if (!$type) return $predictions;
    $data = [];
    foreach ($predictions as $prediction) {
        if ($prediction['type'] == $type) {
            $data[] = $prediction;
        }
    }
    return $data;
}
function WLStatusItem($wl) {
    $type = empty($wl['type']) ? '' : $wl['type'];
    if ($wl['status'] === 'abandon') { $wlNode = 'A'; }
    else if ($wl['status']) { $wlNode = 'W'; }
    else { $wlNode = 'L'; }
    return '<li class="'. $type .' '. $wlNode .'" event="'. $wl["event"] .'" team="'. $wl['team'] .'" item="'. $wl['item'] .'">&nbsp;</li>';
}