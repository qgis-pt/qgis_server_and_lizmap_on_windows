<?php
/**
 * Edition tool web services.
 *
 * @author    3liz
 * @copyright 2011-2019 3liz
 *
 * @see      http://3liz.com
 *
 * @license Mozilla Public License : http://www.mozilla.org/MPL/
 */
class editionCtrl extends jController
{
    /** @var null|lizmapProject */
    private $project;

    /** @var lizmapRepository */
    private $repository;

    /** @var string layer id in the QGIS project file */
    private $layerId = '';

    /** @var string layer name (<layername> in QGIS project) */
    private $layerName = '';

    /** @var string featureId parameter from the request, an integer or a string with coma separated integers */
    private $featureIdParam;

    /** @var int|string an integer or an array of integers */
    private $featureId;

    /** @var object feature data as a PHP object from GeoJSON via json_decode */
    private $featureData;

    /** @var SimpleXMLElement Layer data as simpleXml object */
    private $layerXml = '';

    /** @var qgisVectorLayer Layer data */
    private $layer = '';

    /** @var string[] Primary key for getWfsFeature */
    private $primaryKeys = array();

    /** @var string Geometry column for form */
    private $geometryColumn = '';

    // Geometry srid for form
    private $srid = '';

    // Geometry proj4 string for form
    private $proj4 = '';

    /** @var bool Filter override flag */
    private $loginFilteredOverride = false;

    /**
     * @var string error message during responses processing
     */
    private $errorMessage = '';

    /**
     * @var string error type during responses processing
     */
    private $errorType = 'default';

    protected function setErrorMessage($message, $type = 'default')
    {
        $this->errorMessage = $message;
        $this->errorType = $type;
    }

    /**
     * Send an answer.
     *
     * @return jResponseHtmlFragment HTML fragment
     */
    protected function serviceAnswer()
    {
        if ($this->errorMessage !== '') {
            jMessage::add($this->errorMessage, $this->errorType);
        }
        $title = jLocale::get('view~edition.modal.title.default');

        // Get title layer
        if ($this->layer) {
            $_title = $this->layer->getTitle();
            if ($_title && $_title != '') {
                $title = $_title;
            }
        }

        $rep = $this->getResponse('htmlfragment');
        $tpl = new jTpl();
        $tpl->assign('title', $title);
        $content = $tpl->fetch('view~jmessage_answer');
        $rep->addContent($content);
        jMessage::clearAll();

        return $rep;
    }

    /**
     * Get parameters and set classes for the project and repository given.
     *
     * @param bool $save If true, we have to save the form. So take liz_repository and others instead of repository from request parameters.
     *
     * @return bool
     */
    private function getEditionParameters($save = false)
    {

        // Get the project
        if ($save) {
            $project = $this->param('liz_project');
            $repository = $this->param('liz_repository');
            $layerId = $this->param('liz_layerId');
            $featureIdParam = $this->param('liz_featureId');
        } else {
            $project = $this->param('project');
            $repository = $this->param('repository');
            $layerId = $this->param('layerId');
            $featureIdParam = $this->param('featureId');
        }

        if (!$project) {
            $this->setErrorMessage(jLocale::get('view~edition.message.error.parameter.project'), 'ProjectNotDefined');

            return false;
        }

        // Get repository data
        $lrep = lizmap::getRepository($repository);
        if (!$lrep) {
            $this->setErrorMessage('The repository '.strtoupper($repository).' does not exist !', 'RepositoryNotDefined');

            return false;
        }
        // Get the project data
        $lproj = null;

        try {
            $lproj = lizmap::getProject($repository.'~'.$project);
            if (!$lproj) {
                $this->setErrorMessage('The lizmapProject '.strtoupper($project).' does not exist !', 'ProjectNotDefined');

                return false;
            }
        } catch (UnknownLizmapProjectException $e) {
            $this->setErrorMessage('The lizmapProject '.strtoupper($project).' does not exist !', 'ProjectNotDefined');

            return false;
        }

        // Redirect if no rights to access this repository
        if (!$lproj->checkAcl()) {
            $this->setErrorMessage(jLocale::get('view~default.repository.access.denied'), 'AuthorizationRequired');

            return false;
        }

        // Redirect if no rights to use the edition tool
        if (!jAcl2::check('lizmap.tools.edition.use', $lrep->getKey())) {
            $this->setErrorMessage(jLocale::get('view~edition.access.denied'), 'AuthorizationRequired');

            return false;
        }

        $layer = $lproj->getLayer($layerId);
        if (!$layer) {
            $this->setErrorMessage(jLocale::get('view~edition.message.error.layer.editable'), 'LayerNotEditable');

            return false;
        }
        $layerXml = $layer->getXmlLayer();
        $layerName = $layer->getName();

        // Verifying if the layer is editable
        if (!$layer->isEditable()) {
            $this->setErrorMessage(jLocale::get('view~edition.message.error.layer.editable'), 'LayerNotEditable');

            return false;
        }
        $eLayer = $layer->getEditionCapabilities();

        // Check if user groups intersects groups allowed by project editor
        // If user is admin, no need to check for given groups
        if (jAuth::isConnected() and !jAcl2::check('lizmap.admin.repositories.delete') and property_exists($eLayer, 'acl') and $eLayer->acl) {
            // Check if configured groups white list and authenticated user groups list intersects
            $editionGroups = $eLayer->acl;
            $editionGroups = array_map('trim', explode(',', $editionGroups));
            if (is_array($editionGroups) and count($editionGroups) > 0) {
                $userGroups = jAcl2DbUserGroup::getGroups();
                if (!array_intersect($editionGroups, $userGroups)) {
                    $this->setErrorMessage(jLocale::get('view~edition.access.denied'), 'AuthorizationRequired');

                    return false;
                }
            }
        }

        // feature Id (optional, only for edition and save)
        if (strpos($featureIdParam, ',') !== false) {
            $featureId = preg_split('#,#', $featureIdParam);
        } elseif (strpos($featureIdParam, '@@') !== false) {
            $featureId = preg_split('#@@#', $featureIdParam);
        } else {
            $featureId = $featureIdParam;
        }

        // Define class private properties
        $this->project = $lproj;
        $this->repository = $lrep;
        $this->layerId = $layerId;
        $this->featureId = $featureId;
        $this->featureIdParam = $featureIdParam;
        $this->layer = $layer;
        $this->layerXml = $layerXml;
        $this->layerName = $layerName;

        // Optionnaly filter data by login
        $this->loginFilteredOverride = jAcl2::check('lizmap.tools.loginFilteredLayers.override', $lrep->getKey());

        $dbFieldsInfo = $this->layer->getDbFieldsInfo();
        $this->primaryKeys = $dbFieldsInfo->primaryKeys;
        $this->geometryColumn = $dbFieldsInfo->geometryColumn;
        $this->srid = $this->layer->getSrid();
        $this->proj4 = $this->layer->getProj4();

        return true;
    }

