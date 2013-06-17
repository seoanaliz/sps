<?php
class SlotUtility {
    public static function renderEmpty($targetFeedId, $canEditQueue) {
        $timestamp = Request::getInteger('timestamp');
        $date = date('d.m.Y', !empty($timestamp) ? $timestamp : null);
        $grid = GridLineUtility::GetGrid($targetFeedId, $date, Request::getString('type'));
        $gridItem = null;
        foreach ($grid as $key => $gi) {
            if ($gi['gridLineId'] == Request::getString('gridId')) {
                $gridItem = $gi;
                break; // --------------------- BREAK
            }
        }
        if (!$gridItem) {
            return ''; // --------------------- RETURN
        }

        ob_start();
        include Template::GetCachedRealPath('tmpl://fe/elements/articles-queue-list-item.tmpl.php');
        $html = ob_get_clean();
        return $html;
    }
}

?>
