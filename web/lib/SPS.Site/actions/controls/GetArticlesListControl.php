<?php
/**
 * Конторллер списка постов для Socialboard
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class GetArticlesListControl extends BaseGetArticlesListControl {

    /**
     * Entry Point
     */
    public function Execute()
    {
        $this->processRequest();
        $this->getObjects();
        $this->setData();
    }
}

?>
