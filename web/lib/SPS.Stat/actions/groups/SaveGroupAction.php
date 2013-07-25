<?php
    /**
     * Save Group Action
     * 
     * @package Stat
     * @subpackage
     * */

class SaveGroupAction extends BaseSaveAction  {

    /**
     * Constructor
     */
    public function __construct() {
        $this->options = array(
              BaseFactory::WithoutDisabled => false
            , BaseFactory::WithLists       => true
        );

        parent::$factory = new GroupFactory();
        $this->connectionName = 'tst';
    }


    /**
     * Form Object From Request
     *
     * @param Group $originalObject
     * @return Group
     */
    protected function getFromRequest( $originalObject = null ) {
        /**
         * @var Group $object
         */

        $object = parent::$factory->GetFromRequest();
        if ( $originalObject != null ) {
            $object->group_id = $originalObject->group_id;
        }

        return $object;
    }


    /**
     * Validate Object
     *
     * @param Group $object
     * @return array
     */
    protected function validate( $object ) {
        $errors = parent::$factory->Validate( $object );

        return $errors;
    }


    /**
     * Add Object
     *
     * @param Group $object
     * @return bool
     */
    protected function add( $object ) {
        $result = parent::$factory->Add( $object );

        return $result;
    }


    /**
     * Update Object
     *
     * @param Group $object
     * @return bool
     */
    protected function update( $object ) {
        $id = Page::$RequestData[1];
        if(!$id)
            return false;
        $new_object = parent::$factory->GetById($id);
        $new_object->slug      = $object->slug;
        $new_object->name      = $object->name;
        $new_object->group_id  = $object->group_id;

        $result = parent::$factory->Update( $new_object );

        return $result;
    }


    /**
     * Set Foreign Lists
     */
    protected function setForeignLists() {}

    protected function delete( $object ) {
        return self::$factory->PhysicalDelete( $object );
    }
}