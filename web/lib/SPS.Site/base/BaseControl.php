<?php
/**
 * User: x100up
 * Date: 15.12.12 15:45
 * In Code We Trust
 */
abstract class BaseControl {
    /**
     * @var int
     */
    protected $vkId;

    /**
     *
     */
    public function __construct(){
        $this->vkId = AuthVkontakte::IsAuth();
    }

    /**
     * @var bool|Author
     */
    private $currentAuthor = false;

    /**
     * @return Author
     */
    protected function getAuthor(){
        if ($this->currentAuthor === false) {
            $this->currentAuthor = AuthorFactory::GetOne(array('vkId' => $this->vkId));
        }
        return $this->currentAuthor;
    }
}
