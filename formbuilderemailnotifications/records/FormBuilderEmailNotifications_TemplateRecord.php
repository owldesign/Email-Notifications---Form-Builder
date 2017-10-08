<?php
namespace Craft;


class FormBuilderEmailNotifications_TemplateRecord extends BaseRecord
{
    /**
     * @return string
     */
    public function getTableName()
    {
        return 'emailnotifications_templates';
    }

    /**
     * @return array
     */
    public function defineIndexes()
    {
        return array(
            array('columns' => array('id'), 'unique' => true),
            array('columns' => array('handle'), 'unique' => true)
        );
    }

    /**
     * @return array
     */
    public function scopes()
    {
        return array(
            'ordered' => array('order' => 'id')
        );
    }

    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'name'                  => array(AttributeType::Name, 'required' => true),
            'handle'                => array(AttributeType::Handle, 'required' => true),
            'sortOrder'             => AttributeType::SortOrder,
            'content'               => AttributeType::Mixed,
            'styles'                => AttributeType::Mixed,
            'settings'              => AttributeType::Mixed
        );
    }
}