    private function getWfsFeature()
    {
        $featureId = $this->featureId;

        // Get features primary key field values corresponding to featureId(s)
        if (!empty($featureId) || $featureId === 0 || $featureId === '0') {
            $typename = $this->layer->getShortName();
            if (!$typename || $typename == '') {
                $typename = str_replace(' ', '_', $this->layer->getName());
            }
            if (is_array($featureId)) {
                // QGIS3 (at least <=3.4) doesn't support pk with multiple fields
                // but 2.18 supports it.
                $featureId = $typename.'.'.implode('@@', $featureId);
            } else {
                $featureId = $typename.'.'.$featureId;
            }

            $propertyName = array_merge(array(), $this->primaryKeys);
            if (!$this->loginFilteredOverride) {
                $loginFilteredConfig = $this->project->getLoginFilteredConfig($this->layer->getName());
                if ($loginFilteredConfig && property_exists($loginFilteredConfig, 'filterAttribute')) {
                    $propertyName[] = $loginFilteredConfig->filterAttribute;
                }
            }

            $wfsparams = array(
                'SERVICE' => 'WFS',
                'VERSION' => '1.0.0',
                'REQUEST' => 'GetFeature',
                'TYPENAME' => $typename,
                'OUTPUTFORMAT' => 'GeoJSON',
                'GEOMETRYNAME' => 'none',
                'PROPERTYNAME' => implode(',', $propertyName),
                'FEATUREID' => $featureId,
            );

            $wfsrequest = new lizmapWFSRequest($this->project, $wfsparams);
            // FIXME no support of the case where $wfsresponse is the content of serviceException?
            $wfsresponse = $wfsrequest->process();
            if (property_exists($wfsresponse, 'data')) {
                $data = $wfsresponse->data;
                if (property_exists($wfsresponse, 'file') && $wfsresponse->file && is_file($data)) {
                    $data = jFile::read($data);
                }
                $this->featureData = json_decode($data);
                if (empty($this->featureData)) {
                    $this->featureData = null;
                } elseif (empty($this->featureData->features)) {
                    $this->featureData = null;
                }
            }
        }
    }

    /**
     * Create a feature form based on the edition layer.
     *
     * @urlparam string $repository Lizmap Repository
     * @urlparam string $project Name of the project
     * @urlparam string $layerId Qgis id of the layer
     *
     * @return jResponseHtmlFragment|jResponseRedirect redirect to the display action
     */
    public function createFeature()
    {

        // Get repository, project data and do some right checking
        if (!$this->getEditionParameters()) {
            return $this->serviceAnswer();
        }

        // Get editLayer capabilities
        $eCapabilities = $this->layer->getEditionCapabilities();
        if ($eCapabilities->capabilities->createFeature != 'True') {
            jMessage::add(jLocale::get('view~edition.message.error.layer.editable.create'), 'LayerNotEditable');

            return $this->serviceAnswer();
        }

        jForms::destroy('view~edition', $this->featureId);
        // Create form instance
        $form = jForms::create('view~edition', $this->featureId);
        $form->setData('liz_future_action', $this->param('liz_future_action', 'close'));

        // event to add custom field in the jForms form
        jEvent::notify('LizmapEditionCreateForm', array(
            'form' => $form,
            'action' => 'create',
            'project' => $this->project,
            'repository' => $this->repository,
            'layer' => $this->layer,
            'status' => $this->param('status', 0),
        ));

        // Redirect to the display action
        $rep = $this->getResponse('redirect');
        $rep->params = array(
            'project' => $this->project->getKey(),
            'repository' => $this->repository->getKey(),
            'layerId' => $this->layerId,
            'status' => $this->param('status', 0),
        );
        $rep->action = 'lizmap~edition:editFeature';

        return $rep;
    }

    /**
     * Modify a feature.
     *
     * @urlparam string $repository Lizmap Repository
     * @urlparam string $project Name of the project
     * @urlparam string $layerId Qgis id of the layer
     * @urlparam integer $featureId Id of the feature.
     *
     * @return jResponseHtmlFragment|jResponseRedirect redirect to the display action
     */
    public function modifyFeature()
    {

        // Get repository, project data and do some right checking
        if (!$this->getEditionParameters()) {
            return $this->serviceAnswer();
        }

        // Check if data has been fetched via WFS for the feature
        $this->getWfsFeature();
        if (!$this->featureData) {
            jMessage::add(jLocale::get('view~edition.message.error.feature.get'), 'featureNotFoundViaWfs');

            return $this->serviceAnswer();
        }

        if (!$this->loginFilteredOverride) {
            // Get filter by login
            $expByUser = qgisExpressionUtils::getExpressionByUser($this->layer, true);
            if ($expByUser !== '') {
                $results = qgisExpressionUtils::evaluateExpressions(
                    $this->layer,
                    array('filterByLogin' => $expByUser),
                    $this->featureData->features[0]
                );

                if ($results
                    && property_exists($results, 'filterByLogin')
                    && $results->filterByLogin !== 1) {
                    $this->setErrorMessage(jLocale::get('view~edition.message.error.feature.editable'), 'FeatureNotEditable');

                    return $this->serviceAnswer();
                }
            }
        }

        // Create form instance
        $form = jForms::create('view~edition', $this->featureId);
        if (!$form) {
            jMessage::add(jLocale::get('view~edition.message.error.form.get'), 'formNotDefined');

            return $this->serviceAnswer();
        }
        $form->setData('liz_future_action', $this->param('liz_future_action', 'close'));
        // event to add custom field in the jForms form
        jEvent::notify('LizmapEditionCreateForm', array(
            'form' => $form,
            'action' => 'modify',
            'project' => $this->project,
            'repository' => $this->repository,
            'layer' => $this->layer,
            'featureId' => $this->featureId,
            'featureData' => $this->featureData,
            'status' => $this->param('status', 0),
        ));

        // Redirect to the display action
        $rep = $this->getResponse('redirect');
        $rep->params = array(
            'project' => $this->project->getKey(),
            'repository' => $this->repository->getKey(),
            'layerId' => $this->layerId,
            'featureId' => $this->featureIdParam,
            'status' => $this->param('status', 0),
        );

        $rep->action = 'lizmap~edition:editFeature';

        return $rep;
    }

