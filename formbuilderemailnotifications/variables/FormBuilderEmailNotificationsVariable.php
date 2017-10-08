<?php
namespace Craft;

class FormBuilderEmailNotificationsVariable
{
    public function getAllTemplates()
    {
        return emailNotifications()->templates->getAllTemplates();
    }
}