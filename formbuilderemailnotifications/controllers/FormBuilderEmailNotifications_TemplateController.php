<?php
namespace Craft;


class FormBuilderEmailNotifications_TemplateController extends BaseController
{
    /**
     * Edit template
     *
     * @param array $variables
     * @throws HttpException
     */
    public function actionEdit(array $variables = array())
    {

        $variables['saveShortcutRedirect'] = 'formbuilder/templates/{id}/edit';
        $variables['continueEditingUrl'] = 'formbuilder/templates/{id}/edit';

        if (!empty($variables['templateId'])) {
            if (empty($variables['template'])) {
                $variables['template'] = emailNotifications()->templates->getTemplateById($variables['templateId']);

                if (!$variables['template']) {
                    throw new HttpException(404, Craft::t('No templates exist.'));
                }
            }
            $variables['title'] = $variables['template']->name;
        } else {
            if (empty($variables['template'])) {
                $variables['template'] = new EmailNotifications_TemplateModel();
            }
            $variables['title'] = Craft::t('Create a new email');
        }

        // Load Redactor Scripts for Rich Text fields
        craft()->templates->includeCssResource('/lib/redactor/redactor.css');
        craft()->templates->includeJsResource('/lib/redactor/redactor.min.js');
        craft()->templates->includeCssResource('formbuilder/css/libs/alignment.css');
        craft()->templates->includeJsResource('formbuilder/js/libs/alignment.js');
        craft()->templates->includeJsResource('formbuilder/js/libs/fontfamily.js');
        craft()->templates->includeJsResource('formbuilder/js/libs/fontsize.js');
        craft()->templates->includeJsResource('formbuilder/js/libs/fontcolor.js');
        craft()->templates->includeJsResource('formbuilder/js/libs/colorpicker.js');

        $this->renderTemplate('emailnotifications/templates/_edit', $variables);
    }

    /**
     * Save template
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $template = new EmailNotifications_TemplateModel();

        $template->id         = craft()->request->getPost('templateId');
        $template->name       = craft()->request->getPost('name');
        $template->handle     = craft()->request->getPost('handle');
        $template->content    = craft()->request->getPost('content');
        $template->styles     = craft()->request->getPost('styles');
        $template->settings   = craft()->request->getPost('settings');

        if (emailNotifications()->templates->save($template)) {
            craft()->userSession->setNotice(Craft::t('Template saved.'));
            $this->redirectToPostedUrl($template);
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t save email.'));
        }

        craft()->urlManager->setRouteVariables(array(
            'email' => $template
        ));
    }

    /**
     * Delete template
     */
    public function actionDelete()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $templateId = craft()->request->getRequiredPost('id');
        $totalTemplates = emailNotifications()->templates->getTotalTemplates();

        if (emailNotifications()->templates->deleteTemplateById($templateId)) {
            $this->returnJson(array(
                'success' => true,
                'count' => $totalTemplates
            ));
        } else {
            $this->returnJson(array(
                'success' => false,
                'count' => $totalTemplates
            ));
        }

    }

    /**
     * Reorder template list
     *
     * @return null
     */
    public function actionReorder()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $ids = JsonHelper::decode(craft()->request->getRequiredPost('ids'));

        if ($success = emailNotifications()->templates->reorderTemplates($ids)) {
            return $this->returnJson(array('success' => $success));
        }

        return $this->returnJson(array('error' => Craft::t("Couldn't reorder templates.")));
    }
}