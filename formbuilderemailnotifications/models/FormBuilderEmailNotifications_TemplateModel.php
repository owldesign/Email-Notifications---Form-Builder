<?php
namespace Craft;

class FormBuilderEmailNotifications_TemplateModel extends BaseElementModel
{
    // Protected Methods
    // =========================================================================

    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'       => AttributeType::Number,
            'name'     => AttributeType::Name,
            'handle'   => array(AttributeType::Handle, 'required' => true),
            'content'  => AttributeType::Mixed,
            'styles'   => AttributeType::Mixed,
            'settings' => AttributeType::Mixed
        ));
    }
}