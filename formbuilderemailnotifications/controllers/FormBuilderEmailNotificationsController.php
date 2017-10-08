<?php
namespace Craft;

class FormBuilderEmailNotificationsController extends BaseController
{

    protected $allowAnonymous = array('actionAddNotification');

    public function actionAddNotification()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $index = craft()->request->getPost('index');
        $context = craft()->request->getPost('context');
        $formId = craft()->request->getPost('formId');

        if (!empty($formId)) {
            $form = formbuilder()->forms->getFormRecordById($formId);
            if (!$form) {
                throw new HttpException(404);
            }
        } else {
            $form = new FormBuilder_FormModel();
        }

        // notificationModel = new EmailNotificationsModel();

        $variables['form'] = $form;
        $variables['index'] = $index;
        $variables['context'] = $context;
        $variables['formId'] = $formId;

        $oldPath = craft()->templates->getTemplatesPath();
        craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

        $markup = craft()->templates->render('formbuilderemailnotifications/_includes/_notification-markup', $variables);

        craft()->templates->setTemplatesPath($oldPath);

        $this->returnJson(array(
            'success'   => true,
            'markup'    => $markup
        ));
    }
}