    /**
     * Display the edition form (output as html fragment).
     *
     * @return jResponseHtmlFragment|jResponseRedirect HTML code containing the form
     */
    public function editFeature()
    {

        // Get repository, project data and do some right checking
        if (!$this->getEditionParameters()) {
            return $this->serviceAnswer();
        }

        // Check if data has been fetched via WFS for the feature
        $this->getWfsFeature();
        if (($this->featureId || $this->featureId === 0 || $this->featureId === '0') && !$this->featureData) {
            jMessage::add(jLocale::get('view~edition.message.error.feature.get'), 'featureNotFoundViaWfs');

            return $this->serviceAnswer();
        }

        if (!$this->loginFilteredOverride
            && ($this->featureId || $this->featureId === 0 || $this->featureId === '0')
            && $this->featureData) {
            // Get filter by login
            $expByUser = qgisExpressionUtils::getExpressionByUser($this->layer, true);
            if ($expByUser !== '') {
                $results = qgisExpressionUtils::evaluateExpressions(
                    $this->layer,
                    array('filterByLogin' => $expByUser),
                    $this->featureData->features[0]
                );

                if ($results
                    && property_exists($results, 'filterByLogin')
                    && $results->filterByLogin !== 1) {
                    $this->setErrorMessage(jLocale::get('view~edition.message.error.feature.editable'), 'FeatureNotEditable');

                    return $this->serviceAnswer();
                }
            }
        }

        // Get the form instance
        $form = jForms::get('view~edition', $this->featureId);
        if (!$form) {
            jMessage::add(jLocale::get('view~edition.message.error.form.get'), 'formNotDefined');

            return $this->serviceAnswer();
        }
        // Set lizmap form controls (hard-coded in the form xml file)
        $form->setData('liz_repository', $this->repository->getKey());
        $form->setData('liz_project', $this->project->getKey());
        $form->setData('liz_layerId', $this->layerId);
        $form->setData('liz_featureId', $this->featureIdParam);

        // event to add custom field into the jForms form before setting data in it
        $eventParams = array(
            'form' => $form,
            'project' => $this->project,
            'repository' => $this->repository,
            'layer' => $this->layer,
            'featureId' => $this->featureId,
            'featureData' => $this->featureData,
            'status' => $this->param('status', 0),
        );
        jEvent::notify('LizmapEditionEditGetForm', $eventParams);

        // Dynamically add form controls based on QGIS layer information
        $qgisForm = null;

        try {
            $qgisForm = new qgisForm($this->layer, $form, $this->featureId, $this->loginFilteredOverride);
        } catch (Exception $e) {
            jMessage::add($e->getMessage(), 'error');

            return $this->serviceAnswer();
        }

        // Set data for the layer geometry: srid, proj4 and geometryColumn
        $form->setData('liz_srid', $this->srid);
        $form->setData('liz_proj4', $this->proj4);
        $form->setData('liz_geometryColumn', $this->geometryColumn);

        // Set status data to communicate client that the form is reopened after successfull save
        $form->setData('liz_status', $this->param('status', 0));

        // Set future action (close forme, reopen saved form, create new feature)
        // Redirect to the edition form or to the validate message
        $faCtrl = $form->getControl('liz_future_action');
        $faData = $faCtrl->datasource->data;
        $eCapabilities = $this->layer->getEditionCapabilities();
        if ($eCapabilities->capabilities->createFeature != 'True') {
            unset($faData['create']);
        }
        if ($eCapabilities->capabilities->modifyAttribute != 'True') {
            unset($faData['edit']);
        }
        $faCtrl->datasource = new jFormsStaticDatasource();
        $faCtrl->datasource->data = $faData;
        $faCtrl->defaultValue = array(
            0 => $form->getData('liz_future_action'),
        );

        // SELECT data from the database and set the form data accordingly
        $form = $qgisForm->setFormDataFromDefault();
        if ($this->featureId) {
            $form = $qgisForm->setFormDataFromFields($this->featureData->features[0]);
        } elseif ($form->hasUpload()) {
            $repPath = $this->repository->getPath();
            $dtParams = $this->layer->getDatasourceParameters();
            foreach ($form->getUploads() as $upload) {
                $DefaultRoot = $qgisForm->getQgisControl($upload->ref)->DefaultRoot;
                // If not default root is set, the use old method media/upload/projectname/tablename/
                $targetPath = 'media/upload/'.$this->project->getKey().'/'.$dtParams->tablename.'/'.$upload->ref.'/';
                $targetFullPath = $repPath.$targetPath;
                // Else use given root, but only if it is a child or brother of the repository path
                if (!empty($DefaultRoot)) {
                    jFile::createDir($repPath.$DefaultRoot); // Need to create it to then make the realpath checks
                    if (
                        (substr(realpath($repPath.$DefaultRoot), 0, strlen(realpath($repPath))) === realpath($repPath))
                        or (substr(realpath($repPath.$DefaultRoot), 0, strlen(realpath($repPath.'/../'))) === realpath($repPath.'/../'))
                    ) {
                        $targetPath = $DefaultRoot;
                        $targetFullPath = realpath($repPath.$DefaultRoot);
                    }
                }

                if (!is_dir($targetFullPath)) {
                    $createDir = jFile::createDir($targetFullPath);
                }

                $choiceRef = $upload->ref.'_choice';
                $choiceCtrl = $form->getControl($choiceRef);
                if ($choiceCtrl) {
                    $form->setData($choiceRef, 'update');
                    $choiceCtrl->itemsNames['update'] = jLocale::get('view~edition.upload.choice.update');
                    $choiceCtrl->deactivateItem('keep');
                    $choiceCtrl->deactivateItem('delete');
                }
                if (!is_dir($targetFullPath) or !is_writable($targetFullPath)) {
                    $form->setErrorOn($upload->ref, jLocale::get('view~edition.message.error.upload.layer', array($dtParams->tablename)));
                } else {
                    $refPath = $targetFullPath;
                    if (!is_dir($refPath)) {
                        jFile::createDir($refPath);
                    }
                    if (!is_dir($refPath) or !is_writable($refPath)) {
                        $form->setErrorOn($upload->ref, jLocale::get('view~edition.message.error.upload.layer.field', array($choiceCtrl->label, $dtParams->tablename)));
                    }
                }
            }
        }

        // If the user has been redirected here from the saveFeature method
        // Set the form controls data from the request parameters
        if ($this->param('error')) {
            $token = $this->param('error');
            if (isset($_SESSION[$token.$this->layerId]) and $_SESSION[$token.$this->layerId]) {
                foreach ($_SESSION[$token.$this->layerId] as $ctrl => $data) {
                    $form->setData($ctrl, $data);
                }
                unset($_SESSION[$token.$this->layerId]);
            }
        }
        $form = $qgisForm->updateFormByLogin();

        // Get title layer
        $title = $this->layer->getTitle();
        if (!$title || $title == '') {
            $title = 'No title';
        }

        // Prepare template
        $attributeEditorForm = $qgisForm->getAttributesEditorForm();
        $form = $qgisForm->getForm();

        // Use template to create html form content
        $tpl = new jTpl();
        $tpl->assign('attributeEditorForm', $attributeEditorForm);
        $tpl->assign('fieldNames', $qgisForm->getFieldNames());
        $tpl->assign('title', $title);
        $tpl->assign('form', $form);
        $tpl->assign('formPlugins', $qgisForm->getFormPlugins());
        $tpl->assign('ajaxNewFeatureUrl', jUrl::get('lizmap~edition:saveNewFeature'));
        $tpl->assign('groupVisibilities', qgisExpressionUtils::evaluateGroupVisibilities($attributeEditorForm, $form));

        // event to add custom fields into the jForms form, or to modify those that
        // have been added by QgisForm, and to inject custom data into the template
        // useful if the template has been redefined in a theme
        $eventParams['qgisForm'] = $qgisForm;
        $eventParams['tpl'] = $tpl;
        jEvent::notify('LizmapEditionEditQgisForm', $eventParams);

        $content = $tpl->fetch('view~edition_form');

        // Return html fragment response
        $rep = $this->getResponse('htmlfragment');
        $rep->addContent($content);

        return $rep;
    }

