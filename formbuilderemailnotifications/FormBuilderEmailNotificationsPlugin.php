<?php
/**
 * Email notifications for Form Builder
 *
 * @author    Vadim Goncharov
 * @copyright Copyright (c) 2017 Vadim Goncharov
 * @link      http://owl-design.net
 * @package   EmailNotifications
 * @since     1.0.0
 */

namespace Craft;

class FormBuilderEmailNotificationsPlugin extends BasePlugin
{
    /**
     * @return mixed
     */
    public function init()
    {
        parent::init();

        craft()->templates->hook('formBuilder.formNotificationsHtml', function(&$context) {
            $template = craft()->templates->render('formbuilderemailnotifications/email-notifications', array(
                'context' => $context,
            ));

            return $template;
        });
    }

    /**
     * @return mixed
     */
    public function getName()
    {
         return Craft::t('Email Notifications');
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return Craft::t('Email notifications for Form Builder');
    }

    /**
     * @return string
     */
    public function getDocumentationUrl()
    {
        return 'https://github.com/owldesign/Email-Notifications---Form-Builder';
    }

    /**
     * @return string
     */
    public function getReleaseFeedUrl()
    {
        return 'https://github.com/owldesign/Email-Notifications---Form-Builder/master/releases.json';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @return string
     */
    public function getSchemaVersion()
    {
        return '1.0.0';
    }

    /**
     * @return string
     */
    public function getDeveloper()
    {
        return 'Vadim Goncharov';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://owl-design.net';
    }

    /**
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }

    /**
    * Plugin settings.
    *
    * @return array
    */
    protected function defineSettings()
    {
        return array(
            'webhookUrl' => array(AttributeType::String),
        );
    }

    /**
     * Get settings html
     *
     * @return string
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('formbuilderemailnotifications/settings', array(
            'settings' => $this->getSettings()
        ));
    }

    /**
     * Required plugin fields
     *
     * @return array
     */
    public function getRequiredFields()
    {

        return null;
    }

    /**
     */
    public function onBeforeInstall()
    {
    }

    /**
     */
    public function onAfterInstall()
    {
    }

    /**
     */
    public function onBeforeUninstall()
    {
        return formbuilder()->forms->clearEmailNotifications();
    }

    /**
     */
    public function onAfterUninstall()
    {
    }
}

function emailNotifications()
{
    return Craft::app()->getComponent('formBuilderEmailNotifications');
}