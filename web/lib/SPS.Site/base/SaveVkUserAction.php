<?php
/**
 * User: x100up
 * Date: 22.12.12 11:59
 * In Code We Trust
 */
abstract class SaveVkUserAction extends BaseSaveAction
{
    public function __construct() {
        $this->options = array(
            BaseFactory::WithoutDisabled => false,
            BaseFactory::WithLists => true
        );
    }

    /**
     * @param Author $Author
     */
    protected function afterSaveAuthor(Author $Author){
        /** @var $Editor Editor */
        $Editor = EditorFactory::Get(array('vkId' => $Author->vkId));
        $exist = true;
        if (!$Editor) {
            $Editor = new Editor();
            $Editor->vkId = $Author->vkId;
            $exist = false;
        }
        $Editor->avatar = $Author->avatar;
        $Editor->firstName = $Author->firstName;
        $Editor->lastName = $Author->lastName;
        $Editor->statusId = $Author->statusId;
        if ($exist){
            EditorFactory::Update($Editor);
        } else {
            EditorFactory::Add($Editor);
        }
    }

    /**
     * @param Editor $Editor
     */
    protected function afterSaveEditor(Editor $Editor){
        /** @var $Author Author */
        $Author = AuthorFactory::Get(array('vkId' => $Editor->vkId));
        $exist = true;
        if (!$Author) {
            $Author = new Author();
            $Author->vkId = $Editor->vkId;
            $exist = false;
        }
        $Author->avatar = $Editor->avatar;
        $Author->firstName = $Editor->firstName;
        $Author->lastName = $Editor->lastName;
        $Author->statusId = $Editor->statusId;
        if ($exist){
            AuthorFactory::Update($Editor);
        } else {
            AuthorFactory::Add($Editor);
        }
    }

    /**
     * Update Editor|Author
     * @param Editor|Author $object
     * @return bool
     */
    protected function update( $object ) {
        $result = parent::$factory->Update( $object );
        if ($result) {
            if ($object instanceof Author) {
                $this->afterSaveAuthor($object);
            }
            if ($object instanceof Editor) {
                $this->afterSaveEditor($object);
            }
        }
        return $result;
    }

    /**
     * Validate Object
     *
     * @param Editor $object
     * @return array
     */
    protected function validate( $object ) {
        $errors = parent::$factory->Validate( $object );
        return $errors;
    }

    /**
     * Set Foreign Lists
     */
    protected function setForeignLists() {
        $targetFeeds = TargetFeedFactory::Get( null, array( BaseFactory::WithoutDisabled => false ) );
        Response::setArray( 'targetFeeds', $targetFeeds );
    }
}