    /**
     * Save the edition form (output as html fragment).
     *
     * @urlparam string $repository Lizmap Repository
     * @urlparam string $project Name of the project
     * @urlparam string $layerId Qgis id of the layer
     * @urlparam integer $featureId Id of the feature.
     *
     * @return jResponseHtmlFragment|jResponseRedirect redirect to the validation action
     */
    public function saveFeature()
    {

        // Get repository, project data and do some right checking
        $save = true;
        if (!$this->getEditionParameters($save)) {
            return $this->serviceAnswer();
        }

        // Get the form instance
        $form = jForms::get('view~edition', $this->featureId);

        if (!$form) {
            jMessage::add(jLocale::get('view~edition.message.error.form.get'), 'formNotDefined');

            return $this->serviceAnswer();
        }

        $this->getWfsFeature();

        // event to add custom field into the jForms form before setting data in it
        $eventParams = array(
            'form' => $form,
            'project' => $this->project,
            'repository' => $this->repository,
            'layer' => $this->layer,
            'featureId' => $this->featureId,
            'featureData' => $this->featureData,
            'status' => $this->param('status', 0),
        );
        jEvent::notify('LizmapEditionSaveGetForm', $eventParams);

        // Dynamically add form controls based on QGIS layer information
        // And save data into the edition table (insert or update line)
        $save = true;
        $qgisForm = null;

        try {
            $qgisForm = new qgisForm($this->layer, $form, $this->featureId, $this->loginFilteredOverride);
        } catch (Exception $e) {
            jMessage::add($e->getMessage(), 'error');

            return $this->serviceAnswer();
        }

        // event to add or modify some control after QgisForm has added its own controls
        $eventParams['qgisForm'] = $qgisForm;
        jEvent::notify('LizmapEditionSaveGetQgisForm', $eventParams);

        // SELECT data from the database and set the form data accordingly
        // or reset form controls data to null to check modified fields
        // and save default data
        $defaultFormData = array();
        if ($this->featureId) {
            $form = $qgisForm->setFormDataFromFields($this->featureData->features[0]);
        } else {
            $defaultFormData = $form->getAllData();
            $form = $qgisForm->resetFormData();
        }
        // Track modified records
        $form->initModifiedControlsList();
        // Apply default data to get save it
        foreach ($defaultFormData as $ref => $val) {
            if ($val !== null) {
                $form->setdata($ref, $val);
            }
        }
        // Get data from the request and set the form controls data accordingly
        $form->initFromRequest();

        // Check the form data and redirect if needed
        $feature = null;
        if ($this->featureId || $this->featureId === 0 || $this->featureId === '0') {
            $feature = $this->featureData->features[0];
        }
        $check = $qgisForm->check($feature);

        // event to add additionnal checks
        $event = jEvent::notify('LizmapEditionSaveCheckForm', $eventParams);
        if ($event->allResponsesByKeyAreTrue('check') === false) {
            $check = false;
        }

        $rep = $this->getResponse('redirect');
        $rep->params = array(
            'project' => $this->project->getKey(),
            'repository' => $this->repository->getKey(),
            'layerId' => $this->layerId,
            'featureId' => $this->featureIdParam,
        );

        // Save data into database
        // And get returned primary key values
        $pkvals = null;
        if ($check) {
            // Check if featureId is null to get all controls or only modified controls
            if ($this->featureId == null) {
                // Save to database with all controls
                $pkvals = $qgisForm->saveToDb($feature, $form->getControls());
            } else {
                // Save to database with modified controls
                $pkvals = $qgisForm->saveToDb($feature, $form->getModifiedControls());
            }
        }

        // Some errors where encoutered
        if (!$check || !$pkvals) {
            // Redirect to the display action
            $token = uniqid('lizform_');
            $rep->params['error'] = $token;
            $rep->params['status'] = '1';

            // Build array of data for all the controls
            // And save it in session
            $controlData = array();
            foreach (array_keys($form->getControls()) as $ctrl) {
                $controlData[$ctrl] = $form->getData($ctrl);
            }
            $_SESSION[$token.$this->layerId] = $controlData;

            $rep->action = 'lizmap~edition:editFeature';

            return $rep;
        }

        // Redirect to the edition form or to the validate message
        $next_action = $form->getData('liz_future_action');
        if ($next_action == 'close') {
            // Redirect to the close action
            $rep->action = 'lizmap~edition:closeFeature';

            return $rep;
        }

        // Use edition capabilities
        $eCapabilities = $this->layer->getEditionCapabilities();

        // CREATE NEW FEATURE
        if ($next_action == 'create'
            && $eCapabilities->capabilities->createFeature == 'True'
        ) {
            jMessage::add(jLocale::get('view~edition.form.data.saved'), 'success');
            $rep->params = array(
                'project' => $this->project->getKey(),
                'repository' => $this->repository->getKey(),
                'layerId' => $this->layerId,
                'liz_future_action' => $next_action,
            );
            // Destroy form and redirect to create
            if ($form = jForms::get('view~edition', $this->featureId)) {
                jForms::destroy('view~edition', $this->featureId);
            }
            $rep->action = 'lizmap~edition:createFeature';

            return $rep;
        }
        // REOPEN THE FORM FOR THE EDITED FEATURE
        // If there is a single integer primary key
        // This is the featureid, we can redirect to the edition form
        // for the newly created or the updated feature
        if ($next_action == 'edit'
            // and if capabilities is ok for attribute modification
            && $eCapabilities->capabilities->modifyAttribute == 'True'
            // if we have retrieved the pkeys only one integer pkey
            && is_array($pkvals) and count($pkvals) == 1
        ) {
            //Get the fields info
            $dbFieldsInfo = $this->layer->getDbFieldsInfo();
            foreach ($dbFieldsInfo->primaryKeys as $key) {
                if ($dbFieldsInfo->dataFields[$key]->unifiedType != 'integer') {
                    break;
                }
                jMessage::add(jLocale::get('view~edition.form.data.saved'), 'success');
                $rep->params = array(
                    'project' => $this->project->getKey(),
                    'repository' => $this->repository->getKey(),
                    'layerId' => $this->layerId,
                    'featureId' => $pkvals[$key], // use the one returned by the query, not the one in the class property
                    'liz_future_action' => $next_action,
                );
                // Destroy form and redirect to create
                if ($form = jForms::get('view~edition', $this->featureId)) {
                    jForms::destroy('view~edition', $this->featureId);
                }
                $rep->action = 'lizmap~edition:modifyFeature';

                return $rep;
            }
        }

        // Else redirect to the validate method to destroy the form
        // Redirect to the close action
        $rep->action = 'lizmap~edition:closeFeature';

        return $rep;
    }

