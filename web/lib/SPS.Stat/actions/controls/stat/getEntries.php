<?php
/**
 * addPrice Action
 * @package    SPS
 * @subpackage Stat
 */
set_time_limit(10);
class getEntries {

    /**
     * Entry Point
     */
    public function Execute()
    {
        error_reporting( 0 );

        $EntryGetter = new EntryGetter();
        echo ObjectHelper::ToJSON( array('response' => $EntryGetter->getEntriesData()) );
    }
}
?>