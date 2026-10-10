<?php

if(!empty($on) && empty($_SESSION['TourField3D'])) {
    if (subFeatureAcl($acl, AclParticipants, 'pTarget') == AclReadWrite) {
        if (isset($ret['PART']['TARG'])) {
            array_splice($ret['PART']['TARG'], 3, 0, get_text('MenuLM_DragDropTarget') .'|'.$CFG->ROOT_DIR.'Modules/DragDropTarget/Qualification/index.php');
        }
    }
}
