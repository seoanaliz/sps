<?php
/**
 * User: x100up
 * Date: 16.12.12 13:50
 * In Code We Trust
 */
class UserGroup {

    /**
     * Идентификатор группы
     * @var int
     */
    public $userGroupId;

    /**
     * Лента отправки
     * @var int
     */
    public $targetFeedId;

    /**
     * Название группы
     * @var string
     */
    public $name;

    public function toArray(){
        return array(
            'id' => $this->userGroupId,
            'name' => $this->name
        );
    }
}
