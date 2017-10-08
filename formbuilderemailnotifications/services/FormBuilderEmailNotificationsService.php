<?php
namespace Craft;

class FormBuilderEmailNotificationsService extends BaseApplicationComponent
{

    // Properties
    // =========================================================================

    public $templates;

    private $form;
    private $entry;
    private $template = 'formbuilderemailnotifications/frontend/email/html';
    private $defaultTemplate = 'formbuilderemailnotifications/frontend/email/default';

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $this->templates = Craft::app()->getComponent('formBuilderEmailNotifications_template');
    }

    /**
     * Prepare notification
     *
     * @param FormBuilder_EntryModel $entry
     * @param $post
     */
    public function prepareNotification(FormBuilder_EntryModel $entry, $notifications, $post)
    {

        if (isset($post['contextEntryId'])) {
            $contextEntry = craft()->entries->getEntryById($post['contextEntryId']);
        } else {
            $contextEntry = null;
        }

        $this->form = $entry->getForm();
        $this->entry = $entry;

        foreach ($notifications as $key => $notification) {
            $enabled = $notification['enabled'] == '1' ? true : false;

            if ($enabled) {
                $emailModel = $this->_prepareEmailModel($entry, $notification, $contextEntry);
                $this->sendMail($emailModel);
            }
        }
    }

    /**
     * Send email
     *
     * @param EmailModel $email
     * @return bool
     */
    public function sendMail(EmailModel $email)
    {
        return craft()->email->sendEmail($email);
    }

    // Private Methods
    // =========================================================================

    /**
     * Prepare email model
     *
     * @param $type
     * @return EmailModel
     */
    private function _prepareEmailModel($entry, $notification, $contextEntry)
    {
        $emailModel             = new EmailModel();
        $emailModel->fromEmail  = $notification['fromEmail'] != '' ? $notification['fromEmail'] : craft()->systemSettings->getSetting('email', 'emailAddress');
        $emailModel->fromName   = $notification['fromEmail'] != '' ? $notification['fromEmail'] : craft()->systemSettings->getSetting('email', 'senderName');
        
        foreach ($notification['toEmail'] as $type => $value) {
            $target = $notification['toEmail']['selected'] != '' ? $notification['toEmail']['selected'] : null;
            switch ($target) {
                case 'twig':
                    if ($contextEntry) {
                        $emailModel->toEmail = $contextEntry->{$notification['toEmail']['twig']};
                    } else {
                        throw new HttpException(404, Craft::t('Context not available.'));
                    }
                    break;
                case 'field':
                    $emailModel->toEmail = $entry->{$notification['toEmail']['field']};
                    break;
                default:
                    $emailModel->toEmail = craft()->systemSettings->getSetting('email', 'emailAddress');
                    break;
            }
        }

        foreach ($notification['replyTo'] as $type => $value) {
            $target = $notification['replyTo']['selected'] != '' ? $notification['replyTo']['selected'] : null;
            switch ($target) {
                case 'twig':
                    if ($contextEntry) {
                        $emailModel->replyTo = $contextEntry->{$notification['replyTo']['twig']};
                    } else {
                        throw new HttpException(404, Craft::t('Context not available.'));
                    }
                    break;
                case 'field':
                    $emailModel->replyTo = $entry->{$notification['replyTo']['field']};
                    break;
                default:
                    $emailModel->replyTo = craft()->systemSettings->getSetting('email', 'emailAddress');
                    break;
            }
        }

        if (isset($notification['template']['enabled']) && $notification['template']['enabled'] == '1') {
            $emailModel->body     = $this->_renderSiteTemplateIfExists($this->template . '.txt', [
                'templateHandle'    => $notification['template'],
                'notification'      => $notification,
                'entry'             => $this->entry,
                'form'              => $this->form
            ]);
            $emailModel->htmlBody = $this->_renderSiteTemplateIfExists($this->template, [
                'templateHandle'    => $notification['template'],
                'notification'      => $notification,
                'entry'             => $this->entry,
                'form'              => $this->form
            ]);
        } else {
            $emailModel->body     = $this->_renderSiteTemplateIfExists($this->defaultTemplate . '.txt', [
                'entry'           => $this->entry,
                'form'            => $this->form
            ]);
            $emailModel->htmlBody = $this->_renderSiteTemplateIfExists($this->defaultTemplate, [
                'entry'           => $this->entry,
                'form'            => $this->form
            ]);
        }

        foreach ($notification['subject'] as $type => $value) {
            $target = $notification['subject']['selected'] != '' ? $notification['subject']['selected'] : null;
            switch ($target) {
                case 'twig':
                    if ($contextEntry) {
                        $emailModel->subject = $contextEntry->{$notification['subject']['twig']};
                    } else {
                        throw new HttpException(404, Craft::t('Context not available.'));
                    }
                    break;
                case 'field':
                    $emailModel->subject = $entry->{$notification['subject']['field']};
                    break;
                case 'text':
                    $emailModel->subject = $notification['subject']['text'];
                    break;
                default:
                    $emailModel->subject = Craft::t('You have new submission');
                    break;
            }
        }

        if (isset($notification['cc'])) {
            $cc = array();
            foreach ($notification['cc'] as $item) {
                $cc[] = array(
                    'name' => $item['key'],
                    'email' => $item['value']
                );
            }
            $emailModel->cc = $cc;
        }

        if (isset($notification['bcc'])) {
            $bcc = array();
            foreach ($notification['bcc'] as $item) {
                $bcc[] = array(
                    'name' => $item['key'],
                    'email' => $item['value']
                );
            }
            $emailModel->bcc = $bcc;
        }

        if (isset($notification['headers'])) {
            $customHeaders = array();
            foreach ($notification['headers'] as $item) {
                $customHeaders[] = array(
                    'name' => $item['key'],
                    'value' => $item['value']
                );
            }
            $emailModel->customHeaders = $customHeaders;
        }

        foreach ($this->form->getFieldLayout()->getFields() as $field){
            $type = $field->field->fieldType;
            $theField = $field->field;

            if (get_class($type) === 'Craft\\AssetsFieldType') {
                $criteria = $this->entry->{$theField->handle};

                if ($criteria instanceof ElementCriteriaModel) {
                    $assets = $criteria->find();
                    foreach ($assets as $asset) {
                        $type = $asset->getSource()->getSourceType();
                        $path = null;

                        if (get_class($type) === 'Craft\\S3AssetSourceType') {
                            $path = $type->getImageSourcePath($asset);
                        } else {
                            $path = $asset->getSource()->getSourceType()->getBasePath() . $asset->getFolder()->path . $asset->filename;
                        }

                        $emailModel->addAttachment($path, $asset->filename, 'base64', $asset->mimeType);
                    }
                }
            }
        }

        return $emailModel;
    }

    /**
     * Render HTML email
     *
     * @param $template
     * @param array $variables
     * @return null|string
     */
    private function _renderSiteTemplateIfExists($template, array $variables = array())
    {
        $renderedTemplate = null;

        if (empty($template)) {
            return $renderedTemplate;
        }

        $oldPath = craft()->templates->getTemplatesPath();
        craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

        try {
            $renderedTemplate = craft()->templates->render($template, $variables);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        craft()->templates->setTemplatesPath($oldPath);

        return $renderedTemplate;
    }
}