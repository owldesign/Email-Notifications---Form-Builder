<?php
namespace Craft;

class FormBuilderEmailNotificationsModel extends BaseElementModel
{
    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'includeSubmission'     => AttributeType::Bool,
            'fromEmail'             => AttributeType::Email,
            'fromName'              => AttributeType::String,
            'toEmail'               => AttributeType::Email,
            'replyTo'               => AttributeType::Email,
            'subject'               => AttributeType::Mixed,
            'template'              => AttributeType::String,
            'attachments'           => AttributeType::Bool,
            'cc'                    => AttributeType::Mixed,
            'bcc'                   => AttributeType::Mixed,
            'customHeaders'         => AttributeType::Mixed
        );
    }
}