    /**
     * Form close : destroy it and display a message.
     *
     * @urlparam string $repository Lizmap Repository
     * @urlparam string $project Name of the project
     * @urlparam string $layerId Qgis id of the layer
     * @urlparam integer $featureId Id of the feature.
     *
     * @return jResponseHtmlFragment confirmation message that the form has been saved
     */
    public function closeFeature()
    {

        // Get repository, project data and do some right checking
        if (!$this->getEditionParameters()) {
            return $this->serviceAnswer();
        }

        // Destroy the form
        if ($form = jForms::get('view~edition', $this->featureId)) {
            jForms::destroy('view~edition', $this->featureId);
            // Return html fragment response
            jMessage::add(jLocale::get('view~edition.form.data.saved'), 'success');

            return $this->serviceAnswer();
        }
        // undefined form : redirect to error
        jMessage::add(jLocale::get('view~edition.message.error.form.get'), 'error');

        return $this->serviceAnswer();
    }

    /**
     * Delete Feature (output as html fragment).
     *
     * @urlparam string $repository Lizmap Repository
     * @urlparam string $project Name of the project
     * @urlparam string $layerId Qgis id of the layer
     * @urlparam integer $featureId Id of the feature.
     *
     * @return jResponseHtmlFragment
     */
    public function deleteFeature()
    {
        if (!$this->getEditionParameters()) {
            return $this->serviceAnswer();
        }

        // Check if data has been fetched via WFS for the feature
        $this->getWfsFeature();
        if (!$this->featureData) {
            jMessage::add(jLocale::get('view~edition.message.error.feature.get'), 'featureNotFoundViaWfs');

            return $this->serviceAnswer();
        }

        // Get editLayer capabilities
        $eCapabilities = $this->layer->getEditionCapabilities();
        if ($eCapabilities->capabilities->deleteFeature != 'True') {
            jMessage::add(jLocale::get('view~edition.message.error.layer.editable.delete'), 'LayerNotEditable');

            return $this->serviceAnswer();
        }

        if (!$this->featureId && $this->featureId !== 0 && $this->featureId !== '0') {
            jMessage::add(jLocale::get('view~edition.message.error.parameter.featureId'), 'error');

            return $this->serviceAnswer();
        }

        // Create form instance to get uploads file
        $form = jForms::create('view~edition', $this->featureId);
        if (!$form) {
            jMessage::add('An error has been raised when creating the form', 'formNotDefined');

            return $this->serviceAnswer();
        }

        $qgisForm = null;

        try {
            $qgisForm = new qgisForm($this->layer, $form, $this->featureId, $this->loginFilteredOverride);
        } catch (Exception $e) {
            jMessage::add($e->getMessage(), 'error');

            return $this->serviceAnswer();
        }

        $form = $qgisForm->setFormDataFromDefault();
        $feature = $this->featureData->features[0];
        $form = $qgisForm->setFormDataFromFields($feature);

        $deleteFiles = array();
        if ($form->hasUpload()) {
            foreach ($form->getUploads() as $upload) {
                $choiceRef = $upload->ref.'_choice';
                $value = $form->getData($upload->ref);
                $hiddenValue = $form->getData($upload->ref.'_hidden');
                $repPath = $this->repository->getPath();
                if ($hiddenValue && file_exists(realpath($repPath).'/'.$hiddenValue)) {
                    $deleteFiles[] = realpath($repPath).'/'.$hiddenValue;
                }
            }
        }

        // event to add additionnal checks
        $eventParams = array(
            'form' => $form,
            'qgisForm' => $qgisForm,
            'project' => $this->project,
            'repository' => $this->repository,
            'layer' => $this->layer,
            'featureId' => $this->featureId,
            'featureData' => $this->featureData,
            'filesToDelete' => $deleteFiles,
        );
        $event = jEvent::notify('LizmapEditionPreDelete', $eventParams);
        if ($event->allResponsesByKeyAreTrue('filesDeleted')) {
            $deleteFiles = array();
        }

        try {
            $rs = $qgisForm->deleteFromDb($feature);
            if ($rs) {
                jMessage::add(jLocale::get('view~edition.message.success.delete'), 'success');
                $eventParams['deleteFiles'] = $deleteFiles;
                $eventParams['success'] = true;
                foreach ($deleteFiles as $path) {
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            } else {
                jMessage::add(jLocale::get('view~edition.message.error.delete'), 'error');
                $eventParams['success'] = false;
            }
        } catch (Exception $e) {
            jLog::log('An error has been raised when saving form data edition to db:', 'error');
            jLog::logEx($e, 'error');
            jMessage::add(jLocale::get('view~edition.message.error.delete'), 'error');
            $eventParams['success'] = false;
        }
        jEvent::notify('LizmapEditionPostDelete', $eventParams);

        return $this->serviceAnswer();
    }

    /**
     * Save a new feature, without redirecting to an HTML response.
     *
     * @urlparam string $repository Lizmap Repository
     * @urlparam string $project Name of the project
     * @urlparam string $layerId Qgis id of the layer
     * @urlparam integer $featureId Id of the feature.
     *
     * @return jResponseJson
     */
    public function saveNewFeature()
    {
        /** @var jResponseJson $rep */
        $rep = $this->getResponse('json');
        $rep->data = array('success' => true);

        // Get repository, project data and do some right checking
        if (!$this->getEditionParameters(true)) {
            $rep->data['success'] = false;
            $rep->data['message'] = $this->errorMessage;

            return $rep;
        }

        // Get the form instance
        $form = jForms::create('view~edition', '____new__feature___');
        if (!$form) {
            $rep->data['success'] = false;
            $rep->data['message'] = jLocale::get('view~edition.message.error.form.get');

            return $rep;
        }

        // event to add custom field into the jForms form before setting data in it
        $eventParams = array(
            'form' => $form,
            'project' => $this->project,
            'repository' => $this->repository,
            'layer' => $this->layer,
            'featureId' => $this->featureId,
            'featureData' => $this->featureData,
            'status' => $this->param('status', 0),
        );
        jEvent::notify('LizmapEditionSaveGetForm', $eventParams);

        // Dynamically add form controls based on QGIS layer information
        // And save data into the edition table (insert or update line)
        $qgisForm = null;

        try {
            $qgisForm = new qgisForm($this->layer, $form, $this->featureId, $this->loginFilteredOverride);
        } catch (Exception $e) {
            $rep->data['success'] = false;
            $rep->data['message'] = $e->getMessage();

            return $rep;
        }

        // event to add or modify some control after QgisForm has added its own controls
        $eventParams['qgisForm'] = $qgisForm;
        jEvent::notify('LizmapEditionSaveGetQgisForm', $eventParams);

        // Get data from the request and set the form controls data accordingly
        $form->initFromRequest();

        // Check the form data and redirect if needed
        $check = $form->check();

        // event to add additionnal checks
        $event = jEvent::notify('LizmapEditionSaveCheckForm', $eventParams);
        if ($event->allResponsesByKeyAreTrue('check') === false || !$check) {
            $rep->data['success'] = false;
            $rep->data['message'] = 'There are some errors in the form';

            return $rep;
        }

        // Check geometry
        $modifyGeometry = $this->layer->getEditionCapabilities()->capabilities->modifyGeometry;
        if (strtolower($modifyGeometry) == 'true' && $this->geometryColumn != '' && $form->getData($this->geometryColumn) == '') {
            $rep->data['success'] = false;
            $rep->data['message'] = jLocale::get('view~edition.message.error.no.geometry');

            return $rep;
        }

        // Save data into database
        // And get returned primary key values
        $feature = null;
        if ($this->featureId || $this->featureId === 0 || $this->featureId === '0') {
            $feature = $this->featureData->features[0];
        }
        $pkvals = $qgisForm->saveToDb($feature);

        jForms::destroy('view~edition', '____new__feature___');

        // Some errors where encoutered
        if (!$check || !$pkvals) {
            $rep->data['success'] = false;
            $rep->data['message'] = 'Error during the save of the feature';

            return $rep;
        }

        return $rep;
    }

    /**
     * Editable features.
     *
     * @urlparam string $repository Lizmap Repository
     * @urlparam string $project Name of the project
     * @urlparam string $layerId Qgis id of the layer
     *
     * @return jResponseJson
     */
    public function editableFeatures()
    {
        /** @var jResponseJson $rep */
        $rep = $this->getResponse('json');
        $rep->data = array('success' => false);

        $project = $this->param('project');
        $repository = $this->param('repository');
        $layerId = $this->param('layerId');

        if (!$project) {
            $rep->data['message'] = jLocale::get('view~edition.message.error.parameter.project');

            return $rep;
        }

        // Get repository data
        $lrep = lizmap::getRepository($repository);
        if (!$lrep) {
            $rep->data['message'] = 'The repository '.strtoupper($repository).' does not exist !';

            return $rep;
        }

        // Get the project data
        $lproj = null;

        try {
            $lproj = lizmap::getProject($repository.'~'.$project);
            if (!$lproj) {
                $rep->data['message'] = 'The lizmapProject '.strtoupper($project).' does not exist !';

                return $rep;
            }
        } catch (UnknownLizmapProjectException $e) {
            $rep->data['message'] = 'The lizmapProject '.strtoupper($project).' does not exist !';

            return $rep;
        }

        // Redirect if no rights to access this repository
        if (!$lproj->checkAcl()) {
            $rep->data['message'] = jLocale::get('view~default.repository.access.denied');

            return $rep;
        }

        // Redirect if no rights to use the edition tool
        if (!jAcl2::check('lizmap.tools.edition.use', $lrep->getKey())) {
            $rep->data['message'] = jLocale::get('view~edition.access.denied');

            return $rep;
        }

        $layer = $lproj->getLayer($layerId);
        if (!$layer) {
            $rep->data['message'] = jLocale::get('view~edition.message.error.layer.editable');

            return $rep;
        }

        $rep->data['success'] = true;
        $rep->data['message'] = 'Success';
        $rep->data = array_merge($rep->data, $layer->editableFeatures());

        return $rep;
    }

    /**
     * Link features between 2 tables :
     * - Either [1->n] relation, ie a parent layer and a child layer. In this case, passed $features2 layer id = $pivot.
     *   In this case, we set the parent id in the child table foreign key column
     * - Or [n<->m] relation ie 2 tables with a pivot table between them
     *   In this case we add a new line in the pivot table referencing both parent layers.
     *
     * @urlparam string $repository Lizmap Repository
     * @urlparam string $project Name of the project
     * @urlparam string $pivot Pivot layer id. Example : mypivot1234
     * @urlparam string $features1 Layer id + features. Example : mylayer456:1,2
     * @urlparam string $features2 Layer id + features. Example : otherlayer789:5
     * @urlparam integer $featureId Id of the feature.
     *
     * @return jResponseHtmlFragment
     */
    public function linkFeatures()
    {
        $project = $this->param('project');
        $repository = $this->param('repository');

        // Get repository data
        $lrep = lizmap::getRepository($repository);
        if (!$lrep) {
            jMessage::add('The repository '.strtoupper($repository).' does not exist !', 'RepositoryNotDefined');

            return $this->serviceAnswer();
        }

        // Get the project data
        $lproj = null;

        try {
            $lproj = lizmap::getProject($repository.'~'.$project);
            if (!$lproj) {
                jMessage::add('The lizmapProject '.strtoupper($project).' does not exist !', 'ProjectNotDefined');

                return $this->serviceAnswer();
            }
        } catch (UnknownLizmapProjectException $e) {
            jMessage::add('The lizmapProject '.strtoupper($project).' does not exist !', 'ProjectNotDefined');

            return $this->serviceAnswer();
        }

        $this->project = $lproj;
        $this->repository = $lrep;

        // Check the mandatory parameters features1 & features2
        $features1 = $this->param('features1');
        $features2 = $this->param('features2');
        $pivotId = $this->param('pivot');
        if (!$features1 || !$features2 || !$pivotId) {
            jMessage::add(jLocale::get('view~edition.link.error.missing.parameter'), 'error');

            return $this->serviceAnswer();
        }

        // Cut layers id and features ids
        $exp1 = explode(':', $features1);
        $exp2 = explode(':', $features2);
        if (count($exp1) != 3 || count($exp2) != 3) {
            jMessage::add(jLocale::get('view~edition.link.error.missing.parameter'), 'error');

            return $this->serviceAnswer();
        }

        // Get the list of features ids given for each layer
        $ids1 = explode(',', $exp1[2]);
        $ids2 = explode(',', $exp2[2]);
        if (count($ids1) > 1 and count($ids2) > 1) {
            jMessage::add(jLocale::get('view~edition.link.error.multiple.ids'), 'error');

            return $this->serviceAnswer();
        }

        // Not enough id given
        if (count($ids1) == 0 || count($ids2) == 0 || empty($exp1[2]) || empty($exp2[2])) {
            jMessage::add(jLocale::get('view~edition.link.error.missing.id'), 'error');

            return $this->serviceAnswer();
        }

        // Get layer names
        $layer1 = $lproj->getLayer($exp1[0]);
        $layer2 = $lproj->getLayer($exp2[0]);
        if (!$layer1 || !$layer2) {
            jMessage::add(jLocale::get('view~edition.link.error.wrong.layer'), 'error');

            return $this->serviceAnswer();
        }
        $layerName1 = $layer1->getName();
        $layerName2 = $layer2->getName();

        // Verifying the layers are present in the attribute table configuration
        $pConfig = $lproj->getFullCfg();
        if (!$lproj->hasAttributeLayers()
            || !property_exists($pConfig->attributeLayers, $layerName1)
            || !property_exists($pConfig->attributeLayers, $layerName2)
        ) {
            jMessage::add(jLocale::get('view~edition.link.error.not.attribute.layer'), 'error');

            return $this->serviceAnswer();
        }

        // Get the child layer (which can be a third pivot table) qgisVectorLayer instance
        /** @var qgisVectorLayer $layer The QGIS vector layer instance */
        $layer = $lproj->getLayer($pivotId);
        $layerNamePivot = $layer->getName();
        $this->layerId = $pivotId;
        $this->layerName = $layerNamePivot;
        $this->layer = $layer;

        // Get the editing capabilities for the child layer (which can be a third pivot table)
        $eLayer = $layer->getEditionCapabilities();

        // Check if we are in a 1-n relation or in a n-m relation with a pivot layer
        // If the name of the 2nd layer is the same that the pivot layer, we are in a 1-n relation
        $isPivot = false;
        if ($layerNamePivot != $layerName2) {
            // pivot layer (n:m)
            $isPivot = true;
            if ($eLayer->capabilities->createFeature != 'True') {
                jMessage::add(jLocale::get('view~edition.link.error.no.create.feature', array($layerNamePivot)), 'LayerNotEditable');

                return $this->serviceAnswer();
            }
        } else {
            // child layer (1:n)
            $isPivot = false;
            if ($eLayer->capabilities->modifyAttribute != 'True') {
                jMessage::add(jLocale::get('view~edition.link.error.no.modify.attributes', array($layerNamePivot)), 'LayerNotEditable');

                return $this->serviceAnswer();
            }
        }

        // For the 1-n relation, do not allow multiple ids for the parent layer
        // to avoid wrong data editing (a child feature cannot have 2 parent ids)
        // It could be ok if we have a n-m relation with a pivot table, by definition
        // but it could lead to many unwanted links if we do not prevent it also
        // Forbid it for both cases.
        if (count($ids1) > 1 && count($ids2) > 1) {
            jMessage::add(jLocale::get('view~edition.link.error.multiple.ids'), 'error');

            return $this->serviceAnswer();
        }

        // Get fields data from the edition database
        $dbFieldsInfo = $this->layer->getDbFieldsInfo();
        $dataFields = $dbFieldsInfo->dataFields;

        // Check fields
        if (!array_key_exists($exp1[1], $dataFields) || !array_key_exists($exp2[1], $dataFields)) {
            jMessage::add(jLocale::get('view~edition.link.error.no.given.fields'), 'error');

            return $this->serviceAnswer();
        }
        $key1 = $exp1[1];
        $key2 = $exp2[1];

        // Check if we need to insert a new row in a pivot table (n-m relation)
        // or if we need to update a foreign key in a child table (1-n relation)
        if (!$isPivot) {
            // Check if there is only one parent item selected
            if (count($ids1) > 1) {
                jMessage::add(jLocale::get('view~edition.link.error.multiple.ids'), 'error');

                return $this->serviceAnswer();
            }
            // Update the child features to add the parent id
            try {
                $foreign_key_column = $key2;
                $parent_id_value = $ids1[0];
                $child_pkey_column = $key1;
                $child_ids = $ids2;
                $layer->linkChildren($foreign_key_column, $parent_id_value, $child_pkey_column, $child_ids);
                jMessage::add(jLocale::get('view~edition.link.success'), 'success');
            } catch (Exception $e) {
                jLog::log('An error has been raised when create linked data:', 'error');
                jLog::logEx($e, 'error');
                jMessage::add(jLocale::get('view~edition.link.error.sql'), 'error');
            }
        } else {
            // 2 layers and 1 pivot table -> n:m relation
            // Insert lines in the pivot table referencing the 2 parent layers id
            try {
                $layer->insertRelations($key2, $ids2, $key1, $ids1);
                jMessage::add(jLocale::get('view~edition.link.success'), 'success');
            } catch (Exception $e) {
                jLog::log('An error has been raised when create linked data:', 'error');
                jLog::logEx($e, 'error');
                jMessage::add(jLocale::get('view~edition.link.error.sql'), 'error');
            }
        }

        return $this->serviceAnswer();
    }

    /**
     * Unlink child feature from their parent ( 1:n ) relation
     * by setting the foreign key to NULL.
     *
     * @urlparam string $repository Lizmap Repository
     * @urlparam string $project Name of the project
     * @urlparam string $layerId Child layer id.
     * @urlparam string $pkey Child layer primary key value -> id of the line to update
     * @urlparam string $fkey Child layer foreign key column (pointing to the parent layer primary key)
     *
     * @return jResponseHtmlFragment
     */
    public function unlinkChild()
    {
        $lid = $this->param('lid');
        $fkey = $this->param('fkey');
        $pkey = $this->param('pkey');
        $pkeyval = $this->param('pkeyval');
        $project = $this->param('project');
        $repository = $this->param('repository');

        if (!$lid || !$fkey || !$pkey || !$pkeyval || !$project || !$repository) {
            jMessage::add(jLocale::get('view~edition.link.error.missing.parameter'), 'error');

            return $this->serviceAnswer();
        }

        // Get repository data
        $lrep = lizmap::getRepository($repository);
        if (!$lrep) {
            jMessage::add('The repository '.strtoupper($repository).' does not exist !', 'RepositoryNotDefined');

            return $this->serviceAnswer();
        }
        // Get the project data
        $lproj = null;

        try {
            $lproj = lizmap::getProject($repository.'~'.$project);
            if (!$lproj) {
                jMessage::add('The lizmapProject '.strtoupper($project).' does not exist !', 'ProjectNotDefined');

                return $this->serviceAnswer();
            }
        } catch (UnknownLizmapProjectException $e) {
            jMessage::add('The lizmapProject '.strtoupper($project).' does not exist !', 'ProjectNotDefined');

            return $this->serviceAnswer();
        }
        $this->project = $lproj;
        $this->repository = $lrep;

        // Get child layer information
        $layer = $lproj->getLayer($lid);
        $layerName = $layer->getName();
        $this->layerId = $lid;
        $this->layerName = $layerName;
        $this->layer = $layer;

        // Get editLayer capabilities
        $eLayer = $layer->getEditionCapabilities();
        if ($eLayer->capabilities->modifyAttribute != 'True') {
            jMessage::add(jLocale::get('view~edition.link.error.no.modify.attributes', array($layerName)), 'LayerNotEditable');

            return $this->serviceAnswer();
        }

        // Get fields data from the edition database
        $dbFieldsInfo = $this->layer->getDbFieldsInfo();
        $dataFields = $dbFieldsInfo->dataFields;

        // Check fields
        if (!array_key_exists($fkey, $dataFields) || !array_key_exists($pkey, $dataFields)) {
            jMessage::add(jLocale::get('view~edition.link.error.no.given.fields'), 'error');

            return $this->serviceAnswer();
        }

        // Need to break SQL ( if sqlite
        try {
            $layer->unlinkChild($fkey, $pkey, $pkeyval);
            jMessage::add(jLocale::get('view~edition.unlink.success'), 'success');
        } catch (Exception $e) {
            jLog::log('An error has been raised when unlink child: ', 'error');
            jLog::logEx($e, 'error');
            jMessage::add(jLocale::get('view~edition.unlink.error.sql'), 'error');
        }

        return $this->serviceAnswer();
    }

    /**
     * web service for XHR request when group visibilities
     * depending of the value of form controls.
     */
    public function getGroupVisibilities()
    {
        if (!$this->request->isPostMethod()) {
            $rep = $this->getResponse('text', true);
            $rep->setHttpStatus('405', 'Method Not Allowed');

            return $rep;
        }

        $rep = $this->getResponse('json', true);

        try {
            $form = jForms::get($this->param('__form'), $this->param('__formid'));
            if (!$form) {
                throw new Exception('dummy');
            }
        } catch (Exception $e) {
            $rep = $this->getResponse('text', true);
            $rep->setHttpStatus('422', 'Unprocessable entity');
            $rep->content = 'invalid form selector';

            return $rep;
        }

        // check CSRF
        if ($form->securityLevel == jFormsBase::SECURITY_CSRF) {
            if ($form->getContainer()->token !== $this->param('__JFORMS_TOKEN__')) {
                $rep = $this->getResponse('text', true);
                $rep->setHttpStatus('422', 'Unprocessable entity');
                $rep->content = 'invalid token';
                jLog::logEx(new jException('jelix~formserr.invalid.token'), 'error');

                return $rep;
            }
        }

        // Get Private data
        $privateData = $form->getContainer()->privateData;

        // Build QGIS Form
        $repository = $privateData['liz_repository'];
        $project = $privateData['liz_project'];
        $layerId = $privateData['liz_layerId'];
        $featureId = $privateData['liz_featureId'];

        $lrep = lizmap::getRepository($repository);
        $lproj = lizmap::getProject($repository.'~'.$project);
        $layer = $lproj->getLayer($layerId);

        $qgisForm = new qgisForm($layer, $form, $featureId, jAcl2::check('lizmap.tools.loginFilteredLayers.override', $lrep->getKey()));

        // Update form
        $dependencies = $privateData['qgis_groupDependencies'];
        foreach ($dependencies as $ctname) {
            $form->setData($ctname, $this->param($ctname));
        }

        // evaluate group visibilities
        $attributeEditorForm = $qgisForm->getAttributesEditorForm();
        $rep->data = qgisExpressionUtils::evaluateGroupVisibilities($attributeEditorForm, $form);

        return $rep;
    }
}
