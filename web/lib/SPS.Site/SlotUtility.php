<?php
class SlotUtility {
    /**
     * Удобнее для использования в контролах, связанных со статьями (не со слотами)
     * @param int $targetFeedId
     * @param bool $canEditQueue Используется внутри include!
     * @return string
     */
    public static function renderEmptyOld($targetFeedId, $canEditQueue) {
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

    /**
     * @param GridLineItem $GridItem
     * @param DateTimeWrapper $Date
     * @param bool $canEditQueue Используется внутри шаблона!
     * @return string
     */
    public static function renderNew($GridItem, $Date, $canEditQueue) {
        $gridItem = array(
            'gridLineId' => $GridItem->gridLineId,
            'dateTime' => new DateTimeWrapper($Date->DefaultDateFormat() . ' ' . $GridItem->time->DefaultTimeFormat()),
            'repeat' => $GridItem->repeat,
            'gridLineItemId' => null,
            'startDate' => $GridItem->startDate,
            'endDate' => $GridItem->endDate,
        );

        ob_start();
        include Template::GetCachedRealPath('tmpl://fe/elements/articles-queue-list-item.tmpl.php');
        return ob_get_clean();
    }
}

?>
