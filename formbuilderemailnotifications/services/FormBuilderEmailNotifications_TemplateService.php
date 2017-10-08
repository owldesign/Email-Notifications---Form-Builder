<?php
namespace Craft;


class FormBuilderEmailNotifications_TemplateService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $_templatesById;
    private $_allTemplateIds;
    private $_fetchedAllTemplates = false;

    // Public Methods
    // =========================================================================

    /**
     * Get all templates
     *
     * @return EmailNotifications_TemplateModel|array|null
     */
    public function getAllTemplates()
    {
        $forms = craft()->db->createCommand()
            ->select('*')
            ->from('emailnotifications_templates')
            ->order('sortOrder asc')
            ->queryAll();

        return FormBuilderEmailNotifications_TemplateModel::populateModels($forms);
    }

    /**
     * Get total number of templates
     *
     * @return int
     */
    public function getTotalTemplates()
    {
        return count($this->getAllTemplateIds());
    }

    /**
     * Get all email ids
     *
     * @return array|\CDbDataReader
     */
    public function getAllTemplateIds()
    {
        if (!isset($this->_allTemplateIds)) {
            if ($this->_fetchedAllTemplates) {
                $this->_allTemplateIds = array_keys($this->_templatesById);
            } else {
                $this->_allTemplateIds = craft()->db->createCommand()
                    ->select('id')
                    ->from('emailnotifications_templates')
                    ->queryColumn();
            }
        }

        return $this->_allTemplateIds;
    }

    /**
     * Save email
     *
     * @param EmailNotifications_TemplateModel $template
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function save(FormBuilderEmailNotifications_TemplateModel $template)
    {
        if ($template->id) {
            $templateRecord = FormBuilderEmailNotifications_TemplateRecord::model()->findById($template->id);

            if (!$templateRecord) {
                throw new Exception(Craft::t('No email exists with the ID “{id}”', array('id' => $template->id)));
            }

            $oldTemplate = FormBuilderEmailNotifications_TemplateModel::populateModel($templateRecord);
            $isNewTemplate = false;
        } else {
            $templateRecord = new FormBuilderEmailNotifications_TemplateRecord();
            $isNewTemplate = true;
        }

        $templateRecord->name       = $template->name;
        $templateRecord->handle     = $template->handle;
        $templateRecord->content    = JsonHelper::encode($template->content);
        $templateRecord->styles     = JsonHelper::encode($template->styles);
        $templateRecord->settings   = JsonHelper::encode($template->settings);

        $templateRecord->validate();
        $template->addErrors($templateRecord->getErrors());

        if (!$template->hasErrors()) {
            $transaction = craft()->db->getCurrentTransaction() ? false : craft()->db->beginTransaction();

            try {

                $templateRecord->save(false);

                if (!$template->id) {
                    $template->id = $templateRecord->id;
                }

                if ($transaction !== null) {
                    $transaction->commit();
                }

            } catch (\Exception $e) {
                if ($transaction !== null) {
                    $transaction->rollback();
                }

                throw $e;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete email by id
     *
     * @param $templateId
     * @return bool
     * @throws \Exception
     */
    public function deleteTemplateById($templateId)
    {
        if (!$templateId) {
            return false;
        }

        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

        try {
            $affectedRows = craft()->db->createCommand()->delete('emailnotifications_templates', array('id' => $templateId));

            if ($transaction !== null) {
                $transaction->commit();
            }
            return (bool) $affectedRows;
        } catch (\Exception $e) {
            if ($transaction !== null) {
                $transaction->rollback();
            }
            throw $e;
        }
    }

    /**
     * Get email by id
     *
     * @param $templateId
     * @return BaseModel
     * @throws Exception
     */
    public function getTemplateById($templateId)
    {
        $templateRecord = FormBuilderEmailNotifications_TemplateRecord::model()->findById($templateId);

        if ($templateRecord) {
            return FormBuilderEmailNotifications_TemplateModel::populateModel($templateRecord);
        } else {
            throw new Exception(404, Craft::t('No email exists with the ID “{id}”', array('id' => $$templateId)));
        }
    }

    /**
     * Get email by handle
     *
     * @param $handle
     * @return array|mixed|null
     */
    public function getTemplateByHandle($handle)
    {
        $templateRecord = FormBuilderEmailNotifications_TemplateRecord::model()->findByAttributes([
            'handle' => $handle
        ]);

        return FormBuilderEmailNotifications_TemplateModel::populateModel($templateRecord);
    }

    /**
     * Save email list order
     *
     * @param $templateIds
     * @return bool
     * @throws \Exception
     */
    public function reorderTemplates($templateIds)
    {
        $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

        try {
            foreach ($templateIds as $template => $templateId) {
                $templateRecord            = $this->_getTemplateRecordById($templateId);
                $templateRecord->sortOrder = $template + 1;
                $templateRecord->save();
            }

            if ($transaction !== null) {
                $transaction->commit();
            }
        } catch (\Exception $e) {
            if ($transaction !== null) {
                $transaction->rollback();
            }

            throw $e;
        }

        return true;
    }

    /**
     * Get assets by id
     *
     * @param $ids
     * @return array
     */
    public function getAssetsById($ids)
    {
        $criteria = craft()->elements->getCriteria(ElementType::Asset);
        $criteria->id = $ids;
        $assets = $criteria->find();

        return $assets;
    }

    // Private Methods
    // =========================================================================


    /**
     * Get email record by id
     *
     * @param null $templateId
     * @return BaseRecord|EmailNotifications_TemplateRecord
     * @throws Exception
     */
    private function _getTemplateRecordById($templateId = null)
    {
        if ($templateId) {
            $templateRecord = FormBuilderEmailNotifications_TemplateRecord::model()->findById($templateId);

            if (!$templateRecord) {
                throw new Exception(Craft::t('No email exists with the ID “{id}”.', array('id' => $templateId)));
            }
        } else {
            $templateRecord = new FormBuilderEmailNotifications_TemplateRecord();
        }

        return $templateRecord;
    }
}